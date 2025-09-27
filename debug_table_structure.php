<?php
/**
 * Debug Database Table Structure
 */

// Define APP_INIT to allow access
define('APP_INIT', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>🔍 ตรวจสอบโครงสร้างตาราง rooms</h2>";

try {
    require_once __DIR__ . '/config/db.php';
    $pdo = getDatabase();

    // Check if rooms table exists
    echo "<h3>1. ตรวจสอบการมีอยู่ของตาราง</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'rooms'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "✅ ตาราง 'rooms' มีอยู่<br><br>";

        // Show table structure
        echo "<h3>2. โครงสร้างตารางปัจจุบัน</h3>";
        $stmt = $pdo->query("DESCRIBE rooms");
        $columns = $stmt->fetchAll();

        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f5f5f5;'><th>คอลัมน์</th><th>ชนิดข้อมูล</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>" . $col['Field'] . "</strong></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Show existing data
        echo "<h3>3. ข้อมูลในตารางปัจจุบัน</h3>";
        $stmt = $pdo->query("SELECT * FROM rooms LIMIT 5");
        $rooms = $stmt->fetchAll();

        if ($rooms) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #f5f5f5;'>";
            foreach (array_keys($rooms[0]) as $header) {
                if (!is_numeric($header)) {
                    echo "<th>" . $header . "</th>";
                }
            }
            echo "</tr>";

            foreach ($rooms as $room) {
                echo "<tr>";
                foreach ($room as $key => $value) {
                    if (!is_numeric($key)) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>ไม่มีข้อมูลในตาราง</p>";
        }

    } else {
        echo "❌ ไม่พบตาราง 'rooms'<br>";
        echo "<p>ต้องสร้างตารางใหม่</p>";
    }

} catch (Exception $e) {
    echo "❌ ข้อผิดพลาด: " . $e->getMessage() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { font-size: 14px; }
th, td { padding: 8px; text-align: left; }
</style>

<p><a href="rooms/board.php">← กลับไปหน้า Room Board</a></p>