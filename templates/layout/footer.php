<?php
/**
 * Hotel Management System - Layout Footer
 *
 * This file closes the main content area and includes necessary JavaScript files.
 * Contains Bootstrap 5 JS, custom JS, and footer content.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}
?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top py-3 mt-auto">
        <div class="container-fluid px-3 px-md-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted small">
                        © <?php echo date('Y'); ?> Hotel Management System
                        <span class="d-none d-sm-inline">- ระบบจัดการโรงแรม</span>
                    </span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted small">
                        <i class="bi bi-clock me-1"></i>
                        <?php echo format_datetime_thai(now(), 'j M Y H:i'); ?> น.
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Custom JavaScript -->
    <script src="<?php echo $baseUrl; ?>/assets/js/app.js?v=<?php echo time(); ?>"></script>

    <?php
    // Include additional JavaScript if specified
    if (isset($additionalJS) && is_array($additionalJS)):
        foreach ($additionalJS as $jsFile):
    ?>
        <script src="<?php echo $baseUrl; ?><?php echo htmlspecialchars($jsFile); ?>?v=<?php echo time(); ?>"></script>
    <?php
        endforeach;
    endif;
    ?>

    <?php
    // Include inline JavaScript if specified
    if (isset($inlineJS) && !empty($inlineJS)):
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php echo $inlineJS; ?>
        });
    </script>
    <?php endif; ?>

    <!-- Development tools (only in development environment) -->
    <?php if (env('APP_ENV', 'development') === 'development' && env('APP_DEBUG', true)): ?>
    <script>
        // Development console info
        console.log('🏨 Hotel Management System - Development Mode');
        console.log('⏱️ Page generated at: <?php echo now(); ?>');
        <?php if (isLoggedIn()): ?>
        console.log('👤 Current user: <?php echo htmlspecialchars(currentUser()['username'] ?? 'Unknown'); ?>');
        <?php endif; ?>
    </script>
    <?php endif; ?>

</body>
</html><?php
// Clear any output buffering
if (ob_get_level()) {
    ob_end_flush();
}
?>