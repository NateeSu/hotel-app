-- Upgrade bookings table for enhanced check-in
-- Run this SQL in phpMyAdmin

USE hotel_management;

-- Add new columns to bookings table
ALTER TABLE bookings
ADD COLUMN guest_phone VARCHAR(20) DEFAULT NULL AFTER guest_name,
ADD COLUMN guest_id_number VARCHAR(20) DEFAULT NULL AFTER guest_phone,
ADD COLUMN guest_count INT DEFAULT 1 AFTER guest_id_number,
ADD COLUMN booking_code VARCHAR(20) DEFAULT NULL AFTER id,
ADD COLUMN base_amount DECIMAL(10,2) DEFAULT 0.00 AFTER checkout_at,
ADD COLUMN extra_amount DECIMAL(10,2) DEFAULT 0.00 AFTER base_amount,
ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER extra_amount,
ADD COLUMN payment_method ENUM('cash', 'card', 'transfer') DEFAULT 'cash' AFTER total_amount,
ADD COLUMN payment_status ENUM('pending', 'paid', 'partial') DEFAULT 'pending' AFTER payment_method;

-- Add index for booking_code
ALTER TABLE bookings ADD INDEX idx_booking_code (booking_code);

-- Update existing records with booking codes
UPDATE bookings SET
    booking_code = CONCAT('BK', DATE_FORMAT(created_at, '%y%m%d'), LPAD(id, 3, '0')),
    guest_count = 1,
    base_amount = CASE
        WHEN plan_type = 'short' THEN 300.00
        WHEN plan_type = 'overnight' THEN 800.00
        ELSE 0.00
    END,
    total_amount = CASE
        WHEN plan_type = 'short' THEN 300.00
        WHEN plan_type = 'overnight' THEN 800.00
        ELSE 0.00
    END,
    payment_method = 'cash',
    payment_status = 'paid'
WHERE booking_code IS NULL;

-- Show updated structure
DESCRIBE bookings;

-- Show sample data
SELECT id, booking_code, guest_name, guest_phone, plan_type, base_amount, total_amount, payment_status FROM bookings LIMIT 5;