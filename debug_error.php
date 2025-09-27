<?php
/**
 * Debug Specific Error in Room Board
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Define APP_INIT to allow access
define('APP_INIT', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>🔍 Debug Room Board Error</h2>";

try {
    date_default_timezone_set('Asia/Bangkok');

    // Define base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl = $protocol . '://' . $host . $scriptPath;
    $GLOBALS['baseUrl'] = $baseUrl;

    echo "✅ Base URL: " . $baseUrl . "<br>";

    // Test database connection
    echo "<h3>1. Database Connection</h3>";
    require_once __DIR__ . '/config/db.php';
    $pdo = getDatabase();
    echo "✅ Database connected<br>";

    // Test helpers
    echo "<h3>2. Helper Files</h3>";
    require_once __DIR__ . '/includes/helpers.php';
    echo "✅ helpers.php loaded<br>";

    require_once __DIR__ . '/includes/csrf.php';
    echo "✅ csrf.php loaded<br>";

    require_once __DIR__ . '/includes/auth.php';
    echo "✅ auth.php loaded<br>";

    require_once __DIR__ . '/includes/router.php';
    echo "✅ router.php loaded<br>";

    require_once __DIR__ . '/templates/partials/flash.php';
    echo "✅ flash.php loaded<br>";

    // Test auth
    echo "<h3>3. Authentication</h3>";
    if (isLoggedIn()) {
        $user = currentUser();
        echo "✅ User logged in: " . $user['username'] . "<br>";
    } else {
        echo "❌ User not logged in<br>";
    }

    // Test query
    echo "<h3>4. Database Query</h3>";
    $sql = "SELECT id, room_number, room_type as type, status, notes FROM rooms ORDER BY room_number";
    echo "SQL: " . $sql . "<br>";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rooms = $stmt->fetchAll();

    echo "✅ Query executed successfully<br>";
    echo "Found " . count($rooms) . " rooms<br>";

    // Show first few rooms
    if ($rooms) {
        echo "<h4>Sample data:</h4>";
        foreach (array_slice($rooms, 0, 3) as $room) {
            echo "- Room " . $room['room_number'] . " (" . $room['type'] . ", " . $room['status'] . ")<br>";
        }
    }

    // Test helper functions
    echo "<h3>5. Helper Functions</h3>";
    if ($rooms) {
        $testRoom = $rooms[0];
        echo "Testing with room: " . $testRoom['room_number'] . "<br>";

        echo "Status Color: " . getRoomStatusColor($testRoom['status']) . "<br>";
        echo "Status Icon: " . getRoomStatusIcon($testRoom['status']) . "<br>";
        echo "Status Text: " . getRoomStatusText($testRoom['status']) . "<br>";
        echo "✅ Helper functions working<br>";
    }

    // Test CSRF
    echo "<h3>6. CSRF Functions</h3>";
    $token = get_csrf_token();
    echo "CSRF Token: " . substr($token, 0, 10) . "...<br>";
    echo "✅ CSRF functions working<br>";

    // Test routeUrl
    echo "<h3>7. Route Functions</h3>";
    $checkInUrl = routeUrl('rooms.checkin');
    echo "Check-in URL: " . $checkInUrl . "<br>";
    echo "✅ Route functions working<br>";

    echo "<h3>✅ All tests passed - Room Board should work!</h3>";

} catch (Exception $e) {
    echo "<h3>❌ ERROR FOUND:</h3>";
    echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red;'>";
    echo "<strong>Message:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";

    echo "<h4>Stack Trace:</h4>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Helper functions (copy from rooms/board.php)
function getRoomStatusColor($status) {
    switch ($status) {
        case 'available': return 'success';
        case 'occupied': return 'danger';
        case 'cleaning':
        case 'cg': return 'warning';
        case 'maintenance': return 'secondary';
        default: return 'light';
    }
}

function getRoomStatusIcon($status) {
    switch ($status) {
        case 'available': return 'bi-check-circle';
        case 'occupied': return 'bi-person-fill';
        case 'cleaning':
        case 'cg': return 'bi-brush';
        case 'maintenance': return 'bi-tools';
        default: return 'bi-question-circle';
    }
}

function getRoomStatusText($status) {
    switch ($status) {
        case 'available': return 'ว่าง';
        case 'occupied': return 'มีผู้พัก';
        case 'cleaning':
        case 'cg': return 'ทำความสะอาด';
        case 'maintenance': return 'ซ่อมบำรุง';
        default: return 'ไม่ระบุ';
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h3 { color: #333; margin-top: 20px; }
</style>

<p><a href="index.php">← กลับไปหน้าหลัก</a></p>