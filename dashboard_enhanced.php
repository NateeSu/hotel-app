<?php
/**
 * Hotel Management System - Enhanced Dashboard with Analytics
 * Advanced dashboard with charts and business intelligence
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
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/router.php';
require_once __DIR__ . '/lib/reports_engine.php';

// Require login
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'แผงควบคุมขั้นสูง - Hotel Management System';
$pageDescription = 'ภาพรวมระบบและสถิติธุรกิจ';

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
    $totalRooms = array_sum($roomStats);

    // Get analytics data
    $salesData = $reportsEngine->getDailySalesReport(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
    $occupancyReport = $reportsEngine->getOccupancyReport(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
    $revenueReport = $reportsEngine->getRevenueReport(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));

    // Recent bookings
    $stmt = $pdo->query("
        SELECT b.*, r.room_number, r.room_type
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        ORDER BY b.created_at DESC
        LIMIT 8
    ");
    $recentBookings = $stmt->fetchAll();

} catch (Exception $e) {
    $roomStats = [];
    $totalRooms = 0;
    $recentBookings = [];
    $dashboardSummary = ['today' => [], 'room_status' => [], 'monthly_comparison' => []];
    $salesData = [];
    $occupancyReport = ['occupancy_by_date' => []];
    $revenueReport = ['monthly_trend' => []];
}

// Calculate performance metrics
$todayStats = $dashboardSummary['today'] ?? [];
$monthlyComparison = $dashboardSummary['monthly_comparison'] ?? [];

$revenueGrowth = 0;
$bookingGrowth = 0;
if (!empty($monthlyComparison)) {
    $thisMonth = floatval($monthlyComparison['this_month_revenue']);
    $lastMonth = floatval($monthlyComparison['last_month_revenue']);
    $revenueGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

    $thisMonthBookings = intval($monthlyComparison['this_month_bookings']);
    $lastMonthBookings = intval($monthlyComparison['last_month_bookings']);
    $bookingGrowth = $lastMonthBookings > 0 ? (($thisMonthBookings - $lastMonthBookings) / $lastMonthBookings) * 100 : 0;
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
                        แผงควบคุมขั้นสูง
                    </h1>
                    <p class="text-muted mb-0">ยินดีต้อนรับ, <?php echo htmlspecialchars($currentUser['full_name']); ?></p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <a href="<?php echo routeUrl('dashboard'); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-grid me-1"></i>แผงธรรมดา
                        </a>
                        <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-grid-3x3-gap me-1"></i>ห้องพัก
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75 mb-1">Check-in วันนี้</h6>
                            <h3 class="mb-0"><?php echo intval($todayStats['today_checkins'] ?? 0); ?></h3>
                            <small class="opacity-75">รายการ</small>
                        </div>
                        <div class="opacity-75">
                            <i class="bi bi-box-arrow-in-right" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75 mb-1">Check-out วันนี้</h6>
                            <h3 class="mb-0"><?php echo intval($todayStats['today_checkouts'] ?? 0); ?></h3>
                            <small class="opacity-75">รายการ</small>
                        </div>
                        <div class="opacity-75">
                            <i class="bi bi-box-arrow-left" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75 mb-1">รายได้วันนี้</h6>
                            <h3 class="mb-0"><?php echo money_format_thb($todayStats['today_revenue'] ?? 0); ?></h3>
                            <small class="opacity-75">บาท</small>
                        </div>
                        <div class="opacity-75">
                            <i class="bi bi-currency-exchange" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">ห้องที่เข้าพัก</h6>
                            <h3 class="mb-0"><?php echo intval($todayStats['current_occupied'] ?? 0); ?>/<?php echo $totalRooms; ?></h3>
                            <small>ห้อง</small>
                        </div>
                        <div>
                            <i class="bi bi-building" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Comparison -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        เปรียบเทียบประสิทธิภาพ (เดือนนี้ vs เดือนที่แล้ว)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h5 text-success mb-1">
                                    <?php echo money_format_thb($monthlyComparison['this_month_revenue'] ?? 0); ?>
                                </div>
                                <small class="text-muted">รายได้เดือนนี้</small>
                                <?php if ($revenueGrowth != 0): ?>
                                <div class="mt-1">
                                    <span class="badge bg-<?php echo $revenueGrowth > 0 ? 'success' : 'danger'; ?>">
                                        <i class="bi bi-arrow-<?php echo $revenueGrowth > 0 ? 'up' : 'down'; ?>"></i>
                                        <?php echo abs(number_format($revenueGrowth, 1)); ?>%
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h5 text-primary mb-1">
                                    <?php echo number_format($monthlyComparison['this_month_bookings'] ?? 0); ?>
                                </div>
                                <small class="text-muted">การจองเดือนนี้</small>
                                <?php if ($bookingGrowth != 0): ?>
                                <div class="mt-1">
                                    <span class="badge bg-<?php echo $bookingGrowth > 0 ? 'success' : 'danger'; ?>">
                                        <i class="bi bi-arrow-<?php echo $bookingGrowth > 0 ? 'up' : 'down'; ?>"></i>
                                        <?php echo abs(number_format($bookingGrowth, 1)); ?>%
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        สถานะห้องปัจจุบัน
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="roomStatusChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        รายได้ 7 วันล่าสุด
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-speedometer me-2"></i>
                        อัตราการเข้าพัก 7 วัน
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="occupancyChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row g-3">
        <div class="col-lg-8">
            <!-- Recent Bookings -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            การจองล่าสุด
                        </h6>
                        <a href="<?php echo routeUrl('receipts.history'); ?>" class="btn btn-sm btn-outline-primary">
                            ดูทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentBookings)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">รหัส</th>
                                    <th class="border-0">ลูกค้า</th>
                                    <th class="border-0">ห้อง</th>
                                    <th class="border-0">แผน</th>
                                    <th class="border-0 text-end">ยอด</th>
                                    <th class="border-0">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentBookings, 0, 6) as $booking): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($booking['booking_code']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['guest_name']); ?></strong>
                                        <?php if ($booking['guest_phone']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($booking['guest_phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($booking['room_number']); ?></span>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($booking['room_type']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $booking['plan_type'] === 'short' ? 'warning' : 'success'; ?>">
                                            <?php echo $booking['plan_type'] === 'short' ? 'รายชั่วโมง' : 'รายคืน'; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong><?php echo money_format_thb($booking['total_amount']); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColor = [
                                            'active' => 'success',
                                            'completed' => 'primary',
                                            'cancelled' => 'danger'
                                        ][$booking['status']] ?? 'secondary';

                                        $statusText = [
                                            'active' => 'กำลังเข้าพัก',
                                            'completed' => 'เสร็จสิ้น',
                                            'cancelled' => 'ยกเลิก'
                                        ][$booking['status']] ?? $booking['status'];
                                        ?>
                                        <span class="badge bg-<?php echo $statusColor; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">ยังไม่มีการจอง</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        การดำเนินการด่วน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-primary">
                            <i class="bi bi-grid-3x3-gap me-2"></i>
                            แผงควบคุมห้องพัก
                        </a>
                        <a href="<?php echo routeUrl('reports.sales'); ?>" class="btn btn-outline-success">
                            <i class="bi bi-currency-dollar me-2"></i>
                            รายงานยอดขาย
                        </a>
                        <a href="<?php echo routeUrl('reports.occupancy'); ?>" class="btn btn-outline-info">
                            <i class="bi bi-pie-chart me-2"></i>
                            รายงานการเข้าพัก
                        </a>
                        <a href="<?php echo routeUrl('receipts.history'); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-receipt me-2"></i>
                            ประวัติใบเสร็จ
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        สถานะระบบ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>เซิร์ฟเวอร์</span>
                        <span class="badge bg-success">ออนไลน์</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>ฐานข้อมูล</span>
                        <span class="badge bg-success">ปกติ</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>ล่าสุด</span>
                        <small class="text-muted"><?php echo date('d/m/Y H:i'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Room Status Chart
const roomStatusCtx = document.getElementById('roomStatusChart').getContext('2d');
const roomStatusData = {
    available: <?php echo intval($roomStats['available'] ?? 0); ?>,
    occupied: <?php echo intval($roomStats['occupied'] ?? 0); ?>,
    cleaning: <?php echo intval($roomStats['cleaning'] ?? 0); ?>,
    maintenance: <?php echo intval($roomStats['maintenance'] ?? 0); ?>
};

const roomStatusChart = new Chart(roomStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['ว่าง', 'มีผู้พัก', 'ทำความสะอาด', 'ซ่อมบำรุง'],
        datasets: [{
            data: [roomStatusData.available, roomStatusData.occupied, roomStatusData.cleaning, roomStatusData.maintenance],
            backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#6c757d'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 10,
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: [<?php echo "'" . implode("','", array_reverse(array_column($salesData, 'sale_date'))) . "'"; ?>],
        datasets: [{
            label: 'รายได้ (บาท)',
            data: [<?php echo implode(',', array_reverse(array_column($salesData, 'total_revenue'))); ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '฿' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Occupancy Chart
const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
const occupancyChart = new Chart(occupancyCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo "'" . implode("','", array_reverse(array_column($occupancyReport['occupancy_by_date'], 'occupancy_date'))) . "'"; ?>],
        datasets: [{
            label: 'อัตราการเข้าพัก (%)',
            data: [<?php echo implode(',', array_reverse(array_column($occupancyReport['occupancy_by_date'], 'occupancy_rate'))); ?>],
            backgroundColor: 'rgba(255, 193, 7, 0.8)',
            borderColor: 'rgba(255, 193, 7, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});

// Auto-refresh every 5 minutes
setTimeout(() => {
    location.reload();
}, 300000);
</script>

<?php require_once __DIR__ . '/templates/layout/footer.php'; ?>