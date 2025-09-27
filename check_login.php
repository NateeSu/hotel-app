<?php
/**
 * Login Debug - Check what's actually in the database
 */

require_once __DIR__ . '/config/db.php';

echo "<h1>🔍 Login Debug</h1>";

try {
    $pdo = getDatabase();

    echo "<h2>👥 All Users in Database:</h2>";
    $stmt = $pdo->query("SELECT id, username, full_name, role, is_active FROM users");
    $users = $stmt->fetchAll();

    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Active</th><th>Test Login</th></tr>";

    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>" . ($user['is_active'] ? '✅' : '❌') . "</td>";
        echo "<td>";

        // Test password verification for each user
        $stmt2 = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt2->execute([$user['id']]);
        $hash = $stmt2->fetchColumn();

        $passwords = ['password123', 'admin123', 'admin', '123456', 'rec123', 'hk123'];
        $working_password = null;

        foreach ($passwords as $pass) {
            if (password_verify($pass, $hash)) {
                $working_password = $pass;
                break;
            }
        }

        if ($working_password) {
            echo "<span style='color: green;'>✅ Password: <strong>{$working_password}</strong></span>";
        } else {
            echo "<span style='color: red;'>❌ No working password found</span>";
        }

        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>🎯 Login Instructions:</h2>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Use these credentials:</h3>";

    // Show working credentials
    foreach ($users as $user) {
        if ($user['is_active']) {
            $stmt2 = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt2->execute([$user['id']]);
            $hash = $stmt2->fetchColumn();

            $passwords = ['password123', 'admin123', 'admin', '123456', 'rec123', 'hk123'];

            foreach ($passwords as $pass) {
                if (password_verify($pass, $hash)) {
                    echo "<p><strong>Username:</strong> {$user['username']} | <strong>Password:</strong> {$pass} | <strong>Role:</strong> {$user['role']}</p>";
                    break;
                }
            }
        }
    }
    echo "</div>";

    echo "<h2>🔗 Quick Links:</h2>";
    echo "<p><a href='http://localhost/hotel-app/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Go to Login Page</a></p>";
    echo "<p><a href='test_system.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Run System Test</a></p>";

    // Check if login form is working
    echo "<h2>🧪 Test Login Form:</h2>";
    echo "<form method='POST' action='auth/login.php' style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>";
    echo "<p><strong>Test login directly:</strong></p>";
    echo "Username: <input type='text' name='username' value='admin' style='margin: 5px; padding: 5px;'><br>";
    echo "Password: <input type='text' name='password' value='password123' style='margin: 5px; padding: 5px;'><br>";
    echo "<input type='hidden' name='csrf_token' value='test'>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 10px 0;'>Test Login</button>";
    echo "</form>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>