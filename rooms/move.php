<?php
/**
 * Hotel Management System - Room Move Placeholder
 *
 * TODO: Implement room move functionality
 */

// Only initialize if not already loaded by index.php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);

    // Start session and initialize application
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Asia/Bangkok');

    // Define base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go up from /hotel-app/rooms to /hotel-app
    $baseUrl = $protocol . '://' . $host . $scriptPath;
    $GLOBALS['baseUrl'] = $baseUrl;

    // Load required files
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/csrf.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/router.php';
    require_once __DIR__ . '/../templates/partials/flash.php';

} else {
    // Already initialized by index.php
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

// Require login with reception role or higher
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'Move Guest - Hotel Management System';
$pageDescription = 'Move guest to different room';

// Get room ID from POST
$roomId = $_POST['room_id'] ?? $_GET['room_id'] ?? null;

// Include header
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-arrow-left-right text-info me-2"></i>
                        Move Guest
                    </h1>
                    <p class="text-muted mb-0">Move guest to a different room</p>
                </div>

                <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Room Board
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4 class="alert-heading">
                            <i class="bi bi-info-circle me-2"></i>
                            TODO: Guest Move Implementation
                        </h4>
                        <p class="mb-2">This page is a placeholder. The following features need to be implemented:</p>
                        <ul class="mb-0">
                            <li>Current guest and room details</li>
                            <li>Available rooms selection</li>
                            <li>Reason for move (maintenance, upgrade, etc.)</li>
                            <li>Rate adjustment if needed</li>
                            <li>Update booking records</li>
                            <li>Update room statuses</li>
                        </ul>

                        <?php if ($roomId): ?>
                            <hr>
                            <p class="mb-0"><strong>From Room ID:</strong> <?php echo htmlspecialchars($roomId); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Placeholder form structure -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Current Room (Placeholder)</h5>
                            <div class="border p-3 bg-light rounded">
                                <p class="text-muted">Current room details</p>
                                <p class="text-muted">Guest information</p>
                                <p class="text-muted">Current rate</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Available Rooms (Placeholder)</h5>
                            <div class="border p-3 bg-light rounded">
                                <p class="text-muted">Room selection dropdown</p>
                                <p class="text-muted">Rate comparison</p>
                                <p class="text-muted">Move reason field</p>
                                <p class="text-muted">Confirm move button</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>