<?php
/**
 * Hotel Management System - Receipt Viewer
 *
 * Display receipt in print-ready format
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
require_once __DIR__ . '/../lib/receipt_generator.php';
require_once __DIR__ . '/../templates/partials/flash.php';

// Require login with reception role or higher
requireLogin(['reception', 'admin']);

// Get parameters
$receiptNumber = $_GET['receipt_number'] ?? null;
$bookingId = $_GET['booking_id'] ?? null;
$extraAmount = floatval($_GET['extra_amount'] ?? 0);
$extraNotes = $_GET['extra_notes'] ?? '';

if (!$receiptNumber && !$bookingId) {
    flash_error('ไม่ได้ระบุใบเสร็จที่ต้องการดู');
    redirectToRoute('rooms.board');
}

try {
    $receiptGenerator = new ReceiptGenerator();
    $receiptData = null;

    if ($receiptNumber) {
        // Look up existing receipt
        $receiptData = $receiptGenerator->findReceiptByNumber($receiptNumber);
        if (!$receiptData) {
            flash_error('ไม่พบใบเสร็จที่ระบุ');
            redirectToRoute('rooms.board');
        }
    } elseif ($bookingId) {
        // Generate new receipt
        $receiptData = $receiptGenerator->generateReceipt($bookingId, $extraAmount, $extraNotes);
    }

    if (!$receiptData) {
        flash_error('ไม่สามารถสร้างใบเสร็จได้');
        redirectToRoute('rooms.board');
    }

    // Generate HTML receipt
    $htmlReceipt = $receiptGenerator->generateHTMLReceipt($receiptData);

    // Output the receipt directly
    echo $htmlReceipt;

} catch (Exception $e) {
    error_log("Receipt view error: " . $e->getMessage());
    flash_error('เกิดข้อผิดพลาดในการแสดงใบเสร็จ: ' . $e->getMessage());
    redirectToRoute('rooms.board');
}
?>