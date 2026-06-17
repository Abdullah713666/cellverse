<?php
/**
 * TEMPORARY: Reset admin password. DELETE AFTER USE.
 * Visit: /admin/fix-password.php?user=admin&pass=admin123
 * Then delete this file.
 */
require_once __DIR__ . '/../config/init.php';
$db = getDB();

$username = $_GET['user'] ?? '';
$password = $_GET['pass'] ?? '';

if (!$username || !$password || strlen($password) < 6) {
    http_response_code(400);
    echo "Provide ?user=xxx&pass=xxx (min 6 chars)";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE username = ?");
$stmt->execute([$hash, $username]);
$count = $stmt->rowCount();

echo "Updated $count user(s). Hash: " . substr($hash, 0, 15) . "...";
echo "<br>Password is now: $password";
echo "<br><br><strong>DELETE THIS FILE NOW: admin/fix-password.php</strong>";
