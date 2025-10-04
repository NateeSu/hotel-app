<?php
/**
 * Hotel Management System - Room Transfer Interface
 *
 * Interface for transferring guests to different rooms
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
$pageTitle = 'ย้ายห้องแขก - Hotel Management System';
$pageDescription = 'ย้ายแขกไปยังห้องอื่น';

// Get parameters
$roomId = $_GET['room_id'] ?? null;
$bookingId = $_GET['booking_id'] ?? null;

if (!$roomId) {
    flash_error('ไม่ได้ระบุห้องที่ต้องการย้าย');
    header('Location: ' . $GLOBALS['baseUrl'] . '/?r=rooms.board');
    exit;
}

try {
    $pdo = getDatabase();
    $transferEngine = new TransferEngine();

    // Get current booking
    $currentBooking = $transferEngine->getCurrentBooking($roomId);
    if (!$currentBooking) {
        flash_error('ไม่พบการจองที่ใช้งานอยู่ในห้องนี้');
        header('Location: ' . $GLOBALS['baseUrl'] . '/?r=rooms.board');
        exit;
    }

    // Ensure we have the booking data
    if (!isset($currentBooking['room_number'])) {
        flash_error('ข้อมูลการจองไม่ถูกต้อง');
        header('Location: ' . $GLOBALS['baseUrl'] . '/?r=rooms.board');
        exit;
    }

    // Get available rooms (all rooms are same rate, so plan type doesn't matter for transfers)
    $availableRooms = $transferEngine->getAvailableRooms(
        $roomId,
        $currentBooking['checkin_at'],
        $currentBooking['checkout_at']
    );

    // Get transfer reasons from settings
    $transferReasons = [
        'maintenance' => 'ซ่อมบำรุงห้อง',
        'guest_request' => 'ตามความต้องการแขก',
        'overbooking' => 'จองเกิน',
        'room_issue' => 'ปัญหาห้อง',
        'other' => 'อื่นๆ'
    ];

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        if ($_POST['action'] === 'process_transfer') {
            $transferData = [
                'booking_id' => $currentBooking['id'],
                'from_room_id' => $roomId,
                'to_room_id' => $_POST['to_room_id'],
                'transfer_reason' => $_POST['transfer_reason'],
                'notes' => $_POST['notes'] ?? '',
                'transferred_by' => currentUser()['id'],
                'notify_guest' => isset($_POST['notify_guest']),
                'notify_housekeeping' => isset($_POST['notify_housekeeping'])
            ];

            $result = $transferEngine->processTransfer($transferData);

            flash_success('ย้ายห้องเรียบร้อยแล้ว จากห้อง ' . $currentBooking['room_number'] . ' ไปยังห้อง ' . $_POST['to_room_number']);
            header('Location: ' . $GLOBALS['baseUrl'] . '/?r=rooms.board');
            exit;
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Transfer error: " . $e->getMessage());
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
                        <i class="bi bi-arrow-left-right text-info me-2"></i>
                        ย้ายห้องแขก
                    </h1>
                    <p class="text-muted mb-0">ย้ายแขกจากห้อง <?php echo htmlspecialchars($currentBooking['room_number']); ?> ไปยังห้องอื่น (ไม่มีค่าใช้จ่ายเพิ่มเติม)</p>
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

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Current Booking Info -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person-check me-2"></i>
                                ข้อมูลการจองปัจจุบัน
                                <?php if ($currentBooking['plan_type'] === 'short'): ?>
                                    <span class="badge bg-warning ms-2">ชั่วคราว</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-2">รายวัน</span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>ชื่อแขก:</strong></td>
                                            <td><?php echo htmlspecialchars($currentBooking['guest_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>เบอร์โทร:</strong></td>
                                            <td><?php echo htmlspecialchars($currentBooking['guest_phone']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>ห้องปัจจุบัน:</strong></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($currentBooking['room_number']); ?></span>
                                                (<?php echo htmlspecialchars($currentBooking['room_type']); ?>)
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>เช็คอิน:</strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($currentBooking['checkin_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>เช็คเอาท์:</strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($currentBooking['checkout_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>จำนวนคืน:</strong></td>
                                            <td>
                                                <?php
                                                $checkIn = new DateTime($currentBooking['checkin_at']);
                                                $checkOut = new DateTime($currentBooking['checkout_at']);
                                                $totalNights = $checkOut->diff($checkIn)->days;
                                                $today = new DateTime();
                                                $remainingNights = $checkOut->diff($today)->days;
                                                echo "$totalNights คืน (เหลือ $remainingNights คืน)";
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Form -->
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-house-door me-2"></i>
                                เลือกห้องใหม่
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="transferForm">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="process_transfer">

                                <!-- Room Selection -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        เลือกห้องปลายทาง:
                                        <small class="text-info">(ห้องทุกห้องราคาเดียวกัน ฿350)</small>
                                    </label>
                                    <div class="row" id="roomGrid">
                                        <?php if (empty($availableRooms)): ?>
                                            <div class="col-12">
                                                <div class="alert alert-warning">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    ไม่มีห้องว่างที่สามารถย้ายได้ในขณะนี้
                                                    <br><small>ทุกห้องมีการจองหรือไม่พร้อมใช้งาน</small>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($availableRooms as $room): ?>
                                                <div class="col-md-4 col-lg-3 mb-3">
                                                    <div class="card room-card h-100" style="cursor: pointer;"
                                                         data-room-id="<?php echo $room['id']; ?>"
                                                         data-room-number="<?php echo htmlspecialchars($room['room_number']); ?>"
                                                         data-room-type="<?php echo htmlspecialchars($room['room_type']); ?>"
                                                         data-room-rate="<?php echo $room['current_rate']; ?>">
                                                        <div class="card-body text-center p-3">
                                                            <h6 class="card-title mb-1">
                                                                <i class="bi bi-door-open me-1"></i>
                                                                <?php echo htmlspecialchars($room['room_number']); ?>
                                                            </h6>
                                                            <div class="badge bg-success mb-2">
                                                                ฿350
                                                            </div>
                                                            <div class="small text-muted">
                                                                ชั้น <?php echo $room['floor']; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <input type="hidden" name="to_room_id" id="selectedRoomId" required>
                                    <input type="hidden" name="to_room_number" id="selectedRoomNumber">
                                </div>

                                <!-- Cost Calculation -->
                                <div class="mb-4" id="costCalculation" style="display: none;">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="bi bi-calculator me-2"></i>
                                                การคำนวณค่าใช้จ่าย
                                            </h6>
                                        </div>
                                        <div class="card-body" id="costDetails">
                                            <!-- Cost details will be loaded here -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Transfer Reason -->
                                <div class="mb-3">
                                    <label for="transfer_reason" class="form-label">เหตุผลการย้าย:</label>
                                    <select class="form-select" name="transfer_reason" id="transfer_reason" required>
                                        <option value="">-- เลือกเหตุผล --</option>
                                        <?php foreach ($transferReasons as $key => $reason): ?>
                                            <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($reason); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Notes -->
                                <div class="mb-3">
                                    <label for="notes" class="form-label">หมายเหตุเพิ่มเติม:</label>
                                    <textarea class="form-control" name="notes" id="notes" rows="3"
                                              placeholder="ระบุรายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>
                                </div>

                                <!-- Notification Options -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="notify_housekeeping" id="notify_housekeeping" checked>
                                        <label class="form-check-label" for="notify_housekeeping">
                                            <i class="bi bi-telegram me-1"></i>
                                            แจ้งเตือนทีมแม่บ้าน (Telegram)
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=rooms.board" class="btn btn-outline-secondary me-md-2">
                                        <i class="bi bi-x-circle me-1"></i>
                                        ยกเลิก
                                    </a>
                                    <button type="submit" class="btn btn-warning" id="submitBtn" disabled>
                                        <i class="bi bi-arrow-left-right me-1"></i>
                                        ยืนยันการย้ายห้อง
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomCards = document.querySelectorAll('.room-card');
    const selectedRoomId = document.getElementById('selectedRoomId');
    const selectedRoomNumber = document.getElementById('selectedRoomNumber');
    const costCalculation = document.getElementById('costCalculation');
    const costDetails = document.getElementById('costDetails');
    const submitBtn = document.getElementById('submitBtn');

    roomCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            roomCards.forEach(c => c.classList.remove('border-primary', 'bg-light'));

            // Add selection to clicked card
            this.classList.add('border-primary', 'bg-light');

            // Set form values
            selectedRoomId.value = this.dataset.roomId;
            selectedRoomNumber.value = this.dataset.roomNumber;

            // Calculate cost
            calculateTransferCost(this.dataset.roomId, this.dataset.roomNumber, this.dataset.roomType, this.dataset.roomRate);

            // Enable submit button
            submitBtn.disabled = false;
        });
    });

    function calculateTransferCost(toRoomId, toRoomNumber, toRoomType, toRoomRate) {
        // Show loading
        costCalculation.style.display = 'block';
        costDetails.innerHTML = '<div class="text-center"><i class="bi bi-hourglass-split"></i> กำลังคำนวณ...</div>';

        // AJAX call to calculate cost
        fetch('<?php echo $GLOBALS['baseUrl']; ?>/api/calculate_transfer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?php echo get_csrf_token(); ?>'
            },
            body: JSON.stringify({
                from_room_id: <?php echo $roomId; ?>,
                to_room_id: toRoomId,
                booking_id: <?php echo $currentBooking['id']; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCostCalculation(data.calculation, toRoomNumber, toRoomType);
            } else {
                costDetails.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(error => {
            costDetails.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการคำนวณค่าใช้จ่าย</div>';
        });
    }

    function displayCostCalculation(calc, toRoomNumber, toRoomType) {
        let html = '<div class="row">';

        // Current vs New Room
        html += '<div class="col-md-6">';
        html += '<h6>ข้อมูลการเปลี่ยนแปลง</h6>';
        html += '<table class="table table-sm">';
        html += '<tr><td>จาก:</td><td>ห้อง <?php echo $currentBooking['room_number']; ?></td></tr>';
        html += '<tr><td>ไป:</td><td>ห้อง ' + toRoomNumber + '</td></tr>';
        html += '<tr><td>คืนที่เหลือ:</td><td>' + calc.remaining_nights + ' คืน</td></tr>';
        html += '</table>';
        html += '</div>';

        // Cost Breakdown
        html += '<div class="col-md-6">';
        html += '<h6>รายละเอียดค่าใช้จ่าย</h6>';
        html += '<table class="table table-sm">';
        html += '<tr><td>ราคาห้อง:</td><td>฿350</td></tr>';
        html += '<tr><td>ค่าใช้จ่ายเพิ่มเติม:</td><td>ไม่มี</td></tr>';

        if (calc.total_adjustment !== 0) {
            html += '<tr><td>รวมค่าต่าง:</td><td>' + (calc.subtotal >= 0 ? '+' : '') + '฿' + numberFormat(calc.subtotal) + '</td></tr>';
            if (calc.tax_amount > 0) {
                html += '<tr><td>ภาษี (7%):</td><td>+฿' + numberFormat(calc.tax_amount) + '</td></tr>';
            }
            if (calc.service_charge > 0) {
                html += '<tr><td>ค่าบริการ (10%):</td><td>+฿' + numberFormat(calc.service_charge) + '</td></tr>';
            }
            html += '<tr class="fw-bold"><td>ยอดรวม:</td><td>' + (calc.total_adjustment >= 0 ? '+' : '') + '฿' + numberFormat(calc.total_adjustment) + '</td></tr>';
        } else {
            html += '<tr class="fw-bold text-success"><td colspan="2">ไม่มีค่าใช้จ่ายเพิ่มเติม</td></tr>';
        }

        html += '</table>';
        html += '</div>';
        html += '</div>';

        // Transfer message
        html += '<div class="alert alert-success"><i class="bi bi-arrow-left-right"></i> <strong>การย้ายห้อง:</strong> ' + calc.message + '</div>';

        costDetails.innerHTML = html;
    }

    function numberFormat(num) {
        return new Intl.NumberFormat('th-TH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(num);
    }
});
</script>

<style>
.room-card {
    transition: all 0.3s ease;
}

.room-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.room-card.border-primary {
    border-width: 2px !important;
}

#costCalculation {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>