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

    // Build query
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