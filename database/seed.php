<?php
/**
 * Hotel Management System - Database Seeding Script
 *
 * This script populates the database with initial sample data.
 * It properly hashes passwords and can be run multiple times safely.
 *
 * Usage: php database/seed.php
 * Or via web browser: http://localhost/hotel-app/database/seed.php
 */

// Set PHP configuration for better error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if running from command line or web browser
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h2>Hotel Management System - Database Seeding</h2>\n";
    echo "<pre>\n";
}

try {
    echo "Starting database seeding...\n\n";

    // Get the directory of this script
    $scriptDir = dirname(__FILE__);
    $rootDir = dirname($scriptDir);

    // Include database configuration
    $configFile = $rootDir . '/config/db.php';
    if (!file_exists($configFile)) {
        throw new Exception("Database configuration file not found: {$configFile}");
    }

    require_once $configFile;
    echo "✓ Database configuration loaded\n";

    // Get database connection
    $pdo = getDatabaseConnection();
    echo "✓ Database connection established\n";

    // Check if database exists and has tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        throw new Exception("No tables found. Please run migrate.php first.");
    }

    echo "✓ Found " . count($tables) . " tables in database\n\n";

    // Seed users with properly hashed passwords
    echo "Seeding users...\n";
    seedUsers($pdo);

    // Seed rates
    echo "Seeding rates...\n";
    seedRates($pdo);

    // Seed rooms
    echo "Seeding rooms...\n";
    seedRooms($pdo);

    // Seed system settings
    echo "Seeding system settings...\n";
    seedSettings($pdo);

    // Seed sample bookings
    echo "Seeding sample bookings...\n";
    seedBookings($pdo);

    // Seed housekeeping jobs
    echo "Seeding housekeeping jobs...\n";
    seedHousekeepingJobs($pdo);

    // Seed receipts
    echo "Seeding receipts...\n";
    seedReceipts($pdo);

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✓ Database seeding completed successfully!\n";

    // Show summary
    showDataSummary($pdo);

} catch (Exception $e) {
    echo "\n✗ Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (!$isCLI) {
    echo "</pre>\n";
    echo "<p><a href='../index.php'>← Return to Hotel System</a></p>\n";
}

/**
 * Seed users with properly hashed passwords
 */
function seedUsers($pdo) {
    $users = [
        ['id' => 1, 'username' => 'admin', 'password' => 'admin123', 'full_name' => 'System Administrator', 'role' => 'admin', 'email' => 'admin@hotel.local', 'phone' => '02-000-0001'],
        ['id' => 2, 'username' => 'reception', 'password' => 'rec123', 'full_name' => 'Reception Staff', 'role' => 'reception', 'email' => 'reception@hotel.local', 'phone' => '02-000-0002'],
        ['id' => 3, 'username' => 'housekeeping', 'password' => 'hk123', 'full_name' => 'Housekeeping Staff', 'role' => 'housekeeping', 'email' => 'hk@hotel.local', 'phone' => '02-000-0003']
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (id, username, password_hash, full_name, role, email, phone, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");

    foreach ($users as $user) {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt->execute([
            $user['id'],
            $user['username'],
            $hashedPassword,
            $user['full_name'],
            $user['role'],
            $user['email'],
            $user['phone']
        ]);

        echo "  ✓ User '{$user['username']}' created (password: {$user['password']})\n";
    }
}

/**
 * Seed rate information
 */
function seedRates($pdo) {
    $rates = [
        ['rate_type' => 'short_3h', 'description' => '3-hour short stay', 'price' => 200.00, 'duration_hours' => 3],
        ['rate_type' => 'overnight', 'description' => 'Overnight stay', 'price' => 350.00, 'duration_hours' => 12],
        ['rate_type' => 'extended', 'description' => 'Extended stay (per hour)', 'price' => 50.00, 'duration_hours' => 1]
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO rates (rate_type, description, price, duration_hours, is_active)
        VALUES (?, ?, ?, ?, 1)
    ");

    foreach ($rates as $rate) {
        $stmt->execute([
            $rate['rate_type'],
            $rate['description'],
            $rate['price'],
            $rate['duration_hours']
        ]);

        echo "  ✓ Rate '{$rate['rate_type']}': ฿{$rate['price']} ({$rate['duration_hours']}h)\n";
    }
}

/**
 * Seed room information
 */
function seedRooms($pdo) {
    // Short-stay rooms: 101-110
    echo "  Creating short-stay rooms (101-110)...\n";
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO rooms (room_number, room_type, status, floor, max_occupancy, amenities)
        VALUES (?, 'short', 'available', 1, 2, 'Air conditioning, TV, Private bathroom')
    ");

    for ($i = 101; $i <= 110; $i++) {
        $stmt->execute([(string)$i]);
    }

    // Overnight rooms: 201-210
    echo "  Creating overnight rooms (201-210)...\n";
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO rooms (room_number, room_type, status, floor, max_occupancy, amenities)
        VALUES (?, 'overnight', 'available', 2, 2, 'Air conditioning, TV, Private bathroom, Mini fridge')
    ");

    for ($i = 201; $i <= 210; $i++) {
        $stmt->execute([(string)$i]);
    }

    echo "  ✓ Created 20 rooms (10 short-stay + 10 overnight)\n";
}

/**
 * Seed system settings
 */
function seedSettings($pdo) {
    $settings = [
        ['hotel_name', 'Hotel Management System', 'string', 'Hotel name displayed in the system'],
        ['hotel_address', '123 Main Street, Bangkok, Thailand', 'string', 'Hotel address'],
        ['hotel_phone', '02-000-0000', 'string', 'Hotel contact phone'],
        ['hotel_email', 'info@hotel.local', 'string', 'Hotel contact email'],
        ['tax_rate', '7', 'number', 'Tax rate percentage'],
        ['currency', 'THB', 'string', 'Currency code'],
        ['timezone', 'Asia/Bangkok', 'string', 'System timezone'],
        ['receipt_prefix', 'RCP', 'string', 'Receipt number prefix'],
        ['booking_prefix', 'BK', 'string', 'Booking code prefix'],
        ['auto_checkout_hours', '24', 'number', 'Auto checkout after hours'],
        ['housekeeping_check_interval', '30', 'number', 'Housekeeping check interval in minutes'],
        ['backup_retention_days', '30', 'number', 'Number of days to keep backup files']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO settings (setting_key, setting_value, setting_type, description)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            description = VALUES(description)
    ");

    foreach ($settings as $setting) {
        $stmt->execute($setting);
        echo "  ✓ Setting '{$setting[0]}': {$setting[1]}\n";
    }
}

/**
 * Seed sample bookings for testing
 */
function seedBookings($pdo) {
    // Get room IDs for booking
    $stmt = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number LIMIT 3");
    $rooms = $stmt->fetchAll();

    if (count($rooms) < 3) {
        echo "  ⚠ Not enough rooms to create sample bookings\n";
        return;
    }

    $bookings = [
        [
            'booking_code' => 'BK001',
            'room_id' => $rooms[0]['id'],
            'customer_name' => 'John Doe',
            'customer_phone' => '081-111-1111',
            'customer_id_number' => '1234567890123',
            'plan_type' => 'short',
            'status' => 'checked_out',
            'check_in_offset' => -48, // 2 days ago
            'check_out_offset' => -45, // 2 days ago + 3 hours
            'amount' => 200.00
        ],
        [
            'booking_code' => 'BK002',
            'room_id' => $rooms[1]['id'],
            'customer_name' => 'Jane Smith',
            'customer_phone' => '081-222-2222',
            'customer_id_number' => '2345678901234',
            'plan_type' => 'short',
            'status' => 'checked_in',
            'check_in_offset' => -1, // 1 hour ago
            'check_out_offset' => 2, // 2 hours from now
            'amount' => 200.00
        ],
        [
            'booking_code' => 'BK003',
            'room_id' => $rooms[2]['id'],
            'customer_name' => 'Bob Wilson',
            'customer_phone' => '081-333-3333',
            'customer_id_number' => '3456789012345',
            'plan_type' => 'overnight',
            'status' => 'confirmed',
            'check_in_offset' => 2, // 2 hours from now
            'check_out_offset' => 14, // 14 hours from now
            'amount' => 350.00
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO bookings (
            booking_code, room_id, customer_name, customer_phone, customer_id_number,
            guest_count, plan_type, status, planned_check_in, planned_check_out,
            actual_check_in, actual_check_out, base_amount, total_amount, created_by
        ) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, 2)
    ");

    foreach ($bookings as $booking) {
        $planned_check_in = date('Y-m-d H:i:s', strtotime("{$booking['check_in_offset']} hours"));
        $planned_check_out = date('Y-m-d H:i:s', strtotime("{$booking['check_out_offset']} hours"));

        $actual_check_in = null;
        $actual_check_out = null;

        if ($booking['status'] === 'checked_in' || $booking['status'] === 'checked_out') {
            $actual_check_in = $planned_check_in;
        }

        if ($booking['status'] === 'checked_out') {
            $actual_check_out = $planned_check_out;
        }

        $stmt->execute([
            $booking['booking_code'],
            $booking['room_id'],
            $booking['customer_name'],
            $booking['customer_phone'],
            $booking['customer_id_number'],
            $booking['plan_type'],
            $booking['status'],
            $planned_check_in,
            $planned_check_out,
            $actual_check_in,
            $actual_check_out,
            $booking['amount'],
            $booking['amount']
        ]);

        echo "  ✓ Booking '{$booking['booking_code']}' - {$booking['customer_name']} (Room {$rooms[array_search($booking['room_id'], array_column($rooms, 'id'))]['room_number']})\n";
    }

    // Update room status for checked-in booking
    $pdo->exec("UPDATE rooms SET status = 'occupied' WHERE id = {$rooms[1]['id']}");
    echo "  ✓ Updated room {$rooms[1]['room_number']} status to 'occupied'\n";
}

/**
 * Seed housekeeping jobs
 */
function seedHousekeepingJobs($pdo) {
    $stmt = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number LIMIT 5");
    $rooms = $stmt->fetchAll();

    if (empty($rooms)) {
        echo "  ⚠ No rooms found to create housekeeping jobs\n";
        return;
    }

    $jobs = [
        [
            'room_id' => $rooms[0]['id'],
            'job_type' => 'cleaning',
            'status' => 'completed',
            'priority' => 'normal',
            'description' => 'Post-checkout cleaning'
        ],
        [
            'room_id' => $rooms[3]['id'] ?? $rooms[0]['id'],
            'job_type' => 'cleaning',
            'status' => 'pending',
            'priority' => 'normal',
            'description' => 'Regular cleaning'
        ],
        [
            'room_id' => $rooms[4]['id'] ?? $rooms[0]['id'],
            'job_type' => 'maintenance',
            'status' => 'pending',
            'priority' => 'high',
            'description' => 'Air conditioning repair'
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO housekeeping_jobs (
            room_id, job_type, status, priority, description, assigned_to, estimated_duration, created_by
        ) VALUES (?, ?, ?, ?, ?, 3, 30, 2)
    ");

    foreach ($jobs as $job) {
        $stmt->execute([
            $job['room_id'],
            $job['job_type'],
            $job['status'],
            $job['priority'],
            $job['description']
        ]);

        $roomNumber = $rooms[array_search($job['room_id'], array_column($rooms, 'id'))]['room_number'];
        echo "  ✓ Housekeeping job: {$job['job_type']} for room {$roomNumber} ({$job['status']})\n";
    }
}

/**
 * Seed receipts
 */
function seedReceipts($pdo) {
    // Get completed bookings
    $stmt = $pdo->query("SELECT id, booking_code, total_amount FROM bookings WHERE status IN ('checked_out', 'checked_in') LIMIT 2");
    $bookings = $stmt->fetchAll();

    if (empty($bookings)) {
        echo "  ⚠ No bookings found to create receipts\n";
        return;
    }

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO receipts (
            receipt_number, booking_id, amount, payment_method, payment_status, issued_by
        ) VALUES (?, ?, ?, ?, 'paid', 2)
    ");

    foreach ($bookings as $index => $booking) {
        $receiptNumber = 'RCP' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
        $paymentMethod = $index === 0 ? 'cash' : 'card';

        $stmt->execute([
            $receiptNumber,
            $booking['id'],
            $booking['total_amount'],
            $paymentMethod
        ]);

        echo "  ✓ Receipt '{$receiptNumber}' for booking '{$booking['booking_code']}' (฿{$booking['total_amount']})\n";
    }
}

/**
 * Show summary of seeded data
 */
function showDataSummary($pdo) {
    echo "\nData Summary:\n";

    $tables = [
        'users' => 'Users',
        'rates' => 'Rates',
        'rooms' => 'Rooms',
        'bookings' => 'Bookings',
        'housekeeping_jobs' => 'Housekeeping Jobs',
        'receipts' => 'Receipts',
        'settings' => 'Settings'
    ];

    foreach ($tables as $table => $label) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
        $count = $stmt->fetchColumn();
        echo "- {$label}: {$count} records\n";
    }

    echo "\nTest Login Credentials:\n";
    echo "- Admin: admin / admin123\n";
    echo "- Reception: reception / rec123\n";
    echo "- Housekeeping: housekeeping / hk123\n";
}

/**
 * Get database connection using the config
 */
function getDatabaseConnection() {
    // Try to get connection from config file
    if (function_exists('getDatabase')) {
        return getDatabase();
    }

    // Fallback connection
    $config = [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'hotel_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];

    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $pdo->exec("SET time_zone = '+07:00'");

    return $pdo;
}
?>