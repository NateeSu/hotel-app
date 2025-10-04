<?php
/**
 * Test Routing & Login Debug Page
 * Access: http://hotelapp.udoncoop.com/test_routing.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Routing & Login Debug Test</h1>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;} .ok{color:green;} .error{color:red;} pre{background:#fff;padding:10px;}</style>";

// Test 1: Server Variables
echo "<h2>1. Server Variables</h2>";
echo "HTTP_HOST: <strong>" . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "</strong><br>";
echo "REQUEST_URI: <strong>" . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "</strong><br>";
echo "SCRIPT_NAME: <strong>" . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "</strong><br>";
echo "QUERY_STRING: <strong>" . ($_SERVER['QUERY_STRING'] ?? 'NOT SET') . "</strong><br>";
echo "HTTPS: <strong>" . ($_SERVER['HTTPS'] ?? 'NOT SET') . "</strong><br>";
echo "DOCUMENT_ROOT: <strong>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "</strong><br>";

// Test 2: Calculate BaseURL
echo "<h2>2. BaseURL Calculation</h2>";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$scriptName = rtrim($scriptName, '/');
if ($scriptName === '' || $scriptName === '.') {
    $scriptName = '';
}
$baseUrl = $protocol . '://' . $host . $scriptName;
echo "Calculated baseUrl: <strong class='ok'>" . $baseUrl . "</strong><br>";

// Test 3: .htaccess Check
echo "<h2>3. .htaccess & mod_rewrite Check</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $hasRewrite = in_array('mod_rewrite', $modules);
    echo "mod_rewrite: <strong class='" . ($hasRewrite ? 'ok'>✅ Enabled' : 'error'>❌ NOT Enabled') . "</strong><br>";
} else {
    echo "mod_rewrite: ⚠️ Cannot check (apache_get_modules not available)<br>";
}

$htaccessExists = file_exists(__DIR__ . '/.htaccess');
echo ".htaccess exists: <strong class='" . ($htaccessExists ? 'ok'>✅ YES' : 'error'>❌ NO') . "</strong><br>";

if ($htaccessExists) {
    echo "<details><summary>View .htaccess content</summary><pre>";
    echo htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess'));
    echo "</pre></details>";
}

// Test 4: Test Routing
echo "<h2>4. Routing Test</h2>";
echo "\$_GET['r'] = <strong>" . ($_GET['r'] ?? '<span class="error">NOT SET</span>') . "</strong><br>";

// Test 5: Database Check
echo "<h2>5. Database Connection</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    $pdo = getDatabase();
    echo "<span class='ok'>✅ Database connection successful!</span><br>";

    // Check users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "Users in database: <strong>$userCount</strong><br>";

    if ($userCount > 0) {
        $stmt = $pdo->query("SELECT username, role, is_active FROM users LIMIT 5");
        echo "<h3>Sample users:</h3><pre>";
        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Username: {$user['username']}, Role: {$user['role']}, Active: {$user['is_active']}\n";
        }
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "<span class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}

// Test 6: Session
echo "<h2>6. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session ID: <strong>" . session_id() . "</strong><br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

// Test 7: Links to test
echo "<h2>7. Test Links</h2>";
echo "<ul>";
echo "<li><a href='" . $baseUrl . "/' target='_blank'>Test: " . $baseUrl . "/</a> (should redirect to login)</li>";
echo "<li><a href='" . $baseUrl . "/?r=auth.login' target='_blank'>Test: " . $baseUrl . "/?r=auth.login</a> (direct login)</li>";
echo "<li><a href='" . $baseUrl . "/index.php' target='_blank'>Test: " . $baseUrl . "/index.php</a> (index.php direct)</li>";
echo "<li><a href='" . $baseUrl . "/debug.php' target='_blank'>Test: " . $baseUrl . "/debug.php</a> (full debug)</li>";
echo "</ul>";

// Test 8: Error Log
echo "<h2>8. Recent Error Log</h2>";
$errorLog = __DIR__ . '/error.log';
if (file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -30); // Last 30 lines
    echo "<pre style='max-height:300px;overflow:auto;background:#ffe;'>";
    echo htmlspecialchars(implode('', $recentLines));
    echo "</pre>";
    echo "<p><a href='javascript:location.reload()'>🔄 Refresh to see updated logs</a></p>";
} else {
    echo "<span class='error'>No error.log file found</span><br>";
}

// Test 9: Instructions
echo "<h2>9. Troubleshooting Steps</h2>";
echo "<ol>";
echo "<li><strong>If redirect to login doesn't work:</strong>";
echo "<ul>";
echo "<li>Check if mod_rewrite is enabled: <code>sudo a2enmod rewrite && sudo systemctl restart apache2</code></li>";
echo "<li>Check Apache config allows .htaccess: <code>AllowOverride All</code> in VirtualHost</li>";
echo "<li>Check .htaccess file exists and is readable</li>";
echo "</ul></li>";

echo "<li><strong>If login fails:</strong>";
echo "<ul>";
echo "<li>Check error.log above for 'LOGIN ATTEMPT' messages</li>";
echo "<li>Check database users exist and passwords are hashed with bcrypt</li>";
echo "<li>Try creating a test user: see SQL below</li>";
echo "</ul></li>";

echo "<li><strong>Create test user (run in MySQL):</strong>";
echo "<pre style='background:#fff;padding:10px;'>";
echo "INSERT INTO users (username, password_hash, full_name, role, email, is_active)
VALUES (
    'admin',
    '" . password_hash('admin123', PASSWORD_DEFAULT) . "',
    'Administrator',
    'admin',
    'admin@hotel.local',
    1
);";
echo "</pre></li>";
echo "</ol>";

echo "<hr><p><em>Debug page generated at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
