<?php
/**
 * Hotel Management System - Router
 *
 * This file provides a simple routing system for the hotel management application.
 * Routes are in the format ?r=module.action and map to corresponding PHP files.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Route configuration
 * Maps route patterns to file paths and required permissions
 */
$routeConfig = [
    // Authentication routes
    'auth.login' => ['file' => 'auth/login.php', 'public' => true],
    'auth.logout' => ['file' => 'auth/logout.php', 'public' => false],

    // Home/Dashboard routes
    'home' => ['file' => 'dashboard.php', 'public' => false],
    'dashboard' => ['file' => 'dashboard.php', 'public' => false],
    'dashboard.analytics' => ['file' => 'dashboard_enhanced.php', 'roles' => ['reception', 'admin']],

    // Room management routes
    'rooms.board' => ['file' => 'rooms/board.php', 'roles' => ['reception', 'admin']],
    'rooms.list' => ['file' => 'rooms/list.php', 'roles' => ['reception', 'admin']],
    'rooms.create' => ['file' => 'rooms/create.php', 'roles' => ['admin']],
    'rooms.edit' => ['file' => 'rooms/edit.php', 'roles' => ['admin']],
    'rooms.delete' => ['file' => 'rooms/delete.php', 'roles' => ['admin']],

    // Room action routes (placeholders)
    'rooms.checkin' => ['file' => 'rooms/checkin.php', 'roles' => ['reception', 'admin']],
    'rooms.checkout' => ['file' => 'rooms/checkout.php', 'roles' => ['reception', 'admin']],
    'rooms.checkoutSuccess' => ['file' => 'rooms/checkoutSuccess.php', 'roles' => ['reception', 'admin']],
    'rooms.move' => ['file' => 'rooms/move.php', 'roles' => ['reception', 'admin']],
    'rooms.cleanDone' => ['file' => 'rooms/cleanDone.php', 'roles' => ['reception', 'admin']],
    'rooms.transfer' => ['file' => 'rooms/transfer.php', 'roles' => ['reception', 'admin']],

    // Booking management routes
    'bookings.list' => ['file' => 'bookings/list.php', 'roles' => ['reception', 'admin']],
    'bookings.create' => ['file' => 'bookings/create.php', 'roles' => ['reception', 'admin']],
    'bookings.edit' => ['file' => 'bookings/edit.php', 'roles' => ['reception', 'admin']],
    'bookings.view' => ['file' => 'bookings/view.php', 'roles' => ['reception', 'admin']],
    'bookings.cancel' => ['file' => 'bookings/cancel.php', 'roles' => ['reception', 'admin']],

    // Check-in/Check-out routes
    'checkin.list' => ['file' => 'checkin/list.php', 'roles' => ['reception', 'admin']],
    'checkin.process' => ['file' => 'checkin/process.php', 'roles' => ['reception', 'admin']],
    'checkout.list' => ['file' => 'checkout/list.php', 'roles' => ['reception', 'admin']],
    'checkout.process' => ['file' => 'checkout/process.php', 'roles' => ['reception', 'admin']],

    // Customer management routes
    'customers.list' => ['file' => 'customers/list.php', 'roles' => ['reception', 'admin']],
    'customers.create' => ['file' => 'customers/create.php', 'roles' => ['reception', 'admin']],
    'customers.edit' => ['file' => 'customers/edit.php', 'roles' => ['reception', 'admin']],
    'customers.view' => ['file' => 'customers/view.php', 'roles' => ['reception', 'admin']],

    // Housekeeping routes
    'housekeeping.jobs' => ['file' => 'housekeeping/jobs.php', 'roles' => ['housekeeping', 'admin']],
    'housekeeping.job' => ['file' => 'housekeeping/job.php', 'roles' => ['housekeeping', 'admin', 'reception']],
    'housekeeping.reports' => ['file' => 'housekeeping/reports.php', 'roles' => ['housekeeping', 'admin', 'reception']],
    'housekeeping.assign' => ['file' => 'housekeeping/assign.php', 'roles' => ['admin']],
    'housekeeping.complete' => ['file' => 'housekeeping/complete.php', 'roles' => ['housekeeping', 'admin']],

    // Receipt routes
    'receipts.view' => ['file' => 'receipts/view.php', 'roles' => ['reception', 'admin']],
    'receipts.history' => ['file' => 'receipts/history.php', 'roles' => ['reception', 'admin']],

    // Report routes
    'reports.sales' => ['file' => 'reports/sales.php', 'roles' => ['reception', 'admin']],
    'reports.occupancy' => ['file' => 'reports/occupancy.php', 'roles' => ['reception', 'admin']],
    'reports.bookings' => ['file' => 'reports/bookings.php', 'roles' => ['reception', 'admin']],

    // System routes
    'system.rates' => ['file' => 'system/rates_simple.php', 'roles' => ['admin']],
    'system.settings' => ['file' => 'system/settings.php', 'roles' => ['admin']],

    // Admin routes
    'admin.users' => ['file' => 'admin/users.php', 'roles' => ['admin']],
    'admin.rooms' => ['file' => 'admin/rooms.php', 'roles' => ['admin']],
    'admin.rates' => ['file' => 'admin/rates.php', 'roles' => ['admin']],
    'admin.settings' => ['file' => 'admin/settings.php', 'roles' => ['admin']],

    // Profile routes
    'profile.edit' => ['file' => 'profile/edit.php', 'public' => false],
    'profile.password' => ['file' => 'profile/password.php', 'public' => false],
];

/**
 * Get the current route from the request
 */
function getCurrentRoute() {
    $route = $_GET['r'] ?? null;

    // Debug logging
    error_log("getCurrentRoute() called");
    error_log("  \$_GET['r']: " . var_export($_GET['r'] ?? 'NOT SET', true));
    error_log("  REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));
    error_log("  QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'NOT SET'));

    // If no route specified, determine default based on login status
    if (!$route || $route === '') {
        // Check if user is logged in
        $isLoggedIn = function_exists('isLoggedIn') && isLoggedIn();
        error_log("  No route specified, isLoggedIn: " . ($isLoggedIn ? 'YES' : 'NO'));

        if ($isLoggedIn) {
            $route = 'home'; // Logged in users go to dashboard
        } else {
            $route = 'auth.login'; // Not logged in users go to login
        }
    }

    error_log("  Final route: " . $route);
    return $route;
}

/**
 * Get route configuration for a given route
 */
function getRouteConfig($route) {
    global $routeConfig;
    return $routeConfig[$route] ?? null;
}

/**
 * Check if a route exists
 */
function routeExists($route) {
    return getRouteConfig($route) !== null;
}

/**
 * Check if user has permission to access a route
 */
function hasRoutePermission($route, $userRole = null) {
    $config = getRouteConfig($route);

    if (!$config) {
        return false;
    }

    // Public routes don't require authentication
    if (isset($config['public']) && $config['public']) {
        return true;
    }

    // Must be logged in for non-public routes
    if (!isLoggedIn()) {
        return false;
    }

    // If no specific roles required, any logged-in user can access
    if (!isset($config['roles'])) {
        return true;
    }

    // Check if user has required role
    $userRole = $userRole ?? (currentUser()['role'] ?? null);

    if (!$userRole) {
        return false;
    }

    // Admin can access everything
    if ($userRole === 'admin') {
        return true;
    }

    // Check if user role is in allowed roles
    return in_array($userRole, $config['roles']);
}

/**
 * Get the file path for a route
 */
function getRouteFile($route) {
    $config = getRouteConfig($route);
    return $config ? $config['file'] : null;
}

/**
 * Redirect to a route
 */
function redirectToRoute($route, $params = []) {
    $url = $GLOBALS['baseUrl'] . '/?r=' . urlencode($route);

    if (!empty($params)) {
        $queryParams = [];
        foreach ($params as $key => $value) {
            $queryParams[] = urlencode($key) . '=' . urlencode($value);
        }
        $url .= '&' . implode('&', $queryParams);
    }

    header('Location: ' . $url);
    exit;
}

/**
 * Generate URL for a route
 */
function routeUrl($route, $params = []) {
    $url = $GLOBALS['baseUrl'] . '/?r=' . urlencode($route);

    if (!empty($params)) {
        $queryParams = [];
        foreach ($params as $key => $value) {
            $queryParams[] = urlencode($key) . '=' . urlencode($value);
        }
        $url .= '&' . implode('&', $queryParams);
    }

    return $url;
}

/**
 * Handle the current route
 */
function handleRoute() {
    $route = getCurrentRoute();

    // Prevent infinite redirect loop - track redirect depth
    static $redirectCount = 0;
    if ($redirectCount > 3) {
        error_log("Infinite redirect loop detected for route: {$route}");
        die('Error: Infinite redirect loop detected. Please check your configuration.');
    }
    $redirectCount++;

    // Validate baseUrl is set
    if (empty($GLOBALS['baseUrl'])) {
        error_log("baseUrl is not set in GLOBALS");
        die('Error: Application baseUrl is not configured properly.');
    }

    // Check if route exists
    if (!routeExists($route)) {
        http_response_code(404);
        $pageTitle = 'ไม่พบหน้าที่ต้องการ';
        $pageDescription = 'หน้าที่คุณค้นหาไม่พบในระบบ';

        require_once __DIR__ . '/../templates/layout/header.php';
        ?>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="card border-0">
                        <div class="card-body py-5">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                            <h1 class="h3 mt-3 mb-2">ไม่พบหน้าที่ต้องการ</h1>
                            <p class="text-muted mb-4">หน้าที่คุณค้นหาไม่มีอยู่ในระบบ หรืออาจถูกย้ายไปแล้ว</p>
                            <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=home" class="btn btn-primary">
                                <i class="bi bi-house me-1"></i>
                                กลับสู่หน้าหลัก
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        require_once __DIR__ . '/../templates/layout/footer.php';
        return;
    }

    // Check permissions
    if (!hasRoutePermission($route)) {
        if (!isLoggedIn()) {
            // Prevent redirect loop for auth.login route
            if ($route === 'auth.login') {
                error_log("ERROR: No permission for auth.login route - this should not happen!");
                error_log("Route config: " . print_r(getRouteConfig('auth.login'), true));

                // Force show the login page even if permission check fails
                $routeFile = getRouteFile($route);
                if ($routeFile) {
                    $filePath = __DIR__ . '/../' . $routeFile;
                    $filePath = str_replace('\\', '/', $filePath);

                    if (file_exists($filePath)) {
                        error_log("Loading login page directly: " . $filePath);
                        require_once $filePath;
                        return;
                    }
                }

                // If we still can't load login, show error
                http_response_code(500);
                die('Error: Cannot load login page. Please check auth/login.php exists.');
            }

            // Redirect to login for unauthenticated users
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

            // Direct redirect without using redirectToRoute to avoid potential loops
            $loginUrl = $GLOBALS['baseUrl'] . '/?r=auth.login';
            error_log("Redirecting to login: " . $loginUrl);
            header('Location: ' . $loginUrl);
            exit;
        } else {
            // Show access denied for authenticated but unauthorized users
            http_response_code(403);
            $pageTitle = 'ไม่ได้รับอนุญาต';
            $pageDescription = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';

            require_once __DIR__ . '/../templates/layout/header.php';
            ?>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <div class="card border-0">
                            <div class="card-body py-5">
                                <i class="bi bi-shield-exclamation text-danger" style="font-size: 4rem;"></i>
                                <h1 class="h3 mt-3 mb-2">ไม่ได้รับอนุญาต</h1>
                                <p class="text-muted mb-4">คุณไม่มีสิทธิ์เข้าถึงหน้านี้ กรุณาติดต่อผู้ดูแลระบบ</p>
                                <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=home" class="btn btn-primary">
                                    <i class="bi bi-house me-1"></i>
                                    กลับสู่หน้าหลัก
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            require_once __DIR__ . '/../templates/layout/footer.php';
            return;
        }
    }

    // Get the file to include
    $routeFile = getRouteFile($route);
    $filePath = __DIR__ . '/../' . $routeFile;

    // Normalize path for cross-platform compatibility
    $filePath = str_replace('\\', '/', $filePath);

    // Check if file exists
    if (!file_exists($filePath)) {
        error_log("Route file not found: {$filePath}");
        error_log("Current route: {$route}, Route file: {$routeFile}");
        error_log("__DIR__: " . __DIR__);
        http_response_code(500);

        $pageTitle = 'เกิดข้อผิดพลาด';
        $pageDescription = 'ระบบเกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';

        require_once __DIR__ . '/../templates/layout/header.php';
        ?>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="card border-0">
                        <div class="card-body py-5">
                            <i class="bi bi-bug text-danger" style="font-size: 4rem;"></i>
                            <h1 class="h3 mt-3 mb-2">เกิดข้อผิดพลาด</h1>
                            <p class="text-muted mb-4">ระบบเกิดข้อผิดพลาดชั่วคราว กรุณาลองใหม่อีกครั้ง</p>
                            <?php if (!empty($_ENV['APP_DEBUG']) || (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost')): ?>
                                <div class="alert alert-danger text-start mt-3">
                                    <strong>Debug Info:</strong><br>
                                    Route: <?php echo htmlspecialchars($route); ?><br>
                                    Expected file: <?php echo htmlspecialchars($filePath); ?><br>
                                    File exists: <?php echo file_exists($filePath) ? 'Yes' : 'No'; ?>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo $GLOBALS['baseUrl']; ?>/?r=home" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                ลองใหม่
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        require_once __DIR__ . '/../templates/layout/footer.php';
        return;
    }

    // Include the route file
    require_once $filePath;
}

/**
 * Get breadcrumbs for the current route
 */
function getBreadcrumbs($route) {
    $breadcrumbsMap = [
        'rooms.board' => [
            ['title' => 'ห้องพัก', 'url' => routeUrl('rooms.board')]
        ],
        'rooms.list' => [
            ['title' => 'ห้องพัก', 'url' => routeUrl('rooms.board')],
            ['title' => 'รายการห้อง', 'url' => routeUrl('rooms.list')]
        ],
        'bookings.list' => [
            ['title' => 'การจอง', 'url' => routeUrl('bookings.list')]
        ],
        'bookings.create' => [
            ['title' => 'การจอง', 'url' => routeUrl('bookings.list')],
            ['title' => 'จองใหม่', 'url' => routeUrl('bookings.create')]
        ],
        'customers.list' => [
            ['title' => 'ลูกค้า', 'url' => routeUrl('customers.list')]
        ],
        'reports.sales' => [
            ['title' => 'รายงาน', 'url' => '#'],
            ['title' => 'ยอดขาย', 'url' => routeUrl('reports.sales')]
        ],
        'admin.users' => [
            ['title' => 'ระบบ', 'url' => '#'],
            ['title' => 'ผู้ใช้งาน', 'url' => routeUrl('admin.users')]
        ]
    ];

    return $breadcrumbsMap[$route] ?? [];
}

/**
 * Check if current route matches pattern
 */
function isCurrentRoute($pattern) {
    $currentRoute = getCurrentRoute();

    if (strpos($pattern, '*') !== false) {
        $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
        return preg_match($regex, $currentRoute);
    }

    return $currentRoute === $pattern;
}

/**
 * Get all available routes for a user role
 */
function getAvailableRoutes($userRole = null) {
    global $routeConfig;
    $availableRoutes = [];

    foreach ($routeConfig as $route => $config) {
        if (hasRoutePermission($route, $userRole)) {
            $availableRoutes[] = $route;
        }
    }

    return $availableRoutes;
}