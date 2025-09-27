<?php
/**
 * Hotel Management System - Mark Cleaning Done Placeholder
 *
 * TODO: Implement housekeeping job completion
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
$pageTitle = 'Mark Cleaning Done - Hotel Management System';
$pageDescription = 'Complete housekeeping job';

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
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Mark Cleaning Done
                    </h1>
                    <p class="text-muted mb-0">Complete housekeeping job and make room available</p>
                </div>

                <a href="<?php echo routeUrl('rooms.board'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Room Board
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">
                            <i class="bi bi-brush me-2"></i>
                            TODO: Housekeeping Job Completion
                        </h4>
                        <p class="mb-2">This page is a placeholder. The following features need to be implemented:</p>
                        <ul class="mb-0">
                            <li>Housekeeping checklist display</li>
                            <li>Job completion confirmation</li>
                            <li>Quality control notes</li>
                            <li>Staff assignment record</li>
                            <li>Time tracking</li>
                            <li>Room status update to 'available'</li>
                        </ul>

                        <?php if ($roomId): ?>
                            <hr>
                            <p class="mb-0"><strong>Room ID:</strong> <?php echo htmlspecialchars($roomId); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Placeholder form structure -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Cleaning Checklist (Placeholder)</h5>
                            <div class="border p-3 bg-light rounded">
                                <p class="text-muted">Room inspection items</p>
                                <p class="text-muted">Amenities restocking</p>
                                <p class="text-muted">Maintenance issues</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Completion Details (Placeholder)</h5>
                            <div class="border p-3 bg-light rounded">
                                <p class="text-muted">Staff name</p>
                                <p class="text-muted">Completion time</p>
                                <p class="text-muted">Notes/Comments</p>
                                <p class="text-muted">Mark as available button</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>