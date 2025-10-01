<?php
/**
 * Hotel Management System - Navigation Bar
 *
 * This file contains the main navigation menu with role-based access control.
 * Shows different menu items based on user role and authentication status.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

$currentUser = currentUser();
$isLoggedIn = isLoggedIn();
$userRole = $currentUser['role'] ?? null;
$currentRoute = $_GET['r'] ?? 'home';
$baseUrl = $GLOBALS['baseUrl'];

// Fetch hotel settings for navbar
$hotelName = 'Hotel Management';
try {
    if (function_exists('getDatabase')) {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT setting_value FROM hotel_settings WHERE setting_key = 'hotel_name' LIMIT 1");
        $result = $stmt->fetch();
        if ($result && !empty($result['setting_value'])) {
            $hotelName = $result['setting_value'];
        }
    }
} catch (Exception $e) {
    // Use default if error
}

// Helper function for permission check
if (!function_exists('has_permission')) {
    function has_permission($userRole, $requiredRoles) {
        if ($userRole === 'admin') return true; // Admin has all permissions
        return in_array($userRole, $requiredRoles);
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap');

.custom-navbar {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
    box-shadow: 0 2px 15px rgba(0,0,0,0.15);
    padding: 0.8rem 0;
    font-family: 'Prompt', sans-serif;
}

.custom-navbar .navbar-brand {
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    transition: transform 0.3s ease;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    color: #ffffff !important;
}

.custom-navbar .navbar-brand:hover {
    transform: scale(1.05);
    color: #ffffff !important;
}

.custom-navbar .navbar-brand i {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    color: #ffffff;
}

.custom-navbar .nav-link {
    font-size: 0.95rem;
    font-weight: 500;
    padding: 0.5rem 0.8rem !important;
    margin: 0 0.1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    letter-spacing: 0.2px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    color: #ffffff !important;
}

.custom-navbar .nav-link.disabled {
    color: rgba(255, 255, 255, 0.5) !important;
}

/* Tablet landscape (iPad) optimization */
@media (min-width: 768px) and (max-width: 1024px) {
    .custom-navbar .nav-link {
        font-size: 0.85rem;
        padding: 0.4rem 0.5rem !important;
        margin: 0;
    }

    .custom-navbar .navbar-brand {
        font-size: 1.1rem;
    }

    .custom-navbar .navbar-brand i {
        font-size: 1.5rem !important;
    }

    .custom-navbar .bi {
        font-size: 0.95rem;
    }

    /* Hide text on tablet, show only icons */
    .custom-navbar .nav-link span:not(.badge) {
        display: none;
    }

    /* Keep icon visible */
    .custom-navbar .nav-link i {
        margin-right: 0 !important;
        font-size: 1.2rem;
    }

    /* Add tooltip on hover for icon-only links */
    .custom-navbar .nav-item:not(.dropdown) .nav-link {
        position: relative;
    }

    .custom-navbar .nav-item:not(.dropdown) .nav-link:hover::after {
        content: attr(title);
        position: absolute;
        bottom: -35px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
    }

    /* Adjust dropdown toggle to show text */
    .custom-navbar .dropdown .nav-link span:not(.badge) {
        display: inline;
        margin-left: 0.3rem;
    }

    /* Reduce container padding */
    .custom-navbar .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }

    /* User dropdown on tablet - show only icon and role */
    .custom-navbar .navbar-nav:last-child .nav-link span:first-of-type {
        display: none; /* Hide username */
    }

    .custom-navbar .navbar-nav:last-child .nav-link small {
        display: inline !important; /* Keep role visible */
        margin-left: 0.5rem;
    }
}

/* iPad Pro landscape */
@media (min-width: 1024px) and (max-width: 1366px) {
    .custom-navbar .nav-link {
        font-size: 0.9rem;
        padding: 0.5rem 0.7rem !important;
    }
}

.custom-navbar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.custom-navbar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.25);
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.custom-navbar .dropdown-menu {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    padding: 0.5rem;
    margin-top: 0.5rem;
    font-family: 'Prompt', sans-serif;
}

.custom-navbar .dropdown-item {
    border-radius: 8px;
    padding: 0.6rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.custom-navbar .dropdown-item:hover:not(.disabled) {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    color: white;
    transform: translateX(5px);
}

.custom-navbar .dropdown-item.disabled {
    opacity: 0.5;
}

.custom-navbar .dropdown-header {
    font-weight: 600;
    font-size: 0.85rem;
    color: #3b82f6;
    letter-spacing: 0.5px;
}

.custom-navbar .dropdown-divider {
    margin: 0.5rem 0;
    border-top: 1px solid rgba(59, 130, 246, 0.2);
}

.custom-navbar .badge {
    font-size: 0.65rem;
    padding: 0.25em 0.5em;
    font-weight: 600;
}

.custom-navbar .bi {
    font-size: 1.1rem;
}

.custom-navbar .dropdown-toggle::after {
    transition: transform 0.3s ease;
}

.custom-navbar .dropdown.show .dropdown-toggle::after {
    transform: rotate(180deg);
}

.custom-navbar .navbar-toggler {
    border: 2px solid rgba(255, 255, 255, 0.5);
    padding: 0.5rem;
}

.custom-navbar .navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

/* Animation for dropdown menu */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.custom-navbar .dropdown-menu.show {
    animation: fadeInDown 0.3s ease;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark custom-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Brand/Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $GLOBALS['baseUrl'] ?? '/hotel-app'; ?>/?r=home">
            <i class="bi bi-building-fill me-2" style="font-size: 1.8rem;"></i>
            <div class="d-flex flex-column">
                <span class="fw-bold d-none d-sm-inline" style="line-height: 1.2;"><?php echo htmlspecialchars($hotelName); ?></span>
                <span class="fw-bold d-sm-none"><?php echo htmlspecialchars(mb_substr($hotelName, 0, 10)); ?></span>
            </div>
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation menu -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <?php if ($isLoggedIn): ?>
                <!-- Main navigation for logged-in users -->
                <ul class="navbar-nav me-auto">
                    <!-- Rooms -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo str_starts_with($currentRoute, 'rooms') ? 'active' : ''; ?>"
                           href="<?php echo $GLOBALS['baseUrl']; ?>/?r=rooms.board"
                           title="ห้องพัก">
                            <i class="bi bi-grid-3x3-gap me-1"></i>
                            <span>ห้องพัก</span>
                        </a>
                    </li>

                    <!-- Bookings (Disabled - Coming Soon) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle disabled opacity-50"
                           href="#" id="navbarBookings" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           title="ฟีเจอร์นี้อยู่ระหว่างการพัฒนา">
                            <i class="bi bi-calendar-check me-1"></i>
                            <span>การจอง</span>
                            <small class="badge bg-warning text-dark ms-1">Soon</small>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text text-muted">
                                <i class="bi bi-tools me-2"></i>ฟีเจอร์อยู่ระหว่างการพัฒนา
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-list-ul me-2"></i>รายการจอง
                            </span></li>
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-plus-circle me-2"></i>จองใหม่
                            </span></li>
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-box-arrow-in-right me-2"></i>เช็คอิน
                            </span></li>
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-box-arrow-right me-2"></i>เช็คเอาท์
                            </span></li>
                        </ul>
                    </li>

                    <!-- Customers (Reception and above) - Disabled -->
                    <?php if (has_permission($userRole, ['reception', 'admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link disabled opacity-50"
                           href="#" title="ลูกค้า (ฟีเจอร์อยู่ระหว่างพัฒนา)">
                            <i class="bi bi-people me-1"></i>
                            <span class="d-none d-md-inline">ลูกค้า</span>
                            <small class="badge bg-warning text-dark ms-1 d-none d-lg-inline">Soon</small>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Housekeeping -->
                    <?php if (has_permission($userRole, ['housekeeping', 'admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo str_starts_with($currentRoute, 'housekeeping') ? 'active' : ''; ?>"
                           href="<?php echo $GLOBALS['baseUrl']; ?>/?r=housekeeping.jobs"
                           title="แม่บ้าน">
                            <i class="bi bi-tools me-1"></i>
                            <span class="d-none d-lg-inline">แม่บ้าน</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Reports -->
                    <?php if (has_permission($userRole, ['reception', 'admin'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo str_starts_with($currentRoute, 'reports') ? 'active' : ''; ?>"
                           href="#" id="navbarReports" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-graph-up me-1"></i>
                            <span class="d-none d-lg-inline">รายงาน</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=receipts.history">
                                <i class="bi bi-receipt me-2"></i>ประวัติใบเสร็จ
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=reports.sales">
                                <i class="bi bi-currency-dollar me-2"></i>ยอดขาย
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=reports.occupancy">
                                <i class="bi bi-pie-chart me-2"></i>การเข้าพัก
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=reports.bookings">
                                <i class="bi bi-bar-chart me-2"></i>การจอง
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <!-- Admin -->
                    <?php if (has_permission($userRole, ['admin'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo str_starts_with($currentRoute, 'admin') || str_starts_with($currentRoute, 'system') ? 'active' : ''; ?>"
                           href="#" id="navbarAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear me-1"></i>
                            <span class="d-none d-xl-inline">ระบบ</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-people me-2"></i>ผู้ใช้งาน
                                <small class="badge bg-warning text-dark ms-1">Soon</small>
                            </span></li>
                            <li><a class="dropdown-item" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=admin.rooms">
                                <i class="bi bi-door-open me-2"></i>จัดการห้อง
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=system.rates">
                                <i class="bi bi-currency-dollar me-2"></i>อัตราค่าห้อง
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=system.settings">
                                <i class="bi bi-sliders me-2"></i>ตั้งค่าระบบ
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-gear me-2"></i>ตั้งค่าอื่นๆ
                                <small class="badge bg-warning text-dark ms-1">Soon</small>
                            </span></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- User info and logout -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <span class="d-none d-sm-inline">
                                <?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['username']); ?>
                            </span>
                            <small class="text-light opacity-75 ms-1 d-none d-md-inline">
                                (<?php echo ucfirst($userRole); ?>)
                            </small>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="bi bi-person me-1"></i>
                                    <?php echo htmlspecialchars($currentUser['full_name']); ?>
                                </h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-person-gear me-2"></i>โปรไฟล์
                                <small class="badge bg-warning text-dark ms-1">Soon</small>
                            </span></li>
                            <li><span class="dropdown-item disabled text-muted">
                                <i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน
                                <small class="badge bg-warning text-dark ms-1">Soon</small>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?php echo $GLOBALS['baseUrl']; ?>/?r=auth.logout" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>

            <?php else: ?>
                <!-- Login prompt for non-authenticated users -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $GLOBALS['baseUrl']; ?>/?r=auth.login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            เข้าสู่ระบบ
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php
// Add breadcrumb for better navigation (optional)
if ($isLoggedIn && isset($breadcrumbs) && !empty($breadcrumbs)):
?>
<nav aria-label="breadcrumb" class="bg-light border-bottom">
    <div class="container-fluid px-3 px-md-4">
        <ol class="breadcrumb mb-0 py-2">
            <li class="breadcrumb-item">
                <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=home" class="text-decoration-none">
                    <i class="bi bi-house-door"></i>
                </a>
            </li>
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                <?php if ($index === array_key_last($breadcrumbs)): ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($breadcrumb['title']); ?>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($breadcrumb['title']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
<?php endif; ?>