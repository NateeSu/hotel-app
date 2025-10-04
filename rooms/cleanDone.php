<?php
/**
 * Hotel Management System - Mark Cleaning Done
 *
 * Mark housekeeping job as complete and update room status
 */

// Only initialize if not already loaded by index.php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);

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

} else {
    // Already initialized by index.php
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

// Require login with reception role or higher
requireLogin(['reception', 'admin', 'housekeeping']);

// Set page variables
$pageTitle = 'Mark Cleaning Done - Hotel Management System';
$pageDescription = 'Complete housekeeping job';

// Get room ID from GET or POST
$roomId = $_GET['room_id'] ?? $_POST['room_id'] ?? null;
$success = false;
$error = null;

if (!$roomId) {
    flash_error('ไม่ได้ระบุห้องที่ต้องการทำความสะอาด');
    header('Location: ' . $GLOBALS['baseUrl'] . '/?r=rooms.board');
    exit;
}

try {
    $pdo = getDatabase();

    // Get room details
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new Exception('ไม่พบห้องที่ระบุ');
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        if ($_POST['action'] === 'mark_clean_done') {
            $notes = $_POST['completion_notes'] ?? '';
            $currentUser = currentUser();

            $pdo->beginTransaction();

            try {
                // Update room status to available
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'available', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$roomId]);

                // Update any active housekeeping jobs for this room
                $stmt = $pdo->prepare("
                    UPDATE housekeeping_jobs
                    SET status = 'completed',
                        completed_at = NOW(),
                        notes = ?
                    WHERE room_id = ? AND status IN ('pending', 'in_progress')
                ");
                $stmt->execute([$notes, $roomId]);

                // Log the action (only if room_status_logs table exists)
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO room_status_logs
                        (room_id, previous_status, new_status, changed_by, changed_at, notes)
                        VALUES (?, ?, 'available', ?, NOW(), ?)
                    ");
                    $stmt->execute([$roomId, $room['status'], $currentUser['id'], 'Cleaning completed: ' . $notes]);
                } catch (Exception $logError) {
                    // Table might not exist yet, continue without logging
                    error_log("Room status log error: " . $logError->getMessage());
                }

                $pdo->commit();

                flash_success('ทำความสะอาดห้อง ' . $room['room_number'] . ' เสร็จสิ้น ห้องพร้อมให้บริการแล้ว');
                header('Location: ' . $GLOBALS['baseUrl'] . '/?r=rooms.board');
                exit;

            } catch (Exception $e) {
                $pdo->rollback();
                throw $e;
            }
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Clean done error: " . $e->getMessage());
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
                        <i class="bi bi-check-circle text-success me-2"></i>
                        ทำความสะอาดเสร็จสิ้น
                    </h1>
                    <p class="text-muted mb-0">ยืนยันการทำความสะอาดห้อง <?php echo htmlspecialchars($room['room_number']); ?></p>
                </div>

                <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=rooms.board" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    กลับสู่แดชบอร์ด
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clipboard-check me-2"></i>
                                ยืนยันการทำความสะอาด
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>ข้อมูลห้อง</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>หมายเลขห้อง:</strong></td>
                                            <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>สถานะปัจจุบัน:</strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo $room['status'] === 'cleaning' ? 'warning' : 'secondary'; ?>">
                                                    <?php
                                                    echo $room['status'] === 'cleaning' ? 'กำลังทำความสะอาด' :
                                                         ($room['status'] === 'available' ? 'ว่าง' :
                                                          ($room['status'] === 'occupied' ? 'มีแขก' : $room['status']));
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>ชั้น:</strong></td>
                                            <td><?php echo htmlspecialchars($room['floor']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>รายการตรวจสอบ</h6>
                                    <div class="bg-light p-3 rounded">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="check1" checked disabled>
                                            <label class="form-check-label" for="check1">
                                                ทำความสะอาดห้องนอน
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="check2" checked disabled>
                                            <label class="form-check-label" for="check2">
                                                ทำความสะอาดห้องน้ำ
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="check3" checked disabled>
                                            <label class="form-check-label" for="check3">
                                                เปลี่ยนผ้าปูที่นอน
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="check4" checked disabled>
                                            <label class="form-check-label" for="check4">
                                                เติมของใช้ในห้อง
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="check5" checked disabled>
                                            <label class="form-check-label" for="check5">
                                                ตรวจสอบอุปกรณ์
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="mark_clean_done">
                                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($roomId); ?>">

                                <div class="mb-3">
                                    <label for="completion_notes" class="form-label">หมายเหตุการทำความสะอาด</label>
                                    <textarea class="form-control" id="completion_notes" name="completion_notes" rows="3"
                                              placeholder="เพิ่มหมายเหตุเกี่ยวกับการทำความสะอาด (ถ้ามี)"></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>หมายเหตุ:</strong> เมื่อกดยืนยัน สถานะห้องจะเปลี่ยนเป็น "ว่าง" และพร้อมให้บริการลูกค้าใหม่
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=rooms.board" class="btn btn-outline-secondary me-md-2">
                                        <i class="bi bi-x-circle me-1"></i>
                                        ยกเลิก
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        ยืนยันทำความสะอาดเสร็จสิ้น
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>