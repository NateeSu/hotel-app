<?php
/**
 * Hotel Management System - Front Controller
 *
 * This file serves as the main entry point for the application.
 * It initializes the application, handles routing, and manages the request flow.
 */

// Define application constants
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}
if (!defined('APP_START_TIME')) {
    define('APP_START_TIME', microtime(true));
}

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting based on environment
$isProduction = ($_SERVER['HTTP_HOST'] ?? 'localhost') !== 'localhost'
    && !str_contains($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1');

// ALWAYS show errors during debugging (remove this in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Log to file as well
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Set default timezone
date_default_timezone_set('Asia/Bangkok');

// Define base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Remove trailing slash and handle root directory
$scriptName = rtrim($scriptName, '/');
if ($scriptName === '' || $scriptName === '.') {
    $scriptName = '';
}

$baseUrl = $protocol . '://' . $host . $scriptName;

// Make baseUrl globally available
$GLOBALS['baseUrl'] = $baseUrl;

try {
    // Debug: Log initialization
    error_log("=== Application Starting ===");
    error_log("baseUrl: " . $baseUrl);
    error_log("__DIR__: " . __DIR__);
    error_log("HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set'));
    error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set'));

    // Load core files in proper order
    error_log("Loading config/db.php");
    require_once __DIR__ . '/config/db.php';

    error_log("Loading includes/helpers.php");
    require_once __DIR__ . '/includes/helpers.php';

    error_log("Loading includes/csrf.php");
    require_once __DIR__ . '/includes/csrf.php';

    error_log("Loading includes/auth.php");
    require_once __DIR__ . '/includes/auth.php';

    error_log("Loading includes/router.php");
    require_once __DIR__ . '/includes/router.php';

    // Load flash message functions from partials/flash.php
    error_log("Loading templates/partials/flash.php");
    require_once __DIR__ . '/templates/partials/flash.php';

    // Check session timeout for logged-in users
    error_log("Checking if user is logged in");
    if (isLoggedIn()) {
        error_log("User is logged in, checking session timeout");
        checkSessionTimeout();
    } else {
        error_log("User is NOT logged in");
    }

    // Handle the current route
    error_log("Calling handleRoute()");
    handleRoute();
    error_log("handleRoute() completed");

} catch (Exception $e) {
    // Log the error with full details
    error_log("=== FATAL ERROR ===");
    error_log("Application error: " . $e->getMessage());
    error_log("File: " . $e->getFile());
    error_log("Line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Show error page
    http_response_code(500);
    $pageTitle = 'เกิดข้อผิดพลาด - Hotel Management System';
    $pageDescription = 'ระบบเกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($pageTitle); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    </head>
    <body class="bg-light">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-md-6 text-center">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-5">
                            <i class="bi bi-exclamation-triangle text-warning display-1 mb-3"></i>
                            <h1 class="h3 mb-3">เกิดข้อผิดพลาดในระบบ</h1>
                            <p class="text-muted mb-4">
                                ขออภัย ระบบเกิดข้อผิดพลาดชั่วคราว<br>
                                กรุณาลองใหม่อีกครั้งในภายหลัง
                            </p>

                            <!-- Always show debug info during troubleshooting -->
                            <div class="alert alert-danger text-start">
                                <strong>Debug Info:</strong><br>
                                <strong>Error:</strong> <?php echo htmlspecialchars($e->getMessage()); ?><br>
                                <strong>File:</strong> <?php echo htmlspecialchars($e->getFile()); ?><br>
                                <strong>Line:</strong> <?php echo htmlspecialchars($e->getLine()); ?><br>
                                <hr>
                                <strong>Stack Trace:</strong><br>
                                <pre style="font-size: 11px; max-height: 300px; overflow: auto;"><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
                            </div>

                            <div class="d-flex gap-2 justify-content-center">
                                <button onclick="window.location.reload()" class="btn btn-primary">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    ลองใหม่
                                </button>
                                <a href="<?php echo $baseUrl; ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-house me-1"></i>
                                    หน้าแรก
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Auto-reload after 5 seconds if not in production
            <?php if (!$isProduction): ?>
            setTimeout(function() {
                if (confirm('ต้องการโหลดหน้าใหม่หรือไม่?')) {
                    window.location.reload();
                }
            }, 5000);
            <?php endif; ?>
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Performance tracking (development only)
if (!$isProduction && env('APP_DEBUG', false)) {
    $endTime = microtime(true);
    $executionTime = round(($endTime - APP_START_TIME) * 1000, 2);

    if (function_exists('memory_get_peak_usage')) {
        $memoryUsage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        error_log("Page execution: {$executionTime}ms, Memory: {$memoryUsage}MB");
    }
}
?>