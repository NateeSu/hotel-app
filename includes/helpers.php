<?php
/**
 * Hotel Management System - Helper Functions
 *
 * This file contains utility functions used throughout the application.
 * These functions provide common functionality for formatting, validation,
 * date/time operations, and other utilities.
 */

// Prevent direct access
if (!defined('HELPERS_LOADED')) {
    define('HELPERS_LOADED', true);
}

/**
 * Get environment variable with fallback (duplicate from db.php for standalone use)
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        // Convert string representations of boolean values
        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'empty':
                case '(empty)':
                    return '';
                case 'null':
                case '(null)':
                    return null;
            }
        }

        return $value;
    }
}

/**
 * Get current timestamp in Asia/Bangkok timezone
 */
function now($format = 'Y-m-d H:i:s') {
    $timezone = new DateTimeZone('Asia/Bangkok');
    $date = new DateTime('now', $timezone);
    return $date->format($format);
}

/**
 * Format Thai Baht currency
 */
function money_format_thb($amount, $showSymbol = true, $decimals = 2) {
    if (!is_numeric($amount)) {
        return $showSymbol ? '฿0.00' : '0.00';
    }

    $formatted = number_format(floatval($amount), $decimals, '.', ',');

    if ($showSymbol) {
        return '฿' . $formatted;
    }

    return $formatted;
}

/**
 * Format date to Thai locale
 */
function format_date_thai($date, $format = 'd/m/Y') {
    if (!$date) return '';

    try {
        if (is_string($date)) {
            $dateObj = new DateTime($date);
        } else {
            $dateObj = $date;
        }

        return $dateObj->format($format);

    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format datetime to Thai locale
 */
function format_datetime_thai($datetime, $format = 'd/m/Y H:i') {
    return format_date_thai($datetime, $format);
}

/**
 * Calculate time difference in human readable format
 */
function time_ago($datetime) {
    if (!$datetime) return '';

    try {
        $time = new DateTime($datetime);
        $now = new DateTime();
        $diff = $now->diff($time);

        if ($diff->d > 0) {
            return $diff->d . ' วันที่แล้ว';
        } elseif ($diff->h > 0) {
            return $diff->h . ' ชั่วโมงที่แล้ว';
        } elseif ($diff->i > 0) {
            return $diff->i . ' นาทีที่แล้ว';
        } else {
            return 'เมื่อกี้นี้';
        }

    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Calculate duration between two timestamps
 */
function calculate_duration($start, $end, $format = 'hours') {
    if (!$start || !$end) return 0;

    try {
        $startTime = new DateTime($start);
        $endTime = new DateTime($end);
        $diff = $endTime->diff($startTime);

        switch ($format) {
            case 'minutes':
                return ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            case 'hours':
                return $diff->days * 24 + $diff->h + ($diff->i / 60);
            case 'days':
                return $diff->days + ($diff->h / 24);
            default:
                return $diff->format('%d days %h hours %i minutes');
        }

    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Sanitize and validate input
 */
function sanitize_input($input, $type = 'string') {
    if ($input === null || $input === '') {
        return $input;
    }

    switch ($type) {
        case 'string':
            return trim(strip_tags($input));

        case 'html':
            return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));

        case 'email':
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);

        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);

        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        case 'phone':
            return preg_replace('/[^0-9\-\+\(\)\s]/', '', trim($input));

        case 'id_number':
            return preg_replace('/[^0-9]/', '', trim($input));

        default:
            return trim(strip_tags($input));
    }
}

/**
 * Validate Thai phone number
 */
function validate_phone_thai($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Thai mobile numbers (10 digits starting with 06, 08, 09)
    if (preg_match('/^(06|08|09)[0-9]{8}$/', $phone)) {
        return true;
    }

    // Thai landline numbers (9 digits starting with 02-07)
    if (preg_match('/^(02|03|04|05|07)[0-9]{7}$/', $phone)) {
        return true;
    }

    return false;
}

/**
 * Validate Thai ID number (13 digits)
 */
function validate_thai_id($id) {
    $id = preg_replace('/[^0-9]/', '', $id);

    if (strlen($id) !== 13) {
        return false;
    }

    // Calculate checksum
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += intval($id[$i]) * (13 - $i);
    }

    $checksum = (11 - ($sum % 11)) % 10;

    return $checksum == intval($id[12]);
}

/**
 * Generate booking code
 */
function generate_booking_code($prefix = 'BK') {
    $timestamp = date('ymd');
    $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    return $prefix . $timestamp . $random;
}

/**
 * Generate receipt number
 */
function generate_receipt_number($prefix = 'RCP') {
    $timestamp = date('ymd');
    $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return $prefix . $timestamp . $random;
}

/**
 * Format room number for display
 */
function format_room_number($roomNumber) {
    return str_pad($roomNumber, 3, '0', STR_PAD_LEFT);
}

/**
 * Get room type label in Thai (ประเภทห้อง)
 */
function get_room_type_label($type) {
    $labels = [
        'double' => 'เตียงคู่',
        'single' => 'เตียงเดี่ยว'
    ];

    return $labels[$type] ?? $type;
}

/**
 * Get plan type label in Thai (ประเภทการเข้าพัก)
 */
function get_plan_type_label($type) {
    $labels = [
        'short' => 'ชั่วคราว',
        'overnight' => 'ค้างคืน'
    ];

    return $labels[$type] ?? $type;
}

/**
 * Get room status label and CSS class
 */
function get_room_status_info($status) {
    $statusInfo = [
        'available' => ['label' => 'ว่าง', 'class' => 'success'],
        'occupied' => ['label' => 'มีผู้เข้าพัก', 'class' => 'danger'],
        'cleaning' => ['label' => 'ทำความสะอาด', 'class' => 'warning'],
        'maintenance' => ['label' => 'ซ่อมบำรุง', 'class' => 'info']
    ];

    return $statusInfo[$status] ?? ['label' => $status, 'class' => 'secondary'];
}

/**
 * Get booking status label and CSS class
 */
function get_booking_status_info($status) {
    $statusInfo = [
        'pending' => ['label' => 'รอยืนยัน', 'class' => 'warning'],
        'confirmed' => ['label' => 'ยืนยันแล้ว', 'class' => 'info'],
        'checked_in' => ['label' => 'เช็คอินแล้ว', 'class' => 'primary'],
        'checked_out' => ['label' => 'เช็คเอาท์แล้ว', 'class' => 'success'],
        'cancelled' => ['label' => 'ยกเลิกแล้ว', 'class' => 'danger']
    ];

    return $statusInfo[$status] ?? ['label' => $status, 'class' => 'secondary'];
}

/**
 * Get payment status label and CSS class
 */
function get_payment_status_info($status) {
    $statusInfo = [
        'pending' => ['label' => 'รอชำระ', 'class' => 'warning'],
        'paid' => ['label' => 'ชำระแล้ว', 'class' => 'success'],
        'partial' => ['label' => 'ชำระบางส่วน', 'class' => 'info'],
        'refunded' => ['label' => 'คืนเงินแล้ว', 'class' => 'secondary']
    ];

    return $statusInfo[$status] ?? ['label' => $status, 'class' => 'secondary'];
}

/**
 * Get room status label and CSS class
 */
function getRoomStatusInfo($status) {
    $statusInfo = [
        'available' => ['label' => 'ว่าง', 'class' => 'success'],
        'occupied' => ['label' => 'มีผู้เข้าพัก', 'class' => 'danger'],
        'cleaning' => ['label' => 'ทำความสะอาด', 'class' => 'warning'],
        'maintenance' => ['label' => 'ซ่อมบำรุง', 'class' => 'secondary'],
        'reserved' => ['label' => 'จองแล้ว', 'class' => 'info']
    ];

    return $statusInfo[$status] ?? ['label' => $status, 'class' => 'secondary'];
}

/**
 * Check if user has permission
 */
function has_permission($user_role, $required_permissions) {
    if (!is_array($required_permissions)) {
        $required_permissions = [$required_permissions];
    }

    $role_permissions = [
        'admin' => ['admin', 'reception', 'housekeeping'],
        'reception' => ['reception'],
        'housekeeping' => ['housekeeping']
    ];

    $user_permissions = $role_permissions[$user_role] ?? [];

    return !empty(array_intersect($required_permissions, $user_permissions));
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'info') {
    session_start();
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
    header("Location: {$url}");
    exit;
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    session_start();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Debug helper - pretty print variables (only in development)
 */
function dd($var, $die = true) {
    if (env('APP_ENV', 'development') === 'development') {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';

        if ($die) {
            die();
        }
    }
}

/**
 * Log activity for audit trail
 */
function log_activity($user_id, $action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $action,
            $table_name,
            $record_id,
            $old_values ? json_encode($old_values) : null,
            $new_values ? json_encode($new_values) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

    } catch (Exception $e) {
        // Log to error log but don't break the application
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Calculate room rate based on plan type and duration
 */
function calculate_room_rate($plan_type, $check_in, $check_out) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        // Get base rate
        $stmt = $pdo->prepare("SELECT price, duration_hours FROM rates WHERE rate_type = ? AND is_active = 1");
        $stmt->execute([$plan_type === 'short' ? 'short_3h' : 'overnight']);
        $rate = $stmt->fetch();

        if (!$rate) {
            return ['base_amount' => 0, 'extra_amount' => 0, 'total_amount' => 0];
        }

        $duration_hours = calculate_duration($check_in, $check_out, 'hours');
        $base_amount = $rate['price'];
        $extra_amount = 0;

        // Calculate extra charges for overtime
        if ($duration_hours > $rate['duration_hours']) {
            $extra_hours = $duration_hours - $rate['duration_hours'];
            // Get hourly rate for extensions
            $stmt = $pdo->prepare("SELECT price FROM rates WHERE rate_type = 'extended' AND is_active = 1");
            $stmt->execute();
            $hourly_rate = $stmt->fetch();

            if ($hourly_rate) {
                $extra_amount = ceil($extra_hours) * $hourly_rate['price'];
            }
        }

        return [
            'base_amount' => $base_amount,
            'extra_amount' => $extra_amount,
            'total_amount' => $base_amount + $extra_amount
        ];

    } catch (Exception $e) {
        return ['base_amount' => 0, 'extra_amount' => 0, 'total_amount' => 0];
    }
}

/**
 * Check if room is available for given time period
 */
function is_room_available($room_id, $check_in, $check_out, $exclude_booking_id = null) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        $sql = "
            SELECT COUNT(*) FROM bookings
            WHERE room_id = ?
            AND status IN ('confirmed', 'checked_in')
            AND (
                (planned_check_in < ? AND planned_check_out > ?) OR
                (planned_check_in < ? AND planned_check_out > ?) OR
                (planned_check_in >= ? AND planned_check_out <= ?)
            )
        ";

        $params = [$room_id, $check_out, $check_in, $check_out, $check_in, $check_in, $check_out];

        if ($exclude_booking_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_booking_id;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() == 0;

    } catch (Exception $e) {
        return false;
    }
}

/**
 * Calculate billing based on plan type and duration
 * New business rules: overnight stays calculated by nights until 12:00 checkout
 */
function calculate_billing($plan_type, $check_in, $check_out) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        if ($plan_type === 'short') {
            return calculate_short_term_billing($pdo, $check_in, $check_out);
        } else {
            return calculate_overnight_billing($pdo, $check_in, $check_out);
        }

    } catch (Exception $e) {
        return [
            'base_amount' => 0,
            'extra_amount' => 0,
            'total_amount' => 0,
            'nights' => 0,
            'hours' => 0,
            'is_overdue' => false
        ];
    }
}

/**
 * Calculate short-term billing (hour-based)
 */
function calculate_short_term_billing($pdo, $check_in, $check_out) {
    // Get short-term rate from room_rates table
    $stmt = $pdo->prepare("SELECT price, duration_hours FROM room_rates WHERE rate_type = 'short' AND is_active = 1");
    $stmt->execute();
    $rate = $stmt->fetch();

    if (!$rate) {
        $rate = ['price' => 250, 'duration_hours' => 3]; // Default values
    }

    $check_in_time = new DateTime($check_in);
    $check_out_time = new DateTime($check_out);
    $duration_hours = ($check_out_time->getTimestamp() - $check_in_time->getTimestamp()) / 3600;

    $base_amount = $rate['price'];
    $extra_amount = 0;
    $is_overdue = false;

    // Calculate overtime
    if ($duration_hours > $rate['duration_hours']) {
        $extra_hours = ceil($duration_hours - $rate['duration_hours']);
        $extra_amount = $extra_hours * 100; // 100 baht per hour
        $is_overdue = true;
    }

    return [
        'base_amount' => $base_amount,
        'extra_amount' => $extra_amount,
        'total_amount' => $base_amount + $extra_amount,
        'nights' => 0,
        'hours' => $duration_hours,
        'is_overdue' => $is_overdue,
        'overdue_hours' => $is_overdue ? ceil($duration_hours - $rate['duration_hours']) : 0
    ];
}

/**
 * Calculate overnight billing (night-based until 12:00)
 */
function calculate_overnight_billing($pdo, $check_in, $check_out) {
    // Get overnight rate from room_rates table
    $stmt = $pdo->prepare("SELECT price FROM room_rates WHERE rate_type = 'overnight' AND is_active = 1");
    $stmt->execute();
    $rate = $stmt->fetch();

    if (!$rate) {
        $rate = ['price' => 400]; // Default value
    }

    $check_in_time = new DateTime($check_in);
    $check_out_time = new DateTime($check_out);

    // Calculate nights based on the number of days between check-in and check-out
    // Each night allows checkout until 12:00 PM of the next day
    $check_in_date = $check_in_time->format('Y-m-d');
    $check_out_date = $check_out_time->format('Y-m-d');

    // Calculate the number of days difference
    $date_diff = (new DateTime($check_out_date))->diff(new DateTime($check_in_date))->days;
    $nights = max(1, $date_diff); // Minimum 1 night

    // If checking out on the same day, it's still 1 night
    if ($date_diff == 0) {
        $nights = 1;
    }

    // Check if overdue (after 12:00 PM of the expected checkout day)
    $expected_checkout = new DateTime($check_in_date);
    $expected_checkout->add(new DateInterval('P' . $nights . 'D')); // Add nights
    $expected_checkout->setTime(12, 0, 0); // Set to 12:00 PM

    $is_overdue = $check_out_time > $expected_checkout;
    $overdue_hours = 0;

    if ($is_overdue) {
        $overdue_hours = ($check_out_time->getTimestamp() - $expected_checkout->getTimestamp()) / 3600;
    }

    $base_amount = $rate['price'] * $nights;
    $extra_amount = 0;

    // Calculate late checkout fee
    if ($is_overdue) {
        $extra_amount = ceil($overdue_hours) * 100; // 100 baht per hour after 12:00
    }

    return [
        'base_amount' => $base_amount,
        'extra_amount' => $extra_amount,
        'total_amount' => $base_amount + $extra_amount,
        'nights' => $nights,
        'hours' => 0,
        'is_overdue' => $is_overdue,
        'overdue_hours' => $overdue_hours
    ];
}

/**
 * Check if room is overdue for checkout
 */
function is_room_overdue($booking) {
    if (!$booking || $booking['status'] !== 'active') {
        return false;
    }

    $now = new DateTime();
    $checkout_at = new DateTime($booking['checkout_at']);

    // Simple comparison: if current time is past the scheduled checkout time
    return $now > $checkout_at;
}

/**
 * Get overdue duration for a booking
 */
function get_overdue_duration($booking) {
    if (!is_room_overdue($booking)) {
        return 0;
    }

    $now = new DateTime();
    $checkout_at = new DateTime($booking['checkout_at']);

    // Calculate overdue time in hours
    $overdue_seconds = $now->getTimestamp() - $checkout_at->getTimestamp();
    return $overdue_seconds / 3600; // Return hours
}