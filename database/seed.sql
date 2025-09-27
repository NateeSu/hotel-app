-- Hotel Management System - Seed Data
-- Sample data for testing and initial setup
-- This script is idempotent (can be run multiple times)

USE `hotel_db`;

-- Disable foreign key checks for clean insertion
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Insert default users (passwords will be hashed by seed.php)
-- Default passwords: admin123, rec123, hk123
INSERT IGNORE INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `email`, `phone`, `is_active`) VALUES
(1, 'admin', '$2y$10$placeholder_will_be_replaced_by_php_script', 'System Administrator', 'admin', 'admin@hotel.local', '02-000-0001', 1),
(2, 'reception', '$2y$10$placeholder_will_be_replaced_by_php_script', 'Reception Staff', 'reception', 'reception@hotel.local', '02-000-0002', 1),
(3, 'housekeeping', '$2y$10$placeholder_will_be_replaced_by_php_script', 'Housekeeping Staff', 'housekeeping', 'hk@hotel.local', '02-000-0003', 1);

-- 2. Insert default rates
INSERT IGNORE INTO `rates` (`rate_type`, `description`, `price`, `duration_hours`, `is_active`) VALUES
('short_3h', '3-hour short stay', 200.00, 3, 1),
('overnight', 'Overnight stay', 350.00, 12, 1),
('extended', 'Extended stay (per hour)', 50.00, 1, 1);

-- 3. Insert sample rooms
-- Short-stay rooms: 101-110 (Floor 1)
INSERT IGNORE INTO `rooms` (`room_number`, `room_type`, `status`, `floor`, `max_occupancy`, `amenities`) VALUES
('101', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('102', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('103', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('104', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('105', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('106', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('107', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('108', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('109', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom'),
('110', 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom');

-- Overnight rooms: 201-210 (Floor 2)
INSERT IGNORE INTO `rooms` (`room_number`, `room_type`, `status`, `floor`, `max_occupancy`, `amenities`) VALUES
('201', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('202', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('203', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('204', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('205', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('206', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('207', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('208', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('209', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge'),
('210', 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge');

-- 4. Insert system settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('hotel_name', 'Hotel Management System', 'string', 'Hotel name displayed in the system'),
('hotel_address', '123 Main Street, Bangkok, Thailand', 'string', 'Hotel address'),
('hotel_phone', '02-000-0000', 'string', 'Hotel contact phone'),
('hotel_email', 'info@hotel.local', 'string', 'Hotel contact email'),
('tax_rate', '7', 'number', 'Tax rate percentage'),
('currency', 'THB', 'string', 'Currency code'),
('timezone', 'Asia/Bangkok', 'string', 'System timezone'),
('receipt_prefix', 'RCP', 'string', 'Receipt number prefix'),
('booking_prefix', 'BK', 'string', 'Booking code prefix'),
('auto_checkout_hours', '24', 'number', 'Auto checkout after hours'),
('housekeeping_check_interval', '30', 'number', 'Housekeeping check interval in minutes'),
('backup_retention_days', '30', 'number', 'Number of days to keep backup files')
ON DUPLICATE KEY UPDATE
    `setting_value` = VALUES(`setting_value`),
    `description` = VALUES(`description`);

-- 5. Create sample bookings for testing (past and current)
INSERT IGNORE INTO `bookings` (
    `booking_code`, `room_id`, `customer_name`, `customer_phone`, `customer_id_number`,
    `guest_count`, `plan_type`, `status`, `planned_check_in`, `planned_check_out`,
    `actual_check_in`, `actual_check_out`, `base_amount`, `total_amount`, `created_by`
) VALUES
-- Completed booking
('BK001', 1, 'John Doe', '081-111-1111', '1234567890123', 1, 'short', 'checked_out',
 DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY) + INTERVAL 3 HOUR,
 DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY) + INTERVAL 3 HOUR,
 200.00, 200.00, 2),
-- Current check-in
('BK002', 2, 'Jane Smith', '081-222-2222', '2345678901234', 2, 'short', 'checked_in',
 NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 2 HOUR,
 NOW() - INTERVAL 1 HOUR, NULL,
 200.00, 200.00, 2),
-- Future booking
('BK003', 3, 'Bob Wilson', '081-333-3333', '3456789012345', 1, 'overnight', 'confirmed',
 NOW() + INTERVAL 2 HOUR, NOW() + INTERVAL 14 HOUR,
 NULL, NULL,
 350.00, 350.00, 2);

-- Update room status based on bookings
UPDATE `rooms` SET `status` = 'occupied' WHERE `id` = 2;

-- 6. Create sample housekeeping jobs
INSERT IGNORE INTO `housekeeping_jobs` (
    `room_id`, `job_type`, `status`, `priority`, `description`, `assigned_to`,
    `estimated_duration`, `created_by`
) VALUES
(1, 'cleaning', 'completed', 'normal', 'Post-checkout cleaning', 3, 30, 2),
(4, 'cleaning', 'pending', 'normal', 'Regular cleaning', 3, 30, 2),
(5, 'maintenance', 'pending', 'high', 'Air conditioning repair', NULL, 120, 2);

-- 7. Create sample receipts
INSERT IGNORE INTO `receipts` (
    `receipt_number`, `booking_id`, `amount`, `payment_method`, `payment_status`, `issued_by`
) VALUES
('RCP001', 1, 200.00, 'cash', 'paid', 2),
('RCP002', 2, 200.00, 'card', 'paid', 2);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;