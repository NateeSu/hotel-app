<?php
/**
 * Test Redirect Functionality
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h3>Testing Redirect...</h3>";

// Test 1: Manual redirect
if (isset($_GET['test']) && $_GET['test'] === 'redirect') {
    echo "Attempting redirect in 2 seconds...<br>";
    echo '<script>setTimeout(() => window.location.href = "?success=1", 2000);</script>';
    exit;
}

// Test 2: Success page
if (isset($_GET['success'])) {
    echo "<div style='color: green; font-size: 20px; padding: 20px; border: 2px solid green;'>";
    echo "✅ Redirect SUCCESS! You are now on the success page.";
    echo "</div>";
    echo "<p><a href='?'>Test Again</a></p>";
    exit;
}

// Test 3: Login simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // No output before this point
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['test_user'] = 'admin';

        // Try different redirect methods
        $method = $_POST['method'] ?? 'header';

        if ($method === 'header') {
            header('Location: ?success=1');
            exit;
        } elseif ($method === 'javascript') {
            echo '<script>window.location.href = "?success=1";</script>';
            exit;
        } elseif ($method === 'meta') {
            echo '<meta http-equiv="refresh" content="0;url=?success=1">';
            exit;
        }
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Redirect</title>
</head>
<body>
    <h2>🧪 Test Redirect Methods</h2>

    <?php if (isset($error)): ?>
        <div style="color: red; padding: 10px; border: 1px solid red;">
            ❌ <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <h3>Method 1: Direct Redirect Test</h3>
    <p><a href="?test=redirect" style="background: #007bff; color: white; padding: 10px; text-decoration: none;">Test JavaScript Redirect</a></p>

    <h3>Method 2: Login Form Test</h3>
    <form method="POST" style="border: 1px solid #ccc; padding: 20px; max-width: 400px;">
        <div style="margin-bottom: 10px;">
            <label>Username:</label><br>
            <input type="text" name="username" value="admin" style="width: 100%; padding: 5px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Password:</label><br>
            <input type="password" name="password" value="admin123" style="width: 100%; padding: 5px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Redirect Method:</label><br>
            <select name="method" style="width: 100%; padding: 5px;">
                <option value="header">PHP header()</option>
                <option value="javascript">JavaScript</option>
                <option value="meta">HTML meta refresh</option>
            </select>
        </div>
        <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none;">
            Test Login & Redirect
        </button>
    </form>

    <h3>Current Session:</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <p><a href="debug_login.php">← Back to Debug Page</a></p>
</body>
</html>