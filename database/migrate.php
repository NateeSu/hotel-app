<?php
/**
 * Hotel Management System - Database Migration Script
 *
 * This script reads schema.sql and executes all SQL statements to create the database structure.
 * It can be run multiple times safely (idempotent).
 *
 * Usage: php database/migrate.php
 * Or via web browser: http://localhost/hotel-app/database/migrate.php
 */

// Set PHP configuration for better error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if running from command line or web browser
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h2>Hotel Management System - Database Migration</h2>\n";
    echo "<pre>\n";
}

try {
    echo "Starting database migration...\n\n";

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

    // Read schema.sql file
    $schemaFile = $scriptDir . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: {$schemaFile}");
    }

    $sql = file_get_contents($schemaFile);
    if ($sql === false) {
        throw new Exception("Failed to read schema file: {$schemaFile}");
    }

    echo "✓ Schema file loaded (" . number_format(strlen($sql)) . " bytes)\n";

    // Get database connection
    $pdo = getDatabaseConnection();
    echo "✓ Database connection established\n";

    // Set SQL mode to be more permissive
    $pdo->exec("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    echo "✓ SQL mode configured\n";

    // Split SQL into individual statements (improved parsing)
    $statements = explode(';', $sql);
    $statements = array_filter($statements, function($stmt) {
        $stmt = trim($stmt);
        // Skip empty statements and comments
        return !empty($stmt) &&
               !preg_match('/^\s*--/', $stmt) &&
               !preg_match('/^\s*\/\*/', $stmt) &&
               strlen($stmt) > 10; // Skip very short statements
    });

    // Clean up statements
    $statements = array_map('trim', $statements);

    echo "✓ Found " . count($statements) . " SQL statements to execute\n\n";

    // Execute each statement
    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;

        try {
            // Extract statement type for logging
            $firstWords = preg_split('/\s+/', trim($statement), 3);
            $firstWord = strtoupper($firstWords[0] ?? '');

            if (in_array($firstWord, ['CREATE', 'ALTER', 'DROP', 'INSERT', 'UPDATE', 'DELETE', 'SET', 'USE'])) {
                echo "Executing {$firstWord} statement " . ($index + 1) . "... ";

                // For very long statements (like views), use prepare/execute
                if (strlen($statement) > 1000) {
                    $stmt = $pdo->prepare($statement);
                    $result = $stmt->execute();
                } else {
                    $result = $pdo->exec($statement);
                }

                $successCount++;

                if ($firstWord === 'CREATE') {
                    if (stripos($statement, 'DATABASE') !== false) {
                        echo "✓ Database created/verified\n";
                    } elseif (stripos($statement, 'TABLE') !== false) {
                        preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches);
                        $tableName = $matches[1] ?? 'unknown';
                        echo "✓ Table '{$tableName}' created/verified\n";
                    } elseif (stripos($statement, 'VIEW') !== false) {
                        preg_match('/CREATE\s+(?:OR\s+REPLACE\s+)?VIEW\s+`?(\w+)`?/i', $statement, $matches);
                        $viewName = $matches[1] ?? 'unknown';
                        echo "✓ View '{$viewName}' created/updated\n";
                    } else {
                        echo "✓ Success\n";
                    }
                } else {
                    echo "✓ Success\n";
                }
            } else {
                echo "Skipping non-executable statement: " . substr($statement, 0, 50) . "...\n";
            }

        } catch (PDOException $e) {
            $errorCount++;
            echo "✗ Error: " . $e->getMessage() . "\n";

            // Log the statement that caused the error (first 100 chars only)
            if (strlen($statement) < 200) {
                echo "   Statement: " . $statement . "\n";
            } else {
                echo "   Statement: " . substr($statement, 0, 100) . "...\n";
            }

            // For debugging - show more context
            echo "   Statement type: " . $firstWord . "\n";
        }
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Migration Summary:\n";
    echo "- Successful statements: {$successCount}\n";
    echo "- Failed statements: {$errorCount}\n";

    if ($errorCount === 0) {
        echo "✓ Database migration completed successfully!\n";

        // Verify database structure
        echo "\nVerifying database structure...\n";
        verifyDatabaseStructure($pdo);

    } else {
        echo "⚠ Migration completed with {$errorCount} error(s)\n";
        echo "Please check the errors above and fix any issues\n";
    }

} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (!$isCLI) {
    echo "</pre>\n";
    echo "<p><a href='../index.php'>← Return to Hotel System</a></p>\n";
}

/**
 * Verify that all expected tables were created
 */
function verifyDatabaseStructure($pdo) {
    $expectedTables = [
        'users', 'rates', 'rooms', 'bookings', 'room_transfers',
        'housekeeping_jobs', 'receipts', 'settings', 'activity_logs'
    ];

    $expectedViews = ['v_room_status', 'v_booking_summary'];

    try {
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($expectedTables as $tableName) {
            if (in_array($tableName, $tables)) {
                echo "✓ Table '{$tableName}' exists\n";
            } else {
                echo "✗ Table '{$tableName}' not found\n";
            }
        }

        // Check views
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
        $views = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($expectedViews as $viewName) {
            if (in_array($viewName, $views)) {
                echo "✓ View '{$viewName}' exists\n";
            } else {
                echo "✗ View '{$viewName}' not found\n";
            }
        }

        echo "\nDatabase verification completed.\n";

    } catch (PDOException $e) {
        echo "Warning: Could not verify database structure: " . $e->getMessage() . "\n";
    }
}

/**
 * Get database connection using the config
 */
function getDatabaseConnection() {
    // Database configuration (fallback if config/db.php doesn't work)
    $config = [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'hotel_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];

    // Try to get connection from config file
    if (function_exists('getDatabase')) {
        try {
            return getDatabase();
        } catch (Exception $e) {
            echo "Warning: Could not use config/db.php, using fallback connection\n";
        }
    }

    // Fallback connection
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";

    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Set timezone
    $pdo->exec("SET time_zone = '+07:00'");

    return $pdo;
}
?>