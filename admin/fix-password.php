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

// Also show existing users
$users = $db->query("SELECT id, username, email, role FROM admin_users")->fetchAll(PDO::FETCH_ASSOC);
echo "Updated $count user(s). Hash: " . substr($hash, 0, 15) . "...<br>";
echo "Password is now: $password<br><br>";
echo "<strong>Existing users:</strong><br>";
foreach ($users as $u) {
    echo "ID={$u['id']} user={$u['username']} email=" . ($u['email'] ?: 'NULL') . " role=" . ($u['role'] ?: 'NULL') . "<br>";
}
echo "<br><strong>DELETE THIS FILE NOW: admin/fix-password.php</strong>";
