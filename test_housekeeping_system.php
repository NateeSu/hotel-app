<?php
/**
 * Hotel Management System - Test Housekeeping Notification System
 *
 * Complete test of housekeeping workflow from checkout to completion
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

// HTML Header
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทดสอบระบบ Housekeeping Notification - Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .test-section { margin-bottom: 2rem; border: 1px solid #dee2e6; border-radius: 0.375rem; }
        .test-header { background-color: #f8f9fa; padding: 1rem; border-bottom: 1px solid #dee2e6; }
        .test-body { padding: 1rem; }
        .test-result { margin-top: 1rem; padding: 0.75rem; border-radius: 0.25rem; }
        .test-success { background-color: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; }
        .test-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .test-warning { background-color: #fff3cd; border: 1px solid #ffecb5; color: #856404; }
        .code-block { background-color: #f8f9fa; padding: 0.75rem; border-radius: 0.25rem; font-family: monospace; font-size: 0.875rem; }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="text-center mb-4">
        <h1 class="h2">
            <i class="bi bi-gear-fill text-primary me-2"></i>
            ทดสอบระบบ Housekeeping Notification
        </h1>
        <p class="text-muted">ทดสอบระบบแจ้งเตือนงานทำความสะอาดผ่าน Telegram</p>
    </div>

<?php

// Test functions
function displayTest($title, $description, $callback) {
    echo "<div class='test-section'>";
    echo "<div class='test-header'>";
    echo "<h5 class='mb-1'><i class='bi bi-arrow-right-circle text-primary me-2'></i>{$title}</h5>";
    echo "<p class='text-muted mb-0'>{$description}</p>";
    echo "</div>";
    echo "<div class='test-body'>";

    try {
        $result = $callback();
        if ($result['success']) {
            echo "<div class='test-result test-success'>";
            echo "<i class='bi bi-check-circle me-2'></i><strong>สำเร็จ:</strong> " . $result['message'];
            if (isset($result['data'])) {
                echo "<div class='code-block mt-2'>" . htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='test-result test-error'>";
            echo "<i class='bi bi-x-circle me-2'></i><strong>ล้มเหลว:</strong> " . $result['message'];
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-result test-error'>";
        echo "<i class='bi bi-exclamation-triangle me-2'></i><strong>ข้อผิดพลาด:</strong> " . $e->getMessage();
        echo "</div>";
    }

    echo "</div>";
    echo "</div>";
}

// Test 1: Database Connection and Structure
displayTest(
    "1. การเชื่อมต่อฐานข้อมูลและโครงสร้าง",
    "ตรวจสอบการเชื่อมต่อฐานข้อมูลและโครงสร้างตารางที่จำเป็น",
    function() {
        try {
            $pdo = getDatabase();

            // Check required tables
            $requiredTables = [
                'housekeeping_jobs',
                'telegram_notifications',
                'hotel_settings',
                'users',
                'rooms',
                'bookings'
            ];

            $missingTables = [];
            foreach ($requiredTables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if (!$stmt->fetch()) {
                    $missingTables[] = $table;
                }
            }

            if (!empty($missingTables)) {
                return [
                    'success' => false,
                    'message' => 'ตารางที่ขาดหายไป: ' . implode(', ', $missingTables)
                ];
            }

            // Check housekeeping_jobs columns
            $stmt = $pdo->query("DESCRIBE housekeeping_jobs");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $requiredColumns = ['booking_id', 'task_type', 'priority', 'telegram_sent', 'started_at', 'completed_at'];
            $missingColumns = array_diff($requiredColumns, $columns);

            if (!empty($missingColumns)) {
                return [
                    'success' => false,
                    'message' => 'คอลัมน์ที่ขาดหายไปใน housekeeping_jobs: ' . implode(', ', $missingColumns)
                ];
            }

            return [
                'success' => true,
                'message' => 'ฐานข้อมูลและโครงสร้างพร้อมใช้งาน',
                'data' => ['tables' => $requiredTables, 'columns' => $columns]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
);

// Test 2: Telegram Service Configuration
displayTest(
    "2. การตั้งค่าบริการ Telegram",
    "ตรวจสอบการกำหนดค่า Telegram Bot และการเชื่อมต่อ",
    function() {
        try {
            $telegramService = new TelegramService();

            // Check if bot token is configured
            $pdo = getDatabase();
            $stmt = $pdo->prepare("SELECT setting_value FROM hotel_settings WHERE setting_key = 'telegram_bot_token'");
            $stmt->execute();
            $botToken = $stmt->fetchColumn();

            if (empty($botToken)) {
                return [
                    'success' => false,
                    'message' => 'ยังไม่ได้ตั้งค่า Bot Token ใน hotel_settings'
                ];
            }

            // Test bot connection (if token is not empty placeholder)
            if ($botToken !== '' && strlen($botToken) > 10) {
                $connectionTest = $telegramService->testBotConnection();
                if (!$connectionTest['success']) {
                    return [
                        'success' => false,
                        'message' => 'ไม่สามารถเชื่อมต่อ Telegram Bot: ' . ($connectionTest['error'] ?? 'Unknown error')
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Bot Token กำหนดค่าถูกต้องและสามารถเชื่อมต่อได้',
                    'data' => $connectionTest['bot_info']
                ];
            } else {
                return [
                    'success' => true,
                    'message' => 'Bot Token ถูกสร้างในฐานข้อมูลแล้ว (ยังไม่ได้ใส่ค่าจริง)',
                    'data' => ['token_configured' => !empty($botToken)]
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
);

// Test 3: Create Test Housekeeping Job
displayTest(
    "3. การสร้างงานทำความสะอาดทดสอบ",
    "สร้างงาน housekeeping จำลองเพื่อทดสอบระบบ",
    function() {
        try {
            $pdo = getDatabase();

            // Get a test room
            $stmt = $pdo->query("SELECT id, room_number FROM rooms LIMIT 1");
            $room = $stmt->fetch();

            if (!$room) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบห้องในระบบสำหรับทดสอบ'
                ];
            }

            // Create test booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (room_id, guest_name, guest_phone, plan_type, base_amount, status, checkin_at, checkout_at)
                VALUES (?, 'ลูกค้าทดสอบ', '0812345678', 'short', 300, 'completed', NOW() - INTERVAL 2 HOUR, NOW())
            ");
            $stmt->execute([$room['id']]);
            $bookingId = $pdo->lastInsertId();

            // Create test housekeeping job
            $stmt = $pdo->prepare("
                INSERT INTO housekeeping_jobs (room_id, booking_id, task_type, priority, status, description, created_by)
                VALUES (?, ?, 'checkout_cleaning', 'normal', 'pending', 'ทดสอบระบบแจ้งเตือน', 1)
            ");
            $stmt->execute([$room['id'], $bookingId]);
            $jobId = $pdo->lastInsertId();

            return [
                'success' => true,
                'message' => "สร้างงานทดสอบสำเร็จ ID: {$jobId}",
                'data' => [
                    'job_id' => $jobId,
                    'booking_id' => $bookingId,
                    'room_number' => $room['room_number']
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
);

// Test 4: Telegram Notification Sending
displayTest(
    "4. การส่งการแจ้งเตือน Telegram",
    "ทดสอบการส่งการแจ้งเตือนไปยังเจ้าหน้าที่ทำความสะอาด",
    function() {
        try {
            $pdo = getDatabase();
            $telegramService = new TelegramService();

            // Get the latest test job
            $stmt = $pdo->query("SELECT id FROM housekeeping_jobs ORDER BY id DESC LIMIT 1");
            $jobId = $stmt->fetchColumn();

            if (!$jobId) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบงาน housekeeping สำหรับทดสอบ'
                ];
            }

            // Check if there are any housekeeping staff with chat IDs
            $stmt = $pdo->query("
                SELECT COUNT(*)
                FROM users
                WHERE role = 'housekeeping'
                AND telegram_chat_id IS NOT NULL
                AND is_active = 1
            ");
            $staffCount = $stmt->fetchColumn();

            if ($staffCount == 0) {
                // Try to send to default chat ID instead
                $stmt = $pdo->prepare("SELECT setting_value FROM hotel_settings WHERE setting_key = 'default_housekeeping_chat_id'");
                $stmt->execute();
                $defaultChatId = $stmt->fetchColumn();

                if (empty($defaultChatId)) {
                    return [
                        'success' => false,
                        'message' => 'ไม่มีเจ้าหน้าที่ทำความสะอาดที่กำหนดค่า Chat ID และไม่มี default_housekeeping_chat_id'
                    ];
                }
            }

            // Send notification
            $result = $telegramService->sendHousekeepingNotification($jobId);

            if ($result === false) {
                return [
                    'success' => false,
                    'message' => 'ส่งการแจ้งเตือนล้มเหลว (ตรวจสอบ log สำหรับรายละเอียด)'
                ];
            }

            // Check notification log
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM telegram_notifications
                WHERE housekeeping_job_id = ?
            ");
            $stmt->execute([$jobId]);
            $notificationCount = $stmt->fetchColumn();

            return [
                'success' => true,
                'message' => "ส่งการแจ้งเตือนสำเร็จ (บันทึก {$notificationCount} รายการ)",
                'data' => [
                    'job_id' => $jobId,
                    'notification_results' => $result,
                    'logged_notifications' => $notificationCount
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
);

// Test 5: Job Status Workflow
displayTest(
    "5. การทำงานของสถานะงาน",
    "ทดสอบการเปลี่ยนสถานะงานจาก pending → in_progress → completed",
    function() {
        try {
            $pdo = getDatabase();

            // Get the latest test job
            $stmt = $pdo->query("SELECT id FROM housekeeping_jobs ORDER BY id DESC LIMIT 1");
            $jobId = $stmt->fetchColumn();

            if (!$jobId) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบงาน housekeeping สำหรับทดสอบ'
                ];
            }

            // Step 1: Start job (pending → in_progress)
            $stmt = $pdo->prepare("
                UPDATE housekeeping_jobs
                SET status = 'in_progress', started_at = NOW()
                WHERE id = ? AND status = 'pending'
            ");
            $started = $stmt->execute([$jobId]);

            if (!$started || $stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถเริ่มงานได้ (อาจเริ่มแล้วหรือไม่พบงาน)'
                ];
            }

            // Step 2: Complete job (in_progress → completed)
            $stmt = $pdo->prepare("
                UPDATE housekeeping_jobs
                SET status = 'completed',
                    completed_at = NOW(),
                    actual_duration = TIMESTAMPDIFF(MINUTE, started_at, NOW()),
                    assigned_to = 1
                WHERE id = ? AND status = 'in_progress'
            ");
            $completed = $stmt->execute([$jobId]);

            if (!$completed || $stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถเสร็จสิ้นงานได้'
                ];
            }

            // Step 3: Update room status to available
            $stmt = $pdo->prepare("
                UPDATE rooms
                SET status = 'available'
                WHERE id = (SELECT room_id FROM housekeeping_jobs WHERE id = ?)
            ");
            $stmt->execute([$jobId]);

            // Get job details for verification
            $stmt = $pdo->prepare("
                SELECT hj.*, r.room_number
                FROM housekeeping_jobs hj
                JOIN rooms r ON hj.room_id = r.id
                WHERE hj.id = ?
            ");
            $stmt->execute([$jobId]);
            $jobDetails = $stmt->fetch();

            return [
                'success' => true,
                'message' => "ทดสอบ workflow สำเร็จ: pending → in_progress → completed",
                'data' => [
                    'job_id' => $jobId,
                    'status' => $jobDetails['status'],
                    'duration_minutes' => $jobDetails['actual_duration'],
                    'room_number' => $jobDetails['room_number']
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
);

// Test 6: Performance Reports
displayTest(
    "6. รายงานประสิทธิภาพ",
    "ทดสอบการสร้างรายงานประสิทธิภาพงานทำความสะอาด",
    function() {
        try {
            $pdo = getDatabase();

            // Test overall statistics query
            $stmt = $pdo->query("
                SELECT
                    COUNT(*) as total_jobs,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_jobs,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_jobs,
                    AVG(CASE WHEN actual_duration IS NOT NULL THEN actual_duration ELSE NULL END) as avg_duration
                FROM housekeeping_jobs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats = $stmt->fetch();

            // Test staff performance query
            $stmt = $pdo->query("
                SELECT
                    u.full_name,
                    COUNT(hj.id) as total_jobs,
                    SUM(CASE WHEN hj.status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
                    AVG(CASE WHEN hj.actual_duration IS NOT NULL THEN hj.actual_duration ELSE NULL END) as avg_duration
                FROM users u
                LEFT JOIN housekeeping_jobs hj ON u.id = hj.assigned_to
                WHERE u.role = 'housekeeping' AND u.is_active = 1
                GROUP BY u.id, u.full_name
                LIMIT 5
            ");
            $staffPerformance = $stmt->fetchAll();

            // Test view existence
            $stmt = $pdo->query("SHOW TABLES LIKE 'housekeeping_performance'");
            $viewExists = $stmt->fetch() !== false;

            return [
                'success' => true,
                'message' => "รายงานประสิทธิภาพทำงานได้ปกติ",
                'data' => [
                    'overall_stats' => $stats,
                    'staff_count' => count($staffPerformance),
                    'view_exists' => $viewExists
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
);

// Test 7: Complete Integration Test
displayTest(
    "7. ทดสอบการทำงานครบวงจร",
    "จำลอง checkout → สร้างงาน → ส่งแจ้งเตือน → เสร็จสิ้นงาน",
    function() {
        try {
            $pdo = getDatabase();
            $telegramService = new TelegramService();

            // Simulate checkout process
            $stmt = $pdo->query("SELECT id, room_number FROM rooms WHERE status = 'available' LIMIT 1");
            $room = $stmt->fetch();

            if (!$room) {
                return [
                    'success' => false,
                    'message' => 'ไม่มีห้องที่พร้อมใช้สำหรับทดสอบ'
                ];
            }

            $pdo->beginTransaction();

            // Create booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (room_id, guest_name, guest_phone, plan_type, base_amount, status, checkin_at, checkout_at)
                VALUES (?, 'Integration Test Guest', '0987654321', 'overnight', 800, 'completed', NOW() - INTERVAL 8 HOUR, NOW())
            ");
            $stmt->execute([$room['id']]);
            $bookingId = $pdo->lastInsertId();

            // Update room to cleaning
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'cleaning' WHERE id = ?");
            $stmt->execute([$room['id']]);

            // Create housekeeping job
            $stmt = $pdo->prepare("
                INSERT INTO housekeeping_jobs (room_id, booking_id, task_type, priority, status, description, created_by)
                VALUES (?, ?, 'checkout_cleaning', 'normal', 'pending', 'ทดสอบครบวงจร', 1)
            ");
            $stmt->execute([$room['id'], $bookingId]);
            $jobId = $pdo->lastInsertId();

            $pdo->commit();

            // Send notification
            $notificationResult = $telegramService->sendHousekeepingNotification($jobId);

            // Simulate job completion
            $pdo->beginTransaction();

            // Start job
            $stmt = $pdo->prepare("UPDATE housekeeping_jobs SET status = 'in_progress', started_at = NOW() WHERE id = ?");
            $stmt->execute([$jobId]);

            // Complete job
            $stmt = $pdo->prepare("
                UPDATE housekeeping_jobs
                SET status = 'completed', completed_at = NOW(), actual_duration = 25, assigned_to = 1
                WHERE id = ?
            ");
            $stmt->execute([$jobId]);

            // Update room to available
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'available' WHERE id = ?");
            $stmt->execute([$room['id']]);

            $pdo->commit();

            // Send completion notification
            $telegramService->sendJobCompletionNotification($jobId, 'Test User');

            return [
                'success' => true,
                'message' => "ทดสอบครบวงจรสำเร็จ! ห้อง {$room['room_number']} กลับสู่สถานะพร้อมใช้",
                'data' => [
                    'room_number' => $room['room_number'],
                    'booking_id' => $bookingId,
                    'job_id' => $jobId,
                    'notification_sent' => $notificationResult !== false
                ]
            ];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollback();
            }
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
);

?>

    <div class="mt-4 p-3 bg-primary text-white rounded">
        <h5><i class="bi bi-info-circle me-2"></i>หมายเหตุการตั้งค่า</h5>
        <div class="small">
            <p class="mb-2"><strong>เพื่อใช้งานระบบแจ้งเตือน Telegram:</strong></p>
            <ol class="mb-2">
                <li>สร้าง Telegram Bot ใหม่ผ่าน @BotFather</li>
                <li>อัปเดต setting 'telegram_bot_token' ในตาราง hotel_settings</li>
                <li>เพิ่ม telegram_chat_id ให้กับ users ที่มี role = 'housekeeping'</li>
                <li>หรือตั้งค่า 'default_housekeeping_chat_id' ในตาราง hotel_settings</li>
            </ol>
            <p class="mb-0">
                <strong>ตัวอย่างการอัปเดต:</strong><br>
                <code class="text-light">UPDATE hotel_settings SET setting_value = 'YOUR_BOT_TOKEN' WHERE setting_key = 'telegram_bot_token';</code>
            </p>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?php echo $baseUrl; ?>" class="btn btn-primary">
            <i class="bi bi-house me-1"></i>กลับหน้าหลัก
        </a>
        <a href="<?php echo $baseUrl; ?>/?r=housekeeping.jobs" class="btn btn-outline-primary">
            <i class="bi bi-list me-1"></i>ดูรายการงาน
        </a>
        <a href="<?php echo $baseUrl; ?>/?r=housekeeping.reports" class="btn btn-outline-primary">
            <i class="bi bi-graph-up me-1"></i>ดูรายงาน
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>