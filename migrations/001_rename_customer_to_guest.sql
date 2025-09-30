-- Migration: Rename customer_* fields to guest_* fields
-- Purpose: Align database schema with existing codebase
-- Date: 2025-09-30
--
-- This migration updates the bookings table to use 'guest_*' naming convention
-- instead of 'customer_*' to match the application code.

-- Step 1: Rename columns in bookings table
ALTER TABLE `bookings`
  CHANGE COLUMN `customer_name` `guest_name` VARCHAR(100) NOT NULL,
  CHANGE COLUMN `customer_phone` `guest_phone` VARCHAR(20) NOT NULL,
  CHANGE COLUMN `customer_id_number` `guest_id_number` VARCHAR(20) DEFAULT NULL;

-- Step 2: Drop and recreate the booking summary view with updated field names
DROP VIEW IF EXISTS `v_booking_summary`;

CREATE VIEW `v_booking_summary` AS
SELECT
    b.id,
    b.booking_code,
    b.guest_name,
    b.guest_phone,
    b.guest_id_number,
    b.plan_type,
    b.planned_check_in,
    b.planned_check_out,
    b.actual_check_in,
    b.actual_check_out,
    b.room_id,
    r.room_number,
    r.room_type,
    b.adults,
    b.children,
    b.total_amount,
    b.deposit_amount,
    b.status,
    b.notes,
    b.created_at,
    b.created_by,
    u.username as created_by_username,
    u.full_name as created_by_name
FROM bookings b
JOIN rooms r ON b.room_id = r.id
LEFT JOIN users u ON b.created_by = u.id;

-- Migration completed successfully