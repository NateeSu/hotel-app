<?php
/**
 * Test complete login flow
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🧪 Login Flow Test</h1>";

// Test 1: Check current session
echo "<h2>1. Current Session Status:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data:<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test 2: Try login programmatically
echo "<h2>2. Test Login Function:</h2>";
try {
    define('APP_INIT', true);
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/config/db.php';

    // Clear session first
    $_SESSION = [];

    $result = login('admin', 'password123');
    echo "Login result:<br>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";

    if ($result['success']) {
        echo "✅ Login function works!<br>";
        echo "Session after login:<br>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";

        // Test auth check
        echo "<h3>Auth Check:</h3>";
        $loggedIn = isLoggedIn();
        echo "isLoggedIn(): " . ($loggedIn ? "✅ TRUE" : "❌ FALSE") . "<br>";

        $user = currentUser();
        if ($user) {
            echo "✅ currentUser(): " . $user['username'] . "<br>";
        } else {
            echo "❌ currentUser(): NULL<br>";
        }

    } else {
        echo "❌ Login failed: " . $result['message'] . "<br>";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Manual login form
echo "<h2>3. Manual Login Test:</h2>";
?>

<form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
    <h4>Test Login Form:</h4>
    <p>Username: <input type="text" name="test_username" value="admin" style="padding: 5px;"></p>
    <p>Password: <input type="text" name="test_password" value="password123" style="padding: 5px;"></p>
    <button type="submit" name="test_login" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Test Login</button>
</form>

<?php
if (isset($_POST['test_login'])) {
    echo "<h4>Form Login Test Result:</h4>";

    $username = $_POST['test_username'];
    $password = $_POST['test_password'];

    $result = login($username, $password);

    if ($result['success']) {
        echo "✅ Form login successful!<br>";
        echo "Session: <pre>" . print_r($_SESSION, true) . "</pre>";

        echo "<p><a href='?r=rooms.board' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Room Board</a></p>";
    } else {
        echo "❌ Form login failed: " . $result['message'] . "<br>";
    }
}

echo "<h2>4. Direct Links:</h2>";
echo "<p><a href='auth/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Official Login Page</a></p>";
echo "<p><a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Main Index</a></p>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1, h2 { color: #333; }
pre { background: #f1f1f1; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>