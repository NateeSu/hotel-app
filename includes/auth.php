<?php
/**
 * Hotel Management System - Authentication
 *
 * This file provides authentication functions for user login, logout,
 * and permission checking throughout the application.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Check if user is currently logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user data
 */
function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    // Check if user data is cached in session
    if (isset($_SESSION['user_data']) && is_array($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }

    // Fetch user data from database
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        $stmt = $pdo->prepare("
            SELECT id, username, full_name, role, email, phone, is_active, created_at
            FROM users
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            // Cache user data in session
            $_SESSION['user_data'] = $user;
            return $user;
        } else {
            // User not found or inactive, clear session
            logout();
            return null;
        }

    } catch (Exception $e) {
        error_log("Failed to get current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Require user to be logged in, redirect to login if not
 */
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        // Store current URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        // Add flash message (if function exists)
        if (function_exists('flash_info')) {
            flash_info('กรุณาเข้าสู่ระบบเพื่อใช้งาน');
        } else {
            $_SESSION['flash_message'] = [
                'message' => 'กรุณาเข้าสู่ระบบเพื่อใช้งาน',
                'type' => 'info'
            ];
        }

        // Redirect to login
        header('Location: ' . $GLOBALS['baseUrl'] . '/?r=auth.login');
        exit;
    }

    // Check role permission if specified
    if ($role !== null) {
        $user = currentUser();
        if (!$user || !has_permission($user['role'], $role)) {
            // Set flash message if function exists
            if (function_exists('flash_error')) {
                flash_error('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            } else {
                $_SESSION['flash_message'] = [
                    'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้',
                    'type' => 'error'
                ];
            }
            header('Location: ' . $GLOBALS['baseUrl'] . '/?r=home');
            exit;
        }
    }
}

/**
 * Attempt to log in a user
 */
function login($username, $password) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        // Get user by username
        $stmt = $pdo->prepare("
            SELECT id, username, password_hash, full_name, role, email, phone, is_active
            FROM users
            WHERE username = ? AND is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
            ];
        }

        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'email' => $user['email'],
            'phone' => $user['phone']
        ];

        // Log the login (skip if function doesn't exist)
        try {
            if (function_exists('log_activity')) {
                log_activity($user['id'], 'login', 'users', $user['id']);
            }
        } catch (Exception $e) {
            // Log activity failed, but don't break login process
        }

        // Update last login time
        $stmt = $pdo->prepare("
            UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?
        ");
        $stmt->execute([$user['id']]);

        return [
            'success' => true,
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'user' => $_SESSION['user_data']
        ];

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง'
        ];
    }
}

/**
 * Log out the current user
 */
function logout() {
    $userId = $_SESSION['user_id'] ?? null;

    if ($userId) {
        // Log the logout (skip if function doesn't exist)
        try {
            if (function_exists('log_activity')) {
                log_activity($userId, 'logout', 'users', $userId);
            }
        } catch (Exception $e) {
            // Log activity failed, but don't break logout process
        }
    }

    // Clear all session data
    $_SESSION = [];

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
    session_start();

    return true;
}

/**
 * Check if current user has specific permission
 */
function hasPermission($requiredRoles) {
    $user = currentUser();
    if (!$user) {
        return false;
    }

    return has_permission($user['role'], $requiredRoles);
}

/**
 * Get user display name
 */
function getUserDisplayName($user = null) {
    $user = $user ?: currentUser();
    if (!$user) {
        return 'ผู้ใช้ไม่ระบุ';
    }

    return $user['full_name'] ?: $user['username'];
}

/**
 * Get role display name in Thai
 */
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'ผู้ดูแลระบบ',
        'reception' => 'พนักงานต้อนรับ',
        'housekeeping' => 'พนักงานแม่บ้าน'
    ];

    return $roles[$role] ?? $role;
}

/**
 * Check if user can perform specific action
 */
function canPerformAction($action, $resource = null, $user = null) {
    $user = $user ?: currentUser();
    if (!$user) {
        return false;
    }

    $role = $user['role'];

    // Define permissions matrix
    $permissions = [
        'admin' => [
            'users' => ['view', 'create', 'edit', 'delete'],
            'rooms' => ['view', 'create', 'edit', 'delete'],
            'bookings' => ['view', 'create', 'edit', 'delete', 'cancel'],
            'customers' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view', 'export'],
            'settings' => ['view', 'edit'],
            'housekeeping' => ['view', 'assign', 'complete'],
            'checkin' => ['view', 'process'],
            'checkout' => ['view', 'process']
        ],
        'reception' => [
            'rooms' => ['view'],
            'bookings' => ['view', 'create', 'edit', 'cancel'],
            'customers' => ['view', 'create', 'edit'],
            'reports' => ['view', 'export'],
            'checkin' => ['view', 'process'],
            'checkout' => ['view', 'process']
        ],
        'housekeeping' => [
            'rooms' => ['view'],
            'housekeeping' => ['view', 'complete']
        ]
    ];

    if (!isset($permissions[$role])) {
        return false;
    }

    if (!$resource) {
        return true; // If no specific resource, just check if user is logged in
    }

    if (!isset($permissions[$role][$resource])) {
        return false;
    }

    return in_array($action, $permissions[$role][$resource]);
}

/**
 * Middleware to check authentication for AJAX requests
 */
function requireAjaxAuth($role = null) {
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'กรุณาเข้าสู่ระบบเพื่อใช้งาน',
            'redirect' => $GLOBALS['baseUrl'] . '/?r=auth.login'
        ]);
        exit;
    }

    if ($role !== null && !hasPermission($role)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'คุณไม่มีสิทธิ์ในการดำเนินการนี้'
        ]);
        exit;
    }
}

/**
 * Generate secure random password
 */
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $password;
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        $stmt = $pdo->prepare("
            SELECT id, username, full_name, role, email, phone, is_active, created_at
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$userId]);

        return $stmt->fetch();

    } catch (Exception $e) {
        error_log("Failed to get user by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Get user by username
 */
function getUserByUsername($username) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        $stmt = $pdo->prepare("
            SELECT id, username, full_name, role, email, phone, is_active, created_at
            FROM users
            WHERE username = ?
        ");
        $stmt->execute([$username]);

        return $stmt->fetch();

    } catch (Exception $e) {
        error_log("Failed to get user by username: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $data) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        $allowedFields = ['full_name', 'email', 'phone'];
        $updateFields = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return [
                'success' => false,
                'message' => 'ไม่มีข้อมูลที่ต้องอัพเดต'
            ];
        }

        $values[] = $userId;

        $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($values);

        if ($result) {
            // Clear cached user data
            unset($_SESSION['user_data']);

            // Log the update
            if (function_exists('log_activity')) {
                log_activity($userId, 'update_profile', 'users', $userId, null, $data);
            }

            return [
                'success' => true,
                'message' => 'อัพเดตข้อมูลเรียบร้อยแล้ว'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'ไม่สามารถอัพเดตข้อมูลได้'
            ];
        }

    } catch (Exception $e) {
        error_log("Failed to update user profile: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในระบบ'
        ];
    }
}

/**
 * Change user password
 */
function changeUserPassword($userId, $currentPassword, $newPassword) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = getDatabase();

        // Get current password hash
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'ไม่พบข้อมูลผู้ใช้'
            ];
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'
            ];
        }

        // Hash new password
        $newPasswordHash = hashPassword($newPassword);

        // Update password
        $stmt = $pdo->prepare("
            UPDATE users
            SET password_hash = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $result = $stmt->execute([$newPasswordHash, $userId]);

        if ($result) {
            // Log the password change
            if (function_exists('log_activity')) {
                log_activity($userId, 'change_password', 'users', $userId);
            }

            return [
                'success' => true,
                'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'ไม่สามารถเปลี่ยนรหัสผ่านได้'
            ];
        }

    } catch (Exception $e) {
        error_log("Failed to change password: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในระบบ'
        ];
    }
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    $timeout = 3600; // 1 hour in seconds

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logout();

        // Set flash message if function exists
        if (function_exists('flash_warning')) {
            flash_warning('เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่');
        } else {
            $_SESSION['flash_message'] = [
                'message' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
                'type' => 'warning'
            ];
        }

        // Only redirect if not already on login page to prevent loop
        $currentRoute = $_GET['r'] ?? 'home';
        if ($currentRoute !== 'auth.login') {
            header('Location: ' . $GLOBALS['baseUrl'] . '/?r=auth.login');
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
}