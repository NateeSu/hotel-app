-- T008 Housekeeping Notification System - Demo Setup
-- Run this SQL to setup T008 and demo data

USE hotel_management;

-- Create telegram_notifications table
CREATE TABLE IF NOT EXISTS `telegram_notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `housekeeping_job_id` INT UNSIGNED NULL,
    `chat_id` VARCHAR(255) NOT NULL,
    `message_text` TEXT,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('sent', 'failed', 'delivered', 'read') DEFAULT 'sent',
    `response_data` JSON,
    PRIMARY KEY (`id`),
    INDEX `idx_housekeeping_job` (`housekeeping_job_id`),
    INDEX `idx_chat_id` (`chat_id`),
    INDEX `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create hotel_settings table if not exists
CREATE TABLE IF NOT EXISTS `hotel_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(255) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    `updated_by` INT UNSIGNED,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add new columns to housekeeping_jobs table (safely)
ALTER TABLE `housekeeping_jobs`
ADD COLUMN IF NOT EXISTS `booking_id` INT UNSIGNED NULL AFTER `room_id`,
ADD COLUMN IF NOT EXISTS `task_type` ENUM('checkout_cleaning', 'maintenance', 'inspection') DEFAULT 'checkout_cleaning' AFTER `job_type`,
ADD COLUMN IF NOT EXISTS `special_notes` TEXT AFTER `notes`,
ADD COLUMN IF NOT EXISTS `telegram_sent` BOOLEAN DEFAULT FALSE AFTER `special_notes`;

-- Add telegram_chat_id to users table (safely)
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `telegram_chat_id` VARCHAR(255) NULL AFTER `updated_at`;

-- Ensure users.is_active exists
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `is_active` BOOLEAN DEFAULT TRUE AFTER `telegram_chat_id`;

-- Insert default settings
INSERT INTO `hotel_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('telegram_bot_token', 'YOUR_BOT_TOKEN_HERE', 'text'),
('default_housekeeping_chat_id', 'YOUR_CHAT_ID_HERE', 'text'),
('notification_enabled', 'true', 'boolean'),
('housekeeping_notification_template', '🧹 งานทำความสะอาดใหม่!', 'text')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Create or update housekeeping performance view
CREATE OR REPLACE VIEW `housekeeping_performance` AS
SELECT
    hj.id,
    hj.room_id,
    r.room_number,
    COALESCE(hj.task_type, hj.job_type) as task_type,
    hj.priority,
    hj.created_at,
    hj.started_at,
    hj.completed_at,
    CASE
        WHEN hj.completed_at IS NOT NULL AND hj.started_at IS NOT NULL
        THEN TIMESTAMPDIFF(MINUTE, hj.started_at, hj.completed_at)
        ELSE hj.actual_duration
    END as duration_minutes,
    CASE
        WHEN hj.completed_at IS NOT NULL THEN 'completed'
        WHEN hj.started_at IS NOT NULL THEN 'in_progress'
        ELSE 'pending'
    END as current_status,
    hj.assigned_to,
    u.full_name as assigned_to_name,
    COALESCE(hj.telegram_sent, FALSE) as telegram_sent
FROM housekeeping_jobs hj
JOIN rooms r ON hj.room_id = r.id
LEFT JOIN users u ON hj.assigned_to = u.id
ORDER BY hj.created_at DESC;

-- Insert demo users for housekeeping
INSERT INTO `users` (`username`, `password_hash`, `full_name`, `role`, `telegram_chat_id`, `is_active`) VALUES
('housekeeper1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'นาง สะอาด ใจดี', 'housekeeping', '123456789', TRUE),
('housekeeper2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'นาย ถู ข้น', 'housekeeping', '987654321', TRUE),
('reception1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'นางสาว ต้อนรับ ยิ้มแย้ม', 'reception', NULL, TRUE)
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    telegram_chat_id = VALUES(telegram_chat_id),
    is_active = VALUES(is_active);

-- Insert demo rooms if not exists
INSERT IGNORE INTO `rooms` (`room_number`, `room_type`, `status`, `floor`, `max_occupancy`) VALUES
('101', 'short', 'available', 1, 2),
('102', 'short', 'available', 1, 2),
('103', 'overnight', 'available', 1, 2),
('201', 'short', 'available', 2, 2),
('202', 'overnight', 'available', 2, 2),
('203', 'overnight', 'available', 2, 4);

-- Insert demo rates if not exists
INSERT IGNORE INTO `rates` (`rate_type`, `description`, `price`, `duration_hours`, `is_active`) VALUES
('short_3h', 'ห้องชั่วคราว 3 ชั่วโมง', 300.00, 3, 1),
('overnight', 'ห้องค้างคืน', 800.00, 12, 1),
('extended', 'ค่าเกินเวลาต่อชั่วโมง', 100.00, 1, 1);

-- Create demo bookings and housekeeping jobs for testing
-- Demo scenario: Guests who checked out and need cleaning
INSERT INTO `bookings` (`booking_code`, `room_id`, `guest_name`, `guest_phone`, `plan_type`, `status`,
                       `checkin_at`, `checkout_at`, `base_amount`, `total_amount`, `payment_status`, `created_by`)
VALUES
('BK001', 1, 'คุณสมชาย ใจดี', '081-234-5678', 'short', 'completed',
 NOW() - INTERVAL 3 HOUR, NOW() - INTERVAL 30 MINUTE, 300.00, 300.00, 'paid', 1),
('BK002', 3, 'คุณสมหญิง สวยงาม', '089-876-5432', 'overnight', 'completed',
 NOW() - INTERVAL 10 HOUR, NOW() - INTERVAL 1 HOUR, 800.00, 900.00, 'paid', 1),
('BK003', 2, 'Mr. John Smith', '095-555-1234', 'short', 'completed',
 NOW() - INTERVAL 2 HOUR, NOW() - INTERVAL 15 MINUTE, 300.00, 400.00, 'paid', 1);

-- Create corresponding housekeeping jobs
INSERT INTO `housekeeping_jobs` (`room_id`, `booking_id`, `job_type`, `task_type`, `status`, `priority`,
                                `description`, `special_notes`, `telegram_sent`, `created_by`)
VALUES
(1, LAST_INSERT_ID()-2, 'cleaning', 'checkout_cleaning', 'pending', 'normal',
 'ทำความสะอาดหลัง check-out ห้อง 101', 'แขกทิ้งผ้าเปียกในห้องน้ำ', FALSE, 1),
(3, LAST_INSERT_ID()-1, 'cleaning', 'checkout_cleaning', 'in_progress', 'high',
 'ทำความสะอาดหลัง check-out ห้อง 103', 'ห้องค้างคืน ต้องเปลี่ยนผ้าปูที่นอน', TRUE, 1),
(2, LAST_INSERT_ID(), 'cleaning', 'checkout_cleaning', 'completed', 'normal',
 'ทำความสะอาดหลัง check-out ห้อง 102', 'ทำความสะอาดเรียบร้อยแล้ว', TRUE, 1);

-- Update room statuses to match housekeeping job status
UPDATE `rooms` SET `status` = 'cleaning' WHERE `id` IN (1, 3);
UPDATE `rooms` SET `status` = 'available' WHERE `id` = 2;

-- Update housekeeping job with realistic times
UPDATE `housekeeping_jobs` SET
    `started_at` = NOW() - INTERVAL 45 MINUTE,
    `assigned_to` = 1
WHERE `task_type` = 'checkout_cleaning' AND `status` = 'in_progress';

UPDATE `housekeeping_jobs` SET
    `started_at` = NOW() - INTERVAL 90 MINUTE,
    `completed_at` = NOW() - INTERVAL 10 MINUTE,
    `actual_duration` = 80,
    `assigned_to` = 2
WHERE `task_type` = 'checkout_cleaning' AND `status` = 'completed';

-- Insert demo telegram notifications
INSERT INTO `telegram_notifications` (`housekeeping_job_id`, `chat_id`, `message_text`, `status`) VALUES
((SELECT id FROM housekeeping_jobs WHERE room_id = 3 LIMIT 1), '123456789', '🧹 งานทำความสะอาดใหม่!\n\n🏠 ห้อง: 103 (overnight)\n👤 แขกเช็คเอาท์: คุณสมหญิง สวยงาม', 'sent'),
((SELECT id FROM housekeeping_jobs WHERE room_id = 2 LIMIT 1), '987654321', '🧹 งานทำความสะอาดใหม่!\n\n🏠 ห้อง: 102 (short)', 'sent');

-- Final status report
SELECT 'T008 Demo Setup Completed Successfully!' as Status;
SELECT COUNT(*) as 'Demo Bookings Created' FROM bookings WHERE booking_code LIKE 'BK%';
SELECT COUNT(*) as 'Demo Housekeeping Jobs Created' FROM housekeeping_jobs WHERE task_type = 'checkout_cleaning';
SELECT COUNT(*) as 'Demo Telegram Notifications' FROM telegram_notifications;
SELECT 'Ready for T008 demonstration!' as Message;