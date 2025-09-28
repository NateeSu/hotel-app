-- Fix missing columns in housekeeping_jobs table
USE hotel_management;

-- Add missing columns to housekeeping_jobs table
ALTER TABLE housekeeping_jobs
ADD COLUMN IF NOT EXISTS completed_by INT UNSIGNED NULL AFTER assigned_to,
ADD COLUMN IF NOT EXISTS completed_at TIMESTAMP NULL AFTER completed_by,
ADD COLUMN IF NOT EXISTS completion_notes TEXT NULL AFTER completed_at;

-- Add foreign key constraint for completed_by
ALTER TABLE housekeeping_jobs
ADD CONSTRAINT fk_housekeeping_completed_by
FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL;

-- Create room_status_logs table if it doesn't exist
CREATE TABLE IF NOT EXISTS room_status_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    room_id INT UNSIGNED NOT NULL,
    previous_status ENUM('available', 'occupied', 'cleaning', 'maintenance', 'out_of_order') NOT NULL,
    new_status ENUM('available', 'occupied', 'cleaning', 'maintenance', 'out_of_order') NOT NULL,
    changed_by INT UNSIGNED NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_room_status_logs_room_id (room_id),
    INDEX idx_room_status_logs_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Show final structure
DESCRIBE housekeeping_jobs;