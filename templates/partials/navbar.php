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

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container-fluid">
        <!-- Brand/Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $GLOBALS['baseUrl'] ?? '/hotel-app'; ?>/?r=home">
            <i class="bi bi-building me-2 fs-4"></i>
            <span class="fw-bold d-none d-sm-inline"><?php echo htmlspecialchars($hotelName); ?></span>
            <span class="fw-bold d-sm-none"><?php echo htmlspecialchars(mb_substr($hotelName, 0, 10)); ?></span>
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
                           href="<?php echo $GLOBALS['baseUrl']; ?>/?r=rooms.board">
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
                           href="#" title="ฟีเจอร์นี้อยู่ระหว่างการพัฒนา">
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
                           href="<?php echo $GLOBALS['baseUrl']; ?>/?r=housekeeping.jobs">
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