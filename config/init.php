<?php
/**
 * CellVerse Initialization
 * Security headers, session management, database connection
 */

require_once __DIR__ . '/database.php';

// Start session with hardened cookie parameters
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// On Railway (and other PaaS that proxy to /), the app lives at root.
// Detect via RAILWAY_* env vars and force BASE_URL to root.
if (getenv('RAILWAY_ENVIRONMENT') || getenv('RAILWAY_PROJECT_ID') || getenv('RAILWAY_SERVICE_ID')) {
    define('BASE_URL', '');
} else {
    define('BASE_URL', '/' . basename(BASE_PATH));
}
