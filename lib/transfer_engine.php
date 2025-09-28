<?php
/**
 * Hotel Management System - Room Transfer Engine
 *
 * Core logic for room transfer operations
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

class TransferEngine {
    private $pdo;

    public function __construct() {
        $this->pdo = getDatabase();
    }

    /**
     * Get available rooms for transfer
     */
    public function getAvailableRooms($excludeRoomId = null, $checkInDate = null, $checkOutDate = null, $planType = null) {
        $sql = "
            SELECT r.*,
                   350.00 as base_rate,
                   350.00 as weekend_rate,
                   350.00 as holiday_rate,
                   350.00 as current_rate
            FROM rooms r
            WHERE r.status = 'available'
        ";

        $params = [];

        if ($excludeRoomId) {
            $sql .= " AND r.id != ?";
            $params[] = $excludeRoomId;
        }

        // For transfer - all available rooms can be selected regardless of plan type
        // The plan type stays the same, only room changes

        // Check for existing bookings that would conflict
        if ($checkInDate && $checkOutDate) {
            $sql .= " AND r.id NOT IN (
                SELECT DISTINCT room_id
                FROM bookings
                WHERE status IN ('confirmed', 'active', 'checked_in')
                AND NOT (checkout_at <= ? OR checkin_at >= ?)
            )";
            $params = array_merge($params, [$checkInDate, $checkOutDate]);
        }

        $sql .= " ORDER BY r.floor, r.room_number";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get current booking for a room
     */
    public function getCurrentBooking($roomId) {
        // Try multiple booking statuses
        $stmt = $this->pdo->prepare("
            SELECT b.*, r.room_number, r.room_type
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.room_id = ?
            AND b.status IN ('active', 'checked_in', 'confirmed')
            AND b.checkin_at <= NOW()
            AND b.checkout_at > NOW()
            ORDER BY b.checkin_at DESC
            LIMIT 1
        ");

        $stmt->execute([$roomId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no booking found with time constraints, try without time constraints
        if (!$result) {
            $stmt = $this->pdo->prepare("
                SELECT b.*, r.room_number, r.room_type
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                WHERE b.room_id = ?
                AND b.status IN ('active', 'checked_in', 'confirmed')
                ORDER BY b.checkin_at DESC
                LIMIT 1
            ");

            $stmt->execute([$roomId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    /**
     * Calculate transfer cost
     */
    public function calculateTransferCost($fromRoomId, $toRoomId, $bookingId) {
        // Get booking details
        $booking = $this->getCurrentBooking($fromRoomId);
        if (!$booking) {
            throw new Exception("ไม่พบการจองที่ใช้งานอยู่");
        }

        // Get room data
        $fromRoom = $this->getRoomWithRate($fromRoomId);
        $toRoom = $this->getRoomWithRate($toRoomId);

        if (!$fromRoom || !$toRoom) {
            throw new Exception("ไม่พบข้อมูลห้องที่ระบุ");
        }

        // For room transfer - NO COST DIFFERENCE
        // All rooms are same rate (350 baht), only changing room number
        // Plan type and total cost remain the same

        $planText = '';
        if ($booking['plan_type'] === 'short') {
            $planText = 'ชั่วคราว (3 ชม. ฿200)';
        } else {
            $planText = 'ค้างคืน (฿350/คืน)';
        }

        return [
            'from_room' => $fromRoom,
            'to_room' => $toRoom,
            'booking' => $booking,
            'remaining_nights' => 0, // Not applicable for room transfer
            'from_rate' => 350.00,
            'to_rate' => 350.00,
            'rate_difference' => 0.00, // No difference - same room rate
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'service_charge' => 0.00,
            'total_adjustment' => 0.00, // No cost change
            'is_upgrade' => false,
            'is_downgrade' => false,
            'plan_text' => $planText,
            'message' => 'การย้ายห้องไม่มีค่าใช้จ่ายเพิ่มเติม (ห้องราคาเดียวกัน)'
        ];
    }

    /**
     * Process room transfer
     */
    public function processTransfer($data) {
        $this->pdo->beginTransaction();

        try {
            // Validate transfer
            $calculation = $this->calculateTransferCost($data['from_room_id'], $data['to_room_id'], $data['booking_id']);

            // Check if target room is still available
            $targetRoom = $this->getRoomById($data['to_room_id']);
            if ($targetRoom['status'] !== 'available') {
                throw new Exception("ห้องปลายทางไม่ว่างแล้ว");
            }

            // Create transfer record
            $transferId = $this->createTransferRecord($data, $calculation);

            // Create billing record if there's cost adjustment
            if ($calculation['total_adjustment'] != 0) {
                $this->createBillingRecord($transferId, $calculation, $data);
            }

            // Update booking
            $this->updateBookingRoom($data['booking_id'], $data['to_room_id']);

            // Update room statuses
            $this->updateRoomStatus($data['from_room_id'], 'cleaning');
            $this->updateRoomStatus($data['to_room_id'], 'occupied');

            // Update transfer count
            $this->updateTransferCount($data['booking_id']);

            // Send notifications
            if ($data['notify_guest'] ?? true) {
                $this->scheduleGuestNotification($transferId);
            }

            if ($data['notify_housekeeping'] ?? true) {
                $this->scheduleHousekeepingNotification($transferId);
            }

            // Create housekeeping job for old room
            $this->createHousekeepingJob($data['from_room_id'], $data['booking_id'], 'Room transfer cleanup');

            $this->pdo->commit();

            return [
                'success' => true,
                'transfer_id' => $transferId,
                'calculation' => $calculation,
                'message' => 'ย้ายห้องเรียบร้อยแล้ว'
            ];

        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * Create transfer record
     */
    private function createTransferRecord($data, $calculation) {
        $stmt = $this->pdo->prepare("
            INSERT INTO room_transfers
            (booking_id, from_room_id, to_room_id, transfer_reason,
             price_difference, total_adjustment, transferred_by,
             guest_notified, housekeeping_notified, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')
        ");

        $stmt->execute([
            $data['booking_id'],
            $data['from_room_id'],
            $data['to_room_id'],
            $data['transfer_reason'],
            $calculation['rate_difference'],
            $calculation['total_adjustment'],
            $data['transferred_by'],
            $data['notify_guest'] ? 1 : 0,
            $data['notify_housekeeping'] ? 1 : 0,
            $data['notes'] ?? null
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Create billing record
     */
    private function createBillingRecord($transferId, $calculation, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO transfer_billing
            (transfer_id, original_rate, new_rate, rate_difference,
             nights_affected, subtotal, tax_amount, service_charge,
             total_adjustment, payment_status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");

        $stmt->execute([
            $transferId,
            $calculation['from_rate'],
            $calculation['to_rate'],
            $calculation['rate_difference'],
            $calculation['remaining_nights'],
            $calculation['subtotal'],
            $calculation['tax_amount'],
            $calculation['service_charge'],
            $calculation['total_adjustment'],
            'Transfer billing for room change'
        ]);
    }

    /**
     * Update booking room
     */
    private function updateBookingRoom($bookingId, $newRoomId) {
        $stmt = $this->pdo->prepare("
            UPDATE bookings
            SET room_id = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newRoomId, $bookingId]);
    }

    /**
     * Update room status
     */
    private function updateRoomStatus($roomId, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE rooms
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $roomId]);

        if ($status === 'occupied') {
            $stmt = $this->pdo->prepare("
                UPDATE rooms
                SET last_transfer_date = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$roomId]);
        }
    }

    /**
     * Update transfer count
     */
    private function updateTransferCount($bookingId) {
        $stmt = $this->pdo->prepare("
            UPDATE bookings
            SET transfer_count = transfer_count + 1
            WHERE id = ?
        ");
        $stmt->execute([$bookingId]);
    }

    /**
     * Create housekeeping job
     */
    private function createHousekeepingJob($roomId, $bookingId, $description) {
        // Check if housekeeping_jobs table exists
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO housekeeping_jobs
                (room_id, booking_id, task_type, job_type, priority, description,
                 status, created_by, telegram_sent)
                VALUES (?, ?, 'checkout_cleaning', 'cleaning', 'normal', ?,
                        'pending', 1, 0)
            ");
            $stmt->execute([$roomId, $bookingId, $description]);
        } catch (Exception $e) {
            // Table might not exist, log error but don't fail
            error_log("Could not create housekeeping job: " . $e->getMessage());
        }
    }

    /**
     * Schedule guest notification
     */
    private function scheduleGuestNotification($transferId) {
        // Implementation depends on notification system
        // For now, just log it
        error_log("Guest notification scheduled for transfer ID: $transferId");
    }

    /**
     * Schedule housekeeping notification
     */
    private function scheduleHousekeepingNotification($transferId) {
        // Send Telegram notification
        try {
            require_once __DIR__ . '/telegram_service.php';
            $telegramService = new TelegramService();

            $transfer = $this->getTransferDetails($transferId);
            $this->sendHousekeepingTransferNotification($telegramService, $transfer);

        } catch (Exception $e) {
            error_log("Housekeeping notification failed: " . $e->getMessage());
        }
    }

    /**
     * Get room with rate
     */
    private function getRoomWithRate($roomId) {
        $stmt = $this->pdo->prepare("
            SELECT r.*,
                   350.00 as base_rate,
                   350.00 as weekend_rate,
                   350.00 as holiday_rate,
                   350.00 as current_rate
            FROM rooms r
            WHERE r.id = ?
        ");

        $stmt->execute([$roomId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get room by ID
     */
    private function getRoomById($roomId) {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get setting value
     */
    private function getSetting($key, $default = null) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_value FROM hotel_settings WHERE setting_key = ?
            ");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (float)$result['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * Get transfer details
     */
    public function getTransferDetails($transferId) {
        $stmt = $this->pdo->prepare("
            SELECT rt.*,
                   b.guest_name, b.guest_phone,
                   r_from.room_number as from_room_number,
                   r_from.room_type as from_room_type,
                   r_to.room_number as to_room_number,
                   r_to.room_type as to_room_type,
                   u.full_name as transferred_by_name
            FROM room_transfers rt
            JOIN bookings b ON rt.booking_id = b.id
            JOIN rooms r_from ON rt.from_room_id = r_from.id
            JOIN rooms r_to ON rt.to_room_id = r_to.id
            JOIN users u ON rt.transferred_by = u.id
            WHERE rt.id = ?
        ");

        $stmt->execute([$transferId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Send housekeeping transfer notification
     */
    private function sendHousekeepingTransferNotification($telegramService, $transfer) {
        $message = "🔄 <b>การย้ายห้องแขก</b>\n\n";
        $message .= "👤 แขก: {$transfer['guest_name']}\n";
        $message .= "🏠 จาก: ห้อง {$transfer['from_room_number']} ({$transfer['from_room_type']})\n";
        $message .= "➡️ ไป: ห้อง {$transfer['to_room_number']} ({$transfer['to_room_type']})\n";
        $message .= "📅 วันที่: " . date('d/m/Y H:i', strtotime($transfer['transfer_date'])) . "\n";
        $message .= "📋 เหตุผล: " . $this->getTransferReasonText($transfer['transfer_reason']) . "\n\n";

        if ($transfer['total_adjustment'] > 0) {
            $message .= "💰 ค่าต่าง: +฿" . number_format($transfer['total_adjustment'], 2) . "\n";
        } elseif ($transfer['total_adjustment'] < 0) {
            $message .= "💰 ค่าต่าง: ฿" . number_format($transfer['total_adjustment'], 2) . "\n";
        }

        $message .= "\n🧹 งานแม่บ้าน:\n";
        $message .= "• ทำความสะอาดห้อง {$transfer['from_room_number']}\n";
        $message .= "• เตรียมห้อง {$transfer['to_room_number']}\n\n";
        $message .= "👤 ดำเนินการโดย: {$transfer['transferred_by_name']}";

        // Get housekeeping chat IDs
        $chatIds = $this->getHousekeepingChatIds();
        foreach ($chatIds as $chatId) {
            $telegramService->sendMessage($chatId, $message);
        }
    }

    /**
     * Get transfer reason text
     */
    private function getTransferReasonText($reason) {
        $reasons = [
            'upgrade' => 'อัพเกรดห้อง',
            'downgrade' => 'ดาวน์เกรดห้อง',
            'maintenance' => 'ซ่อมบำรุงห้อง',
            'guest_request' => 'ตามความต้องการแขก',
            'overbooking' => 'จองเกิน',
            'room_issue' => 'ปัญหาห้อง',
            'other' => 'อื่นๆ'
        ];

        return $reasons[$reason] ?? $reason;
    }

    /**
     * Get housekeeping chat IDs
     */
    private function getHousekeepingChatIds() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT telegram_chat_id
                FROM users
                WHERE role IN ('housekeeping', 'admin')
                AND telegram_chat_id IS NOT NULL
                AND is_active = 1
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // If no housekeeping staff configured, get from settings
            if (empty($results)) {
                $defaultChatId = $this->getSetting('default_housekeeping_chat_id');
                if ($defaultChatId) {
                    return [$defaultChatId];
                }
            }

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get transfer history
     */
    public function getTransferHistory($limit = 50, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM transfer_summary
            ORDER BY transfer_date DESC
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>