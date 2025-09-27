<?php
// Debug password issue
require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDatabase();

    echo "<h2>🔍 Password Debug & Fix</h2>";

    // Check current passwords
    $stmt = $pdo->query("SELECT username, password_hash FROM users");
    $users = $stmt->fetchAll();

    echo "<h3>Current password hashes:</h3>";
    foreach ($users as $user) {
        echo "User: {$user['username']}<br>";
        echo "Hash: {$user['password_hash']}<br>";
        echo "Length: " . strlen($user['password_hash']) . "<br><br>";
    }

    // Generate NEW proper hash
    $newHash = password_hash('password123', PASSWORD_DEFAULT);
    echo "<h3>🔧 Fixing passwords now...</h3>";
    echo "New hash: {$newHash}<br>";
    echo "Length: " . strlen($newHash) . "<br>";

    // Update ALL users with new hash
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?");
    $result = $stmt->execute([$newHash]);

    if ($result) {
        echo "✅ <strong>Password UPDATE SUCCESS!</strong><br><br>";

        // Verify immediately
        echo "<h3>✅ Verification:</h3>";
        $stmt = $pdo->query("SELECT username, password_hash FROM users LIMIT 1");
        $testUser = $stmt->fetch();

        $verifyResult = password_verify('password123', $testUser['password_hash']);
        echo "Password verify test: " . ($verifyResult ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

        if ($verifyResult) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>🎉 FIXED! Login credentials:</h3>";
            echo "<strong>Username:</strong> admin<br>";
            echo "<strong>Password:</strong> password123<br>";
            echo "<strong>URL:</strong> <a href='http://localhost/hotel-app/'>http://localhost/hotel-app/</a>";
            echo "</div>";

            echo "<p><a href='test_system.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Run System Test</a></p>";
        }

    } else {
        echo "❌ Password update FAILED<br>";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>