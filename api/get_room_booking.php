<?php
/**
 * Hotel Management System - Get Room Booking API
 *
 * Get current booking information for a specific room
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

    // Get room ID from parameters
    $roomId = $_GET['room_id'] ?? null;
    if (!$roomId) {
        throw new Exception('Room ID is required');
    }

    $pdo = getDatabase();

    // Get current booking for the room
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, r.room_type
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.room_id = ?
        AND b.status IN ('active', 'checked_in', 'confirmed')
        AND b.checkin_at <= NOW()
        AND b.checkout_at > NOW()
        ORDER BY b.checkin_at DESC
        LIMIT 1
    ");

    $stmt->execute([$roomId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no booking found with time constraints, try without time constraints
    if (!$booking) {
        $stmt = $pdo->prepare("
            SELECT b.*, r.room_number, r.room_type
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.room_id = ?
            AND b.status IN ('active', 'checked_in', 'confirmed')
            ORDER BY b.checkin_at DESC
            LIMIT 1
        ");

        $stmt->execute([$roomId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Calculate time information if booking exists
    $timeInfo = null;
    if ($booking) {
        $now = new DateTime();
        $checkinTime = new DateTime($booking['checkin_at']);
        $checkoutTime = new DateTime($booking['checkout_at']);

        $timeInfo = [
            'time_since_checkin' => $now->diff($checkinTime),
            'time_until_checkout' => $checkoutTime->diff($now),
            'is_overdue' => $now > $checkoutTime,
            'checkout_approaching' => ($checkoutTime->getTimestamp() - $now->getTimestamp()) <= 900, // 15 minutes
        ];
    }

    // Return successful response
    echo json_encode([
        'success' => true,
        'booking' => $booking,
        'time_info' => $timeInfo
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