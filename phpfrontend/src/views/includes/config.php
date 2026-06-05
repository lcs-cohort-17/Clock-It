<?php
// Database configuration (for future database integration)
define('DB_HOST', 'localhost');
define('DB_NAME', 'attendance_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('APP_NAME', 'Attendance Management System');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/New_York');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
}

// Pagination settings
define('ITEMS_PER_PAGE', 10);

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// Cache settings
define('CACHE_ENABLED', false);
define('CACHE_DIR', __DIR__ . '/../cache/');

// Create cache directory if it doesn't exist
if (CACHE_ENABLED && !file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0777, true);
}

// Helper function to get config value
function getConfig($key, $default = null) {
    $config = [
        'db_host' => DB_HOST,
        'db_name' => DB_NAME,
        'db_user' => DB_USER,
        'db_pass' => DB_PASS,
        'app_name' => APP_NAME,
        'app_version' => APP_VERSION,
        'timezone' => TIMEZONE,
        'items_per_page' => ITEMS_PER_PAGE
    ];
    
    return isset($config[$key]) ? $config[$key] : $default;
}
?>
