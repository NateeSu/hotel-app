<?php
/**
 * Debug script to test basic PHP functionality
 * Run this file to see if PHP is working correctly
 */

// Force error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='utf-8'><title>Debug Test</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}</style></head><body>";

echo "<h1>🔍 PHP Debug Test</h1>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PHP SAPI: " . PHP_SAPI . "<br>";

// Test 2: Required Extensions
echo "<h2>2. Required Extensions</h2>";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '❌';
    echo "$status $ext: " . ($loaded ? 'Loaded' : '<strong style="color:red">NOT LOADED</strong>') . "<br>";
}

// Test 3: File Paths
echo "<h2>3. File Paths</h2>";
echo "__FILE__: " . __FILE__ . "<br>";
echo "__DIR__: " . __DIR__ . "<br>";
echo "getcwd(): " . getcwd() . "<br>";

// Test 4: Check if core files exist
echo "<h2>4. Core Files Check</h2>";
$coreFiles = [
    'config/db.php',
    'includes/helpers.php',
    'includes/csrf.php',
    'includes/auth.php',
    'includes/router.php',
    'templates/partials/flash.php'
];

foreach ($coreFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    $exists = file_exists($fullPath);
    $status = $exists ? '✅' : '❌';
    $readable = $exists && is_readable($fullPath) ? 'readable' : 'not readable';
    echo "$status $file: " . ($exists ? "exists ($readable)" : '<strong style="color:red">NOT FOUND</strong>') . "<br>";
    if (!$exists) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for: $fullPath<br>";
    }
}

// Test 5: Session
echo "<h2>5. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✅ Session started successfully<br>";
} else {
    echo "ℹ️ Session already active<br>";
}
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . " (1=disabled, 2=active)<br>";

// Test 6: Database connection attempt
echo "<h2>6. Database Connection Test</h2>";
try {
    if (file_exists(__DIR__ . '/config/db.php')) {
        require_once __DIR__ . '/config/db.php';
        echo "✅ config/db.php loaded<br>";

        if (function_exists('getDatabase')) {
            try {
                $pdo = getDatabase();
                echo "✅ Database connection successful!<br>";

                // Test query
                $stmt = $pdo->query("SELECT DATABASE() as db_name, NOW() as current_time");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "Database: " . ($result['db_name'] ?? 'unknown') . "<br>";
                echo "Server time: " . ($result['current_time'] ?? 'unknown') . "<br>";

            } catch (Exception $e) {
                echo "❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "<br>";
            }
        } else {
            echo "⚠️ getDatabase() function not found<br>";
        }
    } else {
        echo "❌ config/db.php not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading database config: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test 7: Server variables
echo "<h2>7. Server Variables</h2>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "<br>";
echo "SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'not set') . "<br>";

// Test 8: Try to include index.php
echo "<h2>8. Index.php Test</h2>";
echo "<a href='index.php' target='_blank'>Click here to test index.php</a><br>";

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Check all items above for ❌ marks</li>";
echo "<li>If any required extensions are missing, install them</li>";
echo "<li>If core files are not found, check file paths and permissions</li>";
echo "<li>If database connection fails, check your .env or database credentials</li>";
echo "<li>Check the error.log file in the root directory for detailed errors</li>";
echo "</ol>";

echo "</body></html>";
?>
