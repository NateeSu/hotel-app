-- Create room_rates table for transfer system
USE hotel_management;

-- Create room_rates table
CREATE TABLE IF NOT EXISTS room_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_type ENUM('standard', 'deluxe', 'suite', 'short') NOT NULL,
    base_rate DECIMAL(10,2) NOT NULL DEFAULT 1500.00,
    weekend_rate DECIMAL(10,2) NOT NULL DEFAULT 2000.00,
    holiday_rate DECIMAL(10,2) NOT NULL DEFAULT 2500.00,
    peak_season_rate DECIMAL(10,2) NOT NULL DEFAULT 3000.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_room_type (room_type),
    INDEX idx_room_type (room_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default rates for each room type
INSERT IGNORE INTO room_rates (room_type, base_rate, weekend_rate, holiday_rate, peak_season_rate) VALUES
('standard', 1200.00, 1500.00, 2000.00, 2500.00),
('deluxe', 1800.00, 2200.00, 2800.00, 3500.00),
('suite', 3000.00, 3500.00, 4000.00, 5000.00),
('short', 800.00, 1000.00, 1200.00, 1500.00);

-- Show the created table
SELECT 'Room rates table created successfully!' as status;
SELECT * FROM room_rates;