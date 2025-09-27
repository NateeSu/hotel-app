<?php
/**
 * Hotel Management System - Front Controller
 *
 * This file serves as the main entry point for the application.
 * It initializes the application, handles routing, and manages the request flow.
 */

// Define application constants
define('APP_INIT', true);
define('APP_START_TIME', microtime(true));

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting based on environment
$isProduction = ($_SERVER['HTTP_HOST'] ?? 'localhost') !== 'localhost'
    && !str_contains($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1');

if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set default timezone
date_default_timezone_set('Asia/Bangkok');

// Define base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . '://' . $host . ($scriptName !== '/' ? $scriptName : '');

// Make baseUrl globally available
$GLOBALS['baseUrl'] = $baseUrl;

try {
    // Load core files in proper order
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/includes/helpers.php';
    require_once __DIR__ . '/includes/csrf.php';
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/router.php';

    // Load flash message functions from partials/flash.php
    require_once __DIR__ . '/templates/partials/flash.php';

    // Check session timeout for logged-in users
    if (isLoggedIn()) {
        checkSessionTimeout();
    }

    // Handle the current route
    handleRoute();

} catch (Exception $e) {
    // Log the error
    error_log("Application error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

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

                            <?php if (!$isProduction && env('APP_DEBUG', false)): ?>
                                <div class="alert alert-danger text-start">
                                    <strong>Debug Info:</strong><br>
                                    <?php echo htmlspecialchars($e->getMessage()); ?><br>
                                    <small><?php echo htmlspecialchars($e->getFile() . ':' . $e->getLine()); ?></small>
                                </div>
                            <?php endif; ?>

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