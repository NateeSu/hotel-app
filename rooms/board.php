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

// Handle AJAX requests FIRST to avoid any output
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    // Start session for AJAX
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Asia/Bangkok');

    // Load minimal required files for AJAX
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/auth.php';

    header('Content-Type: application/json');

    try {
        // Check authentication
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'User not authenticated']);
            exit;
        }

        // Get filter parameters
        $statusFilter = $_GET['status'] ?? '';

        // Database query
        $pdo = getDatabase();
        $sql = "SELECT id, room_number, room_type as type, status, notes FROM rooms";
        $params = [];

        if (!empty($statusFilter)) {
            $sql .= " WHERE status = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY room_number";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'rooms' => $rooms,
            'timestamp' => date('Y-m-d H:i:s'),
            'count' => count($rooms)
        ]);

    } catch (Exception $e) {
        error_log("AJAX error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit;
}

// Only initialize if not already loaded by index.php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);

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
} else {
    // Already initialized by index.php
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

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

    // Build query to get room information with booking details
    $sql = "
        SELECT
            r.id,
            r.room_number,
            r.room_type as type,
            r.status,
            r.notes,
            b.plan_type,
            b.checkin_at,
            b.checkout_at,
            b.guest_name
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'active'
    ";
    $params = [];

    if (!empty($statusFilter)) {
        $sql .= " WHERE r.status = ?";
        $params[] = $statusFilter;
    }

    $sql .= " ORDER BY r.room_number";

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

// AJAX handler moved to top of file

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
        <button type="button" class="btn btn-outline-primary" id="refreshBoard">
            <i class="bi bi-arrow-clockwise me-1"></i>
            <span class="d-none d-sm-inline">รีเฟรช</span>
        </button>

        <!-- Real-time Status -->
        <div class="d-flex align-items-center text-muted">
            <div class="spinner-border spinner-border-sm me-2" style="width: 1rem; height: 1rem; display: none;" id="refreshSpinner"></div>
            <i class="bi bi-broadcast text-success me-1"></i>
            <small>Real-time</small>
        </div>

        <!-- Settings (Admin only) -->
        <?php if (has_permission($userRole, ['admin'])): ?>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#settingsModal">
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
                            อัพเดตล่าสุด: <span id="lastUpdate"><?php echo date('H:i:s'); ?></span>
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
                        <h5 class="card-title mb-0">
                            <i class="bi bi-door-closed me-2"></i>
                            <?php echo htmlspecialchars($room['room_number']); ?>
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="room-status-info mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi <?php echo getRoomStatusIcon($room['status']); ?> me-2"></i>
                                <span class="fw-bold"><?php echo getRoomStatusText($room['status']); ?></span>
                            </div>

                            <?php if ($room['status'] === 'occupied' && !empty($room['plan_type'])): ?>
                                <!-- Booking Information -->
                                <div class="booking-info bg-light rounded p-2 mb-2">
                                    <div class="row g-1">
                                        <div class="col-12">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-person me-1"></i>
                                                <strong><?php echo htmlspecialchars($room['guest_name'] ?? 'ไม่ระบุชื่อ'); ?></strong>
                                            </small>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-tag me-1"></i>
                                                <strong><?php echo $room['plan_type'] === 'short' ? 'ชั่วคราว' : 'ค้างคืน'; ?></strong>
                                            </small>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-clock me-1"></i>
                                                เข้า: <?php echo format_datetime_thai($room['checkin_at'], 'd/m H:i'); ?>
                                            </small>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-clock-history me-1"></i>
                                                ออก: <?php echo format_datetime_thai($room['checkout_at'], 'd/m H:i'); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

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

/* Overdue room styling */
.overdue-room {
    animation: pulse-danger 2s infinite;
    box-shadow: 0 0 20px rgba(220, 53, 69, 0.3);
}

.overdue-room .card-header {
    background: linear-gradient(135deg, #dc3545, #c82333) !important;
    border-bottom: 2px solid rgba(255,255,255,0.3);
}

.overdue-warning {
    border-left: 4px solid #dc3545;
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0.5rem 0;
    padding: 0.5rem;
    border-radius: 0.25rem;
}

@keyframes pulse-danger {
    0% { border-color: #dc3545; }
    50% { border-color: #ff6b7a; }
    100% { border-color: #dc3545; }
}

.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

@media (max-width: 576px) {
    .col-sm-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}
</style>

<script>
let autoRefreshInterval = null;
let lastUpdateTime = null;
let notificationSound = null;
let shownNotifications = new Set(); // Track shown notifications

document.addEventListener('DOMContentLoaded', function() {
    console.log('Room board initialized successfully');

    // Initialize notification sound
    try {
        notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmsgBzyR1/K3dyMFl');
        notificationSound.volume = 0.3;
    } catch (e) {
        console.log('Sound notification not supported');
    }

    // Load settings from localStorage
    loadSettings();

    // Initialize real-time updates
    initializeRealTimeUpdates();

    // Initialize notification checking
    initializeNotificationChecking();

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

    // Room card click handlers
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

    // Manual refresh button
    const refreshButton = document.getElementById('refreshBoard');
    if (refreshButton) {
        refreshButton.onclick = function() {
            refreshRoomBoard();
        };
    }
});

// Real-time updates functionality
function initializeRealTimeUpdates() {
    const refreshInterval = localStorage.getItem('autoRefresh') || '30';
    if (refreshInterval > 0) {
        setAutoRefresh(parseInt(refreshInterval));
    }
}

function setAutoRefresh(seconds) {
    clearInterval(autoRefreshInterval);
    if (seconds > 0) {
        autoRefreshInterval = setInterval(refreshRoomBoard, seconds * 1000);
        console.log(`Auto refresh set to ${seconds} seconds`);
    }
}

function refreshRoomBoard() {
    const refreshSpinner = document.getElementById('refreshSpinner');
    if (refreshSpinner) refreshSpinner.style.display = 'inline-block';

    const url = '<?php echo $GLOBALS['baseUrl']; ?>/api/room_status.php?status=' + encodeURIComponent(getStatusFilter());

    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateRoomBoard(data.rooms);
                updateLastUpdateTime();
                checkForNotifications(data.rooms);
                console.log(`🔄 Updated ${data.count} rooms at ${new Date().toLocaleTimeString()}`);
            } else {
                console.error('❌ Failed to refresh room board:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error refreshing room board:', error);
        })
        .finally(() => {
            if (refreshSpinner) refreshSpinner.style.display = 'none';
        });
}

function updateRoomBoard(rooms) {
    const roomBoard = document.getElementById('roomBoard');
    if (!roomBoard) return;

    // Create a map of current rooms for comparison
    const currentRooms = new Map();
    const currentCards = roomBoard.querySelectorAll('.room-card');
    currentCards.forEach(card => {
        const roomNumber = card.querySelector('.card-title').textContent.trim().replace(/.*\s/, '');
        const status = card.className.match(/border-(\w+)/)?.[1];
        currentRooms.set(roomNumber, status);
    });

    // Update each room card
    rooms.forEach(room => {
        const currentStatus = currentRooms.get(room.room_number);
        const newStatus = getRoomStatusColor(room.status);

        if (currentStatus !== newStatus) {
            // Room status changed - trigger notification (but prevent duplicates)
            const statusChangeId = `status_change_${room.id}_${newStatus}_${Math.floor(Date.now() / 30000)}`; // Group by 30 seconds

            if (!shownNotifications.has(statusChangeId)) {
                showRoomStatusNotification(room, currentStatus, newStatus);
                shownNotifications.add(statusChangeId);
            }
        }

        updateRoomCard(room);
    });
}

function updateRoomCard(room) {
    const roomBoard = document.getElementById('roomBoard');
    const cards = roomBoard.querySelectorAll('.room-card');

    for (let card of cards) {
        const roomNumber = card.querySelector('.card-title').textContent.trim().replace(/.*\s/, '');
        if (roomNumber === room.room_number) {
            // Debug log to check room data (comment out in production)
            // console.log(`Updating room ${room.room_number}:`, {
            //     status: room.status,
            //     is_overdue: room.is_overdue,
            //     plan_type: room.plan_type,
            //     guest_name: room.guest_name
            // });

            // Ensure base classes are present
            if (!card.classList.contains('room-card')) {
                card.classList.add('room-card');
            }
            if (!card.classList.contains('h-100')) {
                card.classList.add('h-100');
            }
            if (!card.classList.contains('card')) {
                card.classList.add('card');
            }

            // Reset previous status-related classes
            card.classList.remove('overdue-room', 'border-3');
            // Remove all border color classes
            card.classList.remove('border-success', 'border-danger', 'border-warning', 'border-info', 'border-secondary');

            const header = card.querySelector('.card-header');
            if (header) {
                // Ensure base header classes are present
                if (!header.classList.contains('card-header')) {
                    header.classList.add('card-header');
                }
                if (!header.classList.contains('text-white')) {
                    header.classList.add('text-white');
                }

                // Remove all background color classes from header
                header.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-secondary');
            }

            // Determine status color
            let statusColor = getRoomStatusColor(room.status);

            // Add new border color class
            card.classList.add(`border-${statusColor}`);

            // Add new header background color
            if (header) {
                header.classList.add(`bg-${statusColor}`);
            }

            // Override with overdue styling ONLY if room is actually overdue AND occupied
            if (room.is_overdue && room.status === 'occupied') {
                // Remove regular border color and add overdue styling
                card.classList.remove(`border-${statusColor}`);
                card.classList.add('border-danger', 'border-3', 'overdue-room');

                if (header) {
                    header.classList.remove(`bg-${statusColor}`);
                    header.classList.add('bg-danger');
                }
            }

            // Update status text and icon
            const statusIcon = card.querySelector('.room-status-info i');
            const statusText = card.querySelector('.room-status-info span');

            if (statusIcon) statusIcon.className = `bi ${getRoomStatusIcon(room.status)} me-2`;
            if (statusText) statusText.textContent = getRoomStatusText(room.status);

            // Add or update overdue warning (ONLY for occupied rooms that are actually overdue)
            let overdueWarning = card.querySelector('.overdue-warning');
            if (room.is_overdue && room.status === 'occupied' && room.overdue_text) {
                if (!overdueWarning) {
                    overdueWarning = document.createElement('div');
                    overdueWarning.className = 'overdue-warning alert alert-danger alert-sm mt-2 mb-2';
                    card.querySelector('.card-body').appendChild(overdueWarning);
                }
                overdueWarning.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>เกินเวลา ${room.overdue_text}</strong>
                `;
            } else if (overdueWarning) {
                // Always remove overdue warning if room is not overdue or not occupied
                overdueWarning.remove();
            }


            // Update or add booking information ONLY for occupied rooms with booking data
            let bookingInfo = card.querySelector('.booking-info');
            if (room.status === 'occupied' && room.plan_type && room.guest_name) {
                if (!bookingInfo) {
                    // Create booking info section if it doesn't exist
                    bookingInfo = document.createElement('div');
                    bookingInfo.className = 'booking-info bg-light rounded p-2 mb-2';
                    const statusDiv = card.querySelector('.room-status-info');
                    statusDiv.insertBefore(bookingInfo, statusDiv.lastElementChild);
                }

                // Format dates for display
                const checkinDate = room.checkin_at ? new Date(room.checkin_at).toLocaleDateString('th-TH', {
                    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
                }) : 'ไม่ระบุ';

                const checkoutDate = room.checkout_at ? new Date(room.checkout_at).toLocaleDateString('th-TH', {
                    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
                }) : 'ไม่ระบุ';

                const planTypeText = room.plan_type === 'short' ? 'ชั่วคราว' : 'ค้างคืน';

                bookingInfo.innerHTML = `
                    <div class="row g-1">
                        <div class="col-12">
                            <small class="text-muted d-block">
                                <i class="bi bi-person me-1"></i>
                                <strong>${room.guest_name || 'ไม่ระบุชื่อ'}</strong>
                            </small>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">
                                <i class="bi bi-tag me-1"></i>
                                <strong>${planTypeText}</strong>
                            </small>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">
                                <i class="bi bi-clock me-1"></i>
                                เข้า: ${checkinDate}
                            </small>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">
                                <i class="bi bi-clock-history me-1"></i>
                                ออก: ${checkoutDate}
                            </small>
                        </div>
                    </div>
                `;
            } else if (bookingInfo) {
                // Remove booking info if room is not occupied or has no booking data
                bookingInfo.remove();
            }

            // Force refresh display to ensure clean state
            if (room.status !== 'occupied') {
                // Make sure no overdue classes remain for non-occupied rooms
                card.classList.remove('overdue-room', 'border-3');
                // Make sure no overdue warnings remain
                const anyOverdueWarning = card.querySelector('.overdue-warning');
                if (anyOverdueWarning) {
                    anyOverdueWarning.remove();
                }
                // Make sure no booking info remains
                const anyBookingInfo = card.querySelector('.booking-info');
                if (anyBookingInfo) {
                    anyBookingInfo.remove();
                }
            }

            // Reset any inline styles that might interfere with CSS
            card.style.removeProperty('transform');
            card.style.removeProperty('box-shadow');

            // Update action buttons
            const buttonContainer = card.querySelector('.d-grid');
            if (buttonContainer) {
                buttonContainer.innerHTML = generateActionButtons(room);
            }

            break;
        }
    }
}

function updateLastUpdateTime() {
    const lastUpdateElement = document.getElementById('lastUpdate');
    if (lastUpdateElement) {
        const now = new Date();
        lastUpdateElement.textContent = now.toLocaleTimeString('th-TH');
    }
}

function getStatusFilter() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('status') || '';
}

// Notification checking
function initializeNotificationChecking() {
    // Check every 10 seconds for urgent notifications
    setInterval(checkUrgentNotifications, 10000);
}

function checkUrgentNotifications() {
    fetch('<?php echo $GLOBALS['baseUrl']; ?>/api/check_notifications.php', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications) {
                data.notifications.forEach(notification => {
                    // Create unique ID for notification
                    const notificationId = `${notification.type}_${notification.room_id || 'general'}_${Math.floor(Date.now() / 60000)}`; // Group by minute

                    if (!shownNotifications.has(notificationId)) {
                        showNotification(notification);
                        shownNotifications.add(notificationId);

                        // Clean up old notification IDs (keep last 10 minutes)
                        if (shownNotifications.size > 20) {
                            const oldestIds = Array.from(shownNotifications).slice(0, 10);
                            oldestIds.forEach(id => shownNotifications.delete(id));
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

function checkForNotifications(rooms) {
    // Check for short-term checkout time notifications
    rooms.forEach(room => {
        if (room.status === 'occupied') {
            checkShortTermCheckout(room);
        }
    });
}

function checkShortTermCheckout(room) {
    // Fetch booking details for the room
    fetch(`<?php echo $GLOBALS['baseUrl']; ?>/api/get_room_booking.php?room_id=${room.id}`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.booking) {
                const booking = data.booking;
                if (booking.plan_type === 'short') {
                    const checkoutTime = new Date(booking.checkout_at);
                    const now = new Date();
                    const timeDiff = checkoutTime - now;

                    // Notify 15 minutes before checkout
                    if (timeDiff > 0 && timeDiff <= 15 * 60 * 1000) {
                        const warningId = `checkout_warning_${room.id}_${Math.floor(Date.now() / 300000)}`; // Group by 5 minutes
                        if (!shownNotifications.has(warningId)) {
                            showShortTermCheckoutNotification(room, booking, timeDiff);
                            shownNotifications.add(warningId);
                        }
                    }
                    // Notify when overdue
                    else if (timeDiff <= 0) {
                        const overdueId = `checkout_overdue_${room.id}_${Math.floor(Date.now() / 300000)}`; // Group by 5 minutes
                        if (!shownNotifications.has(overdueId)) {
                            showOverdueNotification(room, booking, Math.abs(timeDiff));
                            shownNotifications.add(overdueId);
                        }
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error checking booking:', error);
        });
}

function showNotification(notification) {
    // Create notification element
    const notificationEl = document.createElement('div');
    notificationEl.className = `alert alert-${notification.type} alert-dismissible fade show position-fixed`;
    notificationEl.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';

    notificationEl.innerHTML = `
        <i class="bi bi-${notification.icon} me-2"></i>
        <strong>${notification.title}</strong><br>
        ${notification.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notificationEl);

    // Play sound if enabled
    if (localStorage.getItem('soundNotifications') === 'true' && notificationSound) {
        notificationSound.play().catch(e => console.log('Could not play sound'));
    }

    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (notificationEl.parentNode) {
            notificationEl.remove();
        }
    }, 10000);
}

function showShortTermCheckoutNotification(room, booking, timeDiff) {
    const minutes = Math.ceil(timeDiff / (60 * 1000));
    showNotification({
        type: 'warning',
        icon: 'clock',
        title: 'ใกล้เวลาเช็คเอาท์',
        message: `ห้อง ${room.room_number} - ${booking.guest_name}<br>เหลือเวลา ${minutes} นาที`
    });
}

function showOverdueNotification(room, booking, overdueTime) {
    const minutes = Math.floor(overdueTime / (60 * 1000));
    showNotification({
        type: 'danger',
        icon: 'exclamation-triangle',
        title: 'เกินเวลาเช็คเอาท์',
        message: `ห้อง ${room.room_number} - ${booking.guest_name}<br>เกินเวลาแล้ว ${minutes} นาที`
    });
}

function showRoomStatusNotification(room, oldStatus, newStatus) {
    let message = '';
    let type = 'info';
    let icon = 'info-circle';

    if (newStatus === 'success') { // available
        message = `ห้อง ${room.room_number} พร้อมให้บริการแล้ว`;
        type = 'success';
        icon = 'check-circle';
    } else if (newStatus === 'danger') { // occupied
        message = `ห้อง ${room.room_number} มีแขกเข้าพัก`;
        type = 'info';
        icon = 'person-fill';
    } else if (newStatus === 'warning') { // cleaning
        message = `ห้อง ${room.room_number} กำลังทำความสะอาด`;
        type = 'warning';
        icon = 'brush';
    }

    if (message) {
        showNotification({
            type: type,
            icon: icon,
            title: 'สถานะห้องเปลี่ยนแปลง',
            message: message
        });
    }
}

// Settings management
function loadSettings() {
    const autoRefresh = localStorage.getItem('autoRefresh') || '30';
    const soundNotifications = localStorage.getItem('soundNotifications') === 'true';

    const autoRefreshSelect = document.getElementById('autoRefresh');
    const soundNotificationsCheck = document.getElementById('soundNotifications');

    if (autoRefreshSelect) autoRefreshSelect.value = autoRefresh;
    if (soundNotificationsCheck) soundNotificationsCheck.checked = soundNotifications;
}

function saveSettings() {
    const autoRefresh = document.getElementById('autoRefresh')?.value || '30';
    const soundNotifications = document.getElementById('soundNotifications')?.checked || false;

    localStorage.setItem('autoRefresh', autoRefresh);
    localStorage.setItem('soundNotifications', soundNotifications);

    // Apply settings
    setAutoRefresh(parseInt(autoRefresh));

    // Close modal
    const modal = document.getElementById('settingsModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    }

    showNotification({
        type: 'success',
        icon: 'check',
        title: 'บันทึกการตั้งค่า',
        message: 'การตั้งค่าถูกบันทึกเรียบร้อยแล้ว'
    });
}

// Helper functions (these need to be available in JavaScript)
function getRoomStatusColor(status) {
    switch (status) {
        case 'available': return 'success';
        case 'occupied': return 'danger';
        case 'cleaning':
        case 'cg': return 'warning';
        case 'maintenance': return 'secondary';
        default: return 'light';
    }
}

function getRoomStatusIcon(status) {
    switch (status) {
        case 'available': return 'bi-check-circle';
        case 'occupied': return 'bi-person-fill';
        case 'cleaning':
        case 'cg': return 'bi-brush';
        case 'maintenance': return 'bi-tools';
        default: return 'bi-question-circle';
    }
}

function getRoomStatusText(status) {
    switch (status) {
        case 'available': return 'ว่าง';
        case 'occupied': return 'มีผู้พัก';
        case 'cleaning':
        case 'cg': return 'ทำความสะอาด';
        case 'maintenance': return 'ซ่อมบำรุง';
        default: return 'ไม่ระบุ';
    }
}

// Generate action buttons based on room status
function generateActionButtons(room) {
    const baseUrl = '<?php echo $GLOBALS['baseUrl']; ?>';
    const csrfToken = '<?php echo get_csrf_token(); ?>';
    let buttons = '';

    switch (room.status) {
        case 'available':
            buttons = `<a href="${baseUrl}/?r=rooms.checkin&room_id=${room.id}" class="btn btn-success btn-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i>Check-in
            </a>`;
            break;

        case 'occupied':
            buttons = `
                <form method="POST" action="${baseUrl}/?r=rooms.checkout" style="display: inline;" class="mb-1">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="room_id" value="${room.id}">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-box-arrow-left me-1"></i>Check-out
                    </button>
                </form>
                <a href="${baseUrl}/?r=rooms.transfer&room_id=${room.id}" class="btn btn-outline-info btn-sm w-100">
                    <i class="bi bi-arrow-left-right me-1"></i>ย้ายห้อง
                </a>`;
            break;

        case 'cleaning':
        case 'cg':
            buttons = `
                <form method="POST" action="${baseUrl}/?r=rooms.cleanDone" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="room_id" value="${room.id}">
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-check-circle me-1"></i>Mark Done
                    </button>
                </form>`;
            break;

        case 'maintenance':
            buttons = `
                <form method="POST" action="${baseUrl}/?r=rooms.edit" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="room_id" value="${room.id}">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                </form>`;
            break;

        default:
            buttons = '<span class="text-muted">ไม่มีการกระทำ</span>';
            break;
    }

    return buttons;
}

// Event listeners for settings
document.addEventListener('DOMContentLoaded', function() {
    const saveSettingsBtn = document.getElementById('saveSettings');
    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', saveSettings);
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../templates/layout/footer.php';
?>