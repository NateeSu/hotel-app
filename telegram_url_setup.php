<?php
/**
 * Telegram URL Setup Guide
 * คู่มือการตั้งค่า URL สำหรับให้ Telegram สามารถเข้าถึงได้
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
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/lib/telegram_service.php';

// Handle URL update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $telegramService = new TelegramService();

        if ($_POST['action'] === 'update_external_url') {
            $externalUrl = trim($_POST['external_url']);
            $telegramService->setSetting('external_url', $externalUrl);
            $success = "อัปเดต External URL เรียบร้อย: $externalUrl";
        } elseif ($_POST['action'] === 'update_ngrok_url') {
            $ngrokUrl = trim($_POST['ngrok_url']);
            $telegramService->setSetting('ngrok_url', $ngrokUrl);
            $success = "อัปเดต Ngrok URL เรียบร้อย: $ngrokUrl";
        }

    } catch (Exception $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// Get current settings
try {
    $pdo = getDatabase();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM hotel_settings WHERE setting_key IN ('external_url', 'ngrok_url')");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $settings = [];
}

// Get current base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$appPath = '/hotel-app';
$currentBaseUrl = $protocol . '://' . $host . $appPath;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่า URL สำหรับ Telegram - T008</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .url-example { background: #f8f9fa; padding: 0.75rem; border-radius: 0.375rem; font-family: monospace; }
        .step-number { width: 40px; height: 40px; background: #0d6efd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="text-primary">
            <i class="bi bi-globe me-2"></i>
            ตั้งค่า URL สำหรับ Telegram
        </h1>
        <p class="lead">แก้ปัญหาลิงก์ใน Telegram กดไม่ได้</p>
    </div>

    <!-- Problem Explanation -->
    <div class="alert alert-warning mb-4">
        <h4><i class="bi bi-exclamation-triangle me-2"></i>ปัญหาที่พบ</h4>
        <p><strong>ลิงก์ใน Telegram กดไม่ได้</strong> เพราะ Telegram ไม่สามารถเข้าถึง URL ที่ใช้ <code>localhost</code> หรือ IP ภายในได้</p>
        <p class="mb-0"><strong>URL ปัจจุบัน:</strong> <code><?php echo htmlspecialchars($currentBaseUrl); ?></code></p>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($success)): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-x-circle me-2"></i><?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <!-- Solutions -->
    <div class="row g-4">
        <!-- Solution 1: Ngrok -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-1-circle me-2"></i>
                        วิธีที่ 1: ใช้ Ngrok (แนะนำ)
                    </h4>
                </div>
                <div class="card-body">
                    <p><strong>Ngrok</strong> เป็นเครื่องมือที่ทำให้ localhost เข้าถึงได้จากอินเทอร์เน็ต</p>

                    <h6>ขั้นตอน:</h6>
                    <ol>
                        <li>ดาวน์โหลด Ngrok จาก <a href="https://ngrok.com" target="_blank">ngrok.com</a></li>
                        <li>รันคำสั่ง: <code>ngrok http 80</code></li>
                        <li>คัดลอก URL ที่ได้ เช่น: <code>https://abc123.ngrok.io</code></li>
                        <li>ใส่ URL ด้านล่าง:</li>
                    </ol>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_ngrok_url">
                        <div class="mb-3">
                            <label class="form-label">Ngrok URL:</label>
                            <input type="url" class="form-control" name="ngrok_url"
                                   value="<?php echo htmlspecialchars($settings['ngrok_url'] ?? ''); ?>"
                                   placeholder="https://abc123.ngrok.io">
                            <div class="form-text">ใส่ URL ที่ได้จาก ngrok (ไม่ต้องใส่ /hotel-app)</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>บันทึก Ngrok URL
                        </button>
                    </form>

                    <div class="mt-3">
                        <h6>สถานะปัจจุบัน:</h6>
                        <?php if (!empty($settings['ngrok_url'])): ?>
                            <div class="alert alert-success">
                                <strong>ตั้งค่าแล้ว:</strong> <?php echo htmlspecialchars($settings['ngrok_url']); ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary">ยังไม่ได้ตั้งค่า</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solution 2: External Server -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-2-circle me-2"></i>
                        วิธีที่ 2: เซิร์ฟเวอร์ภายนอก
                    </h4>
                </div>
                <div class="card-body">
                    <p>หากคุณมีเซิร์ฟเวอร์หรือโดเมนที่เข้าถึงได้จากอินเทอร์เน็ต</p>

                    <h6>ตัวอย่าง:</h6>
                    <ul>
                        <li><code>https://yourdomain.com</code></li>
                        <li><code>https://yourserver.com/hotel-app</code></li>
                        <li><code>http://your-ip-address</code></li>
                    </ul>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_external_url">
                        <div class="mb-3">
                            <label class="form-label">External URL:</label>
                            <input type="url" class="form-control" name="external_url"
                                   value="<?php echo htmlspecialchars($settings['external_url'] ?? ''); ?>"
                                   placeholder="https://yourdomain.com/hotel-app">
                            <div class="form-text">ใส่ URL ที่เข้าถึงได้จากอินเทอร์เน็ต</div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>บันทึก External URL
                        </button>
                    </form>

                    <div class="mt-3">
                        <h6>สถานะปัจจุบัน:</h6>
                        <?php if (!empty($settings['external_url'])): ?>
                            <div class="alert alert-success">
                                <strong>ตั้งค่าแล้ว:</strong> <?php echo htmlspecialchars($settings['external_url']); ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary">ยังไม่ได้ตั้งค่า</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Section -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">
                <i class="bi bi-check-circle me-2"></i>
                ทดสอบ URL ที่จะใช้
            </h4>
        </div>
        <div class="card-body">
            <?php
            // Generate URL that will be used
            $testTelegramService = new TelegramService();
            $testJobUrl = null;

            // Simulate URL generation
            if (!empty($settings['external_url'])) {
                $testJobUrl = $settings['external_url'] . "/?r=housekeeping.job&id=1";
            } elseif (!empty($settings['ngrok_url'])) {
                $testJobUrl = $settings['ngrok_url'] . "/hotel-app/?r=housekeeping.job&id=1";
            } else {
                $testJobUrl = $currentBaseUrl . "/?r=housekeeping.job&id=1";
            }
            ?>

            <h6>URL ที่จะส่งใน Telegram:</h6>
            <div class="url-example mb-3">
                <?php echo htmlspecialchars($testJobUrl); ?>
            </div>

            <div class="d-flex gap-2">
                <a href="<?php echo htmlspecialchars($testJobUrl); ?>" target="_blank" class="btn btn-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i>ทดสอบเปิด URL
                </a>
                <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo htmlspecialchars($testJobUrl); ?>')">
                    <i class="bi bi-clipboard me-1"></i>คัดลอก URL
                </button>
            </div>

            <div class="mt-3">
                <?php if (strpos($testJobUrl, 'localhost') !== false || strpos($testJobUrl, '127.0.0.1') !== false): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>เตือน:</strong> URL นี้ยังคงใช้ localhost - Telegram จะไม่สามารถเข้าถึงได้
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>ดีเยี่ยม!</strong> URL นี้น่าจะเข้าถึงได้จาก Telegram
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">
                <i class="bi bi-lightbulb me-2"></i>
                คำแนะนำเพิ่มเติม
            </h4>
        </div>
        <div class="card-body">
            <h6>สำหรับการใช้งานจริง:</h6>
            <ul>
                <li><strong>Ngrok (ฟรี):</strong> เหมาะสำหรับทดสอบ URL จะเปลี่ยนทุกครั้งที่รีสตาร์ท</li>
                <li><strong>Ngrok Pro:</strong> URL ไม่เปลี่ยน สามารถกำหนด subdomain ได้</li>
                <li><strong>VPS/Cloud:</strong> เหมาะสำหรับใช้งานจริง เสถียรที่สุด</li>
                <li><strong>Dynamic DNS:</strong> สำหรับ IP ที่เปลี่ยนบ่อย</li>
            </ul>

            <h6>การตรวจสอบ:</h6>
            <ol>
                <li>ตั้งค่า URL ตามวิธีข้างต้น</li>
                <li>คลิก "ทดสอบเปิด URL" เพื่อดูว่าเข้าถึงได้หรือไม่</li>
                <li>ส่งการแจ้งเตือน Telegram ใหม่</li>
                <li>ลองคลิกลิงก์ใน Telegram</li>
            </ol>
        </div>
    </div>

    <div class="text-center mt-4">
        <div class="btn-group" role="group">
            <a href="demo_t008.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>กลับ Demo
            </a>
            <a href="test_telegram_links.php" class="btn btn-outline-success">
                <i class="bi bi-link me-1"></i>ทดสอบลิงก์
            </a>
            <a href="test_housekeeping_system.php" class="btn btn-outline-info">
                <i class="bi bi-gear me-1"></i>ทดสอบระบบ
            </a>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('คัดลอก URL เรียบร้อย!');
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>