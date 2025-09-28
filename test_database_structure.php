<?php
/**
 * Test Database Structure for T008
 * ตรวจสอบโครงสร้างฐานข้อมูลว่าพร้อมสำหรับ T008 หรือไม่
 */

// Define constants first
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files
require_once __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบโครงสร้างฐานข้อมูล - T008</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-check { font-family: monospace; }
        .check-success { color: #198754; }
        .check-error { color: #dc3545; }
        .check-warning { color: #fd7e14; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="text-primary">
            <i class="bi bi-database-check me-2"></i>
            ตรวจสอบโครงสร้างฐานข้อมูล
        </h1>
        <p class="lead">ตรวจสอบว่าฐานข้อมูลพร้อมสำหรับ T008 หรือไม่</p>
    </div>

    <?php
    try {
        $pdo = getDatabase();

        // ตรวจสอบตาราง housekeeping_jobs
        echo '<div class="card mb-4">';
        echo '<div class="card-header bg-primary text-white">';
        echo '<h4 class="mb-0">ตาราง housekeeping_jobs</h4>';
        echo '</div>';
        echo '<div class="card-body">';

        $stmt = $pdo->query("DESCRIBE housekeeping_jobs");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $requiredColumns = [
            'booking_id' => 'INT UNSIGNED',
            'task_type' => 'ENUM',
            'special_notes' => 'TEXT',
            'telegram_sent' => 'BOOLEAN',
            'priority' => 'ENUM'
        ];

        echo '<div class="table-responsive">';
        echo '<table class="table table-sm table-check">';
        echo '<thead><tr><th>คอลัมน์</th><th>ประเภท</th><th>สถานะ</th></tr></thead>';
        echo '<tbody>';

        $existingColumns = [];
        foreach ($columns as $col) {
            $existingColumns[$col['Field']] = $col['Type'];
        }

        foreach ($requiredColumns as $colName => $expectedType) {
            echo '<tr>';
            echo '<td><code>' . $colName . '</code></td>';
            echo '<td><code>' . $expectedType . '</code></td>';

            if (isset($existingColumns[$colName])) {
                echo '<td><span class="check-success"><i class="bi bi-check-circle"></i> มีแล้ว</span></td>';
            } else {
                echo '<td><span class="check-error"><i class="bi bi-x-circle"></i> ขาดหายไป</span></td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // ตรวจสอบตาราง users
        echo '<div class="card mb-4">';
        echo '<div class="card-header bg-success text-white">';
        echo '<h4 class="mb-0">ตาราง users</h4>';
        echo '</div>';
        echo '<div class="card-body">';

        $stmt = $pdo->query("DESCRIBE users");
        $userColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $requiredUserColumns = [
            'telegram_chat_id' => 'VARCHAR',
            'is_active' => 'BOOLEAN'
        ];

        echo '<div class="table-responsive">';
        echo '<table class="table table-sm table-check">';
        echo '<thead><tr><th>คอลัมน์</th><th>ประเภท</th><th>สถานะ</th></tr></thead>';
        echo '<tbody>';

        $existingUserColumns = [];
        foreach ($userColumns as $col) {
            $existingUserColumns[$col['Field']] = $col['Type'];
        }

        foreach ($requiredUserColumns as $colName => $expectedType) {
            echo '<tr>';
            echo '<td><code>' . $colName . '</code></td>';
            echo '<td><code>' . $expectedType . '</code></td>';

            if (isset($existingUserColumns[$colName])) {
                echo '<td><span class="check-success"><i class="bi bi-check-circle"></i> มีแล้ว</span></td>';
            } else {
                echo '<td><span class="check-error"><i class="bi bi-x-circle"></i> ขาดหายไป</span></td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // ตรวจสอบตารางใหม่
        echo '<div class="card mb-4">';
        echo '<div class="card-header bg-warning text-dark">';
        echo '<h4 class="mb-0">ตารางใหม่สำหรับ T008</h4>';
        echo '</div>';
        echo '<div class="card-body">';

        $requiredTables = [
            'telegram_notifications',
            'hotel_settings'
        ];

        echo '<div class="table-responsive">';
        echo '<table class="table table-sm table-check">';
        echo '<thead><tr><th>ตาราง</th><th>สถานะ</th></tr></thead>';
        echo '<tbody>';

        foreach ($requiredTables as $tableName) {
            echo '<tr>';
            echo '<td><code>' . $tableName . '</code></td>';

            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
                if ($stmt->fetch()) {
                    echo '<td><span class="check-success"><i class="bi bi-check-circle"></i> มีแล้ว</span></td>';
                } else {
                    echo '<td><span class="check-error"><i class="bi bi-x-circle"></i> ยังไม่มี</span></td>';
                }
            } catch (Exception $e) {
                echo '<td><span class="check-error"><i class="bi bi-x-circle"></i> ไม่สามารถตรวจสอบได้</span></td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // สรุปและคำแนะนำ
        echo '<div class="card">';
        echo '<div class="card-header bg-info text-white">';
        echo '<h4 class="mb-0">คำแนะนำ</h4>';
        echo '</div>';
        echo '<div class="card-body">';

        $missingItems = [];

        // ตรวจสอบคอลัมน์ที่ขาดหายไป
        foreach ($requiredColumns as $colName => $expectedType) {
            if (!isset($existingColumns[$colName])) {
                $missingItems[] = "คอลัมน์ {$colName} ในตาราง housekeeping_jobs";
            }
        }

        foreach ($requiredUserColumns as $colName => $expectedType) {
            if (!isset($existingUserColumns[$colName])) {
                $missingItems[] = "คอลัมน์ {$colName} ในตาราง users";
            }
        }

        // ตรวจสอบตารางที่ขาดหายไป
        foreach ($requiredTables as $tableName) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
                if (!$stmt->fetch()) {
                    $missingItems[] = "ตาราง {$tableName}";
                }
            } catch (Exception $e) {
                $missingItems[] = "ตาราง {$tableName} (ไม่สามารถตรวจสอบได้)";
            }
        }

        if (empty($missingItems)) {
            echo '<div class="alert alert-success">';
            echo '<h5><i class="bi bi-check-circle me-2"></i>ฐานข้อมูลพร้อมใช้งาน!</h5>';
            echo '<p class="mb-0">โครงสร้างฐานข้อมูลครบถ้วนสำหรับ T008 แล้ว</p>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">';
            echo '<h5><i class="bi bi-exclamation-triangle me-2"></i>ต้องอัปเดตฐานข้อมูล</h5>';
            echo '<p><strong>รายการที่ขาดหายไป:</strong></p>';
            echo '<ul class="mb-3">';
            foreach ($missingItems as $item) {
                echo '<li>' . $item . '</li>';
            }
            echo '</ul>';
            echo '<p class="mb-0"><strong>วิธีแก้ไข:</strong> รัน SQL script ต่อไปนี้</p>';
            echo '</div>';

            echo '<div class="alert alert-info">';
            echo '<h6>SQL Scripts ที่ต้องรัน:</h6>';
            echo '<ol>';
            echo '<li><code>setup_t008_demo.sql</code> - ตั้งค่า T008 พร้อมข้อมูลทดสอบ</li>';
            echo '<li><code>fix_housekeeping_table.sql</code> - แก้ไขโครงสร้างตาราง</li>';
            echo '</ol>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';

    } catch (Exception $e) {
        echo '<div class="alert alert-danger">';
        echo '<h5><i class="bi bi-exclamation-triangle me-2"></i>เกิดข้อผิดพลาด</h5>';
        echo '<p>ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    ?>

    <div class="text-center mt-4">
        <div class="btn-group" role="group">
            <a href="demo_t008.php" class="btn btn-primary">
                <i class="bi bi-house me-1"></i>กลับ Demo
            </a>
            <a href="test_housekeeping_system.php" class="btn btn-success">
                <i class="bi bi-gear me-1"></i>ทดสอบระบบ
            </a>
            <a href="housekeeping/reports.php" class="btn btn-info">
                <i class="bi bi-graph-up me-1"></i>ทดสอบรายงาน
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>