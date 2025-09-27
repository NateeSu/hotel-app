<?php
/**
 * Debug Template Loading Issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define APP_INIT to allow access
define('APP_INIT', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Bangkok');

// Define base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . '://' . $host . $scriptPath;
$GLOBALS['baseUrl'] = $baseUrl;

echo "<h2>🔍 Debug Template Loading</h2>";

try {
    // Load required files
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/includes/helpers.php';
    require_once __DIR__ . '/includes/csrf.php';
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/router.php';
    require_once __DIR__ . '/templates/partials/flash.php';

    // Check auth
    requireLogin(['reception', 'admin']);
    $currentUser = currentUser();
    $userRole = $currentUser['role'];

    // Set page variables like in board.php
    $pageTitle = 'แผงควบคุมห้องพัก - Hotel Management System';
    $pageDescription = 'แสดงสถานะห้องพักทั้งหมดในระบบ';

    echo "✅ All includes loaded<br>";
    echo "✅ User role: " . $userRole . "<br>";

    // Test header loading
    echo "<h3>Testing Header Template:</h3>";
    ob_start();
    require_once __DIR__ . '/templates/layout/header.php';
    $headerOutput = ob_get_contents();
    ob_end_clean();

    echo "✅ Header template loaded (" . strlen($headerOutput) . " characters)<br>";

    // Sample minimal room data
    $sampleRooms = [
        ['id' => 1, 'room_number' => '101', 'type' => 'short', 'status' => 'available', 'notes' => 'Test room'],
        ['id' => 2, 'room_number' => '102', 'type' => 'short', 'status' => 'occupied', 'notes' => '']
    ];

    // Output the actual page
    echo $headerOutput;
    ?>

    <!-- Test Room Board Grid -->
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                    แผงควบคุมห้องพัก (TEST)
                </h1>
                <p class="text-muted mb-0">ทดสอบการแสดงผล</p>
            </div>
        </div>

        <div class="row">
            <?php foreach ($sampleRooms as $room): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card room-card h-100 border-success">
                        <div class="card-header bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-door-closed me-2"></i>
                                    <?php echo htmlspecialchars($room['room_number']); ?>
                                </h5>
                                <span class="badge bg-light text-dark">
                                    <?php echo $room['type'] === 'short' ? 'ชั่วคราว' : 'ค้างคืน'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="room-status-info mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <span class="fw-bold">ทดสอบ</span>
                                </div>

                                <?php if (!empty($room['notes'])): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-sticky me-1"></i>
                                        <?php echo htmlspecialchars($room['notes']); ?>
                                    </small>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success btn-sm">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Test Button
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
    .room-card {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    .room-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    </style>

    <?php
    // Test footer loading
    echo "<h4>Testing Footer:</h4>";
    require_once __DIR__ . '/templates/layout/footer.php';

} catch (Exception $e) {
    echo "<h3>❌ ERROR:</h3>";
    echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red;'>";
    echo "<strong>Message:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<script>
console.log("Template debug script loaded successfully");
</script>