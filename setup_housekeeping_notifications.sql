-- Setup Housekeeping Notification System
-- Run this SQL in phpMyAdmin

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

-- Update housekeeping_jobs table structure
ALTER TABLE `housekeeping_jobs`
ADD COLUMN `booking_id` INT UNSIGNED NULL AFTER `room_id`,
ADD COLUMN `started_at` TIMESTAMP NULL AFTER `created_at`,
ADD COLUMN `completed_at` TIMESTAMP NULL AFTER `started_at`,
ADD COLUMN `actual_duration` INT NULL COMMENT 'Duration in minutes' AFTER `completed_at`,
ADD COLUMN `special_notes` TEXT AFTER `description`,
ADD COLUMN `telegram_sent` BOOLEAN DEFAULT FALSE AFTER `special_notes`,
ADD COLUMN `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal' AFTER `telegram_sent`;

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

-- Add telegram_chat_id to users table
ALTER TABLE `users`
ADD COLUMN `telegram_chat_id` VARCHAR(255) NULL AFTER `created_at`,
ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE AFTER `telegram_chat_id`;

-- Insert default settings
INSERT INTO `hotel_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('telegram_bot_token', '', 'text'),
('default_housekeeping_chat_id', '', 'text'),
('notification_enabled', 'true', 'boolean'),
('housekeeping_notification_template', '🧹 งานทำความสะอาดใหม่!', 'text')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Create housekeeping performance view
CREATE OR REPLACE VIEW `housekeeping_performance` AS
SELECT
    hj.id,
    hj.room_id,
    r.room_number,
    hj.task_type,
    hj.priority,
    hj.created_at,
    hj.started_at,
    hj.completed_at,
    CASE
        WHEN hj.completed_at IS NOT NULL AND hj.started_at IS NOT NULL
        THEN TIMESTAMPDIFF(MINUTE, hj.started_at, hj.completed_at)
        ELSE NULL
    END as duration_minutes,
    CASE
        WHEN hj.completed_at IS NOT NULL THEN 'completed'
        WHEN hj.started_at IS NOT NULL THEN 'in_progress'
        ELSE 'pending'
    END as current_status,
    hj.assigned_to,
    u.full_name as assigned_to_name,
    hj.telegram_sent
FROM housekeeping_jobs hj
JOIN rooms r ON hj.room_id = r.id
LEFT JOIN users u ON hj.assigned_to = u.id
ORDER BY hj.created_at DESC;

-- Sample data for testing (optional)
-- INSERT INTO `users` (`username`, `password_hash`, `full_name`, `role`, `telegram_chat_id`, `is_active`) VALUES
-- ('housekeeper1', '$2y$10$dummy', 'นาง สะอาด ใจดี', 'housekeeping', '123456789', TRUE),
-- ('housekeeper2', '$2y$10$dummy', 'นาย ถู ข้น', 'housekeeping', '987654321', TRUE);

SELECT 'Housekeeping notification system setup completed!' as Status;
SELECT COUNT(*) as 'Telegram Notifications Table Rows' FROM telegram_notifications;
SELECT COUNT(*) as 'Hotel Settings Rows' FROM hotel_settings;