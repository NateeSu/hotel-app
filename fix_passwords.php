<?php
/**
 * Fix user passwords in database
 */

require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDatabase();

    // Generate correct password hash for 'password123'
    $correctHash = password_hash('password123', PASSWORD_DEFAULT);

    echo "Generated hash: " . $correctHash . "\n<br>";

    // Update all users with correct password
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username IN ('admin', 'reception', 'housekeeping')");
    $result = $stmt->execute([$correctHash]);

    if ($result) {
        echo "✅ Passwords updated successfully!\n<br>";

        // Verify the fix
        $stmt = $pdo->prepare("SELECT username, password_hash FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();

        echo "\nVerification:\n<br>";
        foreach ($users as $user) {
            $verify = password_verify('password123', $user['password_hash']);
            echo "User: {$user['username']} - Password check: " . ($verify ? "✅ PASS" : "❌ FAIL") . "\n<br>";
        }

    } else {
        echo "❌ Failed to update passwords\n<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n<br>";
}
?>

<p><strong>Now run the test again:</strong> <a href="test_system.php">test_system.php</a></p>