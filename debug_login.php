<?php
/**
 * Debug Login Issues
 * ไฟล์ทดสอบเพื่อหาปัญหาการ login
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Bangkok');

echo "<h2>🔍 Debug Login System</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once __DIR__ . '/config/db.php';
    $pdo = getDatabase();
    echo "✅ Database connection: SUCCESS<br>";

    // Test database
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Database query: SUCCESS<br>";

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Check Users Table
echo "<h3>2. Users Table Test</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, full_name, role FROM users LIMIT 5");
    $users = $stmt->fetchAll();

    echo "📊 Users in database:<br>";
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}<br>";
    }

} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "<br>";
}

// Test 3: Password Hash Test
echo "<h3>3. Password Hash Test</h3>";
try {
    $stmt = $pdo->prepare("SELECT username, password_hash FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        echo "✅ Admin user found<br>";
        echo "Username: {$admin['username']}<br>";
        echo "Stored hash: " . substr($admin['password_hash'], 0, 20) . "...<br>";

        // Test password verification
        $testPassword = 'admin123';
        $isValid = password_verify($testPassword, $admin['password_hash']);
        echo "Password verification for 'admin123': " . ($isValid ? "✅ VALID" : "❌ INVALID") . "<br>";

        // Show what the hash should be
        $correctHash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "New hash for 'admin123': " . substr($correctHash, 0, 20) . "...<br>";

    } else {
        echo "❌ Admin user not found<br>";
    }

} catch (Exception $e) {
    echo "❌ Password test error: " . $e->getMessage() . "<br>";
}

// Test 4: Auth Functions
echo "<h3>4. Auth Functions Test</h3>";
try {
    require_once __DIR__ . '/includes/helpers.php';
    require_once __DIR__ . '/includes/auth.php';

    echo "✅ Auth functions loaded<br>";

    // Test login function
    $loginResult = login('admin', 'admin123');
    echo "Login result: " . json_encode($loginResult) . "<br>";

    if ($loginResult['success']) {
        echo "✅ Login function works<br>";
        echo "Current user: " . json_encode(currentUser()) . "<br>";
    } else {
        echo "❌ Login failed: " . $loginResult['message'] . "<br>";
    }

} catch (Exception $e) {
    echo "❌ Auth function error: " . $e->getMessage() . "<br>";
}

// Test 5: Session Test
echo "<h3>5. Session Test</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data: " . json_encode($_SESSION) . "<br>";

// Test 6: Form Processing Test
echo "<h3>6. Form Processing Test</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "📥 POST data received:<br>";
    echo "Username: " . ($_POST['username'] ?? 'NOT SET') . "<br>";
    echo "Password: " . (isset($_POST['password']) ? '[SET]' : 'NOT SET') . "<br>";
    echo "CSRF Token: " . (isset($_POST['csrf_token']) ? '[SET]' : 'NOT SET') . "<br>";

    // Try manual login
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $result = login($_POST['username'], $_POST['password']);
        echo "Manual login result: " . json_encode($result) . "<br>";
    }
} else {
    echo "No POST data (use form below to test)<br>";
}

?>

<h3>7. Test Login Form</h3>
<form method="POST" style="border: 1px solid #ccc; padding: 20px; max-width: 300px;">
    <div style="margin-bottom: 10px;">
        <label>Username:</label><br>
        <input type="text" name="username" value="admin" style="width: 100%; padding: 5px;">
    </div>
    <div style="margin-bottom: 10px;">
        <label>Password:</label><br>
        <input type="password" name="password" value="admin123" style="width: 100%; padding: 5px;">
    </div>
    <input type="hidden" name="csrf_token" value="test">
    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none;">
        Test Login
    </button>
</form>

<h3>8. Quick Fixes</h3>
<p><a href="?fix_passwords=1" style="background: #28a745; color: white; padding: 10px; text-decoration: none;">🔧 Fix User Passwords</a></p>

<?php
// Quick fix for passwords
if (isset($_GET['fix_passwords'])) {
    echo "<h4>Fixing Passwords...</h4>";

    try {
        $users = [
            ['username' => 'admin', 'password' => 'admin123'],
            ['username' => 'reception', 'password' => 'rec123'],
            ['username' => 'housekeeping', 'password' => 'hk123']
        ];

        foreach ($users as $user) {
            $hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
            $result = $stmt->execute([$hash, $user['username']]);

            if ($result) {
                echo "✅ Updated password for {$user['username']}<br>";
            } else {
                echo "❌ Failed to update password for {$user['username']}<br>";
            }
        }

        echo "<p style='color: green; font-weight: bold;'>✅ Password fix completed! Try logging in again.</p>";

    } catch (Exception $e) {
        echo "❌ Password fix error: " . $e->getMessage() . "<br>";
    }
}
?>

<hr>
<p><a href="index.php">← Back to Main Site</a></p>