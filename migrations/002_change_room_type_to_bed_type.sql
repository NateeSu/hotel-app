-- Migration: Change room_type from short/overnight to double/single
-- Purpose: Separate room type (bed type) from plan type (stay duration)
-- Date: 2025-09-30
--
-- room_type: double (เตียงคู่), single (เตียงเดี่ยว)
-- plan_type: short (ชั่วคราว), overnight (ค้างคืน) - remains unchanged in bookings table

-- Step 1: Change the ENUM type for room_type in rooms table
ALTER TABLE `rooms`
  MODIFY COLUMN `room_type` ENUM('short', 'overnight', 'double', 'single') NOT NULL DEFAULT 'double';

-- Step 2: Update existing data
-- Map: short -> double, overnight -> double (you can adjust mapping as needed)
-- This assumes most rooms should be double bed type
UPDATE `rooms` SET `room_type` = 'double' WHERE `room_type` IN ('short', 'overnight');

-- Step 3: Remove old values from ENUM
ALTER TABLE `rooms`
  MODIFY COLUMN `room_type` ENUM('double', 'single') NOT NULL DEFAULT 'double';

-- Step 4: Update any views that reference room_type
DROP VIEW IF EXISTS `transfer_summary`;

CREATE VIEW `transfer_summary` AS
SELECT
    rt.id,
    rt.transfer_date,
    b.guest_name,
    b.guest_phone,
    r_from.room_number AS from_room,
    r_from.room_type AS from_room_type,
    r_to.room_number AS to_room,
    r_to.room_type AS to_room_type,
    rt.transfer_reason,
    rt.total_adjustment,
    rt.status,
    u.full_name AS transferred_by_name,
    COALESCE(tb.payment_status, 'none') AS payment_status
FROM room_transfers rt
JOIN bookings b ON rt.booking_id = b.id
JOIN rooms r_from ON rt.from_room_id = r_from.id
JOIN rooms r_to ON rt.to_room_id = r_to.id
JOIN users u ON rt.transferred_by = u.id
LEFT JOIN transfer_billing tb ON rt.id = tb.transfer_id;

-- Migration completed successfully
-- Note: bookings.plan_type remains as ENUM('short', 'overnight')