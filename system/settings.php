<?php
/**
 * Hotel Management System - Hotel Settings
 *
 * จัดการตั้งค่าโรงแรม (ชื่อ, ที่อยู่, เบอร์โทร)
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
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/router.php';
require_once __DIR__ . '/../templates/partials/flash.php';

// Require admin access
requireLogin(['admin']);

// Set page variables
$pageTitle = 'ตั้งค่าระบบ - Hotel Management System';
$pageDescription = 'ตั้งค่าข้อมูลพื้นฐานของโรงแรม';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash_error('Invalid CSRF token');
        redirectToRoute('system.settings');
    }

    try {
        $pdo = getDatabase();

        // Update hotel name
        if (isset($_POST['hotel_name'])) {
            $stmt = $pdo->prepare("
                INSERT INTO hotel_settings (setting_key, setting_value)
                VALUES ('hotel_name', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([trim($_POST['hotel_name'])]);
        }

        // Update hotel address
        if (isset($_POST['hotel_address'])) {
            $stmt = $pdo->prepare("
                INSERT INTO hotel_settings (setting_key, setting_value)
                VALUES ('hotel_address', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([trim($_POST['hotel_address'])]);
        }

        // Update hotel phone
        if (isset($_POST['hotel_phone'])) {
            $stmt = $pdo->prepare("
                INSERT INTO hotel_settings (setting_key, setting_value)
                VALUES ('hotel_phone', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([trim($_POST['hotel_phone'])]);
        }

        // Update hotel email
        if (isset($_POST['hotel_email'])) {
            $stmt = $pdo->prepare("
                INSERT INTO hotel_settings (setting_key, setting_value)
                VALUES ('hotel_email', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([trim($_POST['hotel_email'])]);
        }

        flash_success('บันทึกการตั้งค่าเรียบร้อยแล้ว');
        redirectToRoute('system.settings');

    } catch (Exception $e) {
        flash_error('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

// Fetch current settings
try {
    $pdo = getDatabase();

    // Create table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS hotel_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type ENUM('text', 'textarea', 'number', 'boolean') DEFAULT 'text',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_setting_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Add missing columns if they don't exist
    try {
        $pdo->exec("ALTER TABLE hotel_settings ADD COLUMN setting_type ENUM('text', 'textarea', 'number', 'boolean') DEFAULT 'text' AFTER setting_value");
    } catch (Exception $e) {
        // Column already exists
    }

    try {
        $pdo->exec("ALTER TABLE hotel_settings ADD COLUMN description TEXT AFTER setting_type");
    } catch (Exception $e) {
        // Column already exists
    }

    try {
        $pdo->exec("ALTER TABLE hotel_settings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER description");
    } catch (Exception $e) {
        // Column already exists
    }

    try {
        $pdo->exec("ALTER TABLE hotel_settings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    } catch (Exception $e) {
        // Column already exists
    }

    // Insert default settings if table is empty
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM hotel_settings");
    if ($count_stmt->fetchColumn() == 0) {
        $default_settings = [
            ['hotel_name', 'Hotel Management System'],
            ['hotel_address', '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110'],
            ['hotel_phone', '02-123-4567'],
            ['hotel_email', 'info@hotel.com']
        ];

        $insert_stmt = $pdo->prepare("
            INSERT INTO hotel_settings (setting_key, setting_value)
            VALUES (?, ?)
        ");

        foreach ($default_settings as $setting) {
            $insert_stmt->execute($setting);
        }
    }

    $stmt = $pdo->query("SELECT setting_key, setting_value FROM hotel_settings");
    $settingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $settings = [];
    foreach ($settingsData as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }

    // Set defaults if not found
    $settings['hotel_name'] = $settings['hotel_name'] ?? 'Hotel Management System';
    $settings['hotel_address'] = $settings['hotel_address'] ?? '';
    $settings['hotel_phone'] = $settings['hotel_phone'] ?? '';
    $settings['hotel_email'] = $settings['hotel_email'] ?? '';

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage());
    $settings = [
        'hotel_name' => 'Hotel Management System',
        'hotel_address' => '',
        'hotel_phone' => '',
        'hotel_email' => ''
    ];
}

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-sliders text-primary me-2"></i>
                        ตั้งค่าระบบ
                    </h1>
                    <p class="text-muted mb-0">จัดการข้อมูลพื้นฐานของโรงแรม</p>
                </div>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $baseUrl; ?>/?r=dashboard">หน้าหลัก</a></li>
                        <li class="breadcrumb-item">ระบบ</li>
                        <li class="breadcrumb-item active">ตั้งค่าระบบ</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-building me-2"></i>
                                ข้อมูลโรงแรม
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php echo csrf_field(); ?>

                                <!-- Hotel Name -->
                                <div class="mb-4">
                                    <label for="hotel_name" class="form-label">
                                        <i class="bi bi-shop me-1"></i>
                                        ชื่อโรงแรม <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-lg"
                                           id="hotel_name"
                                           name="hotel_name"
                                           value="<?php echo htmlspecialchars($settings['hotel_name']); ?>"
                                           placeholder="กรอกชื่อโรงแรม"
                                           required>
                                    <small class="text-muted">ชื่อนี้จะแสดงในหน้า Login, Navbar และใบเสร็จ</small>
                                </div>

                                <!-- Hotel Address -->
                                <div class="mb-4">
                                    <label for="hotel_address" class="form-label">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        ที่อยู่โรงแรม
                                    </label>
                                    <textarea class="form-control"
                                              id="hotel_address"
                                              name="hotel_address"
                                              rows="3"
                                              placeholder="กรอกที่อยู่โรงแรม"><?php echo htmlspecialchars($settings['hotel_address']); ?></textarea>
                                    <small class="text-muted">ที่อยู่นี้จะแสดงในหัวใบเสร็จ</small>
                                </div>

                                <!-- Hotel Phone -->
                                <div class="mb-4">
                                    <label for="hotel_phone" class="form-label">
                                        <i class="bi bi-telephone me-1"></i>
                                        เบอร์โทรศัพท์ <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel"
                                           class="form-control"
                                           id="hotel_phone"
                                           name="hotel_phone"
                                           value="<?php echo htmlspecialchars($settings['hotel_phone']); ?>"
                                           placeholder="02-xxx-xxxx หรือ 0x-xxxx-xxxx"
                                           required>
                                    <small class="text-muted">เบอร์นี้จะแสดงในหน้า Login และใบเสร็จ</small>
                                </div>

                                <!-- Hotel Email -->
                                <div class="mb-4">
                                    <label for="hotel_email" class="form-label">
                                        <i class="bi bi-envelope me-1"></i>
                                        อีเมล
                                    </label>
                                    <input type="email"
                                           class="form-control"
                                           id="hotel_email"
                                           name="hotel_email"
                                           value="<?php echo htmlspecialchars($settings['hotel_email']); ?>"
                                           placeholder="info@hotel.com">
                                    <small class="text-muted">อีเมลสำหรับติดต่อ (ไม่บังคับ)</small>
                                </div>

                                <!-- Current Info Display -->
                                <div class="alert alert-light border">
                                    <h6 class="alert-heading">
                                        <i class="bi bi-info-circle me-2"></i>
                                        ข้อมูลปัจจุบัน
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>ชื่อโรงแรม:</strong><br>
                                            <span class="text-primary"><?php echo htmlspecialchars($settings['hotel_name']); ?></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>เบอร์โทร:</strong><br>
                                            <span class="text-success"><?php echo htmlspecialchars($settings['hotel_phone'] ?: 'ยังไม่ได้ตั้งค่า'); ?></span>
                                        </div>
                                    </div>
                                    <?php if ($settings['hotel_address']): ?>
                                    <hr class="my-2">
                                    <strong>ที่อยู่:</strong><br>
                                    <span class="text-muted"><?php echo nl2br(htmlspecialchars($settings['hotel_address'])); ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo $baseUrl; ?>/?r=dashboard" class="btn btn-outline-secondary me-md-2">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        กลับ
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        บันทึกการตั้งค่า
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                หมายเหตุ
                            </h6>
                            <ul class="mb-0">
                                <li><strong>ชื่อโรงแรม</strong> จะแสดงในหน้า Login, Navbar และหัวใบเสร็จ</li>
                                <li><strong>ที่อยู่</strong> จะแสดงในหัวใบเสร็จเท่านั้น</li>
                                <li><strong>เบอร์โทร</strong> จะแสดงในหน้า Login และหัวใบเสร็จ</li>
                                <li>การเปลี่ยนแปลงจะมีผลทันทีหลังจากบันทึก</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>