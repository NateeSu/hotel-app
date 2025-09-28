-- Fix Housekeeping Jobs Table Structure
-- แก้ไขโครงสร้างตาราง housekeeping_jobs ให้รองรับ T008

USE hotel_management;

-- เพิ่มคอลัมน์ที่ขาดหายไป (ถ้ายังไม่มี)
ALTER TABLE `housekeeping_jobs`
ADD COLUMN IF NOT EXISTS `booking_id` INT UNSIGNED NULL AFTER `room_id`,
ADD COLUMN IF NOT EXISTS `task_type` ENUM('checkout_cleaning', 'maintenance', 'inspection') DEFAULT 'checkout_cleaning' AFTER `job_type`,
ADD COLUMN IF NOT EXISTS `special_notes` TEXT AFTER `notes`,
ADD COLUMN IF NOT EXISTS `telegram_sent` BOOLEAN DEFAULT FALSE AFTER `special_notes`;

-- เพิ่มคอลัมน์ priority ถ้ายังไม่มี
ALTER TABLE `housekeeping_jobs`
ADD COLUMN IF NOT EXISTS `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal' AFTER `telegram_sent`;

-- เพิ่ม telegram_chat_id และ is_active ให้ users table ถ้ายังไม่มี
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `telegram_chat_id` VARCHAR(255) NULL AFTER `updated_at`,
ADD COLUMN IF NOT EXISTS `is_active` BOOLEAN DEFAULT TRUE AFTER `telegram_chat_id`;

-- อัปเดตข้อมูลเก่าให้เข้ากับ T008
UPDATE housekeeping_jobs SET task_type = 'checkout_cleaning' WHERE task_type IS NULL AND job_type = 'cleaning';
UPDATE housekeeping_jobs SET priority = 'normal' WHERE priority IS NULL;
UPDATE housekeeping_jobs SET telegram_sent = FALSE WHERE telegram_sent IS NULL;
UPDATE users SET is_active = TRUE WHERE is_active IS NULL;

-- ตรวจสอบโครงสร้างตาราง
DESCRIBE housekeeping_jobs;
DESCRIBE users;

SELECT 'Housekeeping table structure fixed!' as Status;