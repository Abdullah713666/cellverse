<?php
/**
 * CellVerse Database Configuration
 * PDO connection with environment auto-detection
 */

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'cellverse_db';
        $username = 'root';
        $password = '';

        // Auto-detect Railway environment
        if (getenv('MYSQLHOST')) {
            $host = getenv('MYSQLHOST');
            $dbname = getenv('MYSQLDATABASE');
            $username = getenv('MYSQLUSER');
            $password = getenv('MYSQLPASSWORD');
        }

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed. Please run install.php to set up the database.");
        }
    }
    return $pdo;
}

/**
 * Get a site setting from the database
 */
function getSetting($key, $default = '') {
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Render a CSRF-protected hidden input field.
 * Usage: <form><?php csrf_field(); ?>...
 */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

/**
 * Verify a POST request has a valid CSRF token. Sends a 403 JSON or HTML
 * response and exits on failure. Use at the top of any state-changing handler.
 */
function require_csrf_or_die($isAjax = false) {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validate_csrf_token($token)) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
        } else {
            http_response_code(403);
            echo '<!DOCTYPE html><html><head><title>403</title></head><body style="font-family:sans-serif;padding:40px;text-align:center;background:#0a0f1a;color:#e2e8f0;height:100vh;"><h1>403 — Invalid security token</h1><p>Please <a href="javascript:history.back()" style="color:#00d4aa;">go back</a> and try again.</p></body></html>';
        }
        exit;
    }
}

/**
 * Validate a phone number string. Allows +, digits, spaces, dashes, parens.
 * Returns the cleaned string or null if invalid.
 */
function validate_phone($phone) {
    $phone = trim((string)$phone);
    if ($phone === '') return null;
    if (!preg_match('/^[+0-9 ()\-]{7,20}$/', $phone)) return false;
    return $phone;
}

/**
 * Clamp a numeric value to a [min, max] range with sane defaults.
 */
function clamp_int($value, $min = 0, $max = PHP_INT_MAX) {
    $n = is_numeric($value) ? (int)$value : $min;
    return max($min, min($max, $n));
}
