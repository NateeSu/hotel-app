<?php
/**
 * Hotel Management System - Receipt History
 *
 * View and manage receipt history
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
require_once __DIR__ . '/../lib/receipt_generator.php';
require_once __DIR__ . '/../templates/partials/flash.php';

// Require login with reception role or higher
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'ประวัติใบเสร็จ - Hotel Management System';
$pageDescription = 'จัดการและดูประวัติใบเสร็จทั้งหมด';

// Get pagination parameters
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Get search parameters
$searchTerm = trim($_GET['search'] ?? '');
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

try {
    $receiptGenerator = new ReceiptGenerator();
    $pdo = getDatabase();

    // Build search query
    $whereConditions = [];
    $params = [];

    if (!empty($searchTerm)) {
        $whereConditions[] = "(r.receipt_number LIKE ? OR r.booking_code LIKE ? OR r.guest_name LIKE ?)";
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(r.generated_at) >= ?";
        $params[] = $dateFrom;
    }

    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(r.generated_at) <= ?";
        $params[] = $dateTo;
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    // Get total count for pagination
    $countSql = "
        SELECT COUNT(*) as total
        FROM receipts r
        {$whereClause}
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalReceipts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalReceipts / $limit);

    // Get receipts for current page
    $sql = "
        SELECT
            r.*,
            b.guest_phone,
            rm.room_type,
            u.full_name as generated_by_name
        FROM receipts r
        LEFT JOIN bookings b ON r.booking_id = b.id
        LEFT JOIN rooms rm ON b.room_id = rm.id
        LEFT JOIN users u ON r.generated_by = u.id
        {$whereClause}
        ORDER BY r.generated_at DESC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดประวัติใบเสร็จ: ' . $e->getMessage());
    $receipts = [];
    $totalReceipts = 0;
    $totalPages = 0;
}

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-receipt text-primary me-2"></i>
            ประวัติใบเสร็จ
        </h1>
        <p class="text-muted mb-0">จัดการและดูประวัติใบเสร็จทั้งหมด</p>
    </div>
    <div>
        <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>กลับสู่แผงควบคุม
        </a>
    </div>
</div>

<!-- Search and Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">ค้นหา</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?php echo htmlspecialchars($searchTerm); ?>"
                       placeholder="เลขใบเสร็จ, รหัสจอง, หรือชื่อแขก">
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                <input type="date" class="form-control" id="date_from" name="date_from"
                       value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                <input type="date" class="form-control" id="date_to" name="date_to"
                       value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        พบ <?php echo number_format($totalReceipts); ?> รายการ
        <?php if (!empty($searchTerm) || !empty($dateFrom) || !empty($dateTo)): ?>
            จากการค้นหา
        <?php endif; ?>
    </div>

    <?php if (!empty($receipts)): ?>
    <div>
        <span class="text-muted">หน้า <?php echo $page; ?> จาก <?php echo $totalPages; ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- Receipts Table -->
<?php if (empty($receipts)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3 text-muted">ไม่พบใบเสร็จ</h5>
        <p class="text-muted">
            <?php if (!empty($searchTerm) || !empty($dateFrom) || !empty($dateTo)): ?>
                ไม่พบใบเสร็จที่ตรงกับเงื่อนไขการค้นหา
            <?php else: ?>
                ยังไม่มีใบเสร็จในระบบ
            <?php endif; ?>
        </p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>เลขใบเสร็จ</th>
                    <th>รหัสจอง</th>
                    <th>ชื่อแขก</th>
                    <th>ห้อง</th>
                    <th>จำนวนเงิน</th>
                    <th>การชำระเงิน</th>
                    <th>วันที่ออก</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receipts as $receipt): ?>
                <tr>
                    <td>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($receipt['receipt_number']); ?></span>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($receipt['booking_code']); ?></strong>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($receipt['guest_name']); ?></strong>
                            <?php if ($receipt['guest_phone']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($receipt['guest_phone']); ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info"><?php echo htmlspecialchars($receipt['room_number']); ?></span>
                        <?php if ($receipt['room_type']): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($receipt['room_type']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong class="text-success">฿<?php echo number_format($receipt['total_amount'], 2); ?></strong>
                    </td>
                    <td>
                        <?php
                        $paymentBadge = [
                            'cash' => 'bg-success',
                            'card' => 'bg-primary',
                            'transfer' => 'bg-info'
                        ][$receipt['payment_method']] ?? 'bg-secondary';

                        $paymentText = [
                            'cash' => 'เงินสด',
                            'card' => 'บัตร',
                            'transfer' => 'โอน'
                        ][$receipt['payment_method']] ?? 'ไม่ระบุ';
                        ?>
                        <span class="badge <?php echo $paymentBadge; ?>"><?php echo $paymentText; ?></span>
                    </td>
                    <td>
                        <div>
                            <?php echo date('d/m/Y', strtotime($receipt['generated_at'])); ?>
                            <br><small class="text-muted"><?php echo date('H:i', strtotime($receipt['generated_at'])); ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="<?php echo routeUrl('receipts.view', ['receipt_number' => $receipt['receipt_number']]); ?>"
                               class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-eye me-1"></i>ดู
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    onclick="printReceipt('<?php echo $receipt['receipt_number']; ?>')">
                                <i class="bi bi-printer me-1"></i>พิมพ์
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <!-- Previous page -->
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($searchTerm); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        <?php endif; ?>

        <!-- Page numbers -->
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);

        for ($i = $startPage; $i <= $endPage; $i++):
        ?>
        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">
                <?php echo $i; ?>
            </a>
        </li>
        <?php endfor; ?>

        <!-- Next page -->
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($searchTerm); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>

<script>
function printReceipt(receiptNumber) {
    const url = '<?php echo routeUrl("receipts.view"); ?>?receipt_number=' + encodeURIComponent(receiptNumber);
    const printWindow = window.open(url, 'receipt_print', 'width=900,height=700,scrollbars=yes,resizable=yes');

    if (printWindow) {
        // Wait for the receipt page to fully load
        let attempts = 0;
        const maxAttempts = 10;

        function tryToPrint() {
            attempts++;
            try {
                if (printWindow.document.readyState === 'complete') {
                    setTimeout(() => {
                        printWindow.print();
                    }, 800);
                } else if (attempts < maxAttempts) {
                    setTimeout(tryToPrint, 300);
                } else {
                    // Give up auto-printing, let user print manually
                    console.log('Auto-print timeout, user can print manually');
                }
            } catch (e) {
                console.log('Print error:', e.message);
            }
        }

        // Start trying to print after initial delay
        setTimeout(tryToPrint, 500);

    } else {
        alert('กรุณาอนุญาตให้เปิดหน้าต่างใหม่ หรือคลิกปุ่ม "ดู" เพื่อดูใบเสร็จ');
    }
}
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>