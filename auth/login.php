<?php
/**
 * Hotel Management System - Login Page
 *
 * This page handles user authentication with CSRF protection and
 * responsive Bootstrap 5 design.
 */

// Start output buffering to prevent header issues
ob_start();

// Only initialize if not already loaded by index.php
if (!defined('APP_INIT')) {
    // Start session and initialize application
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Asia/Bangkok');

    // Define base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']); // /hotel-app
    $baseUrl = $protocol . '://' . $host . $scriptPath;
    $GLOBALS['baseUrl'] = $baseUrl;

    // Load required files (only if not already loaded)
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/csrf.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../templates/partials/flash.php';
} else {
    // Already initialized by index.php, just use existing baseUrl
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

// Fetch hotel settings
$hotelSettings = [
    'hotel_name' => 'Hotel Management System',
    'hotel_phone' => ''
];

try {
    $pdo = getDatabase();

    // Create basic table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS hotel_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            INDEX idx_setting_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $pdo->query("SELECT setting_key, setting_value FROM hotel_settings WHERE setting_key IN ('hotel_name', 'hotel_phone')");
    $settingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($settingsData as $setting) {
        $hotelSettings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (Exception $e) {
    // Use defaults if error
}

// Set page variables
$pageTitle = 'เข้าสู่ระบบ - ' . $hotelSettings['hotel_name'];
$pageDescription = 'เข้าสู่ระบบจัดการโรงแรม';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== LOGIN ATTEMPT ===");
    error_log("POST data: " . print_r($_POST, true));

    // Verify CSRF token
    try {
        require_csrf_token();
        error_log("CSRF token verified");
    } catch (Exception $e) {
        error_log("CSRF token verification failed: " . $e->getMessage());
        $errors[] = 'CSRF token validation failed. Please refresh and try again.';
    }

    $username = sanitize_input($_POST['username'] ?? '', 'string');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    error_log("Username: " . $username);
    error_log("Password length: " . strlen($password));

    // Validation
    $errors = $errors ?? [];

    if (empty($username)) {
        $errors[] = 'กรุณาใส่ชื่อผู้ใช้';
    }

    if (empty($password)) {
        $errors[] = 'กรุณาใส่รหัสผ่าน';
    }

    if (empty($errors)) {
        error_log("Attempting login for user: " . $username);
        // Attempt login
        $loginResult = login($username, $password);
        error_log("Login result: " . print_r($loginResult, true));

        if ($loginResult['success']) {
            // Set remember me cookie if requested
            if ($remember) {
                $cookieValue = hash('sha256', $username . time());
                setcookie('remember_user', $cookieValue, time() + (86400 * 30), '/'); // 30 days
            }

            // Flash success message
            flash_success('เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ' . getUserDisplayName());

            // Redirect to intended page or default
            $redirectUrl = $_SESSION['redirect_after_login'] ?? $baseUrl . '/?r=rooms.board';
            unset($_SESSION['redirect_after_login']);

            // Clean output buffer and redirect
            ob_end_clean();

            // Use JavaScript redirect as fallback if headers already sent
            if (!headers_sent()) {
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                // JavaScript fallback
                echo '<script>window.location.href = "' . htmlspecialchars($redirectUrl) . '";</script>';
                echo '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl) . '">';
                exit;
            }
        } else {
            flash_error($loginResult['message']);
        }
    } else {
        foreach ($errors as $error) {
            flash_error($error);
        }
    }
}

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    ob_end_clean();
    header('Location: ' . $baseUrl . '/?r=rooms.board');
    exit;
}

// Process flash messages before HTML output
$flashMessages = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

// If we reach here, flush the buffer for normal page display
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="th" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="robots" content="noindex, nofollow">
    <?php echo csrf_meta(); ?>

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link href="<?php echo $baseUrl; ?>/assets/css/app.css?v=<?php echo time(); ?>" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #4c84ff 100%);
            min-height: 100vh;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo i {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .login-logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.5rem;
        }

        .login-logo p {
            color: #6c757d;
            margin-bottom: 0;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-floating > .form-control {
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            padding: 0.75rem;
            transition: all 0.15s ease-in-out;
        }

        .form-floating > .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .form-floating > label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .form-check {
            margin-bottom: 1.5rem;
        }

        .demo-accounts {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.85rem;
        }

        .demo-accounts h6 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .demo-account {
            background: white;
            border-radius: 0.25rem;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-left: 3px solid #0d6efd;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .demo-account:hover {
            background: #e3f2fd;
        }

        .demo-account:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 1.5rem;
                margin: 10px;
                border-radius: 0.75rem;
            }

            .login-logo i {
                font-size: 2.5rem;
            }

            .login-logo h1 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo and Title -->
            <div class="login-logo">
                <i class="bi bi-building"></i>
                <h1><?php echo htmlspecialchars($hotelSettings['hotel_name']); ?></h1>
                <p class="small text-muted">ระบบจัดการโรงแรม</p>
                <?php if (!empty($hotelSettings['hotel_phone'])): ?>
                <p class="small text-primary mb-0">
                    <i class="bi bi-telephone me-1"></i>
                    <?php echo htmlspecialchars($hotelSettings['hotel_phone']); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Flash Messages -->
            <?php
            foreach ($flashMessages as $flash):
                $alertClass = $flash['type'] === 'error' ? 'alert-danger' : 'alert-' . $flash['type'];
            ?>
                <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>

            <!-- Login Form -->
            <form method="POST" action="<?php echo $baseUrl; ?>/?r=auth.login" class="needs-validation" novalidate>
                <?php echo csrf_field(); ?>

                <!-- Username -->
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="ชื่อผู้ใช้" required autocomplete="username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <label for="username">
                        <i class="bi bi-person me-1"></i>ชื่อผู้ใช้
                    </label>
                    <div class="invalid-feedback">
                        กรุณาใส่ชื่อผู้ใช้
                    </div>
                </div>

                <!-- Password -->
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="รหัสผ่าน" required autocomplete="current-password">
                    <label for="password">
                        <i class="bi bi-lock me-1"></i>รหัสผ่าน
                    </label>
                    <div class="invalid-feedback">
                        กรุณาใส่รหัสผ่าน
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        จดจำการเข้าสู่ระบบ
                    </label>
                </div>

                <!-- Submit Button -->
                <button class="btn btn-primary btn-login" type="submit">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    เข้าสู่ระบบ
                </button>
            </form>

            <!-- Demo Accounts -->
            <?php if (env('APP_ENV', 'development') === 'development'): ?>
            <div class="demo-accounts">
                <h6 class="text-muted mb-2">
                    <i class="bi bi-info-circle me-1"></i>
                    บัญชีทดสอบ
                </h6>

                <div class="demo-account" onclick="fillLogin('admin', 'admin123')">
                    <div class="fw-bold text-primary">admin</div>
                    <div class="text-muted">ผู้ดูแลระบบ - admin123</div>
                </div>

                <div class="demo-account" onclick="fillLogin('reception', 'rec123')">
                    <div class="fw-bold text-success">reception</div>
                    <div class="text-muted">พนักงานต้อนรับ - rec123</div>
                </div>

                <div class="demo-account" onclick="fillLogin('housekeeping', 'hk123')">
                    <div class="fw-bold text-info">housekeeping</div>
                    <div class="text-muted">พนักงานแม่บ้าน - hk123</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    © <?php echo date('Y'); ?> <?php echo htmlspecialchars($hotelSettings['hotel_name']); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Bootstrap form validation
        (function() {
            'use strict';

            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Fill login form with demo credentials
        function fillLogin(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            document.getElementById('username').focus();
        }

        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            const usernameField = document.getElementById('username');
            if (usernameField && !usernameField.value) {
                usernameField.focus();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // Ctrl + Enter to submit form
            if (event.ctrlKey && event.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });

        // Loading state for submit button
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = document.querySelector('.btn-login');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังเข้าสู่ระบบ...';
            submitBtn.disabled = true;
        });

        // Show/hide password toggle (optional enhancement)
        const passwordInput = document.getElementById('password');
        const passwordLabel = passwordInput.nextElementSibling;

        // Add show/hide password button
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2';
        toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
        toggleButton.style.border = 'none';
        toggleButton.style.background = 'none';

        passwordInput.parentElement.style.position = 'relative';
        passwordInput.parentElement.appendChild(toggleButton);

        toggleButton.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = type === 'password' ? 'bi-eye' : 'bi-eye-slash';
            this.innerHTML = `<i class="bi ${icon}"></i>`;
        });
    </script>
</body>
</html>