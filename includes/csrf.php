<?php
/**
 * Hotel Management System - CSRF Protection
 *
 * This file provides CSRF (Cross-Site Request Forgery) protection functions
 * to secure forms and AJAX requests throughout the application.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Generate a new CSRF token
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Generate a random token
    $token = bin2hex(random_bytes(32));

    // Store in session
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();

    return $token;
}

/**
 * Get current CSRF token, generate new one if doesn't exist
 */
function get_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if token exists and is not expired (1 hour)
    if (isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_token_time'])) {
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge < 3600) { // 1 hour
            return $_SESSION['csrf_token'];
        }
    }

    // Generate new token
    return generate_csrf_token();
}

/**
 * Generate CSRF field HTML for forms
 */
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Generate CSRF meta tag for AJAX requests
 */
function csrf_meta() {
    $token = get_csrf_token();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token from request
 */
function verify_csrf_token($token = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get token from parameter or request
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    // Check if session token exists
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Check token age
    if (isset($_SESSION['csrf_token_time'])) {
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge >= 3600) { // 1 hour
            return false;
        }
    }

    // Verify token using hash_equals to prevent timing attacks
    return $token && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token, exit with error if invalid
 */
function require_csrf_token($token = null) {
    if (!verify_csrf_token($token)) {
        // Log CSRF attempt
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $referer = $_SERVER['HTTP_REFERER'] ?? 'Unknown';

        error_log("CSRF token verification failed. IP: {$ip}, User-Agent: {$userAgent}, Referer: {$referer}");

        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'CSRF token verification failed',
                'error_code' => 'CSRF_ERROR',
                'reload' => true
            ]);
        } else {
            // Regular form submission
            flash_error('การยืนยันความปลอดภัยล้มเหลว กรุณาลองใหม่อีกครั้ง');

            // Redirect back to referer or home
            $referer = $_SERVER['HTTP_REFERER'] ?? ($GLOBALS['baseUrl'] . '/?r=home');
            header('Location: ' . $referer);
        }

        exit;
    }
}

/**
 * CSRF protection middleware for forms
 */
function csrf_protect() {
    // Only check POST, PUT, PATCH, DELETE requests
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        require_csrf_token();
    }
}

/**
 * Generate CSRF token for JavaScript
 */
function csrf_js_token() {
    $token = get_csrf_token();
    return "window.csrfToken = '{$token}';";
}

/**
 * Regenerate CSRF token (useful after successful form submissions)
 */
function regenerate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_time']);

    return generate_csrf_token();
}

/**
 * Check if request has valid CSRF token (non-blocking)
 */
function has_valid_csrf_token($token = null) {
    try {
        return verify_csrf_token($token);
    } catch (Exception $e) {
        error_log("CSRF token verification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get CSRF token for AJAX requests
 */
function get_csrf_header() {
    return [
        'X-CSRF-Token' => get_csrf_token()
    ];
}

/**
 * Validate CSRF token for API endpoints
 */
function validate_api_csrf() {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;

    if (!$token) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'CSRF token is required',
            'error_code' => 'CSRF_MISSING'
        ]);
        exit;
    }

    if (!verify_csrf_token($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token',
            'error_code' => 'CSRF_INVALID'
        ]);
        exit;
    }

    return true;
}

/**
 * CSRF protection for double-submit cookie pattern
 */
function set_csrf_cookie() {
    $token = get_csrf_token();

    // Set secure cookie with CSRF token
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $sameSite = 'Strict';

    setcookie('csrf_token', $token, [
        'expires' => time() + 3600, // 1 hour
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => false, // Needs to be accessible by JavaScript
        'samesite' => $sameSite
    ]);

    return $token;
}

/**
 * Verify CSRF token from cookie
 */
function verify_csrf_cookie() {
    $cookieToken = $_COOKIE['csrf_token'] ?? null;
    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    $formToken = $_POST['csrf_token'] ?? null;

    $requestToken = $headerToken ?: $formToken;

    if (!$cookieToken || !$requestToken) {
        return false;
    }

    // Both tokens must match and be valid
    return hash_equals($cookieToken, $requestToken) && verify_csrf_token($requestToken);
}

/**
 * Initialize CSRF protection for the application
 */
function init_csrf_protection() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Generate token if not exists
    get_csrf_token();

    // Set cookie for double-submit pattern
    set_csrf_cookie();
}

/**
 * Clean up expired CSRF tokens
 */
function cleanup_csrf_tokens() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Remove expired session token
    if (isset($_SESSION['csrf_token_time'])) {
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge >= 3600) { // 1 hour
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
        }
    }
}

/**
 * CSRF-protected form wrapper
 */
function csrf_form($action = '', $method = 'POST', $attributes = []) {
    $attributeString = '';
    foreach ($attributes as $key => $value) {
        $attributeString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }

    $html = '<form action="' . htmlspecialchars($action) . '" method="' . htmlspecialchars($method) . '"' . $attributeString . '>';
    $html .= csrf_field();

    return $html;
}

/**
 * Get CSRF configuration for JavaScript
 */
function get_csrf_config() {
    return [
        'token' => get_csrf_token(),
        'header_name' => 'X-CSRF-Token',
        'field_name' => 'csrf_token',
        'expires_in' => 3600 - (time() - ($_SESSION['csrf_token_time'] ?? time()))
    ];
}

/**
 * Debug function to display CSRF information (development only)
 */
function debug_csrf_info() {
    if (env('APP_ENV', 'development') !== 'development') {
        return;
    }

    echo '<div class="alert alert-info mt-3">';
    echo '<h6>CSRF Debug Information:</h6>';
    echo '<ul class="mb-0">';
    echo '<li>Current token: ' . htmlspecialchars(get_csrf_token()) . '</li>';
    echo '<li>Token age: ' . (time() - ($_SESSION['csrf_token_time'] ?? time())) . ' seconds</li>';
    echo '<li>Session ID: ' . session_id() . '</li>';
    echo '</ul>';
    echo '</div>';
}

// Auto-initialize CSRF protection
init_csrf_protection();