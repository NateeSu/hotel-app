<?php
/**
 * Hotel Management System - Database Configuration
 *
 * This file provides database connection functionality with proper error handling,
 * timezone configuration, and environment variable support.
 *
 * Features:
 * - PDO connection with proper configuration
 * - Asia/Bangkok timezone setting
 * - Environment variable support (.env file)
 * - Fallback to default XAMPP configuration
 * - Connection pooling and reuse
 */

// Prevent direct access
if (!defined('DB_CONFIG_LOADED')) {
    define('DB_CONFIG_LOADED', true);
}

// Global database connection variable
$GLOBALS['db_connection'] = null;

/**
 * Get database configuration from environment or use defaults
 */
function getDatabaseConfig() {
    // Try to load .env file if it exists
    $envFile = dirname(__DIR__) . '/.env';
    if (file_exists($envFile)) {
        $envVars = parse_ini_file($envFile);
        foreach ($envVars as $key => $value) {
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }

    return [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 3306),
        'dbname' => env('DB_NAME', 'hotel_management'),
        'username' => env('DB_USER', 'root'),
        'password' => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'timezone' => env('DB_TIMEZONE', 'Asia/Bangkok')
    ];
}

/**
 * Create and configure PDO database connection
 */
function createDatabaseConnection() {
    $config = getDatabaseConfig();

    try {
        // Build DSN
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        // PDO options for security and performance
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['charset']}_unicode_ci"
        ];

        // Create PDO connection
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);

        // Set timezone for this session
        $timezoneOffset = getTimezoneOffset($config['timezone']);
        $pdo->exec("SET time_zone = '{$timezoneOffset}'");

        // Set SQL mode for compatibility
        $pdo->exec("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

        return $pdo;

    } catch (PDOException $e) {
        // Log error details for debugging
        error_log("Database connection failed: " . $e->getMessage());

        // For development, show detailed error
        if (isDevelopmentEnvironment()) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }

        // For production, show generic error
        throw new Exception("Database connection failed. Please check your configuration.");
    }
}

/**
 * Get database connection (singleton pattern)
 */
function getDatabase() {
    if ($GLOBALS['db_connection'] === null) {
        $GLOBALS['db_connection'] = createDatabaseConnection();
    }

    return $GLOBALS['db_connection'];
}

/**
 * Test database connection
 */
function testDatabaseConnection() {
    try {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT 1 as test, NOW() as current_time, @@time_zone as timezone");
        $result = $stmt->fetch();

        return [
            'success' => true,
            'message' => 'Database connection successful',
            'data' => $result
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'data' => null
        ];
    }
}

/**
 * Close database connection
 */
function closeDatabaseConnection() {
    $GLOBALS['db_connection'] = null;
}

/**
 * Get environment variable with fallback
 */
function env($key, $default = null) {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

    // Convert string representations of boolean values
    if (is_string($value)) {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
    }

    return $value;
}

/**
 * Get timezone offset for MySQL
 */
function getTimezoneOffset($timezone = 'Asia/Bangkok') {
    try {
        $tz = new DateTimeZone($timezone);
        $offset = $tz->getOffset(new DateTime());
        $hours = intval($offset / 3600);
        $minutes = abs(($offset % 3600) / 60);

        return sprintf('%+03d:%02d', $hours, $minutes);

    } catch (Exception $e) {
        // Fallback to +07:00 for Bangkok
        return '+07:00';
    }
}

/**
 * Check if running in development environment
 */
function isDevelopmentEnvironment() {
    return env('APP_ENV', 'development') === 'development' ||
           env('APP_DEBUG', true) === true ||
           $_SERVER['SERVER_NAME'] === 'localhost' ||
           strpos($_SERVER['SERVER_NAME'] ?? '', '127.0.0.1') === 0;
}

/**
 * Execute a database transaction safely
 */
function executeTransaction(callable $callback) {
    $pdo = getDatabase();

    try {
        $pdo->beginTransaction();
        $result = $callback($pdo);
        $pdo->commit();
        return $result;

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Get current timestamp in database format
 */
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

/**
 * Convert database timestamp to display format
 */
function formatTimestamp($timestamp, $format = 'd/m/Y H:i') {
    if (!$timestamp) return '';

    try {
        $date = new DateTime($timestamp);
        return $date->format($format);
    } catch (Exception $e) {
        return $timestamp;
    }
}

/**
 * Sanitize input for database queries (additional layer of protection)
 */
function sanitizeForDb($input) {
    if (is_string($input)) {
        return trim(strip_tags($input));
    }
    return $input;
}

/**
 * Generate unique ID for bookings, receipts, etc.
 */
function generateUniqueId($prefix = '', $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $id = $prefix;

    for ($i = 0; $i < $length; $i++) {
        $id .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $id;
}

// Auto-connect on file include (comment out if you prefer manual connection)
// Uncomment the next line to auto-connect when this file is included
// getDatabase();

?>