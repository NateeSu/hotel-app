<?php
/**
 * Hotel Management System - Housekeeping Performance Reports
 *
 * Reports for tracking housekeeping job performance and statistics
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
$pageTitle = 'รายงานประสิทธิภาพงานทำความสะอาด - Hotel Management System';
$pageDescription = 'รายงานและสถิติประสิทธิภาพงานทำความสะอาด';

// Get filter parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Start of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
$staffFilter = $_GET['staff_filter'] ?? '';
$statusFilter = $_GET['status_filter'] ?? '';

try {
    $pdo = getDatabase();

    // Build WHERE clause for filters
    $whereConditions = ['hj.created_at BETWEEN ? AND ? '];
    $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];

    if ($staffFilter) {
        $whereConditions[] = 'hj.assigned_to = ?';
        $params[] = $staffFilter;
    }

    if ($statusFilter) {
        $whereConditions[] = 'hj.status = ?';
        $params[] = $statusFilter;
    }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Get overall statistics
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_jobs,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_jobs,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_jobs,
            AVG(CASE WHEN actual_duration IS NOT NULL THEN actual_duration ELSE NULL END) as avg_duration,
            MIN(CASE WHEN actual_duration IS NOT NULL THEN actual_duration ELSE NULL END) as min_duration,
            MAX(CASE WHEN actual_duration IS NOT NULL THEN actual_duration ELSE NULL END) as max_duration
        FROM housekeeping_jobs hj
        $whereClause
    ");
    $stmt->execute($params);
    $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get daily completion statistics
    $stmt = $pdo->prepare("
        SELECT
            DATE(completed_at) as completion_date,
            COUNT(*) as completed_count,
            AVG(actual_duration) as avg_duration_day
        FROM housekeeping_jobs hj
        $whereClause
        AND status = 'completed'
        AND completed_at IS NOT NULL
        GROUP BY DATE(completed_at)
        ORDER BY completion_date DESC
    ");
    $stmt->execute($params);
    $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get staff performance (simplified query)
    $stmt = $pdo->query("
        SELECT
            u.id,
            u.full_name,
            COUNT(hj.id) as total_jobs,
            SUM(CASE WHEN hj.status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
            AVG(CASE WHEN hj.actual_duration IS NOT NULL THEN hj.actual_duration ELSE NULL END) as avg_duration,
            MIN(CASE WHEN hj.actual_duration IS NOT NULL THEN hj.actual_duration ELSE NULL END) as min_duration,
            MAX(CASE WHEN hj.actual_duration IS NOT NULL THEN hj.actual_duration ELSE NULL END) as max_duration
        FROM users u
        LEFT JOIN housekeeping_jobs hj ON u.id = hj.assigned_to
        WHERE u.role = 'housekeeping' AND u.is_active = 1
        GROUP BY u.id, u.full_name
        ORDER BY completed_jobs DESC, avg_duration ASC
    ");
    $staffPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent job details
    $stmt = $pdo->prepare("
        SELECT
            hj.id,
            r.room_number,
            COALESCE(hj.task_type, hj.job_type) as task_type,
            hj.priority,
            hj.status,
            hj.created_at,
            hj.started_at,
            hj.completed_at,
            hj.actual_duration,
            u.full_name as assigned_to_name,
            COALESCE(hj.telegram_sent, 0) as telegram_sent
        FROM housekeeping_jobs hj
        JOIN rooms r ON hj.room_id = r.id
        LEFT JOIN users u ON hj.assigned_to = u.id
        $whereClause
        ORDER BY hj.created_at DESC
        LIMIT 20
    ");
    $stmt->execute($params);
    $recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get staff list for filter
    $stmt = $pdo->prepare("
        SELECT id, full_name
        FROM users
        WHERE role = 'housekeeping' AND is_active = 1
        ORDER BY full_name
    ");
    $stmt->execute();
    $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดรายงาน: ' . $e->getMessage());
    $overallStats = [];
    $dailyStats = [];
    $staffPerformance = [];
    $recentJobs = [];
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
                <i class="bi bi-graph-up text-primary me-2"></i>
                รายงานประสิทธิภาพงานทำความสะอาด
            </h1>
            <p class="text-muted mb-0">สถิติและการวิเคราะห์ประสิทธิภาพ</p>
        </div>
        <div>
            <a href="<?php echo routeUrl('housekeeping.jobs'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-list me-1"></i>รายการงาน
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">วันที่เริ่มต้น:</label>
                    <input type="date" class="form-control" name="start_date"
                           value="<?php echo htmlspecialchars($startDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">วันที่สิ้นสุด:</label>
                    <input type="date" class="form-control" name="end_date"
                           value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">พนักงาน:</label>
                    <select class="form-control" name="staff_filter">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($staffList as $staff): ?>
                        <option value="<?php echo $staff['id']; ?>"
                                <?php echo $staffFilter == $staff['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($staff['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">สถานะ:</label>
                    <select class="form-control" name="status_filter">
                        <option value="">ทั้งหมด</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                        <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Overall Statistics -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        สถิติรวม
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h2 mb-1 text-primary"><?php echo number_format($overallStats['total_jobs'] ?? 0); ?></div>
                                <div class="text-muted">งานทั้งหมด</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h2 mb-1 text-success"><?php echo number_format($overallStats['completed_jobs'] ?? 0); ?></div>
                                <div class="text-muted">งานเสร็จสิ้น</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h2 mb-1 text-info"><?php echo number_format($overallStats['in_progress_jobs'] ?? 0); ?></div>
                                <div class="text-muted">กำลังดำเนินการ</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h2 mb-1 text-warning"><?php echo number_format($overallStats['pending_jobs'] ?? 0); ?></div>
                                <div class="text-muted">รอดำเนินการ</div>
                            </div>
                        </div>
                    </div>

                    <?php if ($overallStats['avg_duration']): ?>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-1 text-primary"><?php echo number_format($overallStats['avg_duration'], 1); ?> นาที</div>
                                <div class="text-muted">เวลาเฉลี่ย</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-1 text-success"><?php echo number_format($overallStats['min_duration']); ?> นาที</div>
                                <div class="text-muted">เวลาน้อยที่สุด</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-1 text-danger"><?php echo number_format($overallStats['max_duration']); ?> นาที</div>
                                <div class="text-muted">เวลามากที่สุด</div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Staff Performance -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people me-2"></i>
                        ประสิทธิภาพพนักงาน
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($staffPerformance)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>พนักงาน</th>
                                    <th class="text-center">งานทั้งหมด</th>
                                    <th class="text-center">เสร็จสิ้น</th>
                                    <th class="text-center">อัตราสำเร็จ</th>
                                    <th class="text-center">เวลาเฉลี่ย</th>
                                    <th class="text-center">เวลาน้อยสุด</th>
                                    <th class="text-center">เวลามากสุด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staffPerformance as $staff): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($staff['full_name']); ?></strong>
                                    </td>
                                    <td class="text-center"><?php echo number_format($staff['total_jobs']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo number_format($staff['completed_jobs']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $successRate = $staff['total_jobs'] > 0 ? ($staff['completed_jobs'] / $staff['total_jobs']) * 100 : 0;
                                        $badgeClass = $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?php echo $badgeClass; ?>">
                                            <?php echo number_format($successRate, 1); ?>%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $staff['avg_duration'] ? number_format($staff['avg_duration'], 1) . ' นาที' : '-'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $staff['min_duration'] ? number_format($staff['min_duration']) . ' นาที' : '-'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $staff['max_duration'] ? number_format($staff['max_duration']) . ' นาที' : '-'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle me-2"></i>
                        ไม่มีข้อมูลประสิทธิภาพพนักงานในช่วงเวลาที่เลือก
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Daily Statistics -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        สถิติรายวัน
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($dailyStats)): ?>
                    <div class="small">
                        <?php foreach ($dailyStats as $day): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong><?php echo date('d/m/Y', strtotime($day['completion_date'])); ?></strong>
                                <br>
                                <small class="text-muted">เวลาเฉลี่ย: <?php echo number_format($day['avg_duration_day'], 1); ?> นาที</small>
                            </div>
                            <div>
                                <span class="badge bg-primary fs-6"><?php echo $day['completed_count']; ?> งาน</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle me-2"></i>
                        ไม่มีข้อมูลสถิติรายวัน
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        งานล่าสุด (20 รายการ)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentJobs)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ห้อง</th>
                                    <th>ประเภทงาน</th>
                                    <th>ความสำคัญ</th>
                                    <th>สถานะ</th>
                                    <th>พนักงาน</th>
                                    <th>เวลาที่ใช้</th>
                                    <th>Telegram</th>
                                    <th>สร้างเมื่อ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentJobs as $job): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo routeUrl('housekeeping.job', ['id' => $job['id']]); ?>"
                                           class="text-decoration-none">
                                            #<?php echo $job['id']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['room_number']); ?></td>
                                    <td>
                                        <?php
                                        $taskTypes = [
                                            'checkout_cleaning' => 'ทำความสะอาดหลังเช็คเอาท์',
                                            'maintenance' => 'งานซ่อมบำรุง',
                                            'inspection' => 'ตรวจสอบห้อง'
                                        ];
                                        echo $taskTypes[$job['task_type']] ?? 'งานทั่วไป';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityColors = ['low' => 'secondary', 'normal' => 'primary', 'high' => 'warning', 'urgent' => 'danger'];
                                        $priorityTexts = ['low' => 'ต่ำ', 'normal' => 'ปกติ', 'high' => 'สูง', 'urgent' => 'เร่งด่วน'];
                                        ?>
                                        <span class="badge bg-<?php echo $priorityColors[$job['priority']]; ?>">
                                            <?php echo $priorityTexts[$job['priority']]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success'];
                                        $statusTexts = ['pending' => 'รอดำเนินการ', 'in_progress' => 'กำลังดำเนินการ', 'completed' => 'เสร็จสิ้น'];
                                        ?>
                                        <span class="badge bg-<?php echo $statusColors[$job['status']]; ?>">
                                            <?php echo $statusTexts[$job['status']]; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['assigned_to_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php echo $job['actual_duration'] ? $job['actual_duration'] . ' นาที' : '-'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($job['telegram_sent']): ?>
                                            <i class="bi bi-check-circle text-success" title="ส่งแล้ว"></i>
                                        <?php else: ?>
                                            <i class="bi bi-x-circle text-muted" title="ยังไม่ส่ง"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($job['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle me-2"></i>
                        ไม่มีงานในช่วงเวลาที่เลือก
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>