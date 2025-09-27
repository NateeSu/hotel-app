<?php
/**
 * Minimal Room Board for Testing
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_INIT', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Bangkok');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $protocol . '://' . $host . $scriptPath;
$GLOBALS['baseUrl'] = $baseUrl;

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/router.php';

requireLogin(['reception', 'admin']);

// Fetch rooms
try {
    $pdo = getDatabase();
    $sql = "SELECT id, room_number, room_type as type, status, notes FROM rooms ORDER BY room_number";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rooms = $stmt->fetchAll();
} catch (Exception $e) {
    $rooms = [];
    $error = $e->getMessage();
}

$pageTitle = 'Room Board - Minimal';
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
<body>
    <div class="container mt-4">
        <h1><i class="bi bi-grid-3x3-gap"></i> Room Board (Minimal Test)</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <?php if (empty($rooms)): ?>
                <div class="col-12">
                    <div class="alert alert-info">ไม่พบข้อมูลห้องพัก</div>
                </div>
            <?php else: ?>
                <?php foreach ($rooms as $room): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><?php echo htmlspecialchars($room['room_number']); ?></h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($room['type']); ?></p>
                                <p><strong>Status:</strong> <?php echo htmlspecialchars($room['status']); ?></p>
                                <?php if (!empty($room['notes'])): ?>
                                    <p><small><?php echo htmlspecialchars($room['notes']); ?></small></p>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-success">Test Action</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <a href="<?php echo $baseUrl; ?>/?r=rooms.board" class="btn btn-secondary">กลับไป Room Board จริง</a>
            <a href="<?php echo $baseUrl; ?>/debug_templates.php" class="btn btn-info">Debug Templates</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log("Minimal room board loaded successfully");
        console.log("Found <?php echo count($rooms); ?> rooms");
    </script>
</body>
</html>