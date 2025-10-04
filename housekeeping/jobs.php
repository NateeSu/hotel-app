<?php
/**
 * Hotel Management System - Housekeeping Jobs List
 *
 * รายการงานทำความสะอาดทั้งหมด
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

// Check authentication
requireLogin();

// Set page variables
$pageTitle = 'รายการงานทำความสะอาด - Hotel Management System';
$pageDescription = 'รายการงานทำความสะอาดทั้งหมด';

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$assignedFilter = $_GET['assigned'] ?? '';
$roomFilter = $_GET['room'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

try {
    $pdo = getDatabase();

    // Build WHERE clause for filters
    $whereConditions = ['1=1'];
    $params = [];

    if ($statusFilter) {
        $whereConditions[] = 'hj.status = ?';
        $params[] = $statusFilter;
    }

    if ($priorityFilter) {
        $whereConditions[] = 'hj.priority = ?';
        $params[] = $priorityFilter;
    }

    if ($assignedFilter) {
        $whereConditions[] = 'hj.assigned_to = ?';
        $params[] = $assignedFilter;
    }

    if ($roomFilter) {
        $whereConditions[] = 'r.room_number LIKE ?';
        $params[] = '%' . $roomFilter . '%';
    }

    if ($startDate) {
        $whereConditions[] = 'DATE(hj.created_at) >= ?';
        $params[] = $startDate;
    }

    if ($endDate) {
        $whereConditions[] = 'DATE(hj.created_at) <= ?';
        $params[] = $endDate;
    }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Get housekeeping jobs
    $stmt = $pdo->prepare("
        SELECT
            hj.id,
            hj.status,
            hj.priority,
            hj.created_at,
            hj.started_at,
            hj.completed_at,
            hj.actual_duration,
            hj.telegram_sent,
            hj.task_type,
            hj.job_type,
            r.room_number,
            r.room_type,
            r.status as room_status,
            b.guest_name,
            b.checkout_at,
            u_assigned.full_name as assigned_to_name,
            u_created.full_name as created_by_name,
            CASE
                WHEN hj.started_at IS NOT NULL AND hj.completed_at IS NULL
                THEN TIMESTAMPDIFF(MINUTE, hj.started_at, NOW())
                ELSE hj.actual_duration
            END as current_duration_minutes
        FROM housekeeping_jobs hj
        JOIN rooms r ON hj.room_id = r.id
        LEFT JOIN bookings b ON hj.booking_id = b.id
        LEFT JOIN users u_assigned ON hj.assigned_to = u_assigned.id
        LEFT JOIN users u_created ON hj.created_by = u_created.id
        $whereClause
        ORDER BY
            CASE hj.status
                WHEN 'in_progress' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'completed' THEN 3
            END,
            CASE hj.priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'normal' THEN 3
                WHEN 'low' THEN 4
            END,
            hj.created_at DESC
    ");
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get summary statistics
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_jobs,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_jobs,
            COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) as in_progress_jobs,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_jobs
        FROM housekeeping_jobs hj
        $whereClause
    ");
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure all values are integers (not null)
    $stats['total_jobs'] = (int)($stats['total_jobs'] ?? 0);
    $stats['pending_jobs'] = (int)($stats['pending_jobs'] ?? 0);
    $stats['in_progress_jobs'] = (int)($stats['in_progress_jobs'] ?? 0);
    $stats['completed_jobs'] = (int)($stats['completed_jobs'] ?? 0);

    // Get staff list for filter
    $stmt = $pdo->query("
        SELECT id, full_name
        FROM users
        WHERE role = 'housekeeping' AND is_active = 1
        ORDER BY full_name
    ");
    $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage());
    $jobs = [];
    $stats = ['total_jobs' => 0, 'pending_jobs' => 0, 'in_progress_jobs' => 0, 'completed_jobs' => 0];
    $staffList = [];
}

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-list-task text-primary me-2"></i>
                รายการงานทำความสะอาด
            </h1>
            <p class="text-muted mb-0">จัดการและติดตามงานทำความสะอาดทั้งหมด</p>
        </div>
        <div>
            <a href="<?php echo routeUrl('housekeeping.reports'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-graph-up me-1"></i>รายงาน
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="h3 mb-1 text-primary"><?php echo number_format($stats['total_jobs']); ?></div>
                    <div class="text-muted">งานทั้งหมด</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="h3 mb-1 text-warning"><?php echo number_format($stats['pending_jobs']); ?></div>
                    <div class="text-muted">รอดำเนินการ</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="h3 mb-1 text-info"><?php echo number_format($stats['in_progress_jobs']); ?></div>
                    <div class="text-muted">กำลังดำเนินการ</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="h3 mb-1 text-success"><?php echo number_format($stats['completed_jobs']); ?></div>
                    <div class="text-muted">เสร็จสิ้น</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="bi bi-funnel me-2"></i>
                ตัวกรองข้อมูล
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">สถานะ:</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">ทั้งหมด</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                        <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">ความสำคัญ:</label>
                    <select class="form-select form-select-sm" name="priority">
                        <option value="">ทั้งหมด</option>
                        <option value="urgent" <?php echo $priorityFilter === 'urgent' ? 'selected' : ''; ?>>เร่งด่วน</option>
                        <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>สูง</option>
                        <option value="normal" <?php echo $priorityFilter === 'normal' ? 'selected' : ''; ?>>ปกติ</option>
                        <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>ต่ำ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">พนักงาน:</label>
                    <select class="form-select form-select-sm" name="assigned">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($staffList as $staff): ?>
                        <option value="<?php echo $staff['id']; ?>" <?php echo $assignedFilter == $staff['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($staff['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">ห้อง:</label>
                    <input type="text" class="form-control form-control-sm" name="room"
                           value="<?php echo htmlspecialchars($roomFilter); ?>" placeholder="เลขห้อง">
                </div>
                <div class="col-md-2">
                    <label class="form-label">วันที่เริ่ม:</label>
                    <input type="date" class="form-control form-control-sm" name="start_date"
                           value="<?php echo htmlspecialchars($startDate); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">วันที่สิ้นสุด:</label>
                    <input type="date" class="form-control form-control-sm" name="end_date"
                           value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                    <a href="<?php echo routeUrl('housekeeping.jobs'); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="bi bi-table me-2"></i>
                รายการงาน (<?php echo count($jobs); ?> รายการ)
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($jobs)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ห้อง</th>
                            <th>ประเภทงาน</th>
                            <th>แขก</th>
                            <th>สถานะ</th>
                            <th>ความสำคัญ</th>
                            <th>พนักงาน</th>
                            <th>เวลาที่ใช้</th>
                            <th>Telegram</th>
                            <th>สร้างเมื่อ</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $job['id']; ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($job['room_number']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($job['room_type']); ?></small>
                                </div>
                            </td>
                            <td>
                                <?php
                                $taskTypes = [
                                    'checkout_cleaning' => 'ทำความสะอาดหลังเช็คเอาท์',
                                    'maintenance' => 'งานซ่อมบำรุง',
                                    'inspection' => 'ตรวจสอบห้อง',
                                    'cleaning' => 'ทำความสะอาด'
                                ];
                                $taskType = $job['task_type'] ?: $job['job_type'];
                                echo $taskTypes[$taskType] ?? 'งานทั่วไป';
                                ?>
                            </td>
                            <td>
                                <?php if ($job['guest_name']): ?>
                                    <strong><?php echo htmlspecialchars($job['guest_name']); ?></strong>
                                    <?php if ($job['checkout_at']): ?>
                                        <br><small class="text-muted">เช็คเอาท์: <?php echo date('d/m H:i', strtotime($job['checkout_at'])); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
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
                                ?>
                                <span class="badge bg-<?php echo $statusColors[$job['status']]; ?>">
                                    <?php echo $statusTexts[$job['status']]; ?>
                                </span>
                            </td>
                            <td>
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
                                ?>
                                <span class="badge bg-<?php echo $priorityColors[$job['priority']]; ?>">
                                    <?php echo $priorityTexts[$job['priority']]; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($job['assigned_to_name'] ?: '-'); ?>
                            </td>
                            <td>
                                <?php if ($job['current_duration_minutes']): ?>
                                    <strong><?php echo $job['current_duration_minutes']; ?> นาที</strong>
                                    <?php if ($job['status'] === 'in_progress'): ?>
                                        <br><small class="text-info">กำลังนับ...</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($job['telegram_sent']): ?>
                                    <i class="bi bi-check-circle text-success fs-5" title="ส่งแล้ว"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle text-muted fs-5" title="ยังไม่ส่ง"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($job['created_at'])); ?></div>
                                <small class="text-muted"><?php echo date('H:i', strtotime($job['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo routeUrl('housekeeping.job', ['id' => $job['id']]); ?>"
                                       class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($job['status'] !== 'completed'): ?>
                                        <a href="<?php echo routeUrl('housekeeping.job', ['id' => $job['id']]); ?>"
                                           class="btn btn-sm btn-outline-success" title="จัดการงาน">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">ไม่มีงานที่ตรงกับเงื่อนไขการค้นหา</h5>
                <p class="text-muted">ลองเปลี่ยนเงื่อนไขการกรองข้อมูล</p>
                <a href="<?php echo routeUrl('housekeeping.jobs'); ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i>แสดงทั้งหมด
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-refresh page every 2 minutes for active jobs
<?php if ($stats['in_progress_jobs'] > 0): ?>
setTimeout(function() {
    if (!document.hidden) {
        window.location.reload();
    }
}, 120000); // 2 minutes
<?php endif; ?>

// Live time updates for in-progress jobs
document.addEventListener('DOMContentLoaded', function() {
    function updateDurations() {
        // Update current duration for in-progress jobs
        // This would require AJAX calls to get real-time data
        // For now, we'll just refresh the page periodically
    }

    // Update every minute
    setInterval(updateDurations, 60000);
});
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>