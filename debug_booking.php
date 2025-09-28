<?php
// Debug booking detection for transfer system
require_once 'config/db.php';

$roomId = $_GET['room_id'] ?? 1; // Change this to your test room ID

try {
    $pdo = getDatabase();

    echo "<h3>Debug: Booking Detection for Room ID: $roomId</h3>";

    // Check room status
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h4>Room Information:</h4>";
    echo "<pre>";
    print_r($room);
    echo "</pre>";

    // Check all bookings for this room
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE room_id = ? ORDER BY created_at DESC");
    $stmt->execute([$roomId]);
    $allBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h4>All Bookings for Room:</h4>";
    echo "<pre>";
    print_r($allBookings);
    echo "</pre>";

    // Check active bookings
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, r.room_type
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.room_id = ?
        AND b.status = 'active'
        ORDER BY b.checkin_at DESC
    ");
    $stmt->execute([$roomId]);
    $activeBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h4>Active Bookings:</h4>";
    echo "<pre>";
    print_r($activeBookings);
    echo "</pre>";

    // Check current time bookings
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, r.room_type,
               b.checkin_at <= NOW() as checked_in,
               b.checkout_at > NOW() as not_checked_out,
               NOW() as current_time
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.room_id = ?
        AND b.status = 'active'
        AND b.checkin_at <= NOW()
        AND b.checkout_at > NOW()
        ORDER BY b.checkin_at DESC
    ");
    $stmt->execute([$roomId]);
    $currentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h4>Current Time Valid Bookings:</h4>";
    echo "<pre>";
    print_r($currentBookings);
    echo "</pre>";

    // Check booking statuses
    $stmt = $pdo->query("SELECT DISTINCT status FROM bookings");
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h4>Available Booking Statuses:</h4>";
    echo "<pre>";
    print_r($statuses);
    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>