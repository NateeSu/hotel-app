<?php
/**
 * Hotel Management System - Room Management
 *
 * Admin interface for managing rooms (CRUD operations)
 */

// Start session and initialize application
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Bangkok');

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

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

// Require admin access
requireLogin(['admin']);

// Set page variables
$pageTitle = 'จัดการห้องพัก - Hotel Management System';
$pageDescription = 'เพิ่ม แก้ไข ลบ และจัดการข้อมูลห้องพัก';

$pdo = getDatabase();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('CSRF token verification failed');
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'add_room') {
            // Add new room
            $roomNumber = trim($_POST['room_number'] ?? '');
            $roomType = $_POST['room_type'] ?? '';
            $maxOccupancy = intval($_POST['max_occupancy'] ?? 2);
            $notes = trim($_POST['notes'] ?? '');

            if (empty($roomNumber)) {
                throw new Exception('กรุณาระบุหมายเลขห้อง');
            }

            if (!in_array($roomType, ['double', 'single'])) {
                throw new Exception('กรุณาเลือกประเภทห้อง');
            }

            // Check if room number already exists
            $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
            $stmt->execute([$roomNumber]);
            if ($stmt->fetch()) {
                throw new Exception('หมายเลขห้องนี้มีอยู่แล้วในระบบ');
            }

            // Insert new room
            $stmt = $pdo->prepare("
                INSERT INTO rooms (room_number, room_type, status, max_occupancy, notes, created_at)
                VALUES (?, ?, 'available', ?, ?, NOW())
            ");
            $stmt->execute([$roomNumber, $roomType, $maxOccupancy, $notes]);

            flash_success("เพิ่มห้อง {$roomNumber} เรียบร้อยแล้ว");
            header("Location: " . routeUrl('admin.rooms'));
            exit;

        } elseif ($action === 'edit_room') {
            // Edit existing room
            $roomId = intval($_POST['room_id'] ?? 0);
            $roomNumber = trim($_POST['room_number'] ?? '');
            $roomType = $_POST['room_type'] ?? '';
            $maxOccupancy = intval($_POST['max_occupancy'] ?? 2);
            $notes = trim($_POST['notes'] ?? '');

            if (!$roomId) {
                throw new Exception('ไม่พบข้อมูลห้อง');
            }

            if (empty($roomNumber)) {
                throw new Exception('กรุณาระบุหมายเลขห้อง');
            }

            // Check if room number exists for other rooms
            $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
            $stmt->execute([$roomNumber, $roomId]);
            if ($stmt->fetch()) {
                throw new Exception('หมายเลขห้องนี้มีอยู่แล้วในระบบ');
            }

            // Update room
            $stmt = $pdo->prepare("
                UPDATE rooms
                SET room_number = ?, room_type = ?, max_occupancy = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$roomNumber, $roomType, $maxOccupancy, $notes, $roomId]);

            flash_success("แก้ไขข้อมูลห้อง {$roomNumber} เรียบร้อยแล้ว");
            header("Location: " . routeUrl('admin.rooms'));
            exit;

        } elseif ($action === 'change_status') {
            // Change room status
            $roomId = intval($_POST['room_id'] ?? 0);
            $newStatus = $_POST['status'] ?? '';
            $notes = trim($_POST['status_notes'] ?? '');

            if (!$roomId) {
                throw new Exception('ไม่พบข้อมูลห้อง');
            }

            if (!in_array($newStatus, ['available', 'maintenance'])) {
                throw new Exception('สถานะไม่ถูกต้อง');
            }

            // Check if room is currently occupied
            $stmt = $pdo->prepare("SELECT status FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();

            if ($room['status'] === 'occupied') {
                throw new Exception('ไม่สามารถเปลี่ยนสถานะห้องที่มีผู้เข้าพักได้');
            }

            // Update room status
            $stmt = $pdo->prepare("
                UPDATE rooms
                SET status = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$newStatus, $notes, $roomId]);

            $statusText = $newStatus === 'available' ? 'พร้อมใช้งาน' : 'ซ่อมบำรุง';
            flash_success("เปลี่ยนสถานะห้องเป็น '{$statusText}' เรียบร้อยแล้ว");
            header("Location: " . routeUrl('admin.rooms'));
            exit;

        } elseif ($action === 'delete_room') {
            // Delete room
            $roomId = intval($_POST['room_id'] ?? 0);

            if (!$roomId) {
                throw new Exception('ไม่พบข้อมูลห้อง');
            }

            // Check if room has any bookings
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ?");
            $stmt->execute([$roomId]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                throw new Exception('ไม่สามารถลบห้องที่มีประวัติการจองได้ กรุณาเปลี่ยนสถานะเป็นซ่อมบำรุงแทน');
            }

            // Check if room is occupied
            $stmt = $pdo->prepare("SELECT status FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();

            if ($room['status'] === 'occupied') {
                throw new Exception('ไม่สามารถลบห้องที่มีผู้เข้าพักได้');
            }

            // Delete room
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);

            flash_success("ลบห้องพักเรียบร้อยแล้ว");
            header("Location: " . routeUrl('admin.rooms'));
            exit;
        }

    } catch (Exception $e) {
        flash_error('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

// Fetch all rooms
try {
    $sql = "
        SELECT
            r.id,
            r.room_number,
            r.room_type,
            r.status,
            r.max_occupancy,
            r.notes,
            r.created_at,
            r.updated_at,
            COUNT(b.id) as booking_count,
            MAX(b.checkin_at) as last_booking
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id
        GROUP BY r.id
        ORDER BY r.room_number
    ";
    $stmt = $pdo->query($sql);
    $rooms = $stmt->fetchAll();

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage());
    $rooms = [];
}

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-door-open text-primary me-2"></i>
                จัดการห้องพัก
            </h1>
            <p class="text-muted mb-0">เพิ่ม แก้ไข และจัดการข้อมูลห้องพักทั้งหมด</p>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="bi bi-plus-circle me-1"></i>
            เพิ่มห้องพักใหม่
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <?php
        $totalRooms = count($rooms);
        $availableRooms = count(array_filter($rooms, fn($r) => $r['status'] === 'available'));
        $occupiedRooms = count(array_filter($rooms, fn($r) => $r['status'] === 'occupied'));
        $maintenanceRooms = count(array_filter($rooms, fn($r) => $r['status'] === 'maintenance'));
        ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">ห้องทั้งหมด</p>
                            <h3 class="mb-0"><?php echo $totalRooms; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-building fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">ว่าง</p>
                            <h3 class="mb-0 text-success"><?php echo $availableRooms; ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">มีผู้เข้าพัก</p>
                            <h3 class="mb-0 text-danger"><?php echo $occupiedRooms; ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-person-fill fs-3 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">ซ่อมบำรุง</p>
                            <h3 class="mb-0 text-warning"><?php echo $maintenanceRooms; ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-tools fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="card-title mb-0">
                <i class="bi bi-list-ul me-2"></i>
                รายการห้องพักทั้งหมด
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($rooms)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">ยังไม่มีห้องพักในระบบ</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="bi bi-plus-circle me-1"></i>
                        เพิ่มห้องพักแรก
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>หมายเลขห้อง</th>
                                <th>ประเภทห้อง</th>
                                <th>สถานะ</th>
                                <th>จำนวนคนพักได้</th>
                                <th>จำนวนการจอง</th>
                                <th>หมายเหตุ</th>
                                <th class="text-end">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?php echo htmlspecialchars($room['room_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $typeText = get_room_type_label($room['room_type']);
                                        $typeColor = $room['room_type'] === 'single' ? 'info' : 'purple';
                                        ?>
                                        <span class="badge bg-<?php echo $typeColor; ?>"><?php echo $typeText; ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusInfo = getRoomStatusInfo($room['status']);
                                        ?>
                                        <span class="badge bg-<?php echo $statusInfo['class']; ?>">
                                            <?php echo $statusInfo['label']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-people me-1"></i>
                                        <?php echo $room['max_occupancy']; ?> คน
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $room['booking_count']; ?> ครั้ง</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($room['notes']) ?: '-'; ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                    onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning"
                                                    onclick="changeStatus(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['room_number']); ?>', '<?php echo $room['status']; ?>')">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <?php if ($room['booking_count'] == 0 && $room['status'] !== 'occupied'): ?>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="deleteRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['room_number']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-secondary" disabled title="ไม่สามารถลบห้องที่มีประวัติการจองได้">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    เพิ่มห้องพักใหม่
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_room">

                    <div class="mb-3">
                        <label for="room_number" class="form-label">
                            หมายเลขห้อง <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="room_number" name="room_number" required
                               placeholder="เช่น 101, 201, A01">
                    </div>

                    <div class="mb-3">
                        <label for="room_type" class="form-label">
                            ประเภทห้อง <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="room_type" name="room_type" required>
                            <option value="">เลือกประเภท</option>
                            <option value="double">เตียงคู่</option>
                            <option value="single">เตียงเดี่ยว</option>
                        </select>
                        <div class="form-text">
                            <small>ประเภทห้องเป็นข้อมูลอ้างอิง ไม่จำกัดการเลือกประเภทการพักเมื่อเช็คอิน</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="max_occupancy" class="form-label">
                            จำนวนคนพักได้สูงสุด
                        </label>
                        <input type="number" class="form-control" id="max_occupancy" name="max_occupancy"
                               value="2" min="1" max="10" required>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="ข้อมูลเพิ่มเติมเกี่ยวกับห้อง"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        เพิ่มห้อง
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    แก้ไขข้อมูลห้อง
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="edit_room">
                    <input type="hidden" name="room_id" id="edit_room_id">

                    <div class="mb-3">
                        <label for="edit_room_number" class="form-label">
                            หมายเลขห้อง <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_room_number" name="room_number" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_room_type" class="form-label">
                            ประเภทห้อง <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="edit_room_type" name="room_type" required>
                            <option value="double">เตียงคู่</option>
                            <option value="single">เตียงเดี่ยว</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_max_occupancy" class="form-label">
                            จำนวนคนพักได้สูงสุด
                        </label>
                        <input type="number" class="form-control" id="edit_max_occupancy" name="max_occupancy"
                               min="1" max="10" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    เปลี่ยนสถานะห้อง
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="room_id" id="status_room_id">

                    <p class="mb-3">
                        เปลี่ยนสถานะห้อง: <strong id="status_room_number"></strong>
                    </p>

                    <div class="mb-3">
                        <label for="status" class="form-label">
                            สถานะใหม่ <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available">พร้อมใช้งาน</option>
                            <option value="maintenance">ซ่อมบำรุง</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status_notes" class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" id="status_notes" name="status_notes" rows="3"
                                  placeholder="เหตุผลในการเปลี่ยนสถานะ"></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>หมายเหตุ: ไม่สามารถเปลี่ยนสถานะห้องที่มีผู้เข้าพักได้</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>
                        เปลี่ยนสถานะ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Room Modal -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ยืนยันการลบห้อง
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="delete_room">
                    <input type="hidden" name="room_id" id="delete_room_id">

                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>คำเตือน!</strong> การลบห้องไม่สามารถย้อนกลับได้
                    </div>

                    <p class="mb-0">
                        คุณแน่ใจหรือไม่ที่จะลบห้อง: <strong id="delete_room_number"></strong>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        ลบห้อง
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit room function
function editRoom(room) {
    document.getElementById('edit_room_id').value = room.id;
    document.getElementById('edit_room_number').value = room.room_number;
    document.getElementById('edit_room_type').value = room.room_type;
    document.getElementById('edit_max_occupancy').value = room.max_occupancy;
    document.getElementById('edit_notes').value = room.notes || '';

    const modal = new bootstrap.Modal(document.getElementById('editRoomModal'));
    modal.show();
}

// Change status function
function changeStatus(roomId, roomNumber, currentStatus) {
    document.getElementById('status_room_id').value = roomId;
    document.getElementById('status_room_number').textContent = roomNumber;

    // Set opposite status as default
    const newStatus = currentStatus === 'available' ? 'maintenance' : 'available';
    document.getElementById('status').value = newStatus;

    const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
    modal.show();
}

// Delete room function
function deleteRoom(roomId, roomNumber) {
    document.getElementById('delete_room_id').value = roomId;
    document.getElementById('delete_room_number').textContent = roomNumber;

    const modal = new bootstrap.Modal(document.getElementById('deleteRoomModal'));
    modal.show();
}
</script>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>