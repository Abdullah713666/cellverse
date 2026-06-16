<?php
/**
 * CellVerse - Admin Authentication
 */
require_once __DIR__ . '/../config/init.php';

function tableExists($tableName) {
    static $cache = [];
    if (isset($cache[$tableName])) return $cache[$tableName];
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$tableName]);
    $cache[$tableName] = (int)$stmt->fetchColumn() > 0;
    return $cache[$tableName];
}

function columnExists($tableName, $columnName) {
    $key = $tableName . '.' . $columnName;
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
    $stmt->execute([$tableName, $columnName]);
    $cache[$key] = (int)$stmt->fetchColumn() > 0;
    return $cache[$key];
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    // Check max absolute session lifetime (12 hours)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 43200) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }

    // Check session inactivity (30 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function requireRole($allowedRoles) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    $role = $_SESSION['admin_role'] ?? 'admin';
    if (!in_array($role, (array)$allowedRoles)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title><link rel="stylesheet" href="style.css"></head><body class="login-page"><div class="login-container"><div class="login-card"><div class="login-header"><h1 style="color:#f87171;">403 Forbidden</h1></div><p style="color:#94a3b8;text-align:center;">You do not have permission to access this page.</p><p style="text-align:center;"><a href="index.php" style="color:#6366f1;">Back to Dashboard</a></p></div></div></body></html>';
        exit;
    }
}

function isSuperAdmin() {
    return ($_SESSION['admin_role'] ?? '') === 'super_admin';
}

function getLoginAttempts($username, $ipAddress) {
    if (!tableExists('login_attempts')) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$username, $ipAddress]);
    return (int)$stmt->fetchColumn();
}

function recordLoginAttempt($username, $ipAddress) {
    if (!tableExists('login_attempts')) return;
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO login_attempts (username, ip_address, attempted_at) VALUES (?, ?, NOW())");
    $stmt->execute([$username, $ipAddress]);
}

function clearLoginAttempts($username, $ipAddress) {
    if (!tableExists('login_attempts')) return;
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE username = ? AND ip_address = ?");
    $stmt->execute([$username, $ipAddress]);
}

function getLockoutRemaining($username, $ipAddress) {
    if (!tableExists('login_attempts')) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT MIN(attempted_at) FROM login_attempts WHERE username = ? AND ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$username, $ipAddress]);
    $firstAttempt = $stmt->fetchColumn();
    if (!$firstAttempt) return 0;
    $elapsed = strtotime('now') - strtotime($firstAttempt);
    $remaining = 900 - $elapsed;
    return $remaining > 0 ? $remaining : 0;
}

function loginAdmin($username, $password) {
    $db = getDB();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Check rate limit (5 attempts per 15 min per IP+username)
    $attempts = getLoginAttempts($username, $ipAddress);
    if ($attempts >= 5) {
        $remaining = getLockoutRemaining($username, $ipAddress);
        $minutes = ceil($remaining / 60);
        return ['success' => false, 'message' => "Too many failed attempts. Try again in {$minutes} minute" . ($minutes !== 1 ? 's' : '') . "."];
    }

    // Check if role column exists, query accordingly
    if (columnExists('admin_users', 'role')) {
        $stmt = $db->prepare("SELECT id, username, password_hash, role FROM admin_users WHERE username = ?");
    } else {
        $stmt = $db->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
    }
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        clearLoginAttempts($username, $ipAddress);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'] ?? 'admin';
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        $_SESSION['admin_tab_token'] = bin2hex(random_bytes(16));

        return ['success' => true, 'tab_token' => $_SESSION['admin_tab_token']];
    }

    recordLoginAttempt($username, $ipAddress);
    return ['success' => false, 'message' => 'Invalid username or password.'];
}
