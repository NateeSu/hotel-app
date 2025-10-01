<?php
/**
 * Hotel Management System - Enhanced Room Check-out
 *
 * Complete check-out process with billing calculation and payment
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
require_once __DIR__ . '/../templates/partials/flash.php';

// Require login with reception role or higher
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'Check-out - Hotel Management System';
$pageDescription = 'Enhanced guest check-out process';

// Get room ID and validate
$roomId = $_POST['room_id'] ?? $_GET['room_id'] ?? null;
$booking = null;
$room = null;

if (!$roomId) {
    flash_error('ไม่ได้ระบุห้องที่ต้องการ check-out');
    redirectToRoute('rooms.board');
}

// Get current booking and room information
try {
    $pdo = getDatabase();

    // Get room details
    $stmt = $pdo->prepare("
        SELECT id, room_number, room_type, status, max_occupancy
        FROM rooms
        WHERE id = ?
    ");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        flash_error('ไม่พบห้องที่ระบุ');
        redirectToRoute('rooms.board');
    }

    if ($room['status'] !== 'occupied') {
        flash_error('ห้องนี้ไม่มีแขกเข้าพัก ไม่สามารถ check-out ได้');
        redirectToRoute('rooms.board');
    }

    // Get active booking for this room
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.room_id = ? AND b.status = 'active'
        ORDER BY b.checkin_at DESC
        LIMIT 1
    ");
    $stmt->execute([$roomId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        flash_error('ไม่พบข้อมูลการจองสำหรับห้องนี้');
        redirectToRoute('rooms.board');
    }

    // Get rate information for calculation
    $rateType = $booking['plan_type']; // 'short' or 'overnight'
    $stmt = $pdo->prepare("SELECT price, duration_hours FROM room_rates WHERE rate_type = ? AND is_active = 1");
    $stmt->execute([$rateType]);
    $rate = $stmt->fetch();

    // Get extended rate (default 100 baht per hour for overtime)
    $extendedRate = ['price' => 100];

    // Use base amount from booking record (price agreed at check-in)
    $calculatedBaseAmount = $booking['base_amount'] ?? 0;

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage());
    redirectToRoute('rooms.board');
}

// Handle POST request (checkout processing)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_checkout'])) {
    try {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('CSRF token verification failed');
        }

        // Get form data
        $extraAmount = floatval($_POST['extra_amount'] ?? 0);
        $extraNotes = trim($_POST['extra_notes'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? $booking['payment_method'];
        $currentTime = now();

        // Calculate billing using new business rules
        $billingData = calculate_billing($booking['plan_type'], $booking['checkin_at'], $currentTime);

        $baseAmount = $billingData['base_amount'];
        $overtimeAmount = $billingData['extra_amount'];
        $totalAmount = $baseAmount + $overtimeAmount + $extraAmount;

        // Additional billing information for display
        $nights = $billingData['nights'] ?? 0;
        $hours = $billingData['hours'] ?? 0;
        $overtimeHours = $billingData['overdue_hours'] ?? 0;

        // Get current user
        $currentUser = currentUser();

        // Start database transaction
        $pdo->beginTransaction();

        // Update booking record
        $stmt = $pdo->prepare("
            UPDATE bookings SET
                checkout_at = ?,
                extra_amount = ?,
                total_amount = ?,
                payment_method = ?,
                payment_status = 'paid',
                status = 'completed',
                notes = CONCAT(COALESCE(notes, ''), '\nCheck-out: ', ?, COALESCE(?, ''))
            WHERE id = ?
        ");
        $stmt->execute([
            $currentTime,
            $overtimeAmount + $extraAmount,
            $totalAmount,
            $paymentMethod,
            $currentTime,
            $extraNotes ? ' | ' . $extraNotes : '',
            $booking['id']
        ]);

        // Update room status to cleaning
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'cleaning' WHERE id = ?");
        $stmt->execute([$roomId]);

        // Create housekeeping job
        $stmt = $pdo->prepare("
            INSERT INTO housekeeping_jobs (room_id, booking_id, task_type, priority, status, description, created_by)
            VALUES (?, ?, 'checkout_cleaning', 'normal', 'pending', 'ทำความสะอาดหลัง check-out', ?)
        ");
        $stmt->execute([$roomId, $booking['id'], $currentUser['id']]);

        // Get the housekeeping job ID
        $housekeepingJobId = $pdo->lastInsertId();

        // Commit transaction
        $pdo->commit();

        // Send Telegram notification to housekeeping staff
        try {
            require_once __DIR__ . '/../lib/telegram_service.php';
            $telegramService = new TelegramService();
            $telegramService->sendHousekeepingNotification($housekeepingJobId);

            // Mark notification as sent
            $stmt = $pdo->prepare("UPDATE housekeeping_jobs SET telegram_sent = TRUE WHERE id = ?");
            $stmt->execute([$housekeepingJobId]);

        } catch (Exception $telegramError) {
            // Log error but don't fail the checkout process
            error_log("Telegram notification failed: " . $telegramError->getMessage());
        }

        // Generate receipt after successful checkout
        try {
            require_once __DIR__ . '/../lib/receipt_generator.php';
            $receiptGenerator = new ReceiptGenerator();
            $receiptData = $receiptGenerator->generateReceipt($booking['id'], $extraAmount, $extraNotes);

            $message = "Check-out สำเร็จ! ห้อง {$room['room_number']} - {$booking['guest_name']} | ";
            $message .= "ยอดชำระทั้งหมด: " . money_format_thb($totalAmount);
            if ($overtimeHours > 0) {
                $message .= " (รวมค่าเกินเวลา {$overtimeHours} ชั่วโมง: " . money_format_thb($overtimeAmount) . ")";
            }

            // Store receipt info in session for success page
            $_SESSION['checkout_success'] = [
                'booking_code' => $booking['booking_code'],
                'guest_name' => $booking['guest_name'],
                'room_number' => $room['room_number'],
                'total_amount' => $totalAmount,
                'receipt_number' => $receiptData['receipt_number'],
                'receipt_url' => routeUrl('receipts.view', ['receipt_number' => $receiptData['receipt_number']])
            ];

            flash_success($message);
            redirectToRoute('rooms.checkoutSuccess');

        } catch (Exception $receiptError) {
            error_log("Receipt generation failed: " . $receiptError->getMessage());

            $message = "Check-out สำเร็จ! ห้อง {$room['room_number']} - {$booking['guest_name']} | ";
            $message .= "ยอดชำระทั้งหมด: " . money_format_thb($totalAmount);
            if ($overtimeHours > 0) {
                $message .= " (รวมค่าเกินเวลา {$overtimeHours} ชั่วโมง: " . money_format_thb($overtimeAmount) . ")";
            }
            $message .= " (หมายเหตุ: ไม่สามารถสร้างใบเสร็จได้)";

            flash_success($message);
            redirectToRoute('rooms.board');
        }

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        flash_error('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

// Calculate current billing information for display
$currentTime = now();
$checkInTime = new DateTime($booking['checkin_at']);
$currentDateTime = new DateTime($currentTime);
$checkoutDateTime = new DateTime($booking['checkout_at']);

// Calculate actual duration for display
$currentDuration = $currentDateTime->diff($checkInTime);

// Get base hours from rate
$baseHours = $rate['duration_hours'] ?? 3;

// Calculate overtime information (display only, no automatic charge)
$currentOvertimeHours = 0;
$currentOvertimeMinutes = 0;
$isOvertime = false;

if ($currentDateTime > $checkoutDateTime) {
    // Calculate time past scheduled checkout
    $overdue_seconds = $currentDateTime->getTimestamp() - $checkoutDateTime->getTimestamp();
    $overdue_hours_decimal = $overdue_seconds / 3600;
    $currentOvertimeHours = floor($overdue_hours_decimal);
    $currentOvertimeMinutes = floor(($overdue_hours_decimal - $currentOvertimeHours) * 60);
    $isOvertime = true;
}

// Total = Base amount only (no automatic overtime charge)
$currentTotalAmount = $calculatedBaseAmount;

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-box-arrow-left text-primary me-2"></i>
                        Check-out ห้องพัก
                    </h1>
                    <p class="text-muted mb-0">ดำเนินการ check-out และคำนวณค่าใช้จ่าย</p>
                </div>

                <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    กลับหน้าห้องพัก
                </a>
            </div>

            <div class="row">
                <!-- Guest Information -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-check me-2"></i>
                                ข้อมูลแขก
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>ชื่อแขก:</strong><br>
                                        <?php echo htmlspecialchars($booking['guest_name']); ?>
                                    </p>
                                    <p class="mb-2"><strong>เบอร์โทร:</strong><br>
                                        <?php echo htmlspecialchars($booking['guest_phone'] ?: 'ไม่ระบุ'); ?>
                                    </p>
                                    <p class="mb-2"><strong>จำนวนผู้เข้าพัก:</strong><br>
                                        <?php echo $booking['guest_count'] ?? 1; ?> คน
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>ห้อง:</strong><br>
                                        <?php echo htmlspecialchars($booking['room_number']); ?>
                                    </p>
                                    <p class="mb-2"><strong>ประเภทการพัก:</strong><br>
                                        <?php echo $booking['plan_type'] === 'short' ? 'ชั่วคราว' : 'ค้างคืน'; ?>
                                    </p>
                                    <p class="mb-2"><strong>รหัสจอง:</strong><br>
                                        <code><?php echo htmlspecialchars($booking['booking_code'] ?: 'N/A'); ?></code>
                                    </p>
                                </div>
                            </div>

                            <!-- Check-in Information -->
                            <hr>
                            <div class="row">
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>เวลา Check-in:</strong><br>
                                        <?php echo format_datetime_thai($booking['checkin_at'], 'd/m/Y H:i'); ?>
                                    </p>
                                    <p class="mb-2"><strong>เวลาปัจจุบัน:</strong><br>
                                        <span id="current_time"><?php echo format_datetime_thai($currentTime, 'd/m/Y H:i'); ?></span>
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>ระยะเวลาพัก:</strong><br>
                                        <span id="duration_display">
                                            <?php
                                            echo $currentDuration->days > 0 ? $currentDuration->days . ' วัน ' : '';
                                            echo $currentDuration->h . ' ชั่วโมง ' . $currentDuration->i . ' นาที';
                                            ?>
                                        </span>
                                    </p>
                                    <?php if ($booking['plan_type'] === 'short'): ?>
                                    <p class="mb-2"><strong>แพ็คเกจ:</strong><br>
                                        <?php echo $baseHours; ?> ชั่วโมง
                                    </p>
                                    <?php else: ?>
                                    <p class="mb-2"><strong>กำหนด Check-out:</strong><br>
                                        <?php echo format_datetime_thai($booking['checkout_at'], 'd/m/Y H:i'); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Information -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calculator me-2"></i>
                                สรุปค่าใช้จ่าย
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="billing-summary">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ค่าห้อง (พื้นฐาน):</span>
                                    <strong><?php echo money_format_thb($calculatedBaseAmount); ?></strong>
                                </div>

                                <?php if ($isOvertime): ?>
                                <div class="alert alert-warning py-2 mb-2">
                                    <i class="bi bi-clock-history me-2"></i>
                                    <strong>เกินเวลา:</strong>
                                    <?php
                                    if ($currentOvertimeHours > 0) {
                                        echo $currentOvertimeHours . ' ชม. ';
                                    }
                                    echo $currentOvertimeMinutes . ' นาที';
                                    ?>
                                    <br>
                                    <small class="text-muted">กรุณากรอกค่าใช้จ่ายเพิ่มเติมด้านล่าง</small>
                                </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>ค่าใช้จ่ายเพิ่มเติม:</span>
                                    <strong id="extra_amount_display">฿0.00</strong>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="h5">รวมทั้งหมด:</span>
                                    <strong class="h5 text-success" id="total_amount_display">
                                        <?php echo money_format_thb($currentTotalAmount); ?>
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>สถานะการชำระ:</span>
                                    <span class="badge bg-<?php echo $booking['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo $booking['payment_status'] === 'paid' ? 'ชำระแล้ว' : 'รอชำระ'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-out Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        ดำเนินการ Check-out
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo routeUrl('rooms.checkout', ['room_id' => $roomId]); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                        <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($roomId); ?>">
                        <input type="hidden" name="process_checkout" value="1">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="extra_amount" class="form-label">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        ค่าใช้จ่ายเพิ่มเติม (บาท)
                                    </label>
                                    <input type="number"
                                           class="form-control"
                                           id="extra_amount"
                                           name="extra_amount"
                                           min="0"
                                           step="0.01"
                                           value="0"
                                           onchange="updateTotal()">
                                    <div class="form-text">เช่น ค่าเสียหาย, minibar, ค่าบริการเพิ่มเติม</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">
                                        <i class="bi bi-credit-card me-1"></i>
                                        วิธีชำระเงิน
                                    </label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="cash" <?php echo $booking['payment_method'] === 'cash' ? 'selected' : ''; ?>>เงินสด</option>
                                        <option value="card" <?php echo $booking['payment_method'] === 'card' ? 'selected' : ''; ?>>บัตรเครดิต/เดบิต</option>
                                        <option value="transfer" <?php echo $booking['payment_method'] === 'transfer' ? 'selected' : ''; ?>>โอนเงิน</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="extra_notes" class="form-label">
                                <i class="bi bi-sticky me-1"></i>
                                หมายเหตุเพิ่มเติม
                            </label>
                            <textarea class="form-control"
                                      id="extra_notes"
                                      name="extra_notes"
                                      rows="3"
                                      placeholder="ระบุรายละเอียดค่าใช้จ่ายเพิ่มเติม หรือหมายเหตุอื่นๆ"></textarea>
                        </div>

                        <!-- Summary -->
                        <div class="bg-light rounded p-3 mb-4">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                สรุปการ Check-out
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>ห้อง:</strong> <?php echo htmlspecialchars($booking['room_number']); ?><br>
                                        <strong>แขก:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?><br>
                                        <strong>เวลา Check-out:</strong> <?php echo format_datetime_thai($currentTime, 'd/m/Y H:i'); ?>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>ผู้ดำเนินการ:</strong> <?php echo htmlspecialchars(currentUser()['full_name']); ?><br>
                                        <strong>หลังจาก Check-out:</strong> ห้องจะเปลี่ยนเป็นสถานะ "ทำความสะอาด"<br>
                                        <strong>งานแม่บ้าน:</strong> จะถูกสร้างอัตโนมัติ
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('ยืนยันการ Check-out หรือไม่?')">
                                <i class="bi bi-check-circle me-1"></i>
                                ยืนยัน Check-out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time total calculation
const baseAmount = <?php echo $calculatedBaseAmount; ?>;

function updateTotal() {
    const extraAmount = parseFloat(document.getElementById('extra_amount').value) || 0;
    const totalAmount = baseAmount + extraAmount;

    // Update display
    document.getElementById('extra_amount_display').textContent = '฿' + extraAmount.toLocaleString('th-TH', {minimumFractionDigths: 2});
    document.getElementById('total_amount_display').textContent = '฿' + totalAmount.toLocaleString('th-TH', {minimumFractionDigits: 2});
}

// Update current time every minute
function updateCurrentTime() {
    const now = new Date();
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };

    document.getElementById('current_time').textContent = now.toLocaleDateString('th-TH', options);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
    setInterval(updateCurrentTime, 60000); // Update every minute
});
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>