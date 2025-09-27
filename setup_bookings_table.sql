-- Hotel Management System - Bookings Table Setup
-- Run this SQL in phpMyAdmin for hotel_management database

USE hotel_management;

-- Create bookings table for check-in/check-out management
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    plan_type ENUM('short', 'overnight') NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    checkin_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    checkout_at TIMESTAMP NULL,
    notes TEXT,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign key constraints
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,

    -- Indexes for performance
    INDEX idx_room_status (room_id, status),
    INDEX idx_checkin_date (checkin_at),
    INDEX idx_status (status)
);

-- Insert test data to verify table structure
-- INSERT INTO bookings (room_id, guest_name, plan_type, status, created_by)
-- VALUES (1, 'Test Guest', 'overnight', 'active', 'admin');

-- Verify table creation
DESCRIBE bookings;

-- Check existing rooms for testing
SELECT id, room_number, status FROM rooms WHERE status = 'available' LIMIT 5;