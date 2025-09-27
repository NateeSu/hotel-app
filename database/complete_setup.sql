-- Hotel Management System - Complete Database Setup
-- Compatible with existing code structure
-- Run this in phpMyAdmin to setup everything

-- Create database
CREATE DATABASE IF NOT EXISTS `hotel_management`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `hotel_management`;

-- Drop existing tables to ensure clean setup
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `housekeeping_jobs`;
DROP TABLE IF EXISTS `receipts`;
DROP TABLE IF EXISTS `room_transfers`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `rates`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `users`;

-- 1. Users table (for authentication)
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'reception', 'housekeeping') NOT NULL DEFAULT 'reception',
    `email` VARCHAR(100) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    UNIQUE KEY `uk_users_email` (`email`),
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Rooms table
CREATE TABLE `rooms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_number` VARCHAR(10) NOT NULL,
    `room_type` ENUM('short', 'overnight') NOT NULL DEFAULT 'short',
    `status` ENUM('available', 'occupied', 'cleaning', 'maintenance') NOT NULL DEFAULT 'available',
    `floor` INT DEFAULT 1,
    `max_occupancy` INT DEFAULT 2,
    `amenities` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_rooms_number` (`room_number`),
    INDEX `idx_rooms_status` (`status`),
    INDEX `idx_rooms_type` (`room_type`),
    INDEX `idx_rooms_floor` (`floor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bookings table (compatible with existing check-in code)
CREATE TABLE `bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT UNSIGNED NOT NULL,
    `guest_name` VARCHAR(255) NOT NULL,
    `plan_type` ENUM('short', 'overnight') NOT NULL,
    `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    `checkin_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `checkout_at` TIMESTAMP NULL,
    `notes` TEXT,
    `created_by` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
    INDEX `idx_room_status` (`room_id`, `status`),
    INDEX `idx_checkin_date` (`checkin_at`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Rates table
CREATE TABLE `rates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rate_type` VARCHAR(50) NOT NULL,
    `description` VARCHAR(100) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `duration_hours` INT DEFAULT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_rates_type` (`rate_type`),
    INDEX `idx_rates_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Housekeeping jobs table
CREATE TABLE `housekeeping_jobs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT UNSIGNED NOT NULL,
    `job_type` ENUM('cleaning', 'maintenance', 'inspection') NOT NULL DEFAULT 'cleaning',
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    `description` TEXT DEFAULT NULL,
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `started_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `estimated_duration` INT DEFAULT NULL COMMENT 'Estimated duration in minutes',
    `actual_duration` INT DEFAULT NULL COMMENT 'Actual duration in minutes',
    `notes` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_housekeeping_status_room` (`status`, `room_id`),
    INDEX `idx_housekeeping_assigned` (`assigned_to`),
    INDEX `idx_housekeeping_priority` (`priority`),
    INDEX `idx_housekeeping_type` (`job_type`),
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Activity logs table (for audit trail)
CREATE TABLE `activity_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `table_name` VARCHAR(50) DEFAULT NULL,
    `record_id` INT UNSIGNED DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_logs_user` (`user_id`),
    INDEX `idx_logs_action` (`action`),
    INDEX `idx_logs_table_record` (`table_name`, `record_id`),
    INDEX `idx_logs_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test users
INSERT INTO `users` (`username`, `password_hash`, `full_name`, `role`, `email`, `phone`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin', 'admin@hotel.com', '02-123-4567', 1),
('reception', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'พนักงานต้อนรับ', 'reception', 'reception@hotel.com', '02-123-4568', 1),
('housekeeping', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'พนักงานแม่บ้าน', 'housekeeping', 'housekeeping@hotel.com', '02-123-4569', 1);

-- Insert test rooms (20 rooms as per project specification)
INSERT INTO `rooms` (`room_number`, `room_type`, `status`, `floor`, `max_occupancy`, `amenities`, `notes`) VALUES
-- Floor 1 (Short-term rooms)
('101', 'short', 'available', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room'),
('102', 'short', 'occupied', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room'),
('103', 'short', 'cleaning', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room'),
('104', 'short', 'available', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room'),
('105', 'short', 'maintenance', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room'),

-- Floor 2 (Overnight rooms)
('201', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('202', 'overnight', 'occupied', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('203', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('204', 'overnight', 'cleaning', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('205', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('206', 'overnight', 'occupied', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('207', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('208', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('209', 'overnight', 'cleaning', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('210', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('211', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('212', 'overnight', 'occupied', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('213', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('214', 'overnight', 'maintenance', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('215', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room'),
('216', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room');

-- Insert sample rates
INSERT INTO `rates` (`rate_type`, `description`, `price`, `duration_hours`, `is_active`) VALUES
('short_3h', 'Short-term stay (3 hours)', 300.00, 3, 1),
('overnight', 'Overnight stay', 800.00, 12, 1),
('extended', 'Extended hourly rate', 100.00, 1, 1);

-- Insert sample bookings for occupied rooms
INSERT INTO `bookings` (`room_id`, `guest_name`, `plan_type`, `status`, `notes`, `created_by`) VALUES
((SELECT id FROM rooms WHERE room_number = '102'), 'คุณสมชาย ใจดี', 'short', 'active', 'Check-in 14:00', 'reception'),
((SELECT id FROM rooms WHERE room_number = '202'), 'คุณสมหญิง รักดี', 'overnight', 'active', 'Check-in 20:00', 'reception'),
((SELECT id FROM rooms WHERE room_number = '206'), 'คุณสมปอง มีสุข', 'overnight', 'active', 'VIP guest', 'admin'),
((SELECT id FROM rooms WHERE room_number = '212'), 'คุณสมศรี อยู่ดี', 'overnight', 'active', 'Regular customer', 'reception');

-- Verification queries
SELECT 'Database setup completed successfully!' as Status;
SELECT COUNT(*) as 'Total Users' FROM users;
SELECT COUNT(*) as 'Total Rooms' FROM rooms;
SELECT COUNT(*) as 'Total Bookings' FROM bookings;
SELECT COUNT(*) as 'Total Rates' FROM rates;

-- Show room distribution by status
SELECT status, COUNT(*) as count FROM rooms GROUP BY status;

-- Show users for login testing
SELECT username, role, 'Password: password123' as login_info FROM users WHERE is_active = 1;