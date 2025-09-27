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
        $this->hotelInfo = [
            'name' => 'HOTEL MANAGEMENT SYSTEM',
            'address' => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110',
            'phone' => '02-123-4567',
            'email' => 'info@hotel.com',
            'tax_id' => '0-1234-56789-01-2'
        ];
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
     * Calculate billing details for the booking
     */
    private function calculateBilling($booking, $extraAmount = 0) {
        // Get rate information
        $rates = [
            'short' => ['duration_hours' => 3, 'price' => 300],
            'overnight' => ['duration_hours' => 12, 'price' => 800]
        ];

        $planType = $booking['plan_type'];
        $baseHours = $rates[$planType]['duration_hours'];
        $baseAmount = $rates[$planType]['price'];

        // Calculate actual duration
        $checkinTime = strtotime($booking['checkin_at']);
        $checkoutTime = $booking['checkout_at'] ? strtotime($booking['checkout_at']) : time();
        $actualHours = ($checkoutTime - $checkinTime) / 3600;

        // Calculate overtime
        $overtimeHours = max(0, ceil($actualHours) - $baseHours);
        $overtimeRate = 100; // ฿100 per hour
        $overtimeAmount = $overtimeHours * $overtimeRate;

        // Total calculation
        $totalAmount = $baseAmount + $overtimeAmount;

        // Duration text
        $durationText = sprintf('%.1f ชั่วโมง', $actualHours);
        if ($actualHours >= 24) {
            $days = floor($actualHours / 24);
            $hours = $actualHours % 24;
            $durationText = sprintf('%d วัน %.1f ชั่วโมง', $days, $hours);
        }

        return [
            'base_hours' => $baseHours,
            'base_amount' => $baseAmount,
            'actual_hours' => $actualHours,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'total_amount' => $totalAmount,
            'duration_text' => $durationText
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
            // Create receipts table if not exists
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS receipts (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    receipt_number VARCHAR(20) NOT NULL UNIQUE,
                    booking_id INT UNSIGNED NOT NULL,
                    booking_code VARCHAR(20) NOT NULL,
                    guest_name VARCHAR(255) NOT NULL,
                    room_number VARCHAR(10) NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL,
                    payment_method ENUM('cash', 'card', 'transfer') NOT NULL,
                    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    generated_by INT UNSIGNED,
                    receipt_data JSON,
                    PRIMARY KEY (id),
                    INDEX idx_receipt_number (receipt_number),
                    INDEX idx_booking_code (booking_code),
                    INDEX idx_generated_at (generated_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

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