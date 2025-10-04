<?php
/**
 * Simple Room Rates Management
 */

// Only initialize if not already loaded by index.php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Asia/Bangkok');

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appPath = '/hotel-app';
    $baseUrl = $protocol . '://' . $host . $appPath;
    $GLOBALS['baseUrl'] = $baseUrl;

    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/csrf.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../templates/partials/flash.php';

} else {
    // Already initialized by index.php
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

requireLogin(['admin']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash_error('Invalid CSRF token');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    try {
        $pdo = getDatabase();

        // Update short-term rate
        if (isset($_POST['short_price']) && isset($_POST['short_hours'])) {
            $stmt = $pdo->prepare("UPDATE room_rates SET price = ?, duration_hours = ? WHERE rate_type = 'short'");
            $stmt->execute([floatval($_POST['short_price']), intval($_POST['short_hours'])]);
        }

        // Update overnight rate
        if (isset($_POST['overnight_price']) && isset($_POST['overnight_hours'])) {
            $stmt = $pdo->prepare("UPDATE room_rates SET price = ?, duration_hours = ? WHERE rate_type = 'overnight'");
            $stmt->execute([floatval($_POST['overnight_price']), intval($_POST['overnight_hours'])]);
        }

        flash_success('อัปเดตอัตราค่าห้องเรียบร้อยแล้ว');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;

    } catch (Exception $e) {
        flash_error('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

// Get current rates
try {
    $pdo = getDatabase();

    $stmt = $pdo->query("SELECT * FROM room_rates WHERE is_active = 1");
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $shortRate = ['price' => 200, 'duration_hours' => 3];
    $overnightRate = ['price' => 350, 'duration_hours' => 12];

    foreach ($rates as $rate) {
        if ($rate['rate_type'] === 'short') {
            $shortRate = $rate;
        } elseif ($rate['rate_type'] === 'overnight') {
            $overnightRate = $rate;
        }
    }

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาด: ' . $e->getMessage());
    $shortRate = ['price' => 200, 'duration_hours' => 3];
    $overnightRate = ['price' => 350, 'duration_hours' => 12];
}

require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="bi bi-currency-dollar text-success me-2"></i>
                จัดการอัตราค่าห้อง
            </h1>

            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">ตั้งค่าอัตราค่าห้อง</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php echo csrf_field(); ?>

                                <!-- Short-term Rate -->
                                <div class="mb-4">
                                    <h6 class="text-warning">การเข้าพักแบบชั่วคราว</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label">ราคา (บาท)</label>
                                            <input type="number" class="form-control" name="short_price"
                                                   value="<?php echo $shortRate['price']; ?>" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">ระยะเวลา (ชั่วโมง)</label>
                                            <input type="number" class="form-control" name="short_hours"
                                                   value="<?php echo $shortRate['duration_hours']; ?>" min="1" max="24" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overnight Rate -->
                                <div class="mb-4">
                                    <h6 class="text-primary">การเข้าพักแบบค้างคืน</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label">ราคา (บาท)</label>
                                            <input type="number" class="form-control" name="overnight_price"
                                                   value="<?php echo $overnightRate['price']; ?>" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">ระยะเวลา (ชั่วโมง)</label>
                                            <input type="number" class="form-control" name="overnight_hours"
                                                   value="<?php echo $overnightRate['duration_hours']; ?>" min="1" max="24" required readonly>
                                            <div class="form-text text-info">
                                                <i class="bi bi-info-circle me-1"></i>
                                                การพักค้างคืนคิดเป็นจำนวนคืน (เช็คเอาท์ถึง 12:00)
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Status -->
                                <div class="alert alert-light">
                                    <h6>อัตราปัจจุบัน</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            ชั่วคราว: <strong>฿<?php echo number_format($shortRate['price']); ?> / <?php echo $shortRate['duration_hours']; ?> ชม.</strong>
                                        </div>
                                        <div class="col-6">
                                            ค้างคืน: <strong>฿<?php echo number_format($overnightRate['price']); ?> / คืน</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo $baseUrl; ?>/?r=rooms.board" class="btn btn-outline-secondary">กลับ</a>
                                    <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Business Rules Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                เงื่อนไขทางธุรกิจใหม่
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">
                                        <i class="bi bi-clock me-1"></i>
                                        การพักแบบชั่วคราว
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>คิดค่าบริการตามชั่วโมง</li>
                                        <li><i class="bi bi-check text-success me-2"></i>ระยะเวลามาตรฐาน: 3 ชั่วโมง</li>
                                        <li><i class="bi bi-check text-success me-2"></i>เกินเวลา: 100 บาท/ชั่วโมง</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success">
                                        <i class="bi bi-moon me-1"></i>
                                        การพักแบบค้างคืน
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>คิดค่าบริการตามจำนวนคืน (ไม่ใช่ชั่วโมง)</li>
                                        <li><i class="bi bi-check text-success me-2"></i>เช็คเอาท์: จนถึง 12:00 ของวันสุดท้าย</li>
                                        <li><i class="bi bi-check text-success me-2"></i>เกิน 12:00: 100 บาท/ชั่วโมง</li>
                                        <li><i class="bi bi-check text-success me-2"></i>สามารถพักได้หลายคืน (1 คืน, 2 คืน, 3 คืน...)</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>หมายเหตุสำคัญ:</strong> ระบบจะแสดงห้องที่เกินเวลาเช็คเอาท์ด้วยสีแดงเด่นชัด
                                และแสดงระยะเวลาที่เกินใน Room Board แบบ Real-time
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>