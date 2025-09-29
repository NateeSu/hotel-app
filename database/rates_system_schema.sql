-- Hotel Room Rates Management System
-- ระบบจัดการอัตราค่าห้อง

USE hotel_management;

-- Create rates table if not exists
CREATE TABLE IF NOT EXISTS room_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rate_name VARCHAR(100) NOT NULL,
    rate_type ENUM('short', 'overnight') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_hours INT NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_active_rate_type (rate_type, is_active),
    INDEX idx_rate_type (rate_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default rates (update existing data)
INSERT INTO room_rates (rate_name, rate_type, price, duration_hours, description, is_active)
VALUES
    ('ชั่วคราว 3 ชั่วโมง', 'short', 200.00, 3, 'การเข้าพักแบบชั่วคราว 3 ชั่วโมง', 1),
    ('ค้างคืน', 'overnight', 350.00, 12, 'การเข้าพักแบบค้างคืน 12 ชั่วโมง', 1)
ON DUPLICATE KEY UPDATE
    price = VALUES(price),
    duration_hours = VALUES(duration_hours),
    description = VALUES(description),
    updated_at = CURRENT_TIMESTAMP;

-- Show current rates
SELECT * FROM room_rates WHERE is_active = 1;