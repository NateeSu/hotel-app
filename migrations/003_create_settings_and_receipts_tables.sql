-- Migration: Create hotel_settings and receipts tables
-- Date: 2025-10-01

-- Create hotel_settings table if not exists
CREATE TABLE IF NOT EXISTS hotel_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create receipts table if not exists
CREATE TABLE IF NOT EXISTS receipts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_number VARCHAR(20) NOT NULL UNIQUE,
    booking_id INT UNSIGNED NOT NULL,
    booking_code VARCHAR(20) NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'transfer') NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    generated_by INT UNSIGNED,
    receipt_data JSON,
    PRIMARY KEY (id),
    INDEX idx_receipt_number (receipt_number),
    INDEX idx_booking_code (booking_code),
    INDEX idx_generated_at (generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
