<?php
/**
 * Test Telegram Links for T008
 * ทดสอบลิงก์ที่ส่งใน Telegram
 */

// Define constants first
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Start session and initialize application
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Bangkok');

// Define base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$appPath = '/hotel-app';
$baseUrl = $protocol . '://' . $host . $appPath;
$GLOBALS['baseUrl'] = $baseUrl;

// Load required files
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/router.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทดสอบลิงก์ Telegram - T008</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="text-primary">
            <i class="bi bi-link-45deg me-2"></i>
            ทดสอบลิงก์ Telegram
        </h1>
        <p class="lead">ตรวจสอบการทำงานของลิงก์ที่ส่งในข้อความ Telegram</p>
    </div>

    <?php
    try {
        $pdo = getDatabase();

        // Get latest housekeeping jobs
        $stmt = $pdo->query("
            SELECT id, room_id
            FROM housekeeping_jobs
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $jobs = $stmt->fetchAll();

        if (empty($jobs)) {
            echo '<div class="alert alert-warning">ไม่มีงาน housekeeping ในระบบ กรุณารัน demo data ก่อน</div>';
        }

    } catch (Exception $e) {
        echo '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . htmlspecialchars($e->getMessage()) . '</div>';
        $jobs = [];
    }
    ?>

    <!-- Test Router -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-gear me-2"></i>
                ทดสอบ Router
            </h4>
        </div>
        <div class="card-body">
            <h5>Routes ที่ลงทะเบียนแล้ว:</h5>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <code>housekeeping.jobs</code>
                            <?php if (routeExists('housekeeping.jobs')): ?>
                                <span class="badge bg-success">✓</span>
                            <?php else: ?>
                                <span class="badge bg-danger">✗</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <code>housekeeping.job</code>
                            <?php if (routeExists('housekeeping.job')): ?>
                                <span class="badge bg-success">✓</span>
                            <?php else: ?>
                                <span class="badge bg-danger">✗</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <code>housekeeping.reports</code>
                            <?php if (routeExists('housekeeping.reports')): ?>
                                <span class="badge bg-success">✓</span>
                            <?php else: ?>
                                <span class="badge bg-danger">✗</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded">
                        <h6>Base URL:</h6>
                        <code><?php echo htmlspecialchars($baseUrl); ?></code>

                        <h6 class="mt-3">Current Route:</h6>
                        <code><?php echo htmlspecialchars($_GET['r'] ?? 'none'); ?></code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Links -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">
                <i class="bi bi-link me-2"></i>
                ทดสอบลิงก์
            </h4>
        </div>
        <div class="card-body">
            <?php if (!empty($jobs)): ?>
            <h5>ลิงก์ที่จะส่งใน Telegram:</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>URL ที่สร้าง</th>
                            <th>ทดสอบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                        <?php
                        $jobUrl = $baseUrl . "/?r=housekeeping.job&id=" . $job['id'];
                        ?>
                        <tr>
                            <td><strong>#<?php echo $job['id']; ?></strong></td>
                            <td>
                                <code class="small"><?php echo htmlspecialchars($jobUrl); ?></code>
                            </td>
                            <td>
                                <a href="<?php echo htmlspecialchars($jobUrl); ?>"
                                   class="btn btn-sm btn-primary" target="_blank">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>ทดสอบ
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                <h6><i class="bi bi-info-circle me-2"></i>วิธีทดสอบ:</h6>
                <ol>
                    <li>คลิก "ทดสอบ" ในแต่ละแถว</li>
                    <li>ตรวจสอบว่าเปิดหน้ารายละเอียดงานได้หรือไม่</li>
                    <li>หากไม่สามารถเปิดได้ แสดงว่ามีปัญหาใน routing</li>
                </ol>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle me-2"></i>ไม่มีงานให้ทดสอบ</h6>
                <p class="mb-0">กรุณาสร้างงาน housekeeping ก่อน หรือรัน <code>setup_t008_demo.sql</code></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Test Navigation -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">
                <i class="bi bi-compass me-2"></i>
                ทดสอบการนำทาง
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>รายการงาน</h6>
                            <a href="<?php echo routeUrl('housekeeping.jobs'); ?>"
                               class="btn btn-outline-primary">
                                <i class="bi bi-list-task me-1"></i>housekeeping.jobs
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>รายงาน</h6>
                            <a href="<?php echo routeUrl('housekeeping.reports'); ?>"
                               class="btn btn-outline-success">
                                <i class="bi bi-graph-up me-1"></i>housekeeping.reports
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>รายละเอียดงาน</h6>
                            <?php if (!empty($jobs)): ?>
                            <a href="<?php echo routeUrl('housekeeping.job', ['id' => $jobs[0]['id']]); ?>"
                               class="btn btn-outline-warning">
                                <i class="bi bi-eye me-1"></i>housekeeping.job
                            </a>
                            <?php else: ?>
                            <span class="text-muted">ไม่มีงาน</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access -->
    <div class="text-center mt-4">
        <div class="btn-group" role="group">
            <a href="demo_t008.php" class="btn btn-primary">
                <i class="bi bi-house me-1"></i>กลับ Demo
            </a>
            <a href="test_housekeeping_system.php" class="btn btn-success">
                <i class="bi bi-gear me-1"></i>ทดสอบระบบ
            </a>
            <a href="telegram_setup_instructions.php" class="btn btn-info">
                <i class="bi bi-telegram me-1"></i>ตั้งค่า Telegram
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>