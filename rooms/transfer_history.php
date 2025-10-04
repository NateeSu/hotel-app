<?php
/**
 * Hotel Management System - Room Transfer History
 *
 * View history of room transfers
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
    require_once __DIR__ . '/../lib/transfer_engine.php';
    require_once __DIR__ . '/../templates/partials/flash.php';

} else {
    // Already initialized by index.php
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

// Require login with reception role or higher
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'ประวัติการย้ายห้อง - Hotel Management System';
$pageDescription = 'ประวัติการย้ายห้องแขก';

// Get parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $pdo = getDatabase();
    $transferEngine = new TransferEngine();

    // Get transfer history
    $transfers = $transferEngine->getTransferHistory($limit, $offset);

    // Get total count for pagination
    $stmt = $pdo->query("SELECT COUNT(*) FROM room_transfers");
    $totalTransfers = $stmt->fetchColumn();
    $totalPages = ceil($totalTransfers / $limit);

} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Transfer history error: " . $e->getMessage());
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
                        <i class="bi bi-clock-history text-info me-2"></i>
                        ประวัติการย้ายห้อง
                    </h1>
                    <p class="text-muted mb-0">รายการการย้ายห้องทั้งหมด</p>
                </div>

                <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=rooms.board" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    กลับสู่แดชบอร์ด
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list me-2"></i>
                            รายการการย้ายห้อง
                        </h5>
                        <small class="text-muted">
                            รายการทั้งหมด <?php echo number_format($totalTransfers); ?> รายการ
                        </small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transfers)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <p class="text-muted mt-3">ยังไม่มีประวัติการย้ายห้อง</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>วันที่</th>
                                        <th>แขก</th>
                                        <th>ห้องเดิม</th>
                                        <th>ห้องใหม่</th>
                                        <th>เหตุผล</th>
                                        <th>ค่าใช้จ่าย</th>
                                        <th>สถานะการชำระ</th>
                                        <th>ดำเนินการโดย</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transfers as $transfer): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($transfer['transfer_date'])); ?>
                                                </small><br>
                                                <small class="text-muted">
                                                    <?php echo date('H:i', strtotime($transfer['transfer_date'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($transfer['guest_name']); ?></strong>
                                                </div>
                                                <small class="text-muted"><?php echo htmlspecialchars($transfer['guest_phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($transfer['from_room']); ?>
                                                </span><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($transfer['from_room_type']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($transfer['to_room']); ?>
                                                </span><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($transfer['to_room_type']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo match($transfer['transfer_reason']) {
                                                        'upgrade' => 'success',
                                                        'downgrade' => 'warning',
                                                        'maintenance' => 'danger',
                                                        'guest_request' => 'info',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php
                                                    $reasons = [
                                                        'upgrade' => 'อัพเกรด',
                                                        'downgrade' => 'ดาวน์เกรด',
                                                        'maintenance' => 'ซ่อมบำรุง',
                                                        'guest_request' => 'ตามความต้องการ',
                                                        'overbooking' => 'จองเกิน',
                                                        'room_issue' => 'ปัญหาห้อง',
                                                        'other' => 'อื่นๆ'
                                                    ];
                                                    echo $reasons[$transfer['transfer_reason']] ?? $transfer['transfer_reason'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($transfer['total_adjustment'] > 0): ?>
                                                    <span class="text-success">
                                                        +฿<?php echo number_format($transfer['total_adjustment'], 2); ?>
                                                    </span>
                                                <?php elseif ($transfer['total_adjustment'] < 0): ?>
                                                    <span class="text-danger">
                                                        ฿<?php echo number_format($transfer['total_adjustment'], 2); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($transfer['total_adjustment'] != 0): ?>
                                                    <span class="badge bg-<?php
                                                        echo match($transfer['payment_status']) {
                                                            'paid' => 'success',
                                                            'pending' => 'warning',
                                                            'waived' => 'info',
                                                            'refunded' => 'secondary',
                                                            default => 'light'
                                                        };
                                                    ?>">
                                                        <?php
                                                        $paymentStatus = [
                                                            'paid' => 'ชำระแล้ว',
                                                            'pending' => 'รอชำระ',
                                                            'waived' => 'ยกเว้น',
                                                            'refunded' => 'คืนเงิน'
                                                        ];
                                                        echo $paymentStatus[$transfer['payment_status']] ?? '-';
                                                        ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($transfer['transferred_by_name']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo match($transfer['status']) {
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php
                                                    $statusText = [
                                                        'completed' => 'เสร็จสิ้น',
                                                        'pending' => 'รอดำเนินการ',
                                                        'cancelled' => 'ยกเลิก'
                                                    ];
                                                    echo $statusText[$transfer['status']] ?? $transfer['status'];
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Transfer history pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>