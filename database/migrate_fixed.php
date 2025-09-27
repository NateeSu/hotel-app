<?php
/**
 * Hotel Management System - Fixed Database Migration Script
 *
 * This version properly handles multi-line SQL statements and views
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h2>Hotel Management System - Database Migration (Fixed)</h2>\n";
    echo "<pre>\n";
}

try {
    echo "Starting database migration...\n\n";

    $scriptDir = dirname(__FILE__);
    $rootDir = dirname($scriptDir);

    // Database connection (direct connection for migration)
    $config = [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];

    echo "Connecting to MySQL...\n";

    $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✓ Connected to MySQL\n";

    // Create database if not exists
    echo "Creating database 'hotel_db'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS hotel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE hotel_db");
    echo "✓ Database 'hotel_db' ready\n\n";

    // Configure SQL mode and disable foreign key checks
    $pdo->exec("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Create tables in correct order
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'reception', 'housekeeping') NOT NULL DEFAULT 'reception',
            email VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_users_username (username),
            UNIQUE KEY uk_users_email (email),
            INDEX idx_users_role (role),
            INDEX idx_users_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'rates' => "CREATE TABLE IF NOT EXISTS rates (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            rate_type VARCHAR(50) NOT NULL,
            description VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            duration_hours INT DEFAULT NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_rates_type (rate_type),
            INDEX idx_rates_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'rooms' => "CREATE TABLE IF NOT EXISTS rooms (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            room_number VARCHAR(10) NOT NULL,
            room_type ENUM('short', 'overnight') NOT NULL DEFAULT 'short',
            status ENUM('available', 'occupied', 'cleaning', 'maintenance') NOT NULL DEFAULT 'available',
            floor INT DEFAULT 1,
            max_occupancy INT DEFAULT 2,
            amenities TEXT DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_rooms_number (room_number),
            INDEX idx_rooms_status (status),
            INDEX idx_rooms_type (room_type),
            INDEX idx_rooms_floor (floor)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'bookings' => "CREATE TABLE IF NOT EXISTS bookings (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_code VARCHAR(20) NOT NULL,
            room_id INT UNSIGNED NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_id_number VARCHAR(20) DEFAULT NULL,
            guest_count INT NOT NULL DEFAULT 1,
            plan_type ENUM('short', 'overnight') NOT NULL,
            status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') NOT NULL DEFAULT 'pending',
            planned_check_in DATETIME NOT NULL,
            planned_check_out DATETIME NOT NULL,
            actual_check_in DATETIME NULL DEFAULT NULL,
            actual_check_out DATETIME NULL DEFAULT NULL,
            base_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            extra_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            extras JSON DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            created_by INT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_bookings_code (booking_code),
            INDEX idx_bookings_room_status (room_id, status),
            INDEX idx_bookings_customer_phone (customer_phone),
            INDEX idx_bookings_dates (planned_check_in, planned_check_out),
            INDEX idx_bookings_status (status),
            INDEX idx_bookings_created_by (created_by),
            FOREIGN KEY fk_bookings_room (room_id) REFERENCES rooms(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            FOREIGN KEY fk_bookings_creator (created_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'room_transfers' => "CREATE TABLE IF NOT EXISTS room_transfers (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id INT UNSIGNED NOT NULL,
            from_room_id INT UNSIGNED NOT NULL,
            to_room_id INT UNSIGNED NOT NULL,
            reason VARCHAR(255) DEFAULT NULL,
            transferred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            transferred_by INT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_transfers_booking (booking_id),
            INDEX idx_transfers_from_room (from_room_id),
            INDEX idx_transfers_to_room (to_room_id),
            FOREIGN KEY fk_transfers_booking (booking_id) REFERENCES bookings(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY fk_transfers_from_room (from_room_id) REFERENCES rooms(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            FOREIGN KEY fk_transfers_to_room (to_room_id) REFERENCES rooms(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            FOREIGN KEY fk_transfers_by (transferred_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'housekeeping_jobs' => "CREATE TABLE IF NOT EXISTS housekeeping_jobs (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            room_id INT UNSIGNED NOT NULL,
            job_type ENUM('cleaning', 'maintenance', 'inspection') NOT NULL DEFAULT 'cleaning',
            status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
            priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
            description TEXT DEFAULT NULL,
            assigned_to INT UNSIGNED DEFAULT NULL,
            started_at DATETIME NULL DEFAULT NULL,
            completed_at DATETIME NULL DEFAULT NULL,
            estimated_duration INT DEFAULT NULL COMMENT 'Estimated duration in minutes',
            actual_duration INT DEFAULT NULL COMMENT 'Actual duration in minutes',
            notes TEXT DEFAULT NULL,
            created_by INT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_housekeeping_status_room (status, room_id),
            INDEX idx_housekeeping_assigned (assigned_to),
            INDEX idx_housekeeping_priority (priority),
            INDEX idx_housekeeping_type (job_type),
            FOREIGN KEY fk_housekeeping_room (room_id) REFERENCES rooms(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            FOREIGN KEY fk_housekeeping_assigned (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
            FOREIGN KEY fk_housekeeping_creator (created_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'receipts' => "CREATE TABLE IF NOT EXISTS receipts (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            receipt_number VARCHAR(20) NOT NULL,
            booking_id INT UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('cash', 'card', 'transfer') NOT NULL DEFAULT 'cash',
            payment_status ENUM('pending', 'paid', 'partial', 'refunded') NOT NULL DEFAULT 'pending',
            pdf_path VARCHAR(255) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            issued_by INT UNSIGNED NOT NULL,
            issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_receipts_number (receipt_number),
            INDEX idx_receipts_booking (booking_id),
            INDEX idx_receipts_status (payment_status),
            INDEX idx_receipts_method (payment_method),
            INDEX idx_receipts_issued_by (issued_by),
            FOREIGN KEY fk_receipts_booking (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            FOREIGN KEY fk_receipts_issuer (issued_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'settings' => "CREATE TABLE IF NOT EXISTS settings (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(100) NOT NULL,
            setting_value TEXT DEFAULT NULL,
            setting_type ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string',
            description VARCHAR(255) DEFAULT NULL,
            updated_by INT UNSIGNED DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_settings_key (setting_key),
            FOREIGN KEY fk_settings_updater (updated_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'activity_logs' => "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT UNSIGNED DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            table_name VARCHAR(50) DEFAULT NULL,
            record_id INT UNSIGNED DEFAULT NULL,
            old_values JSON DEFAULT NULL,
            new_values JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_logs_user (user_id),
            INDEX idx_logs_action (action),
            INDEX idx_logs_table_record (table_name, record_id),
            INDEX idx_logs_created (created_at),
            FOREIGN KEY fk_logs_user (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    // Create tables
    foreach ($tables as $tableName => $sql) {
        echo "Creating table '{$tableName}'... ";
        $pdo->exec($sql);
        echo "✓\n";
    }

    // Create views after all tables are created
    echo "\nCreating views...\n";

    $view1 = "CREATE OR REPLACE VIEW v_room_status AS
    SELECT
        r.id,
        r.room_number,
        r.room_type,
        r.status,
        r.floor,
        r.max_occupancy,
        CASE
            WHEN r.status = 'occupied' THEN (
                SELECT CONCAT(b.customer_name, ' (', b.booking_code, ')')
                FROM bookings b
                WHERE b.room_id = r.id
                AND b.status = 'checked_in'
                ORDER BY b.actual_check_in DESC
                LIMIT 1
            )
            ELSE NULL
        END AS current_guest,
        CASE
            WHEN r.status = 'occupied' THEN (
                SELECT b.planned_check_out
                FROM bookings b
                WHERE b.room_id = r.id
                AND b.status = 'checked_in'
                ORDER BY b.actual_check_in DESC
                LIMIT 1
            )
            ELSE NULL
        END AS expected_checkout,
        r.updated_at
    FROM rooms r";

    echo "Creating view 'v_room_status'... ";
    $pdo->exec($view1);
    echo "✓\n";

    $view2 = "CREATE OR REPLACE VIEW v_booking_summary AS
    SELECT
        b.id,
        b.booking_code,
        b.customer_name,
        b.customer_phone,
        r.room_number,
        b.plan_type,
        b.status,
        b.planned_check_in,
        b.planned_check_out,
        b.actual_check_in,
        b.actual_check_out,
        b.total_amount,
        u.full_name AS created_by_name,
        b.created_at
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.created_by = u.id";

    echo "Creating view 'v_booking_summary'... ";
    $pdo->exec($view2);
    echo "✓\n";

    // Restore foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✓ Migration completed successfully!\n";

    // Verify tables
    echo "\nVerifying database structure...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Created " . count($tables) . " tables: " . implode(', ', $tables) . "\n";

    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $views = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Created " . count($views) . " views: " . implode(', ', $views) . "\n";

    echo "\nDatabase is ready for seeding!\n";
    echo "Next step: Run 'php database/seed.php'\n";

} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (!$isCLI) {
    echo "</pre>\n";
    echo "<p><a href='../index.php'>← Return to Hotel System</a></p>\n";
}
?>