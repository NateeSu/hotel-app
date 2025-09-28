<?php
/**
 * Hotel Management System - Housekeeping Job Detail Form
 *
 * Form for housekeeping staff to view job details and mark completion
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
require_once __DIR__ . '/../lib/telegram_service.php';
require_once __DIR__ . '/../templates/partials/flash.php';

// Get job ID
$jobId = $_GET['id'] ?? null;

if (!$jobId) {
    flash_error('ไม่ได้ระบุรหัสงาน');
    redirectToRoute('housekeeping.jobs');
}

// Set page variables
$pageTitle = 'รายละเอียดงานทำความสะอาด - Hotel Management System';
$pageDescription = 'แบบฟอร์มสำหรับงานทำความสะอาด';

// Handle job actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDatabase();
        $telegramService = new TelegramService();

        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('CSRF token verification failed');
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'start_job') {
            // Start the job
            $stmt = $pdo->prepare("
                UPDATE housekeeping_jobs
                SET status = 'in_progress', started_at = NOW()
                WHERE id = ? AND status = 'pending'
            ");

            if ($stmt->execute([$jobId])) {
                flash_success('เริ่มงานทำความสะอาดแล้ว');
            } else {
                flash_error('ไม่สามารถเริ่มงานได้');
            }

        } elseif ($action === 'complete_job') {
            // Complete the job
            $notes = trim($_POST['completion_notes'] ?? '');
            $currentUser = currentUser();

            $pdo->beginTransaction();

            // Update job status
            $stmt = $pdo->prepare("
                UPDATE housekeeping_jobs
                SET
                    status = 'completed',
                    completed_at = NOW(),
                    actual_duration = TIMESTAMPDIFF(MINUTE, started_at, NOW()),
                    special_notes = CONCAT(COALESCE(special_notes, ''), ?, ' - เสร็จโดย: ', ?),
                    assigned_to = ?
                WHERE id = ? AND status = 'in_progress'
            ");

            if (!$stmt->execute([
                $notes ? "\nหมายเหตุการเสร็จสิ้น: " . $notes : '',
                $currentUser['full_name'] ?? 'ผู้ใช้ไม่ระบุ',
                $currentUser['id'] ?? null,
                $jobId
            ])) {
                throw new Exception('ไม่สามารถอัปเดตสถานะงานได้');
            }

            // Get room ID from job
            $stmt = $pdo->prepare("SELECT room_id FROM housekeeping_jobs WHERE id = ?");
            $stmt->execute([$jobId]);
            $roomId = $stmt->fetchColumn();

            if ($roomId) {
                // Update room status to available
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'available' WHERE id = ?");
                $stmt->execute([$roomId]);
            }

            $pdo->commit();

            // Send completion notification
            $telegramService->sendJobCompletionNotification(
                $jobId,
                $currentUser['full_name'] ?? 'ผู้ใช้ไม่ระบุ'
            );

            flash_success('งานทำความสะอาดเสร็จสิ้นแล้ว! ห้องเปลี่ยนสถานะเป็น "ว่าง"');

        } elseif ($action === 'add_note') {
            // Add progress note
            $note = trim($_POST['progress_note'] ?? '');
            if ($note) {
                $currentUser = currentUser();
                $timestamp = date('d/m/Y H:i');
                $userNote = "[{$timestamp}] {$currentUser['full_name']}: {$note}";

                $stmt = $pdo->prepare("
                    UPDATE housekeeping_jobs
                    SET special_notes = CONCAT(COALESCE(special_notes, ''), '\n', ?)
                    WHERE id = ?
                ");

                if ($stmt->execute([$userNote, $jobId])) {
                    flash_success('เพิ่มหมายเหตุเรียบร้อย');
                } else {
                    flash_error('ไม่สามารถเพิ่มหมายเหตุได้');
                }
            }
        }

        // Refresh page to show updated data
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollback();
        }
        flash_error('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

// Get job details
try {
    $pdo = getDatabase();

    $stmt = $pdo->prepare("
        SELECT
            hj.*,
            r.room_number,
            r.room_type,
            r.status as room_status,
            b.guest_name,
            b.guest_phone,
            b.checkout_at,
            b.total_amount,
            u_assigned.full_name as assigned_to_name,
            u_created.full_name as created_by_name,
            CASE
                WHEN hj.completed_at IS NOT NULL AND hj.started_at IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, hj.started_at, hj.completed_at)
                WHEN hj.started_at IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, hj.started_at, NOW())
                ELSE NULL
            END as current_duration_minutes
        FROM housekeeping_jobs hj
        JOIN rooms r ON hj.room_id = r.id
        LEFT JOIN bookings b ON hj.booking_id = b.id
        LEFT JOIN users u_assigned ON hj.assigned_to = u_assigned.id
        LEFT JOIN users u_created ON hj.created_by = u_created.id
        WHERE hj.id = ?
    ");

    $stmt->execute([$jobId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        flash_error('ไม่พบงานที่ระบุ');
        redirectToRoute('housekeeping.jobs');
    }

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage());
    redirectToRoute('housekeeping.jobs');
}

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-brush text-warning me-2"></i>
                งานทำความสะอาด #<?php echo $job['id']; ?>
            </h1>
            <p class="text-muted mb-0">รายละเอียดและการดำเนินการ</p>
        </div>
        <div>
            <a href="<?php echo routeUrl('housekeeping.jobs'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>กลับรายการงาน
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Job Details -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        ข้อมูลงาน
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-house-door text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">ห้อง</h6>
                                    <div class="h5 mb-0"><?php echo htmlspecialchars($job['room_number']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($job['room_type']); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person text-info" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">แขกที่เช็คเอาท์</h6>
                                    <div class="h6 mb-0"><?php echo htmlspecialchars($job['guest_name'] ?? '-'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($job['guest_phone'] ?? ''); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">เวลาเช็คเอาท์</h6>
                                    <div class="h6 mb-0">
                                        <?php echo $job['checkout_at'] ? date('d/m/Y H:i', strtotime($job['checkout_at'])) : '-'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-flag text-danger" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">ความสำคัญ</h6>
                                    <?php
                                    $priorityColors = [
                                        'low' => 'secondary',
                                        'normal' => 'primary',
                                        'high' => 'warning',
                                        'urgent' => 'danger'
                                    ];
                                    $priorityTexts = [
                                        'low' => 'ต่ำ',
                                        'normal' => 'ปกติ',
                                        'high' => 'สูง',
                                        'urgent' => 'เร่งด่วน'
                                    ];
                                    $priority = $job['priority'] ?? 'normal';
                                    ?>
                                    <span class="badge bg-<?php echo $priorityColors[$priority]; ?> fs-6">
                                        <?php echo $priorityTexts[$priority]; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($job['description']): ?>
                    <div class="mt-3">
                        <h6>รายละเอียดงาน:</h6>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['special_notes']): ?>
                    <div class="mt-3">
                        <h6>หมายเหตุและความคืบหน้า:</h6>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($job['special_notes'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Progress Notes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-dots me-2"></i>
                        เพิ่มหมายเหตุความคืบหน้า
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($job['status'] !== 'completed'): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                        <input type="hidden" name="action" value="add_note">

                        <div class="mb-3">
                            <textarea class="form-control" name="progress_note" rows="3"
                                      placeholder="เพิ่มหมายเหตุความคืบหน้า เช่น พบปัญหาอะไร ทำงานไปแล้วเท่าไหร่"></textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            เพิ่มหมายเหตุ
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        งานนี้เสร็จสิ้นแล้ว
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Status and Actions -->
        <div class="col-lg-4">
            <!-- Current Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-speedometer me-2"></i>
                        สถานะปัจจุบัน
                    </h6>
                </div>
                <div class="card-body text-center">
                    <?php
                    $statusColors = [
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success'
                    ];
                    $statusTexts = [
                        'pending' => 'รอดำเนินการ',
                        'in_progress' => 'กำลังดำเนินการ',
                        'completed' => 'เสร็จสิ้น'
                    ];
                    $currentStatus = $job['status'];
                    ?>

                    <div class="mb-3">
                        <span class="badge bg-<?php echo $statusColors[$currentStatus]; ?> fs-5 px-3 py-2">
                            <?php echo $statusTexts[$currentStatus]; ?>
                        </span>
                    </div>

                    <?php if ($job['started_at']): ?>
                    <div class="mb-2">
                        <small class="text-muted">เริ่มงาน:</small>
                        <br><?php echo date('d/m/Y H:i', strtotime($job['started_at'])); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['completed_at']): ?>
                    <div class="mb-2">
                        <small class="text-muted">เสร็จงาน:</small>
                        <br><?php echo date('d/m/Y H:i', strtotime($job['completed_at'])); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['current_duration_minutes']): ?>
                    <div class="mb-2">
                        <small class="text-muted">ระยะเวลา:</small>
                        <br><strong><?php echo $job['current_duration_minutes']; ?> นาที</strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>
                        การดำเนินการ
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($job['status'] === 'pending'): ?>
                    <!-- Start Job -->
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                        <input type="hidden" name="action" value="start_job">

                        <button type="submit" class="btn btn-primary w-100 btn-lg"
                                onclick="return confirm('เริ่มงานทำความสะอาดใช่หรือไม่?')">
                            <i class="bi bi-play-fill me-2"></i>
                            เริ่มทำความสะอาด
                        </button>
                    </form>

                    <?php elseif ($job['status'] === 'in_progress'): ?>
                    <!-- Complete Job -->
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                        <input type="hidden" name="action" value="complete_job">

                        <div class="mb-3">
                            <label class="form-label">หมายเหตุการเสร็จสิ้น:</label>
                            <textarea class="form-control" name="completion_notes" rows="2"
                                      placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg"
                                onclick="return confirm('ยืนยันว่าทำความสะอาดเรียบร้อยแล้ว?')">
                            <i class="bi bi-check-circle me-2"></i>
                            ทำความสะอาดเรียบร้อย
                        </button>
                    </form>

                    <?php else: ?>
                    <!-- Completed -->
                    <div class="text-center">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2 text-success">งานเสร็จสิ้นแล้ว</h6>
                        <p class="text-muted">ห้องพร้อมให้บริการแล้ว</p>
                    </div>
                    <?php endif; ?>

                    <!-- Additional Info -->
                    <hr>
                    <div class="small text-muted">
                        <div class="mb-1">
                            <strong>สร้างเมื่อ:</strong>
                            <?php echo date('d/m/Y H:i', strtotime($job['created_at'])); ?>
                        </div>
                        <?php if ($job['created_by_name']): ?>
                        <div class="mb-1">
                            <strong>สร้างโดย:</strong>
                            <?php echo htmlspecialchars($job['created_by_name']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($job['assigned_to_name']): ?>
                        <div class="mb-1">
                            <strong>รับผิดชอบโดย:</strong>
                            <?php echo htmlspecialchars($job['assigned_to_name']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>