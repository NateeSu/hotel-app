<?php
/**
 * Hotel Management System - Room Board
 *
 * This page displays the room status board showing all rooms with their
 * current status and availability. Requires reception role or higher.
 */

// Enable error reporting for debugging (comment out for production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

// Start session and initialize application
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Bangkok');

// Define base URL - fix for XAMPP Windows paths
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME']; // /hotel-app/rooms/board.php
$appPath = '/hotel-app'; // Force correct path for XAMPP setup
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
$pageTitle = 'แผงควบคุมห้องพัก - Hotel Management System';
$pageDescription = 'แสดงสถานะห้องพักทั้งหมดในระบบ';

// Set breadcrumbs
$breadcrumbs = [
    ['title' => 'แผงควบคุมห้องพัก', 'url' => routeUrl('rooms.board')]
];

// Get current user for permission checks
$currentUser = currentUser();
$userRole = $currentUser['role'];

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';

// Fetch rooms from database
try {
    $pdo = getDatabase();

    // Build query with correct column names for this database
    $sql = "SELECT id, room_number, room_type as type, status, notes FROM rooms";
    $params = [];

    if (!empty($statusFilter)) {
        $sql .= " WHERE status = ?";
        $params[] = $statusFilter;
    }

    $sql .= " ORDER BY room_number";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดข้อมูลห้องพัก: ' . $e->getMessage());
    $rooms = [];
}

// Helper functions for room display
function getRoomStatusColor($status) {
    switch ($status) {
        case 'available': return 'success';
        case 'occupied': return 'danger';
        case 'cleaning':
        case 'cg': return 'warning';  // Handle both cleaning and cg
        case 'maintenance': return 'secondary';
        default: return 'light';
    }
}

function getRoomStatusIcon($status) {
    switch ($status) {
        case 'available': return 'bi-check-circle';
        case 'occupied': return 'bi-person-fill';
        case 'cleaning':
        case 'cg': return 'bi-brush';  // Handle both cleaning and cg
        case 'maintenance': return 'bi-tools';
        default: return 'bi-question-circle';
    }
}

function getRoomStatusText($status) {
    switch ($status) {
        case 'available': return 'ว่าง';
        case 'occupied': return 'มีผู้พัก';
        case 'cleaning':
        case 'cg': return 'ทำความสะอาด';  // Handle both cleaning and cg
        case 'maintenance': return 'ซ่อมบำรุง';
        default: return 'ไม่ระบุ';
    }
}

function getRoomActionButtons($room) {
    $buttons = '';
    $roomId = $room['id'];
    $csrfToken = get_csrf_token();

    switch ($room['status']) {
        case 'available':
            $buttons .= '<a href="' . routeUrl('rooms.checkin', ['room_id' => $roomId]) . '" class="btn btn-success btn-sm">';
            $buttons .= '<i class="bi bi-box-arrow-in-right me-1"></i>Check-in';
            $buttons .= '</a>';
            break;

        case 'occupied':
            $buttons .= '<form method="POST" action="' . routeUrl('rooms.checkout') . '" style="display: inline;" class="mb-1">';
            $buttons .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">';
            $buttons .= '<input type="hidden" name="room_id" value="' . $roomId . '">';
            $buttons .= '<button type="submit" class="btn btn-primary btn-sm w-100">';
            $buttons .= '<i class="bi bi-box-arrow-left me-1"></i>Check-out';
            $buttons .= '</button>';
            $buttons .= '</form>';

            $buttons .= '<a href="' . $GLOBALS['baseUrl'] . '/?r=rooms.transfer&room_id=' . $roomId . '" class="btn btn-outline-info btn-sm w-100">';
            $buttons .= '<i class="bi bi-arrow-left-right me-1"></i>ย้ายห้อง';
            $buttons .= '</a>';
            break;

        case 'cleaning':
        case 'cg':  // Handle both cleaning and cg status
            $buttons .= '<form method="POST" action="' . routeUrl('rooms.cleanDone') . '" style="display: inline;">';
            $buttons .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">';
            $buttons .= '<input type="hidden" name="room_id" value="' . $roomId . '">';
            $buttons .= '<button type="submit" class="btn btn-warning btn-sm">';
            $buttons .= '<i class="bi bi-check-circle me-1"></i>Mark Done';
            $buttons .= '</button>';
            $buttons .= '</form>';
            break;

        case 'maintenance':
            $buttons .= '<form method="POST" action="' . routeUrl('rooms.edit') . '" style="display: inline;">';
            $buttons .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">';
            $buttons .= '<input type="hidden" name="room_id" value="' . $roomId . '">';
            $buttons .= '<button type="submit" class="btn btn-secondary btn-sm">';
            $buttons .= '<i class="bi bi-pencil me-1"></i>Edit';
            $buttons .= '</button>';
            $buttons .= '</form>';
            break;

        default:
            $buttons .= '<span class="text-muted">ไม่มีการกระทำ</span>';
            break;
    }

    return $buttons;
}

// Handle AJAX requests for real-time updates
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');

    try {
        echo json_encode([
            'success' => true,
            'rooms' => $rooms,
            'timestamp' => now()
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล'
        ]);
    }

    exit;
}

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
            แผงควบคุมห้องพัก
        </h1>
        <p class="text-muted mb-0">แสดงสถานะห้องพักทั้งหมด อัพเดตแบบเรียลไทม์</p>
    </div>

    <div class="d-flex gap-2 align-items-center flex-wrap">
        <!-- Status Filter -->
        <select class="form-select" style="width: auto;" onchange="filterRooms(this.value)">
            <option value="">ทุกสถานะ</option>
            <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>ว่าง</option>
            <option value="occupied" <?php echo $statusFilter === 'occupied' ? 'selected' : ''; ?>>มีผู้พัก</option>
            <option value="cg" <?php echo $statusFilter === 'cg' ? 'selected' : ''; ?>>ทำความสะอาด</option>
            <option value="maintenance" <?php echo $statusFilter === 'maintenance' ? 'selected' : ''; ?>>ซ่อมบำรุง</option>
        </select>

        <!-- Refresh Button -->
        <button type="button" class="btn btn-outline-primary" id="refreshBoard" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i>
            <span class="d-none d-sm-inline">รีเฟรช</span>
        </button>

        <!-- Settings (Admin only) -->
        <?php if (has_permission($userRole, ['admin'])): ?>
        <button type="button" class="btn btn-outline-secondary">
            <i class="bi bi-gear me-1"></i>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Status Legend -->
<div class="row mb-4">
    <div class="col">
        <div class="card border-0 bg-light">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="text-muted fw-medium me-2">สถานะห้อง:</span>

                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-success me-2"></div>
                        <small>ว่าง</small>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-danger me-2"></div>
                        <small>มีผู้เข้าพัก</small>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-warning me-2"></div>
                        <small>ทำความสะอาด</small>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-info me-2"></div>
                        <small>ซ่อมบำรุง</small>
                    </div>

                    <div class="ms-auto">
                        <small class="text-muted">
                            อัพเดตล่าสุด: <span id="lastUpdate"><?php echo format_datetime_thai(now(), 'H:i:s'); ?></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Room Board Grid -->
<div class="row" id="roomBoard">
    <?php if (empty($rooms)): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-house text-muted" style="font-size: 3rem;"></i>
                </div>
                <h4 class="text-muted">ไม่พบข้อมูลห้องพัก</h4>
                <p class="text-muted">กรุณาตรวจสอบการเชื่อมต่อฐานข้อมูลและข้อมูลในตาราง rooms</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($rooms as $room): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card room-card h-100 <?php echo 'border-' . getRoomStatusColor($room['status']); ?>">
                    <div class="card-header bg-<?php echo getRoomStatusColor($room['status']); ?> text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-door-closed me-2"></i>
                                <?php echo htmlspecialchars($room['room_number']); ?>
                            </h5>
                            <span class="badge bg-light text-dark">
                                <?php echo $room['type'] === 'short' ? 'ชั่วคราว' : 'ค้างคืน'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="room-status-info mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi <?php echo getRoomStatusIcon($room['status']); ?> me-2"></i>
                                <span class="fw-bold"><?php echo getRoomStatusText($room['status']); ?></span>
                            </div>

                            <?php if (!empty($room['notes'])): ?>
                                <small class="text-muted">
                                    <i class="bi bi-sticky me-1"></i>
                                    <?php echo htmlspecialchars($room['notes']); ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <?php echo getRoomActionButtons($room); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Settings Modal (Admin only) -->
<?php if (has_permission($userRole, ['admin'])): ?>
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-gear me-2"></i>
                    ตั้งค่าแผงควบคุม
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="autoRefresh" class="form-label">รีเฟรชอัตโนมัติ (วินาที)</label>
                    <select class="form-select" id="autoRefresh">
                        <option value="0">ปิดการรีเฟรช</option>
                        <option value="30" selected>30 วินาที</option>
                        <option value="60">60 วินาที</option>
                        <option value="120">120 วินาที</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showRoomDetails" checked>
                        <label class="form-check-label" for="showRoomDetails">
                            แสดงรายละเอียดห้อง
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="soundNotifications">
                        <label class="form-check-label" for="soundNotifications">
                            เสียงแจ้งเตือน
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="saveSettings">บันทึกการตั้งค่า</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.room-card {
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}

.room-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.room-card .card-header {
    border-bottom: none;
    font-weight: 600;
}

.room-status-info {
    min-height: 60px;
}

@media (max-width: 576px) {
    .col-sm-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Room board initialized successfully');

    // Filter functionality
    window.filterRooms = function(status) {
        const url = new URL(window.location);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    };

    // Room card click handlers (check elements exist first)
    const roomBoard = document.getElementById('roomBoard');
    if (roomBoard) {
        roomBoard.addEventListener('click', function(event) {
            const roomCard = event.target.closest('.room-card');
            if (roomCard && !event.target.closest('button') && !event.target.closest('form')) {
                const cardTitle = roomCard.querySelector('.card-title');
                if (cardTitle) {
                    const roomNumber = cardTitle.textContent.trim().replace(/.*\s/, '');
                    console.log('Room card clicked:', roomNumber);
                }
            }
        });
    }

    // Refresh button functionality (already has onclick in HTML)
    const refreshButton = document.getElementById('refreshBoard');
    if (refreshButton) {
        console.log('Refresh button found and ready');
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../templates/layout/footer.php';
?>