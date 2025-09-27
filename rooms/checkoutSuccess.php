<?php
/**
 * Hotel Management System - Checkout Success Page
 *
 * Shows checkout completion with receipt options
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

// Require login with reception role or higher
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'Check-out สำเร็จ - Hotel Management System';
$pageDescription = 'การ Check-out เสร็จสิ้น พร้อมใบเสร็จ';

// Get checkout success data from session
$checkoutData = $_SESSION['checkout_success'] ?? null;

if (!$checkoutData) {
    flash_error('ไม่พบข้อมูลการ Check-out');
    redirectToRoute('rooms.board');
}

// Clear session data after use
unset($_SESSION['checkout_success']);

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Header -->
            <div class="text-center mb-4">
                <div class="success-icon mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="h2 text-success mb-2">
                    <i class="bi bi-check-circle me-2"></i>
                    Check-out สำเร็จ!
                </h1>
                <p class="lead text-muted">การ Check-out เสร็จสิ้นเรียบร้อยแล้ว</p>
            </div>

            <!-- Checkout Summary Card -->
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>
                        สรุปการ Check-out
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">ข้อมูลการเข้าพัก</h6>
                            <div class="mb-2">
                                <strong>รหัสการจอง:</strong>
                                <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($checkoutData['booking_code']); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong>ห้อง:</strong>
                                <span class="badge bg-info ms-2"><?php echo htmlspecialchars($checkoutData['room_number']); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong>ชื่อแขก:</strong>
                                <?php echo htmlspecialchars($checkoutData['guest_name']); ?>
                            </div>
                            <div class="mb-2">
                                <strong>วันที่ Check-out:</strong>
                                <?php echo date('d/m/Y H:i'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">ข้อมูลการชำระเงิน</h6>
                            <div class="mb-2">
                                <strong>ยอดชำระทั้งหมด:</strong>
                                <span class="h5 text-success ms-2">
                                    <?php echo money_format_thb($checkoutData['total_amount']); ?>
                                </span>
                            </div>
                            <div class="mb-2">
                                <strong>สถานะการชำระ:</strong>
                                <span class="badge bg-success ms-2">ชำระเรียบร้อย</span>
                            </div>
                            <div class="mb-2">
                                <strong>เลขที่ใบเสร็จ:</strong>
                                <span class="badge bg-warning text-dark ms-2"><?php echo htmlspecialchars($checkoutData['receipt_number']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        จัดการใบเสร็จ
                    </h5>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted mb-4">ใบเสร็จสำหรับการ Check-out นี้ถูกสร้างเรียบร้อยแล้ว</p>

                    <div class="row g-3 justify-content-center">
                        <div class="col-md-6">
                            <a href="<?php echo htmlspecialchars($checkoutData['receipt_url']); ?>"
                               target="_blank"
                               class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-eye me-2"></i>
                                ดูใบเสร็จ
                            </a>
                            <small class="text-muted d-block mt-1 text-center">เปิดในหน้าต่างใหม่ และสามารถพิมพ์ได้จากหน้าใบเสร็จ</small>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo routeUrl('receipts.history'); ?>"
                               class="btn btn-outline-secondary btn-lg w-100">
                                <i class="bi bi-folder me-2"></i>
                                ประวัติใบเสร็จ
                            </a>
                            <small class="text-muted d-block mt-1 text-center">ดูใบเสร็จทั้งหมด</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-arrow-right-circle me-2"></i>
                        ขั้นตอนต่อไป
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-house text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">สถานะห้อง</h6>
                                    <p class="text-muted mb-0">
                                        ห้อง <?php echo htmlspecialchars($checkoutData['room_number']); ?>
                                        เปลี่ยนเป็นสถานะ <span class="badge bg-warning text-dark">"ทำความสะอาด"</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-brush text-info" style="font-size: 1.5rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">งานแม่บ้าน</h6>
                                    <p class="text-muted mb-0">
                                        งานทำความสะอาดถูกสร้างอัตโนมัติสำหรับทีมแม่บ้าน
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="d-flex gap-3 justify-content-center mb-4">
                <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-grid-3x3-gap me-2"></i>
                    กลับสู่แผงควบคุมห้อง
                </a>
                <a href="<?php echo routeUrl('dashboard'); ?>" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-house me-2"></i>
                    หน้าหลัก
                </a>
            </div>

            <!-- Success Animation -->
            <div class="text-center">
                <div class="success-animation">
                    <div class="checkmark-circle">
                        <div class="background"></div>
                        <div class="checkmark draw"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-animation {
    margin: 2rem auto;
}

.checkmark-circle {
    width: 80px;
    height: 80px;
    position: relative;
    display: inline-block;
    vertical-align: top;
}

.checkmark-circle .background {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #28a745;
    position: absolute;
}

.checkmark-circle .checkmark {
    border-radius: 5px;
}

.checkmark-circle .checkmark.draw:after {
    animation-delay: 100ms;
    animation-duration: 3s;
    animation-timing-function: ease;
    animation-name: checkmark;
    transform: scaleX(-1) rotate(135deg);
    animation-fill-mode: forwards;
}

.checkmark-circle .checkmark:after {
    opacity: 1;
    height: 60px;
    width: 30px;
    transform-origin: left top;
    border-right: 4px solid white;
    border-top: 4px solid white;
    border-radius: 2.5px !important;
    content: '';
    left: 25px;
    top: 50px;
    position: absolute;
}

@keyframes checkmark {
    0% {
        height: 0;
        width: 0;
        opacity: 1;
    }
    20% {
        height: 0;
        width: 30px;
        opacity: 1;
    }
    40% {
        height: 60px;
        width: 30px;
        opacity: 1;
    }
    100% {
        height: 60px;
        width: 30px;
        opacity: 1;
    }
}
</style>

<script>
// Auto-redirect after 30 seconds if no action
setTimeout(() => {
    if (confirm('ต้องการกลับสู่แผงควบคุมห้องหรือไม่?')) {
        window.location.href = '<?php echo routeUrl("rooms.board"); ?>';
    }
}, 30000);
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>