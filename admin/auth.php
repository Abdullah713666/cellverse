<?php
/**
 * CellVerse - Admin Authentication
 */
require_once __DIR__ . '/../config/init.php';

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
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

function loginAdmin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Check brute force
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $lockout = $_SESSION['lockout_time'] ?? 0;
        
        if ($attempts >= 5 && (time() - $lockout) < 900) {
            return ['success' => false, 'message' => 'Too many failed attempts. Try again in 15 minutes.'];
        }
        
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        $_SESSION['login_attempts'] = 0;
        
        // Tab-scoped session
        $_SESSION['admin_tab_token'] = bin2hex(random_bytes(16));
        
        return ['success' => true, 'tab_token' => $_SESSION['admin_tab_token']];
    }
    
    // Track failed attempts
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['lockout_time'] = time();
    }
    
    return ['success' => false, 'message' => 'Invalid username or password.'];
}
