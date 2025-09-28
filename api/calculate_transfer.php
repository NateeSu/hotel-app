<?php
/**
 * Hotel Management System - Transfer Cost Calculation API
 *
 * AJAX endpoint for calculating room transfer costs
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
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../lib/transfer_engine.php';

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        throw new Exception('User not authenticated');
    }

    // Verify CSRF token
    $headers = getallheaders();
    $csrfToken = $headers['X-CSRF-Token'] ?? $_POST['csrf_token'] ?? null;
    if (!verify_csrf_token($csrfToken)) {
        throw new Exception('Invalid CSRF token');
    }

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required parameters
    $fromRoomId = $input['from_room_id'] ?? null;
    $toRoomId = $input['to_room_id'] ?? null;
    $bookingId = $input['booking_id'] ?? null;

    if (!$fromRoomId || !$toRoomId || !$bookingId) {
        throw new Exception('Missing required parameters');
    }

    // Initialize transfer engine
    $transferEngine = new TransferEngine();

    // Calculate transfer cost
    $calculation = $transferEngine->calculateTransferCost($fromRoomId, $toRoomId, $bookingId);

    // Return successful response
    echo json_encode([
        'success' => true,
        'calculation' => $calculation
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