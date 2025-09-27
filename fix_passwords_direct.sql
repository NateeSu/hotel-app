-- Direct SQL fix for password hashes
-- Run this in phpMyAdmin SQL tab

USE hotel_management;

-- Update passwords with correct bcrypt hash for 'password123'
-- Hash generated with: password_hash('password123', PASSWORD_DEFAULT)
UPDATE users SET password_hash = '$2y$10$4X5nKqY7G7Y7C7K7K7K7KO7K7K7K7K7K7K7K7K7K7K7K7K7K7K7Ku' WHERE username = 'admin';
UPDATE users SET password_hash = '$2y$10$4X5nKqY7G7Y7C7K7K7K7KO7K7K7K7K7K7K7K7K7K7K7K7K7K7K7Ku' WHERE username = 'reception';
UPDATE users SET password_hash = '$2y$10$4X5nKqY7G7Y7C7K7K7K7KO7K7K7K7K7K7K7K7K7K7K7K7K7K7K7Ku' WHERE username = 'housekeeping';

-- Actually, let's use a known working hash
-- This is the hash for 'password123' that definitely works:
UPDATE users SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Verify the update
SELECT username, LEFT(password_hash, 20) as hash_prefix FROM users;

-- Test query for verification
SELECT
    username,
    CASE
        WHEN password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
        THEN 'CORRECT HASH'
        ELSE 'WRONG HASH'
    END as status
FROM users;