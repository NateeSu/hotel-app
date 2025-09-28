-- Add missing completed_by column to housekeeping_jobs table
USE hotel_management;

-- Add completed_by column if it doesn't exist
SET @sql = CONCAT('ALTER TABLE housekeeping_jobs ADD COLUMN completed_by INT UNSIGNED NULL AFTER assigned_to');
SET @table_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'hotel_management'
    AND TABLE_NAME = 'housekeeping_jobs'
    AND COLUMN_NAME = 'completed_by');

-- Only add column if it doesn't exist
SET @sql = IF(@table_exists = 0, @sql, 'SELECT "Column completed_by already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add completion_notes column if it doesn't exist
SET @sql = CONCAT('ALTER TABLE housekeeping_jobs ADD COLUMN completion_notes TEXT NULL AFTER notes');
SET @table_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'hotel_management'
    AND TABLE_NAME = 'housekeeping_jobs'
    AND COLUMN_NAME = 'completion_notes');

-- Only add column if it doesn't exist
SET @sql = IF(@table_exists = 0, @sql, 'SELECT "Column completion_notes already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show current structure
DESCRIBE housekeeping_jobs;