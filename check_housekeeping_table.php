<?php
// Check housekeeping_jobs table structure
require_once 'config/db.php';

try {
    $pdo = getDatabase();

    echo "<h3>Housekeeping Jobs Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE housekeeping_jobs");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>