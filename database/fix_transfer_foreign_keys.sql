-- Fix foreign key constraints for room transfer system
-- Remove dependency on customers table

USE hotel_management;

-- Remove the foreign key constraint that references customers table if it exists
SET FOREIGN_KEY_CHECKS = 0;

-- Drop foreign key constraint for customers if it exists
-- Check if transfer_billing table exists and has the foreign key
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = 'hotel_management'
     AND TABLE_NAME = 'guest_analytics'
     AND REFERENCED_TABLE_NAME = 'customers') > 0,
    'ALTER TABLE guest_analytics DROP FOREIGN KEY guest_analytics_ibfk_1',
    'SELECT "No customers foreign key to drop" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update guest_analytics table structure to not reference customers
-- This removes dependency on customers table
ALTER TABLE guest_analytics MODIFY guest_id INT UNSIGNED NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Update transfer engine to work without customers table
-- The transfer system will work with guest_name from bookings directly

SELECT "Transfer system foreign key dependencies fixed" as status;