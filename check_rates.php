<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDatabase();
    $stmt = $pdo->query('SELECT * FROM rates ORDER BY rate_type');
    $rates = $stmt->fetchAll();

    echo "<h2>Current Rates:</h2>";
    foreach ($rates as $rate) {
        echo "<p>{$rate['rate_type']}: {$rate['price']} THB ({$rate['description']})</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>