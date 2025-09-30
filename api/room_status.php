<?php
/**
 * Hotel Management System - Room Status API
 *
 * Real-time room status updates for Room Board
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

    // Get filter parameters
    $statusFilter = $_GET['status'] ?? '';

    $pdo = getDatabase();

    // Build query with booking information for overdue status
    $sql = "
        SELECT
            r.id,
            r.room_number,
            r.room_type as type,
            r.status,
            r.notes,
            b.id as booking_id,
            b.guest_name,
            b.plan_type,
            b.checkin_at,
            b.checkout_at,
            b.status as booking_status
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
    $rawRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process rooms to add overdue status
    $rooms = [];
    foreach ($rawRooms as $room) {
        $roomData = [
            'id' => $room['id'],
            'room_number' => $room['room_number'],
            'type' => $room['type'],
            'status' => $room['status'],
            'notes' => $room['notes'],
            'guest_name' => $room['guest_name'],
            'plan_type' => $room['plan_type'],
            'checkin_at' => $room['checkin_at'],
            'checkout_at' => $room['checkout_at'],
            'is_overdue' => false,
            'overdue_hours' => 0,
            'overdue_text' => ''
        ];

        // Check if room is overdue
        if ($room['booking_status'] === 'active' && $room['checkin_at'] && $room['checkout_at']) {
            $booking = [
                'status' => 'active',
                'plan_type' => $room['plan_type'],
                'checkin_at' => $room['checkin_at'],
                'checkout_at' => $room['checkout_at']
            ];

            if (is_room_overdue($booking)) {
                $overdue_hours = get_overdue_duration($booking);
                $roomData['is_overdue'] = true;
                $roomData['overdue_hours'] = $overdue_hours;

                if ($overdue_hours < 1) {
                    $minutes = floor($overdue_hours * 60);
                    $roomData['overdue_text'] = "{$minutes} นาที";
                } else {
                    $hours = floor($overdue_hours);
                    $minutes = floor(($overdue_hours - $hours) * 60);
                    if ($minutes > 0) {
                        $roomData['overdue_text'] = "{$hours} ชม. {$minutes} นาที";
                    } else {
                        $roomData['overdue_text'] = "{$hours} ชม.";
                    }
                }
            }
        }

        $rooms[] = $roomData;
    }

    // Return successful response
    echo json_encode([
        'success' => true,
        'rooms' => $rooms,
        'timestamp' => date('Y-m-d H:i:s'),
        'count' => count($rooms)
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