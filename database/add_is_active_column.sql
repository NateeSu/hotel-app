-- Add is_active column to rooms table
USE hotel_management;

-- Add is_active column if it doesn't exist
ALTER TABLE rooms
ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER status;

-- Update all existing rooms to be active
UPDATE rooms SET is_active = 1 WHERE is_active IS NULL;

-- Show the updated structure
DESCRIBE rooms;