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
define('BASE_URL', '/' . basename(BASE_PATH));
