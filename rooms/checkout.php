<?php
/**
 * Hotel Management System - Room Check-out Placeholder
 *
 * TODO: Implement check-out form and process
 */

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

// Require login with reception role or higher
requireLogin(['reception', 'admin']);

// Set page variables
$pageTitle = 'Check-out - Hotel Management System';
$pageDescription = 'Room check-out process';

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
                        <i class="bi bi-box-arrow-left text-primary me-2"></i>
                        Room Check-out
                    </h1>
                    <p class="text-muted mb-0">Process guest check-out</p>
                </div>

                <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Room Board
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            TODO: Check-out Process Implementation
                        </h4>
                        <p class="mb-2">This page is a placeholder. The following features need to be implemented:</p>
                        <ul class="mb-0">
                            <li>Current guest information display</li>
                            <li>Bill calculation and payment</li>
                            <li>Additional charges (mini-bar, damages, etc.)</li>
                            <li>Check-out time recording</li>
                            <li>Room status update to 'cleaning'</li>
                        </ul>

                        <?php if ($roomId): ?>
                            <hr>
                            <p class="mb-0"><strong>Room ID:</strong> <?php echo htmlspecialchars($roomId); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Placeholder form structure -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Guest Information (Placeholder)</h5>
                            <div class="border p-3 bg-light rounded">
                                <p class="text-muted">Current guest details</p>
                                <p class="text-muted">Check-in date/time</p>
                                <p class="text-muted">Stay duration</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Billing Summary (Placeholder)</h5>
                            <div class="border p-3 bg-light rounded">
                                <p class="text-muted">Room charges</p>
                                <p class="text-muted">Additional services</p>
                                <p class="text-muted">Total amount</p>
                                <p class="text-muted">Payment confirmation</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>