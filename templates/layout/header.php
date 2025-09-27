<?php
/**
 * Hotel Management System - Layout Header
 *
 * This file contains the HTML document head and opening body/main tags.
 * Includes Bootstrap 5 CSS, custom CSS, and meta tags for responsive design.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

$pageTitle = $pageTitle ?? 'Hotel Management System';
$pageDescription = $pageDescription ?? 'Hotel management and booking system';
?>
<!DOCTYPE html>
<html lang="th" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="author" content="Hotel Management System">
    <meta name="robots" content="noindex, nofollow">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link href="<?php echo $GLOBALS['baseUrl'] ?? '/hotel-app'; ?>/assets/css/app.css?v=<?php echo time(); ?>" rel="stylesheet">

    <!-- Fallback CSS for immediate styling -->
    <style>
        body {
            background-color: #f8f9fa !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            color: #212529 !important;
        }
        .container-fluid { padding: 2rem; }
        .room-card {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .room-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .room-card .card-header { border-bottom: none; font-weight: 600; }
        .room-status-info { min-height: 60px; }
        .btn { border-radius: 0.5rem; font-weight: 500; }
        @media (max-width: 576px) {
            .container-fluid { padding: 1rem; }
            .col-sm-6 { flex: 0 0 50%; max-width: 50%; }
        }
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo $GLOBALS['baseUrl'] ?? '/hotel-app'; ?>/assets/images/hotel-icon.svg">
    <link rel="alternate icon" href="<?php echo $GLOBALS['baseUrl'] ?? '/hotel-app'; ?>/assets/images/hotel-icon.png">

    <!-- Preload critical fonts -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"></noscript>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 m-2" style="z-index: 9999;">
        ข้ามไปเนื้อหาหลัก
    </a>

    <?php
    // Include navbar if user is logged in or on login page
    $showNavbar = isLoggedIn() || (isset($_GET['r']) && $_GET['r'] === 'auth.login');
    if ($showNavbar):
    ?>
        <?php require_once __DIR__ . '/../partials/navbar.php'; ?>
    <?php endif; ?>

    <!-- Main content area -->
    <main id="main-content" class="flex-grow-1 py-3" role="main">
        <div class="container-fluid px-3 px-md-4">
            <?php
            // Show flash messages
            require_once __DIR__ . '/../partials/flash.php';
            ?>