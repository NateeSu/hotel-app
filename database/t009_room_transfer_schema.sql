-- T009: Room Transfer System Database Schema
-- ระบบย้ายห้องแขกที่เข้าพักแล้ว

USE hotel_management;

-- Table: room_transfers
-- บันทึกประวัติการย้ายห้อง
CREATE TABLE IF NOT EXISTS room_transfers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    from_room_id INT UNSIGNED NOT NULL,
    to_room_id INT UNSIGNED NOT NULL,
    transfer_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    transfer_reason ENUM('upgrade', 'downgrade', 'maintenance', 'guest_request', 'overbooking', 'room_issue', 'other') NOT NULL,
    price_difference DECIMAL(10,2) DEFAULT 0,
    additional_charges DECIMAL(10,2) DEFAULT 0,
    total_adjustment DECIMAL(10,2) DEFAULT 0,
    transferred_by INT UNSIGNED NOT NULL,
    guest_notified TINYINT(1) DEFAULT 0,
    housekeeping_notified TINYINT(1) DEFAULT 0,
    notes TEXT,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (from_room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (to_room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (transferred_by) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_booking_transfer (booking_id),
    INDEX idx_transfer_date (transfer_date),
    INDEX idx_status (status),
    INDEX idx_rooms (from_room_id, to_room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: transfer_billing
-- การคำนวณค่าใช้จ่ายการย้ายห้อง
CREATE TABLE IF NOT EXISTS transfer_billing (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT UNSIGNED NOT NULL,
    original_rate DECIMAL(10,2) NOT NULL,
    new_rate DECIMAL(10,2) NOT NULL,
    rate_difference DECIMAL(10,2) NOT NULL,
    nights_affected INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    service_charge DECIMAL(10,2) DEFAULT 0,
    total_adjustment DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'waived', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) NULL,
    payment_reference VARCHAR(100) NULL,
    payment_date DATETIME NULL,
    processed_by INT UNSIGNED NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (transfer_id) REFERENCES room_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_transfer_billing (transfer_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: transfer_notifications
-- การแจ้งเตือนเกี่ยวกับการย้ายห้อง
CREATE TABLE IF NOT EXISTS transfer_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT UNSIGNED NOT NULL,
    notification_type ENUM('guest_sms', 'guest_email', 'telegram_housekeeping', 'telegram_reception', 'system_alert') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    sent_at DATETIME NULL,
    delivery_status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    response_data TEXT NULL,
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (transfer_id) REFERENCES room_transfers(id) ON DELETE CASCADE,

    INDEX idx_transfer_notifications (transfer_id),
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_notification_type (notification_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to existing tables if needed
-- เพิ่มคอลัมน์ในตารางที่มีอยู่หากจำเป็น

-- Add transfer_count to bookings table for tracking
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS transfer_count INT DEFAULT 0 AFTER status;

-- Add last_transfer_date to rooms table for tracking
ALTER TABLE rooms
ADD COLUMN IF NOT EXISTS last_transfer_date DATETIME NULL AFTER updated_at;

-- Create views for reporting
-- สร้าง views สำหรับรายงาน

-- View: transfer_summary
-- สรุปข้อมูลการย้ายห้อง
CREATE OR REPLACE VIEW transfer_summary AS
SELECT
    rt.id,
    rt.transfer_date,
    b.guest_name,
    b.guest_phone,
    r_from.room_number as from_room,
    r_from.room_type as from_room_type,
    r_to.room_number as to_room,
    r_to.room_type as to_room_type,
    rt.transfer_reason,
    rt.total_adjustment,
    rt.status,
    u.full_name as transferred_by_name,
    tb.payment_status
FROM room_transfers rt
JOIN bookings b ON rt.booking_id = b.id
JOIN rooms r_from ON rt.from_room_id = r_from.id
JOIN rooms r_to ON rt.to_room_id = r_to.id
JOIN users u ON rt.transferred_by = u.id
LEFT JOIN transfer_billing tb ON rt.id = tb.transfer_id;

-- View: daily_transfer_stats
-- สถิติการย้ายห้องรายวัน
CREATE OR REPLACE VIEW daily_transfer_stats AS
SELECT
    DATE(transfer_date) as transfer_date,
    COUNT(*) as total_transfers,
    SUM(CASE WHEN transfer_reason = 'upgrade' THEN 1 ELSE 0 END) as upgrades,
    SUM(CASE WHEN transfer_reason = 'downgrade' THEN 1 ELSE 0 END) as downgrades,
    SUM(CASE WHEN transfer_reason = 'maintenance' THEN 1 ELSE 0 END) as maintenance_moves,
    SUM(CASE WHEN transfer_reason = 'guest_request' THEN 1 ELSE 0 END) as guest_requests,
    SUM(total_adjustment) as total_revenue_impact,
    AVG(total_adjustment) as avg_adjustment
FROM room_transfers
WHERE status = 'completed'
GROUP BY DATE(transfer_date);

-- Sample data for testing
-- ข้อมูลตัวอย่างสำหรับทดสอบ

-- Insert sample transfer reasons reference
INSERT IGNORE INTO hotel_settings (setting_key, setting_value, setting_type) VALUES
('transfer_reasons', '{"upgrade":"อัพเกรดห้อง","downgrade":"ดาวน์เกรดห้อง","maintenance":"ซ่อมบำรุงห้อง","guest_request":"ตามความต้องการแขก","overbooking":"จองเกิน","room_issue":"ปัญหาห้อง","other":"อื่นๆ"}', 'json'),
('transfer_tax_rate', '0.07', 'number'),
('transfer_service_charge_rate', '0.10', 'number'),
('auto_notify_guest', '1', 'boolean'),
('auto_notify_housekeeping', '1', 'boolean');

-- Show final structure
DESCRIBE room_transfers;
DESCRIBE transfer_billing;
DESCRIBE transfer_notifications;