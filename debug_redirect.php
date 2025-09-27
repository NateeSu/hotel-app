<?php
/**
 * Debug what happens after login
 */

// Start session and show all debug info
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🔍 Login Redirect Debug</h1>";

// Check session data
echo "<h2>📋 Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
echo "<h2>👤 Login Status:</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User ID: " . $_SESSION['user_id'] . "<br>";

    if (isset($_SESSION['user_data'])) {
        echo "✅ User Data:<br>";
        echo "<pre>";
        print_r($_SESSION['user_data']);
        echo "</pre>";
    } else {
        echo "⚠️ No user_data in session<br>";
    }
} else {
    echo "❌ Not logged in (no user_id in session)<br>";
}

// Test database connection
echo "<h2>🗄️ Database Test:</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    $pdo = getDatabase();
    echo "✅ Database connection: OK<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test routing
echo "<h2>🛣️ Router Test:</h2>";
try {
    require_once __DIR__ . '/includes/router.php';
    echo "✅ Router loaded<br>";

    // Test default route
    $route = $_GET['r'] ?? 'home';
    echo "Current route: " . $route . "<br>";

    // Check if route exists
    $routes = [
        'home' => 'index.php',
        'rooms.board' => 'rooms/board.php',
        'auth.login' => 'auth/login.php',
        'auth.logout' => 'auth/logout.php'
    ];

    if (isset($routes[$route])) {
        echo "✅ Route '{$route}' exists<br>";
        $filePath = __DIR__ . '/' . $routes[$route];
        if (file_exists($filePath)) {
            echo "✅ File exists: {$routes[$route]}<br>";
        } else {
            echo "❌ File missing: {$routes[$route]}<br>";
        }
    } else {
        echo "❌ Route '{$route}' not found<br>";
    }

} catch (Exception $e) {
    echo "❌ Router error: " . $e->getMessage() . "<br>";
}

// Test auth functions
echo "<h2>🔐 Auth Test:</h2>";
try {
    define('APP_INIT', true);
    require_once __DIR__ . '/includes/auth.php';
    echo "✅ Auth functions loaded<br>";

    if (function_exists('isLoggedIn')) {
        $loggedIn = isLoggedIn();
        echo "isLoggedIn(): " . ($loggedIn ? "✅ TRUE" : "❌ FALSE") . "<br>";
    }

    if (function_exists('currentUser')) {
        $user = currentUser();
        if ($user) {
            echo "✅ Current user: " . $user['username'] . " (" . $user['role'] . ")<br>";
        } else {
            echo "❌ No current user<br>";
        }
    }

} catch (Exception $e) {
    echo "❌ Auth error: " . $e->getMessage() . "<br>";
}

// Check what URL we should redirect to
echo "<h2>🎯 Expected Redirect:</h2>";
if (isset($_SESSION['user_id'])) {
    echo "Should redirect to: <a href='?r=rooms.board'>Room Board</a><br>";
    echo "Or try: <a href='rooms/board.php'>Direct Room Board</a><br>";
} else {
    echo "Should redirect to: <a href='?r=auth.login'>Login Page</a><br>";
}

echo "<h2>🔗 Quick Links:</h2>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>Home</a>";
echo "<a href='?r=rooms.board' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>Room Board</a>";
echo "<a href='auth/logout.php' style='background: #dc3545; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin: 5px;'>Logout</a>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; }
h1, h2 { color: #333; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>