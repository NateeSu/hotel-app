<?php
/**
 * Hotel Management System - Room Check-in
 *
 * Handles guest check-in process with form display and processing
 */

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
$pageTitle = 'Check-in - Hotel Management System';
$pageDescription = 'Guest check-in process';

// Get room ID and validate
$roomId = $_GET['room_id'] ?? $_POST['room_id'] ?? null;
$room = null;

// Debug: Show what we received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST room_id: " . ($_POST['room_id'] ?? 'not set'));
    error_log("GET room_id: " . ($_GET['room_id'] ?? 'not set'));
}

if (!$roomId) {
    flash_error('ไม่ได้ระบุห้องที่ต้องการ check-in - room_id: ' . json_encode(['GET' => $_GET['room_id'] ?? null, 'POST' => $_POST['room_id'] ?? null]));
    redirectToRoute('rooms.board');
}

// Get room information and rates
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT id, room_number, room_type, status, max_occupancy FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        flash_error('ไม่พบห้องที่ระบุ');
        redirectToRoute('rooms.board');
    }

    if ($room['status'] !== 'available') {
        flash_error('ห้องนี้ไม่สามารถ check-in ได้ เนื่องจากสถานะห้องคือ: ' . $room['status']);
        redirectToRoute('rooms.board');
    }

    // Get current room rates
    $stmt = $pdo->prepare("SELECT * FROM room_rates WHERE is_active = 1 ORDER BY rate_type");
    $stmt->execute();
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize rates by type
    $ratesByType = [];
    foreach ($rates as $rate) {
        $ratesByType[$rate['rate_type']] = $rate;
    }

    // Set default rates if not found
    if (empty($ratesByType['short'])) {
        $ratesByType['short'] = ['price' => 200, 'duration_hours' => 3];
    }
    if (empty($ratesByType['overnight'])) {
        $ratesByType['overnight'] = ['price' => 350, 'duration_hours' => 12];
    }

} catch (Exception $e) {
    flash_error('เกิดข้อผิดพลาดในการโหลดข้อมูลห้อง: ' . $e->getMessage());
    redirectToRoute('rooms.board');
}

// Handle POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('CSRF token verification failed');
        }

        // Validate input
        $guestName = trim($_POST['guest_name'] ?? '');
        $guestPhone = trim($_POST['guest_phone'] ?? '');
        $guestIdNumber = trim($_POST['guest_id_number'] ?? '');
        $guestCount = intval($_POST['guest_count'] ?? 1);
        $planType = $_POST['plan_type'] ?? '';
        $duration = intval($_POST['duration'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $notes = trim($_POST['notes'] ?? '');

        if (empty($guestName)) {
            throw new Exception('กรุณากรอกชื่อแขก');
        }

        if (empty($guestPhone)) {
            throw new Exception('กรุณากรอกเบอร์โทรศัพท์');
        }

        if (!validate_phone_thai($guestPhone)) {
            throw new Exception('รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง');
        }

        if (!empty($guestIdNumber) && !validate_thai_id($guestIdNumber)) {
            throw new Exception('รูปแบบเลขบัตรประชาชนไม่ถูกต้อง');
        }

        if (!in_array($planType, ['short', 'overnight'])) {
            throw new Exception('กรุณาเลือกประเภทการพัก');
        }

        if ($guestCount < 1 || $guestCount > $room['max_occupancy']) {
            throw new Exception('จำนวนผู้เข้าพักไม่ถูกต้อง (สูงสุด ' . $room['max_occupancy'] . ' คน)');
        }

        if (!in_array($paymentMethod, ['cash', 'card', 'transfer'])) {
            throw new Exception('กรุณาเลือกวิธีชำระเงิน');
        }

        // Calculate rates and times
        $checkInTime = now();
        $plannedCheckOut = null;
        $baseAmount = 0;

        if ($planType === 'short') {
            // Short-term: hour-based calculation
            $rateType = 'short';
            $stmt = $pdo->prepare("SELECT price, duration_hours FROM room_rates WHERE rate_type = ? AND is_active = 1");
            $stmt->execute([$rateType]);
            $rate = $stmt->fetch();

            if ($rate) {
                $baseAmount = $rate['price'];
                $hours = $duration > 0 ? $duration : $rate['duration_hours'];

                // Calculate extended hours cost
                if ($hours > $rate['duration_hours']) {
                    $extraHours = $hours - $rate['duration_hours'];
                    $baseAmount += ($extraHours * 100); // ฿100 per extra hour
                }

                $plannedCheckOut = date('Y-m-d H:i:s', strtotime($checkInTime . " +{$hours} hours"));
            } else {
                throw new Exception('ไม่พบข้อมูลอัตราค่าบริการแบบชั่วคราว');
            }
        } else if ($planType === 'overnight') {
            // Overnight: night-based calculation
            $rateType = 'overnight';
            $stmt = $pdo->prepare("SELECT price FROM room_rates WHERE rate_type = ? AND is_active = 1");
            $stmt->execute([$rateType]);
            $rate = $stmt->fetch();

            if ($rate) {
                $nights = $duration > 0 ? $duration : 1;
                $baseAmount = $rate['price'] * $nights;

                // Calculate checkout time: 12:00 PM of the last day
                $checkoutDate = date('Y-m-d', strtotime($checkInTime . " +{$nights} days"));
                $plannedCheckOut = $checkoutDate . ' 12:00:00';
            } else {
                throw new Exception('ไม่พบข้อมูลอัตราค่าบริการแบบค้างคืน');
            }
        } else {
            throw new Exception('ประเภทการพักไม่ถูกต้อง');
        }

        // Generate booking code
        $bookingCode = generate_booking_code('BK');

        // Get current user
        $currentUser = currentUser();
        $createdById = $currentUser['id'] ?? 1;

        // Start database transaction
        $pdo->beginTransaction();

        // Double-check room is still available
        $stmt = $pdo->prepare("SELECT status FROM rooms WHERE id = ? FOR UPDATE");
        $stmt->execute([$roomId]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus !== 'available') {
            throw new Exception('ห้องได้ถูก check-in ไปแล้ว กรุณาเลือกห้องอื่น');
        }

        // Insert enhanced booking record
        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                booking_code, room_id, guest_name, guest_phone, guest_id_number,
                guest_count, plan_type, status, checkin_at, checkout_at,
                base_amount, total_amount, payment_method, payment_status,
                notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, 'pending', ?, ?)
        ");

        $success = $stmt->execute([
            $bookingCode, $roomId, $guestName, $guestPhone, $guestIdNumber,
            $guestCount, $planType, $checkInTime, $plannedCheckOut,
            $baseAmount, $baseAmount, $paymentMethod, $notes, $createdById
        ]);

        if (!$success) {
            throw new Exception('ไม่สามารถบันทึกข้อมูลการจองได้');
        }

        $bookingId = $pdo->lastInsertId();

        // Update room status to occupied
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ?");
        $stmt->execute([$roomId]);

        // Commit transaction
        $pdo->commit();

        flash_success("Check-in สำเร็จ! ห้อง {$room['room_number']} - {$guestName} | รหัสจอง: {$bookingCode} | ยอดชำระ: " . money_format_thb($baseAmount));
        redirectToRoute('rooms.board');

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        flash_error('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-box-arrow-in-right text-success me-2"></i>
                        Check-in ห้องพัก
                    </h1>
                    <p class="text-muted mb-0">กรอกข้อมูลแขกเพื่อทำการ check-in</p>
                </div>

                <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    กลับหน้าห้องพัก
                </a>
            </div>

            <!-- Room Information Card -->
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-door-closed me-2"></i>
                        ห้อง <?php echo htmlspecialchars($room['room_number']); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>หมายเลขห้อง:</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>สถานะปัจจุบัน:</strong>
                                <span class="badge bg-success">ว่าง</span>
                            </p>
                            <p class="mb-1"><strong>เวลา:</strong> <?php echo format_datetime_thai(now(), 'd/m/Y H:i'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-plus me-2"></i>
                        ข้อมูลแขก
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo routeUrl('rooms.checkin', ['room_id' => $roomId]); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                        <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($roomId); ?>">

                        <!-- Guest Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guest_name" class="form-label">
                                        <i class="bi bi-person me-1"></i>
                                        ชื่อ-นามสกุลแขก <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-lg"
                                           id="guest_name"
                                           name="guest_name"
                                           required
                                           placeholder="กรอกชื่อ-นามสกุลแขก"
                                           value="<?php echo htmlspecialchars($_POST['guest_name'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guest_phone" class="form-label">
                                        <i class="bi bi-telephone me-1"></i>
                                        เบอร์โทรศัพท์ <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel"
                                           class="form-control form-control-lg"
                                           id="guest_phone"
                                           name="guest_phone"
                                           required
                                           placeholder="08X-XXX-XXXX"
                                           pattern="[0-9]{9,10}"
                                           value="<?php echo htmlspecialchars($_POST['guest_phone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guest_id_number" class="form-label">
                                        <i class="bi bi-card-text me-1"></i>
                                        เลขบัตรประชาชน (ถ้ามี)
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="guest_id_number"
                                           name="guest_id_number"
                                           placeholder="X-XXXX-XXXXX-XX-X"
                                           maxlength="13"
                                           value="<?php echo htmlspecialchars($_POST['guest_id_number'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guest_count" class="form-label">
                                        <i class="bi bi-people me-1"></i>
                                        จำนวนผู้เข้าพัก
                                    </label>
                                    <select class="form-select" id="guest_count" name="guest_count">
                                        <?php for ($i = 1; $i <= ($room['max_occupancy'] ?? 2); $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($_POST['guest_count'] ?? 1) == $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> คน
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Plan & Pricing -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="plan_type" class="form-label">
                                        <i class="bi bi-clock me-1"></i>
                                        ประเภทการพัก <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="plan_type" name="plan_type" required onchange="updatePricing()">
                                        <option value="">เลือกประเภท</option>
                                        <option value="short" data-price="<?php echo $ratesByType['short']['price']; ?>" data-hours="<?php echo $ratesByType['short']['duration_hours']; ?>" <?php echo ($_POST['plan_type'] ?? '') === 'short' ? 'selected' : ''; ?>>
                                            ชั่วคราว (<?php echo $ratesByType['short']['duration_hours']; ?> ชั่วโมง) - ฿<?php echo number_format($ratesByType['short']['price']); ?>
                                        </option>
                                        <option value="overnight" data-price="<?php echo $ratesByType['overnight']['price']; ?>" data-hours="<?php echo $ratesByType['overnight']['duration_hours']; ?>" <?php echo ($_POST['plan_type'] ?? '') === 'overnight' ? 'selected' : ''; ?>>
                                            ค้างคืน (จนถึง 12:00 ของวันถัดไป) - ฿<?php echo number_format($ratesByType['overnight']['price']); ?> / คืน
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        <span id="duration_label">ระยะเวลา (ชั่วโมง)</span>
                                    </label>
                                    <input type="number"
                                           class="form-control"
                                           id="duration"
                                           name="duration"
                                           min="1"
                                           max="48"
                                           placeholder="ปรับได้ถ้าต้องการ"
                                           value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>"
                                           onchange="updatePricing()">
                                    <div class="form-text" id="duration_help">เปลี่ยนได้หากต้องการระยะเวลาที่แตกต่าง</div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">
                                        <i class="bi bi-credit-card me-1"></i>
                                        วิธีชำระเงิน <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="cash" <?php echo ($_POST['payment_method'] ?? 'cash') === 'cash' ? 'selected' : ''; ?>>
                                            <i class="bi bi-cash"></i> เงินสด
                                        </option>
                                        <option value="card" <?php echo ($_POST['payment_method'] ?? '') === 'card' ? 'selected' : ''; ?>>
                                            <i class="bi bi-credit-card"></i> บัตรเครดิต/เดบิต
                                        </option>
                                        <option value="transfer" <?php echo ($_POST['payment_method'] ?? '') === 'transfer' ? 'selected' : ''; ?>>
                                            <i class="bi bi-bank"></i> โอนเงิน
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calculator me-1"></i>
                                        ยอดชำระ
                                    </label>
                                    <div class="form-control-lg bg-light border d-flex align-items-center justify-content-between">
                                        <span>ราคาพื้นฐาน:</span>
                                        <strong id="display_amount" class="text-primary fs-4">เลือกประเภทการพัก</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="bi bi-sticky me-1"></i>
                                หมายเหตุ (ถ้ามี)
                            </label>
                            <textarea class="form-control"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      placeholder="ข้อมูลเพิ่มเติม เช่น จำนวนผู้เข้าพัก, ความต้องการพิเศษ"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <!-- Summary Information -->
                        <div class="bg-light rounded p-3 mb-4">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                สรุปการ Check-in
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>ห้อง:</strong> <?php echo htmlspecialchars($room['room_number']); ?><br>
                                        <strong>เวลา Check-in:</strong> <?php echo format_datetime_thai(now(), 'd/m/Y H:i'); ?>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>ผู้ดำเนินการ:</strong> <?php echo htmlspecialchars(currentUser()['username']); ?><br>
                                        <strong>หลังจาก Check-in:</strong> สถานะห้องจะเปลี่ยนเป็น "มีผู้พัก"
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
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-1"></i>
                                ยืนยัน Check-in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time pricing calculator
function updatePricing() {
    const planType = document.getElementById('plan_type');
    const duration = document.getElementById('duration');
    const displayAmount = document.getElementById('display_amount');
    const durationLabel = document.getElementById('duration_label');
    const durationHelp = document.getElementById('duration_help');

    if (!planType.value) {
        displayAmount.textContent = 'เลือกประเภทการพัก';
        return;
    }

    const selectedOption = planType.options[planType.selectedIndex];
    const basePrice = parseInt(selectedOption.dataset.price);
    const baseHours = parseInt(selectedOption.dataset.hours);
    const planTypeValue = planType.value;

    let totalPrice = basePrice;
    let priceText = '';

    if (planTypeValue === 'short') {
        // Short-term: show hourly pricing
        const customDuration = parseInt(duration.value) || baseHours;

        // Update UI for short-term
        durationLabel.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>ระยะเวลา (ชั่วโมง)';
        durationHelp.textContent = 'เปลี่ยนได้หากต้องการระยะเวลาที่แตกต่าง';
        duration.removeAttribute('readonly');
        duration.placeholder = 'ปรับได้ถ้าต้องการ';
        duration.max = '48';

        totalPrice = basePrice;
        priceText = `฿${basePrice.toLocaleString()}`;

        // Calculate extended hours if needed
        if (customDuration > baseHours) {
            const extraHours = customDuration - baseHours;
            const extendedCost = extraHours * 100; // ฿100 per hour
            totalPrice = basePrice + extendedCost;
            priceText = `฿${basePrice.toLocaleString()} + ฿${extendedCost.toLocaleString()} = ฿${totalPrice.toLocaleString()}`;
        }

        // Update duration field if empty
        if (!duration.value) {
            duration.value = baseHours;
        }
    } else if (planTypeValue === 'overnight') {
        // Overnight: show per-night pricing
        const nights = parseInt(duration.value) || 1;

        // Update UI for overnight
        durationLabel.innerHTML = '<i class="bi bi-calendar-date me-1"></i>ระยะเวลา (คืน)';
        durationHelp.textContent = 'จำนวนคืนที่ต้องการพัก (เช็คเอาท์ 12:00 ของวันสุดท้าย)';
        duration.removeAttribute('readonly');
        duration.placeholder = 'จำนวนคืน';
        duration.max = '30'; // Maximum 30 nights

        totalPrice = basePrice * nights;
        priceText = `฿${basePrice.toLocaleString()} × ${nights} คืน = ฿${totalPrice.toLocaleString()}`;

        if (nights === 1) {
            priceText += `<br><small class="text-muted">เช็คเอาท์: 12:00 ของวันถัดไป</small>`;
        } else {
            priceText += `<br><small class="text-muted">เช็คเอาท์: 12:00 ของวันที่ ${nights + 1}</small>`;
        }
        priceText += `<br><small class="text-muted">เกิน 12:00: +100 บาท/ชั่วโมง</small>`;

        // Set default value if empty
        if (!duration.value) {
            duration.value = 1;
        }
    }

    displayAmount.innerHTML = priceText;

    // Calculate check-out time
    updateCheckoutTime();
}

function updateCheckoutTime() {
    const planType = document.getElementById('plan_type').value;
    const duration = document.getElementById('duration');

    if (planType === 'overnight') {
        // For overnight stays: checkout at 12:00 PM of the last day
        const nights = parseInt(duration.value) || 1;
        const now = new Date();
        const checkoutTime = new Date(now);
        checkoutTime.setDate(checkoutTime.getDate() + nights); // Add number of nights
        checkoutTime.setHours(12, 0, 0, 0); // 12:00 PM

        const options = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };

        // Update summary if element exists
        const summaryElement = document.querySelector('.checkout-time');
        if (summaryElement) {
            summaryElement.textContent = checkoutTime.toLocaleDateString('th-TH', options);
        }
    } else if (planType === 'short') {
        // For short-term stays: normal hour-based calculation
        const durationValue = duration.value;

        if (durationValue) {
            const now = new Date();
            const checkoutTime = new Date(now.getTime() + (durationValue * 60 * 60 * 1000));
            const options = {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };

            // Update summary if element exists
            const summaryElement = document.querySelector('.checkout-time');
            if (summaryElement) {
                summaryElement.textContent = checkoutTime.toLocaleDateString('th-TH', options);
            }
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePricing();

    // Phone number formatting
    const phoneInput = document.getElementById('guest_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) value = value.slice(0, 10);
            e.target.value = value;
        });
    }

    // ID number formatting
    const idInput = document.getElementById('guest_id_number');
    if (idInput) {
        idInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 13) value = value.slice(0, 13);
            e.target.value = value;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>