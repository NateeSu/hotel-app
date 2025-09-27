-- Fix bookings table structure
-- Run this SQL in phpMyAdmin

USE hotel_management;

-- Check current structure
DESCRIBE bookings;

-- Drop and recreate bookings table with correct structure
DROP TABLE IF EXISTS bookings;

CREATE TABLE `bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_code` VARCHAR(20) DEFAULT NULL,
    `room_id` INT UNSIGNED NOT NULL,
    `guest_name` VARCHAR(255) NOT NULL,
    `guest_phone` VARCHAR(20) DEFAULT NULL,
    `guest_id_number` VARCHAR(20) DEFAULT NULL,
    `guest_count` INT DEFAULT 1,
    `plan_type` ENUM('short', 'overnight') NOT NULL,
    `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    `checkin_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `checkout_at` TIMESTAMP NULL,
    `base_amount` DECIMAL(10,2) DEFAULT 0.00,
    `extra_amount` DECIMAL(10,2) DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) DEFAULT 0.00,
    `payment_method` ENUM('cash', 'card', 'transfer') DEFAULT 'cash',
    `payment_status` ENUM('pending', 'paid', 'partial') DEFAULT 'pending',
    `notes` TEXT,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
    INDEX `idx_room_status` (`room_id`, `status`),
    INDEX `idx_checkin_date` (`checkin_at`),
    INDEX `idx_status` (`status`),
    INDEX `idx_booking_code` (`booking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample bookings for occupied rooms
INSERT INTO `bookings` (
    `booking_code`, `room_id`, `guest_name`, `guest_phone`, `plan_type`,
    `status`, `base_amount`, `total_amount`, `payment_method`,
    `payment_status`, `notes`, `created_by`
) VALUES
('BK25092701', (SELECT id FROM rooms WHERE room_number = '102'), 'คุณสมชาย ใจดี', '0812345678', 'short', 'active', 300.00, 300.00, 'cash', 'paid', 'Check-in 14:00', 1),
('BK25092702', (SELECT id FROM rooms WHERE room_number = '202'), 'คุณสมหญิง รักดี', '0891234567', 'overnight', 'active', 800.00, 800.00, 'cash', 'paid', 'Check-in 20:00', 1),
('BK25092703', (SELECT id FROM rooms WHERE room_number = '206'), 'คุณสมปอง มีสุข', '0823456789', 'overnight', 'active', 800.00, 800.00, 'card', 'paid', 'VIP guest', 1),
('BK25092704', (SELECT id FROM rooms WHERE room_number = '212'), 'คุณสมศรี อยู่ดี', '0834567890', 'overnight', 'active', 800.00, 800.00, 'transfer', 'paid', 'Regular customer', 1);

-- Verify the fix
SELECT 'Table recreated successfully!' as Status;
DESCRIBE bookings;
SELECT COUNT(*) as 'Total Bookings' FROM bookings;
SELECT booking_code, guest_name, guest_phone, plan_type, base_amount FROM bookings;