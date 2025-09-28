<?php
/**
 * Telegram Setup Instructions for T008
 * วิธีการหา Chat ID จริงและแก้ไขระบบ
 */

// Define constants first
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Start session and initialize application
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>วิธีตั้งค่า Telegram Chat ID สำหรับ T008</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .step-card { border-left: 4px solid #0088cc; }
        .code-block { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; font-family: monospace; }
        .telegram-blue { color: #0088cc; }
        .alert-telegram { background: linear-gradient(135deg, #0088cc, #229ED9); color: white; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="telegram-blue">
            <i class="bi bi-telegram me-3"></i>
            วิธีตั้งค่า Telegram Chat ID
        </h1>
        <p class="lead">สำหรับระบบ T008 Housekeeping Notification</p>
    </div>

    <!-- Current Status -->
    <div class="alert alert-telegram mb-4">
        <h4 class="mb-3">
            <i class="bi bi-info-circle me-2"></i>
            สถานะปัจจุบัน
        </h4>
        <div class="row">
            <div class="col-md-6">
                ✅ <strong>Bot Token:</strong> ตั้งค่าถูกต้องแล้ว<br>
                ✅ <strong>Bot Connection:</strong> เชื่อมต่อได้<br>
                ✅ <strong>ระบบทำงาน:</strong> พร้อมใช้งาน
            </div>
            <div class="col-md-6">
                ⚠️ <strong>Chat ID:</strong> ใช้ค่าทดสอบอยู่<br>
                🔧 <strong>ต้องแก้ไข:</strong> ใส่ Chat ID จริง<br>
                📱 <strong>ผลลัพธ์:</strong> จะส่งแจ้งเตือนได้
            </div>
        </div>
    </div>

    <!-- Step 1: Get Chat ID -->
    <div class="card step-card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">
                <i class="bi bi-1-circle me-2"></i>
                วิธีหา Chat ID
            </h3>
        </div>
        <div class="card-body">
            <h5 class="telegram-blue">สำหรับแชทส่วนตัว:</h5>
            <ol>
                <li>เปิด Telegram และหาบอท: <code>@hotel_housekeeping_bot</code></li>
                <li>ส่งข้อความอะไรก็ได้ให้บอท เช่น: <kbd>/start</kbd></li>
                <li>เปิดในเว็บเบราว์เซอร์:</li>
            </ol>

            <div class="code-block">
                https://api.telegram.org/bot<?php
                try {
                    $pdo = getDatabase();
                    $stmt = $pdo->prepare("SELECT setting_value FROM hotel_settings WHERE setting_key = 'telegram_bot_token'");
                    $stmt->execute();
                    $token = $stmt->fetchColumn();
                    echo htmlspecialchars($token ?: 'YOUR_BOT_TOKEN');
                } catch (Exception $e) {
                    echo 'YOUR_BOT_TOKEN';
                }
                ?>/getUpdates
            </div>

            <ol start="4">
                <li>ในผลลัพธ์ JSON หา: <code>"chat":{"id":</code></li>
                <li>เลขที่อยู่หลัง <code>"id":</code> คือ Chat ID ของคุณ</li>
            </ol>

            <div class="alert alert-info">
                <strong>ตัวอย่าง:</strong> หากเห็น <code>"chat":{"id":987654321</code> แสดงว่า Chat ID ของคุณคือ <code>987654321</code>
            </div>

            <h5 class="telegram-blue mt-4">สำหรับกลุ่ม:</h5>
            <ol>
                <li>เพิ่มบอทเข้ากลุ่ม</li>
                <li>ส่งข้อความในกลุ่ม</li>
                <li>ทำขั้นตอนเดียวกัน (Chat ID ของกลุ่มจะเป็นเลขลบ เช่น <code>-123456789</code>)</li>
            </ol>
        </div>
    </div>

    <!-- Step 2: Update Database -->
    <div class="card step-card mb-4">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0">
                <i class="bi bi-2-circle me-2"></i>
                อัปเดตฐานข้อมูล
            </h3>
        </div>
        <div class="card-body">
            <p>เมื่อได้ Chat ID แล้ว ให้รัน SQL คำสั่งใดคำสั่งหนึ่งต่อไปนี้:</p>

            <h5 class="text-success">วิธีที่ 1: ใช้ Default Chat ID (แนะนำ)</h5>
            <p class="text-muted">ส่งแจ้งเตือนไปยัง Chat เดียว (บุคคลหรือกลุ่ม)</p>

            <div class="code-block">
UPDATE hotel_settings
SET setting_value = '<span class="text-danger">YOUR_REAL_CHAT_ID</span>'
WHERE setting_key = 'default_housekeeping_chat_id';
            </div>

            <h5 class="text-success mt-4">วิธีที่ 2: Chat ID แยกรายบุคคล</h5>
            <p class="text-muted">ส่งแจ้งเตือนไปยังพนักงานแต่ละคนโดยตรง</p>

            <div class="code-block">
-- พนักงานคนที่ 1
UPDATE users
SET telegram_chat_id = '<span class="text-danger">CHAT_ID_PERSON_1</span>'
WHERE username = 'housekeeper1';

-- พนักงานคนที่ 2
UPDATE users
SET telegram_chat_id = '<span class="text-danger">CHAT_ID_PERSON_2</span>'
WHERE username = 'housekeeper2';
            </div>

            <div class="alert alert-warning">
                <strong>หมายเหตุ:</strong> แทนที่ <code>YOUR_REAL_CHAT_ID</code> ด้วยเลข Chat ID ที่ได้จากขั้นตอนที่ 1
            </div>
        </div>
    </div>

    <!-- Step 3: Test -->
    <div class="card step-card mb-4">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0">
                <i class="bi bi-3-circle me-2"></i>
                ทดสอบระบบ
            </h3>
        </div>
        <div class="card-body">
            <p>หลังจากอัปเดต Chat ID แล้ว ให้ทดสอบระบบ:</p>

            <div class="d-grid gap-2 d-md-flex">
                <a href="test_housekeeping_system.php" class="btn btn-primary">
                    <i class="bi bi-gear me-2"></i>ทดสอบระบบใหม่
                </a>
                <a href="demo_t008.php" class="btn btn-outline-primary">
                    <i class="bi bi-eye me-2"></i>ดู Demo
                </a>
            </div>

            <div class="alert alert-success mt-3">
                <strong>เมื่อทดสอบสำเร็จ:</strong> การทดสอบข้อ 4 "การส่งการแจ้งเตือน Telegram" จะแสดงผล <code>"success": true</code>
            </div>
        </div>
    </div>

    <!-- Current Settings -->
    <div class="card step-card mb-4">
        <div class="card-header bg-info text-white">
            <h3 class="mb-0">
                <i class="bi bi-gear me-2"></i>
                การตั้งค่าปัจจุบัน
            </h3>
        </div>
        <div class="card-body">
            <?php
            try {
                $pdo = getDatabase();

                // Get current settings
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM hotel_settings WHERE setting_key LIKE '%telegram%'");
                $settings = $stmt->fetchAll();

                // Get users with chat IDs
                $stmt = $pdo->query("SELECT username, full_name, telegram_chat_id FROM users WHERE role = 'housekeeping'");
                $users = $stmt->fetchAll();

            } catch (Exception $e) {
                $settings = [];
                $users = [];
            }
            ?>

            <h5>การตั้งค่าระบบ:</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settings as $setting): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($setting['setting_key']); ?></code></td>
                            <td>
                                <?php
                                $value = $setting['setting_value'];
                                if ($setting['setting_key'] === 'telegram_bot_token') {
                                    $value = $value ? substr($value, 0, 10) . '...' : 'ไม่ได้ตั้งค่า';
                                }
                                echo htmlspecialchars($value);
                                ?>
                            </td>
                            <td>
                                <?php if ($setting['setting_value'] && !in_array($setting['setting_value'], ['123456789', '987654321', '', 'YOUR_CHAT_ID_HERE'])): ?>
                                    <span class="badge bg-success">ตั้งค่าแล้ว</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">ต้องแก้ไข</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h5 class="mt-4">พนักงานทำความสะอาด:</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>ชื่อ</th>
                            <th>Chat ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($user['username']); ?></code></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['telegram_chat_id'] ?: '-'); ?></td>
                            <td>
                                <?php if ($user['telegram_chat_id'] && !in_array($user['telegram_chat_id'], ['123456789', '987654321'])): ?>
                                    <span class="badge bg-success">ตั้งค่าแล้ว</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ใช้ default</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Fix -->
    <div class="alert alert-success">
        <h4>
            <i class="bi bi-lightning-charge me-2"></i>
            แก้ไขด่วน
        </h4>
        <p class="mb-3">หากต้องการใช้งานทันที ให้ใส่ Chat ID ของคุณเองในคำสั่งนี้:</p>
        <div class="code-block">
UPDATE hotel_settings SET setting_value = '<span class="text-danger">YOUR_CHAT_ID</span>' WHERE setting_key = 'default_housekeeping_chat_id';
        </div>
        <small class="text-muted">แทนที่ YOUR_CHAT_ID ด้วยเลข Chat ID ของคุณ แล้วรันใน phpMyAdmin</small>
    </div>

    <div class="text-center">
        <a href="demo_t008.php" class="btn btn-primary btn-lg me-3">
            <i class="bi bi-arrow-left me-2"></i>กลับ Demo
        </a>
        <a href="test_housekeeping_system.php" class="btn btn-success btn-lg">
            <i class="bi bi-play-circle me-2"></i>ทดสอบใหม่
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>