<?php
/**
 * Hotel Management System - Receipt Generator
 *
 * Generates receipts for hotel bookings with professional formatting
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

require_once __DIR__ . '/tcpdf_setup.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

class ReceiptGenerator {
    private $pdo;
    private $hotelInfo;

    public function __construct() {
        $this->pdo = getDatabase();
        $this->hotelInfo = $this->loadHotelSettings();
    }

    /**
     * Load hotel settings from database
     */
    private function loadHotelSettings() {
        try {
            $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM hotel_settings");
            $settingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $settings = [];
            foreach ($settingsData as $setting) {
                $settings[$setting['setting_key']] = $setting['setting_value'];
            }

            // Return hotel info with defaults
            return [
                'name' => strtoupper($settings['hotel_name'] ?? 'HOTEL MANAGEMENT SYSTEM'),
                'address' => $settings['hotel_address'] ?? '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110',
                'phone' => $settings['hotel_phone'] ?? '02-123-4567',
                'email' => $settings['hotel_email'] ?? 'info@hotel.com',
                'tax_id' => $settings['hotel_tax_id'] ?? '0-1234-56789-01-2'
            ];

        } catch (Exception $e) {
            // Return defaults if error
            return [
                'name' => 'HOTEL MANAGEMENT SYSTEM',
                'address' => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110',
                'phone' => '02-123-4567',
                'email' => 'info@hotel.com',
                'tax_id' => '0-1234-56789-01-2'
            ];
        }
    }

    /**
     * Generate receipt for a completed booking
     */
    public function generateReceipt($bookingId, $extraAmount = 0, $extraNotes = '') {
        try {
            // Get booking details with room and user info
            $stmt = $this->pdo->prepare("
                SELECT
                    b.*,
                    r.room_number, r.room_type,
                    u.full_name as processed_by
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                LEFT JOIN users u ON b.created_by = u.id
                WHERE b.id = ?
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('ไม่พบข้อมูลการจอง');
            }

            // Calculate billing details
            $billingData = $this->calculateBilling($booking, $extraAmount);

            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber($booking['id']);

            // Create receipt data
            $receiptData = [
                'receipt_number' => $receiptNumber,
                'date' => date('d/m/Y'),
                'time' => date('H:i:s'),
                'booking_code' => $booking['booking_code'],
                'room_number' => $booking['room_number'],
                'room_type' => $booking['room_type'],
                'guest_name' => $booking['guest_name'],
                'guest_phone' => $booking['guest_phone'] ?? '-',
                'guest_id_number' => $booking['guest_id_number'] ?? '-',
                'guest_count' => $booking['guest_count'] ?? 1,
                'checkin_time' => date('d/m/Y H:i', strtotime($booking['checkin_at'])),
                'checkout_time' => $booking['checkout_at'] ? date('d/m/Y H:i', strtotime($booking['checkout_at'])) : date('d/m/Y H:i'),
                'duration' => $billingData['duration_text'],
                'plan_type' => $booking['plan_type'],
                'plan_type_text' => $booking['plan_type'] === 'short' ? 'รายชั่วโมง' : 'รายคืน',
                'base_duration' => $billingData['base_hours'],
                'base_amount' => $billingData['base_amount'],
                'overtime_hours' => $billingData['overtime_hours'],
                'overtime_amount' => $billingData['overtime_amount'],
                'extra_amount' => $extraAmount,
                'extra_notes' => $extraNotes,
                'total_amount' => $billingData['total_amount'] + $extraAmount,
                'payment_method' => $booking['payment_method'],
                'payment_method_text' => $this->getPaymentMethodText($booking['payment_method']),
                'processed_by' => $booking['processed_by'] ?? 'ระบบ',
                'hotel_name' => $this->hotelInfo['name'],
                'hotel_address' => $this->hotelInfo['address'],
                'hotel_phone' => $this->hotelInfo['phone'],
                'hotel_email' => $this->hotelInfo['email'],
                'tax_id' => $this->hotelInfo['tax_id']
            ];

            // Save receipt record
            $this->saveReceiptRecord($receiptData);

            return $receiptData;

        } catch (Exception $e) {
            error_log("Receipt generation error: " . $e->getMessage());
            throw new Exception('เกิดข้อผิดพลาดในการสร้างใบเสร็จ: ' . $e->getMessage());
        }
    }

    /**
     * Calculate billing details for the booking using new business rules
     */
    private function calculateBilling($booking, $extraAmount = 0) {
        $checkoutTime = $booking['checkout_at'] ?? date('Y-m-d H:i:s');
        $billingData = calculate_billing($booking['plan_type'], $booking['checkin_at'], $checkoutTime);

        // Calculate actual duration for display
        $checkinTime = strtotime($booking['checkin_at']);
        $checkoutTimestamp = strtotime($checkoutTime);
        $actualHours = ($checkoutTimestamp - $checkinTime) / 3600;

        // Create duration text based on plan type
        if ($booking['plan_type'] === 'short') {
            $durationText = sprintf('%.1f ชั่วโมง', $actualHours);
        } else {
            // Overnight: show nights and hours
            $nights = $billingData['nights'];
            $durationText = sprintf('%d คืน', $nights);
            if ($billingData['is_overdue']) {
                $overdueHours = $billingData['overdue_hours'];
                if ($overdueHours >= 1) {
                    $durationText .= sprintf(' (เกิน %.1f ชม.)', $overdueHours);
                } else {
                    $minutes = floor($overdueHours * 60);
                    $durationText .= sprintf(' (เกิน %d นาที)', $minutes);
                }
            }
        }

        return [
            'base_hours' => $booking['plan_type'] === 'short' ? 3 : 12, // For display only
            'base_amount' => $billingData['base_amount'],
            'actual_hours' => $actualHours,
            'nights' => $billingData['nights'] ?? 0,
            'overtime_hours' => $billingData['overdue_hours'] ?? 0,
            'overtime_amount' => $billingData['extra_amount'],
            'total_amount' => $billingData['total_amount'],
            'duration_text' => $durationText,
            'is_overdue' => $billingData['is_overdue'] ?? false
        ];
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber($bookingId) {
        $prefix = 'RC';
        $date = date('ymd');
        $sequence = str_pad($bookingId, 4, '0', STR_PAD_LEFT);
        return $prefix . $date . $sequence;
    }

    /**
     * Get payment method text in Thai
     */
    private function getPaymentMethodText($method) {
        $methods = [
            'cash' => 'เงินสด',
            'card' => 'บัตรเครดิต/เดบิต',
            'transfer' => 'โอนเงิน'
        ];
        return $methods[$method] ?? 'ไม่ระบุ';
    }

    /**
     * Save receipt record to database
     */
    private function saveReceiptRecord($receiptData) {
        try {
            // Get booking ID from booking code
            $stmt = $this->pdo->prepare("SELECT id FROM bookings WHERE booking_code = ?");
            $stmt->execute([$receiptData['booking_code']]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('ไม่พบข้อมูลการจอง');
            }

            // Insert receipt record
            $stmt = $this->pdo->prepare("
                INSERT INTO receipts (
                    receipt_number, booking_id, booking_code, guest_name,
                    room_number, total_amount, payment_method, generated_by, receipt_data
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $receiptData['receipt_number'],
                $booking['id'],
                $receiptData['booking_code'],
                $receiptData['guest_name'],
                $receiptData['room_number'],
                $receiptData['total_amount'],
                $receiptData['payment_method'],
                $_SESSION['user_id'] ?? 1,
                json_encode($receiptData, JSON_UNESCAPED_UNICODE)
            ]);

        } catch (Exception $e) {
            error_log("Receipt save error: " . $e->getMessage());
            // Non-fatal error - receipt can still be generated
        }
    }

    /**
     * Generate HTML receipt
     */
    public function generateHTMLReceipt($receiptData) {
        return generateHTMLReceipt($receiptData);
    }

    /**
     * Get all receipts for management
     */
    public function getReceiptHistory($limit = 50, $offset = 0) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    r.*,
                    b.guest_phone,
                    rm.room_type
                FROM receipts r
                LEFT JOIN bookings b ON r.booking_id = b.id
                LEFT JOIN rooms rm ON b.room_id = rm.id
                ORDER BY r.generated_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Receipt history error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find receipt by number
     */
    public function findReceiptByNumber($receiptNumber) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM receipts
                WHERE receipt_number = ?
            ");
            $stmt->execute([$receiptNumber]);
            $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($receipt && $receipt['receipt_data']) {
                return json_decode($receipt['receipt_data'], true);
            }

            return null;

        } catch (Exception $e) {
            error_log("Receipt lookup error: " . $e->getMessage());
            return null;
        }
    }
}
?>