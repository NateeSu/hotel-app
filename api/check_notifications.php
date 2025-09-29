<?php
/**
 * Hotel Management System - Check Notifications API
 *
 * Check for urgent notifications (checkout reminders, cleaning completed, etc.)
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

// Load required files
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        throw new Exception('User not authenticated');
    }

    $pdo = getDatabase();
    $notifications = [];

    // Check for short-term checkouts approaching time limit
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, r.id as room_id
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.status IN ('active', 'checked_in', 'confirmed')
        AND b.plan_type = 'short'
        AND b.checkout_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 15 MINUTE)
        AND b.checkout_at > NOW()
        AND r.status = 'occupied'
    ");
    $stmt->execute();
    $approachingCheckouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($approachingCheckouts as $booking) {
        $checkoutTime = new DateTime($booking['checkout_at']);
        $now = new DateTime();
        $timeDiff = $checkoutTime->diff($now);

        $notifications[] = [
            'type' => 'warning',
            'icon' => 'clock',
            'title' => 'ใกล้เวลาเช็คเอาท์',
            'message' => "ห้อง {$booking['room_number']} - {$booking['guest_name']}<br>เหลือเวลา {$timeDiff->i} นาที",
            'room_id' => $booking['room_id'],
            'booking_id' => $booking['id']
        ];
    }

    // Check for overdue short-term checkouts
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, r.id as room_id
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.status IN ('active', 'checked_in', 'confirmed')
        AND b.plan_type = 'short'
        AND b.checkout_at < NOW()
        AND r.status = 'occupied'
    ");
    $stmt->execute();
    $overdueCheckouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($overdueCheckouts as $booking) {
        $checkoutTime = new DateTime($booking['checkout_at']);
        $now = new DateTime();
        $timeDiff = $now->diff($checkoutTime);

        $notifications[] = [
            'type' => 'danger',
            'icon' => 'exclamation-triangle',
            'title' => 'เกินเวลาเช็คเอาท์',
            'message' => "ห้อง {$booking['room_number']} - {$booking['guest_name']}<br>เกินเวลาแล้ว {$timeDiff->i} นาที",
            'room_id' => $booking['room_id'],
            'booking_id' => $booking['id']
        ];
    }

    // Check for recently completed housekeeping jobs (if table exists)
    try {
        // Check if housekeeping_jobs table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'housekeeping_jobs'");
        if ($tableCheck->rowCount() > 0) {

            // Check if notification_sent column exists
            $columnCheck = $pdo->query("SHOW COLUMNS FROM housekeeping_jobs LIKE 'notification_sent'");
            $hasNotificationColumn = $columnCheck->rowCount() > 0;

            $sql = "
                SELECT hj.*, r.room_number, r.id as room_id
                FROM housekeeping_jobs hj
                JOIN rooms r ON hj.room_id = r.id
                WHERE hj.status = 'completed'
                AND hj.completed_at BETWEEN DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND NOW()
            ";

            if ($hasNotificationColumn) {
                $sql .= " AND hj.notification_sent = 0";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $completedJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($completedJobs as $job) {
                $notifications[] = [
                    'type' => 'success',
                    'icon' => 'check-circle',
                    'title' => 'ทำความสะอาดเสร็จสิ้น',
                    'message' => "ห้อง {$job['room_number']} พร้อมให้บริการแล้ว",
                    'room_id' => $job['room_id'],
                    'job_id' => $job['id']
                ];

                // Mark notification as sent (if column exists)
                if ($hasNotificationColumn) {
                    $updateStmt = $pdo->prepare("UPDATE housekeeping_jobs SET notification_sent = 1 WHERE id = ?");
                    $updateStmt->execute([$job['id']]);
                }
            }
        }
    } catch (Exception $e) {
        // Ignore housekeeping table errors, continue with other notifications
        error_log("Housekeeping table error: " . $e->getMessage());
    }

    // Return successful response
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications)
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>