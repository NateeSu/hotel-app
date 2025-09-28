<?php
/**
 * T008 Housekeeping Notification System - Live Demo
 *
 * แสดงการทำงานของระบบแจ้งเตือนงานทำความสะอาดผ่าน Telegram
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
require_once __DIR__ . '/lib/telegram_service.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T008 Housekeeping Notification System - Live Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4rem 0; }
        .feature-card { transition: transform 0.3s ease; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .feature-card:hover { transform: translateY(-5px); }
        .step-number { width: 40px; height: 40px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .telegram-mockup { background: #0088cc; border-radius: 15px; color: white; padding: 1rem; margin: 1rem 0; }
        .chat-bubble { background: #ffffff; color: #333; border-radius: 15px 15px 3px 15px; padding: 0.75rem; margin: 0.5rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-badge { padding: 0.5rem 1rem; border-radius: 25px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-progress { background: #cff4fc; color: #087990; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        .demo-highlight { border: 3px solid #ffc107; border-radius: 10px; padding: 1rem; background: #fffbf0; }
    </style>
</head>
<body>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="bi bi-telegram me-3"></i>
                    T008: Housekeeping Notification System
                </h1>
                <p class="lead mb-4">ระบบแจ้งเตือนงานทำความสะอาดอัตโนมัติผ่าน Telegram<br>
                เมื่อแขก Check-out ระบบจะแจ้งเตือนเจ้าหน้าที่ทำความสะอาดทันที</p>
                <div class="d-flex gap-3">
                    <a href="#demo" class="btn btn-light btn-lg">
                        <i class="bi bi-play-circle me-2"></i>ดูการทำงาน
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-list-check me-2"></i>ฟีเจอร์
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="telegram-mockup">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-telegram fs-3 me-2"></i>
                        <strong>Housekeeping Bot</strong>
                        <span class="badge bg-success ms-auto">Live</span>
                    </div>
                    <div class="chat-bubble">
                        🧹 <strong>งานทำความสะอาดใหม่!</strong><br><br>
                        🏠 ห้อง: 103 (overnight)<br>
                        👤 แขกเช็คเอาท์: คุณสมหญิง สวยงาม<br>
                        ⏰ เวลาเช็คเอาท์: <?php echo date('d/m/Y H:i'); ?><br>
                        📋 ประเภทงาน: ทำความสะอาดหลังเช็คเอาท์<br>
                        🎯 ความสำคัญ: สูง<br><br>
                        🔗 <a href="#" class="text-primary">คลิกเพื่อดูรายละเอียดและรายงานความคืบหน้า</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<section id="features" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">ฟีเจอร์หลัก T008</h2>
            <p class="lead text-muted">ระบบแจ้งเตือนอัตโนมัติที่ครบครันและใช้งานง่าย</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-telegram text-primary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">แจ้งเตือน Telegram</h5>
                        <p class="text-muted">ส่งแจ้งเตือนอัตโนมัติเมื่อมี checkout พร้อมลิงก์งาน</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-list-task text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">จัดการงาน</h5>
                        <p class="text-muted">ระบบติดตามสถานะงาน pending → in_progress → completed</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-graph-up text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">รายงานประสิทธิภาพ</h5>
                        <p class="text-muted">วิเคราะห์ประสิทธิภาพการทำงานและเวลาที่ใช้</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-check-circle text-info" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">อัปเดตสถานะ</h5>
                        <p class="text-muted">ห้องกลับสู่สถานะ "available" อัตโนมัติเมื่องานเสร็จ</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Demo Section -->
<section id="demo" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">การทำงานของระบบ</h2>
            <p class="lead text-muted">ดูการทำงานแบบ Step-by-Step</p>
        </div>

        <!-- Current System Status -->
        <div class="demo-highlight mb-5">
            <h4 class="text-center mb-4">
                <i class="bi bi-activity text-primary me-2"></i>
                สถานะระบบปัจจุบัน
            </h4>

            <?php
            try {
                $pdo = getDatabase();

                // Get current housekeeping jobs
                $stmt = $pdo->query("
                    SELECT
                        hj.id, hj.status, hj.priority, hj.telegram_sent,
                        r.room_number, r.status as room_status,
                        b.guest_name, b.checkout_at,
                        CASE
                            WHEN hj.started_at IS NOT NULL AND hj.completed_at IS NULL
                            THEN TIMESTAMPDIFF(MINUTE, hj.started_at, NOW())
                            ELSE hj.actual_duration
                        END as duration_minutes
                    FROM housekeeping_jobs hj
                    JOIN rooms r ON hj.room_id = r.id
                    LEFT JOIN bookings b ON hj.booking_id = b.id
                    WHERE hj.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    ORDER BY hj.created_at DESC
                    LIMIT 5
                ");
                $jobs = $stmt->fetchAll();

                // Get telegram notifications count
                $stmt = $pdo->query("SELECT COUNT(*) FROM telegram_notifications WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                $notificationCount = $stmt->fetchColumn();

            } catch (Exception $e) {
                $jobs = [];
                $notificationCount = 0;
            }
            ?>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="text-center">
                        <h3 class="text-primary"><?php echo count($jobs); ?></h3>
                        <p class="mb-0">งานทำความสะอาด (24 ชม.)</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center">
                        <h3 class="text-success"><?php echo $notificationCount; ?></h3>
                        <p class="mb-0">การแจ้งเตือนที่ส่งแล้ว</p>
                    </div>
                </div>
            </div>

            <?php if (!empty($jobs)): ?>
            <div class="table-responsive mt-4">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ห้อง</th>
                            <th>แขก</th>
                            <th>สถานะงาน</th>
                            <th>สถานะห้อง</th>
                            <th>Telegram</th>
                            <th>เวลาที่ใช้</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($job['room_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($job['guest_name'] ?? '-'); ?></td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'pending' => 'status-pending',
                                    'in_progress' => 'status-progress',
                                    'completed' => 'status-completed'
                                ];
                                $statusTexts = [
                                    'pending' => 'รอดำเนินการ',
                                    'in_progress' => 'กำลังดำเนินการ',
                                    'completed' => 'เสร็จสิ้น'
                                ];
                                ?>
                                <span class="status-badge <?php echo $statusClasses[$job['status']]; ?>">
                                    <?php echo $statusTexts[$job['status']]; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $roomStatusColors = [
                                    'available' => 'success',
                                    'occupied' => 'primary',
                                    'cleaning' => 'warning',
                                    'maintenance' => 'danger'
                                ];
                                $roomStatusTexts = [
                                    'available' => 'ว่าง',
                                    'occupied' => 'มีแขก',
                                    'cleaning' => 'ทำความสะอาด',
                                    'maintenance' => 'ซ่อมบำรุง'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $roomStatusColors[$job['room_status']]; ?>">
                                    <?php echo $roomStatusTexts[$job['room_status']]; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($job['telegram_sent']): ?>
                                    <i class="bi bi-check-circle text-success fs-5" title="ส่งแล้ว"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle text-muted fs-5" title="ยังไม่ส่ง"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $job['duration_minutes'] ? $job['duration_minutes'] . ' นาที' : '-'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Workflow Steps -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="d-flex align-items-start mb-4">
                    <div class="step-number me-3">1</div>
                    <div>
                        <h5>แขก Check-out</h5>
                        <p class="text-muted">เมื่อแขก check-out ระบบจะสร้างงานทำความสะอาดอัตโนมัติและเปลี่ยนสถานะห้องเป็น "cleaning"</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="step-number me-3">2</div>
                    <div>
                        <h5>ส่งแจ้งเตือน Telegram</h5>
                        <p class="text-muted">ระบบส่งข้อความแจ้งเตือนไปยังเจ้าหน้าที่ทำความสะอาดพร้อมลิงก์ดูรายละเอียดงาน</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="step-number me-3">3</div>
                    <div>
                        <h5>เจ้าหน้าที่เริ่มงาน</h5>
                        <p class="text-muted">เจ้าหน้าที่คลิกลิงก์ → เริ่มงาน → ระบบบันทึกเวลาเริ่มต้น</p>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="step-number me-3">4</div>
                    <div>
                        <h5>เสร็จสิ้นงาน</h5>
                        <p class="text-muted">เมื่องานเสร็จ → ห้องกลับสู่สถานะ "available" → แจ้งเตือนฝ่ายต้อนรับ</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="telegram-mockup">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-telegram fs-4 me-2"></i>
                            <strong>Housekeeping Bot</strong>
                        </div>
                        <small><?php echo date('H:i'); ?></small>
                    </div>

                    <div class="chat-bubble">
                        🧹 <strong>งานทำความสะอาดใหม่!</strong><br><br>
                        🏠 ห้อง: 101 (short)<br>
                        👤 แขกเช็คเอาท์: คุณสมชาย ใจดี<br>
                        ⏰ เวลาเช็คเอาท์: <?php echo date('d/m/Y H:i', strtotime('-30 minutes')); ?><br>
                        📋 ประเภทงาน: ทำความสะอาดหลังเช็คเอาท์<br>
                        🎯 ความสำคัญ: ปกติ<br><br>
                        📝 หมายเหตุพิเศษ: แขกทิ้งผ้าเปียกในห้องน้ำ<br><br>
                        🔗 <strong>คลิกเพื่อดูรายละเอียดและรายงานความคืบหน้า:</strong><br>
                        <a href="<?php echo $baseUrl; ?>/?r=housekeeping.job&id=1" class="text-light">
                            <?php echo $baseUrl; ?>/?r=housekeeping.job&id=1
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-light">✓ ส่งถึง 2 เจ้าหน้าที่ทำความสะอาด</small>
                    </div>
                </div>

                <!-- Completion notification example -->
                <div class="telegram-mockup mt-3" style="background: #28a745;">
                    <div class="chat-bubble">
                        ✅ <strong>งานทำความสะอาดเสร็จสิ้น</strong><br><br>
                        🏠 ห้อง: 102<br>
                        👤 ดำเนินการโดย: นาย ถู ข้น<br>
                        ⏰ เสร็จเมื่อ: <?php echo date('d/m/Y H:i', strtotime('-10 minutes')); ?><br>
                        📝 สถานะห้อง: เปลี่ยนเป็น 'ว่าง' แล้ว
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Action Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h3 class="mb-4">พร้อมทดสอบระบบ T008?</h3>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="test_housekeeping_system.php" class="btn btn-light btn-lg">
                        <i class="bi bi-gear-fill me-2"></i>ทดสอบระบบ
                    </a>
                    <a href="/?r=housekeeping.jobs" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-list-task me-2"></i>ดูรายการงาน
                    </a>
                    <a href="/?r=housekeeping.reports" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-graph-up me-2"></i>ดูรายงาน
                    </a>
                    <a href="<?php echo $baseUrl; ?>" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-house me-2"></i>หน้าหลัก
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <small class="text-light">
                💡 <strong>หมายเหตุ:</strong> ต้องตั้งค่า Telegram Bot Token และ Chat ID ก่อนใช้งานจริง
            </small>
        </div>
    </div>
</section>

<!-- Features Detail Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-5">รายละเอียดฟีเจอร์</h3>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-telegram text-primary me-2"></i>
                            Telegram Integration
                        </h5>
                        <ul class="list-unstyled">
                            <li>✓ ส่งแจ้งเตือนอัตโนมัติ</li>
                            <li>✓ รองรับหลายเจ้าหน้าที่</li>
                            <li>✓ ข้อความภาษาไทยสวยงาม</li>
                            <li>✓ ลิงก์ไปยังหน้างานโดยตรง</li>
                            <li>✓ บันทึก log การส่ง</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-list-check text-success me-2"></i>
                            Job Management
                        </h5>
                        <ul class="list-unstyled">
                            <li>✓ ติดตามสถานะแบบ Real-time</li>
                            <li>✓ บันทึกเวลาเริ่ม-จบงาน</li>
                            <li>✓ เพิ่มหมายเหตุความคืบหน้า</li>
                            <li>✓ แสดงผู้รับผิดชอบงาน</li>
                            <li>✓ คำนวณเวลาที่ใช้อัตโนมัติ</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up text-warning me-2"></i>
                            Performance Reports
                        </h5>
                        <ul class="list-unstyled">
                            <li>✓ สถิติประสิทธิภาพรายบุคคล</li>
                            <li>✓ เวลาเฉลี่ยในการทำงาน</li>
                            <li>✓ อัตราความสำเร็จของงาน</li>
                            <li>✓ รายงานรายวัน/เดือน</li>
                            <li>✓ กราฟแสดงแนวโน้ม</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-0">&copy; 2024 Hotel Management System - T008 Housekeeping Notification System</p>
        <small class="text-muted">ระบบแจ้งเตือนงานทำความสะอาดอัตโนมัติ</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>