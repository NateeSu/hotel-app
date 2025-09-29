-- Hotel Settings Management System
-- ระบบจัดการตั้งค่าโรงแรม

USE hotel_management;

-- Create hotel_settings table if not exists
CREATE TABLE IF NOT EXISTS hotel_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'number', 'boolean') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default hotel settings
INSERT INTO hotel_settings (setting_key, setting_value, setting_type, description)
VALUES
    ('hotel_name', 'Hotel Management System', 'text', 'ชื่อโรงแรม'),
    ('hotel_address', '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110', 'textarea', 'ที่อยู่โรงแรม'),
    ('hotel_phone', '02-123-4567', 'text', 'เบอร์โทรศัพท์โรงแรม'),
    ('hotel_email', 'info@hotel.com', 'text', 'อีเมลโรงแรม'),
    ('hotel_website', 'www.hotel.com', 'text', 'เว็บไซต์โรงแรม')
ON DUPLICATE KEY UPDATE
    setting_value = setting_value, -- Keep existing values
    updated_at = CURRENT_TIMESTAMP;

-- Show current settings
SELECT setting_key, setting_value, description FROM hotel_settings
WHERE setting_key IN ('hotel_name', 'hotel_address', 'hotel_phone')
ORDER BY setting_key;