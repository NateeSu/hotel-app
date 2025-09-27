<?php
/**
 * Hotel Management System - Flash Messages
 *
 * This file displays flash messages stored in the session.
 * Supports multiple message types: success, error, warning, info
 * Messages are automatically cleared after display.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

// Get flash messages from session
$flashMessages = $_SESSION['flash'] ?? [];

// Clear flash messages after retrieving them
unset($_SESSION['flash']);

// Display flash messages if any exist
if (!empty($flashMessages)):
?>
<div class="flash-messages mb-3">
    <?php foreach ($flashMessages as $flash): ?>
        <?php
        $type = $flash['type'] ?? 'info';
        $message = $flash['message'] ?? '';
        $dismissible = $flash['dismissible'] ?? true;

        // Map flash types to Bootstrap alert classes
        $alertClasses = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            'primary' => 'alert-primary',
            'secondary' => 'alert-secondary'
        ];

        $alertClass = $alertClasses[$type] ?? 'alert-info';

        // Map flash types to Bootstrap icons
        $alertIcons = [
            'success' => 'bi-check-circle-fill',
            'error' => 'bi-exclamation-triangle-fill',
            'danger' => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'info' => 'bi-info-circle-fill',
            'primary' => 'bi-info-circle-fill',
            'secondary' => 'bi-info-circle-fill'
        ];

        $alertIcon = $alertIcons[$type] ?? 'bi-info-circle-fill';
        ?>

        <div class="alert <?php echo $alertClass; ?> <?php echo $dismissible ? 'alert-dismissible' : ''; ?> fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi <?php echo $alertIcon; ?> me-2 mt-1 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <?php echo nl2br(htmlspecialchars($message)); ?>
                </div>
                <?php if ($dismissible): ?>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="ปิด"></button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
// Auto-dismiss flash messages after specified time (optional)
$autoDismissDelay = 5000; // 5 seconds
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss success and info messages after delay
    const flashMessages = document.querySelectorAll('.flash-messages .alert-success, .flash-messages .alert-info');

    flashMessages.forEach(function(alert) {
        if (alert.classList.contains('alert-dismissible')) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, <?php echo $autoDismissDelay; ?>);
        }
    });

    // Add slide-down animation for flash messages
    const flashContainer = document.querySelector('.flash-messages');
    if (flashContainer) {
        flashContainer.style.animation = 'slideDown 0.3s ease-out';
    }
});
</script>

<style>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.flash-messages .alert {
    border: none;
    border-left: 4px solid;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.flash-messages .alert-success {
    border-left-color: #198754;
}

.flash-messages .alert-danger {
    border-left-color: #dc3545;
}

.flash-messages .alert-warning {
    border-left-color: #ffc107;
}

.flash-messages .alert-info {
    border-left-color: #0dcaf0;
}

.flash-messages .alert-primary {
    border-left-color: #0d6efd;
}

.flash-messages .alert .bi {
    font-size: 1.1rem;
}

/* Mobile responsiveness */
@media (max-width: 576px) {
    .flash-messages {
        margin-left: -15px;
        margin-right: -15px;
    }

    .flash-messages .alert {
        border-radius: 0;
        border-left-width: 6px;
    }
}
</style>

<?php endif; ?>

<?php
/**
 * Helper function to add flash message from PHP code
 * This can be used in other parts of the application
 */
if (!function_exists('flash')) {
    function flash($type, $message, $dismissible = true) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message,
            'dismissible' => $dismissible
        ];
    }
}

/**
 * Helper function to add success flash message
 */
if (!function_exists('flash_success')) {
    function flash_success($message, $dismissible = true) {
        flash('success', $message, $dismissible);
    }
}

/**
 * Helper function to add error flash message
 */
if (!function_exists('flash_error')) {
    function flash_error($message, $dismissible = true) {
        flash('error', $message, $dismissible);
    }
}

/**
 * Helper function to add warning flash message
 */
if (!function_exists('flash_warning')) {
    function flash_warning($message, $dismissible = true) {
        flash('warning', $message, $dismissible);
    }
}

/**
 * Helper function to add info flash message
 */
if (!function_exists('flash_info')) {
    function flash_info($message, $dismissible = true) {
        flash('info', $message, $dismissible);
    }
}
?>