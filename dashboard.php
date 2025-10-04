<?php
/**
 * Hotel Management System - Dashboard
 * Default home page after login
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
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/includes/helpers.php';
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/router.php';
} else {
    // Already initialized by index.php, use existing baseUrl
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

// Load ReportsEngine (safe to load even if included multiple times due to class check)
require_once __DIR__ . '/lib/reports_engine.php';

// Require login
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'แผงควบคุม - Hotel Management System';
$pageDescription = 'ภาพรวมระบบจัดการโรงแรม';

// Get current user
$currentUser = currentUser();

// Get dashboard data
try {
    $pdo = getDatabase();
    $reportsEngine = new ReportsEngine();

    // Get dashboard summary
    $dashboardSummary = $reportsEngine->getDashboardSummary();

    // Room statistics
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM rooms GROUP BY status");
    $roomStats = [];
    while ($row = $stmt->fetch()) {
        $roomStats[$row['status']] = $row['count'];
    }

    // Total rooms
    $totalRooms = array_sum($roomStats);

    // Recent bookings
    $stmt = $pdo->query("
        SELECT b.*, r.room_number, r.room_type
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $recentBookings = $stmt->fetchAll();

    // Get quick analytics for charts
    $salesData = $reportsEngine->getDailySalesReport(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
    $occupancyReport = $reportsEngine->getOccupancyReport(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));

} catch (Exception $e) {
    $roomStats = [];
    $totalRooms = 0;
    $recentBookings = [];
    $dashboardSummary = ['today' => [], 'room_status' => [], 'monthly_comparison' => []];
    $salesData = [];
    $occupancyReport = ['occupancy_by_date' => []];
}

// Include header
require_once __DIR__ . '/templates/layout/header.php';
?>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-speedometer2 text-primary me-2"></i>
                        แผงควบคุม
                    </h1>
                    <p class="text-muted mb-0">ยินดีต้อนรับ, <?php echo htmlspecialchars($currentUser['full_name']); ?></p>
                </div>
                <div class="text-end">
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        <?php echo format_datetime_thai(now(), 'd/m/Y H:i'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-door-closed text-primary" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">ห้องทั้งหมด</div>
                            <div class="h4 mb-0"><?php echo $totalRooms; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">ห้องว่าง</div>
                            <div class="h4 mb-0"><?php echo $roomStats['available'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-person-fill text-danger" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">มีผู้พัก</div>
                            <div class="h4 mb-0"><?php echo $roomStats['occupied'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-brush text-warning" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">ทำความสะอาด</div>
                            <div class="h4 mb-0"><?php echo $roomStats['cleaning'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        การดำเนินการด่วน
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-primary w-100 p-3">
                                <i class="bi bi-grid-3x3-gap me-2"></i>
                                <div>
                                    <div class="fw-bold">แผงควบคุมห้องพัก</div>
                                    <small class="text-muted">ดูสถานะห้องพักทั้งหมด</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-success w-100 p-3" disabled>
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                <div>
                                    <div class="fw-bold">Check-in</div>
                                    <small class="text-muted">รับแขกเข้าพัก (ยังไม่เปิดใช้งาน)</small>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-info w-100 p-3" disabled>
                                <i class="bi bi-calendar-check me-2"></i>
                                <div>
                                    <div class="fw-bold">การจอง</div>
                                    <small class="text-muted">จัดการการจองห้องพัก (ยังไม่เปิดใช้งาน)</small>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo routeUrl('reports.sales'); ?>" class="btn btn-outline-secondary w-100 p-3">
                                <i class="bi bi-graph-up me-2"></i>
                                <div>
                                    <div class="fw-bold">รายงาน</div>
                                    <small class="text-muted">ดูรายงานยอดขายและสถิติ</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        การจองล่าสุด
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentBookings)): ?>
                        <div class="text-center py-3">
                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">ยังไม่มีการจอง</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentBookings as $booking): ?>
                                <div class="list-group-item px-0 border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-light rounded-circle p-2">
                                                <i class="bi bi-person text-muted"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-medium"><?php echo htmlspecialchars($booking['guest_name']); ?></div>
                                            <small class="text-muted">
                                                ห้อง <?php echo htmlspecialchars($booking['room_number']); ?> •
                                                <?php echo format_datetime_thai($booking['created_at'], 'd/m H:i'); ?>
                                            </small>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-<?php echo $booking['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo $booking['status'] === 'active' ? 'กำลังพัก' : $booking['status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/layout/footer.php'; ?>