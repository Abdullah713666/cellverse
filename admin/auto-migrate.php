<?php
/**
 * Auto-migration endpoint.
 * Runs all pending DB migrations, then self-deletes on success.
 * Visit once: /admin/auto-migrate.php
 */
require_once __DIR__ . '/../config/init.php';

$db = getDB();
$results = [];

$migrations = [
    'Add email column to admin_users' => "SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'admin_users' AND column_name = 'email'); SET @s = IF(@c = 0, 'ALTER TABLE admin_users ADD COLUMN email VARCHAR(200) AFTER password_hash', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st",
    'Add role column to admin_users' => "SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'admin_users' AND column_name = 'role'); SET @s = IF(@c = 0, \"ALTER TABLE admin_users ADD COLUMN role ENUM('super_admin','admin') DEFAULT 'admin' AFTER email\", 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st",
    'Set admin email and role' => "UPDATE admin_users SET email = 'admin@cellverse.com', role = 'super_admin' WHERE username = 'admin' AND (role IS NULL OR role = '' OR email IS NULL OR email = '')",
    'Create password_resets table' => "CREATE TABLE IF NOT EXISTS password_resets (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, token VARCHAR(64) UNIQUE NOT NULL, expires_at DATETIME NOT NULL, used TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE) ENGINE=InnoDB",
    'Create login_attempts table' => "CREATE TABLE IF NOT EXISTS login_attempts (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL, ip_address VARCHAR(45) NOT NULL, attempted_at DATETIME NOT NULL, INDEX idx_lookup (username, ip_address, attempted_at)) ENGINE=InnoDB",
    'Create password_reset_attempts table' => "CREATE TABLE IF NOT EXISTS password_reset_attempts (id INT AUTO_INCREMENT PRIMARY KEY, ip_address VARCHAR(45) NOT NULL, attempted_at DATETIME NOT NULL, INDEX idx_lookup (ip_address, attempted_at)) ENGINE=InnoDB",
    'Add SMTP settings to site_settings' => "INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_host', ''), ('smtp_port', '587'), ('smtp_user', ''), ('smtp_pass', ''), ('smtp_from', ''), ('smtp_from_name', 'CellVerse Admin')",
];

$allOk = true;
foreach ($migrations as $name => $sql) {
    try {
        $db->exec($sql);
        $results[] = ['ok', $name];
    } catch (Exception $e) {
        $results[] = ['fail', $name . ': ' . $e->getMessage()];
        $allOk = false;
    }
}

$okCount = count(array_filter($results, fn($r) => $r[0] === 'ok'));
$failCount = count(array_filter($results, fn($r) => $r[0] === 'fail'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-Migrate - CellVerse</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}
        .card{background:#1e293b;border-radius:12px;padding:32px;max-width:520px;width:100%}
        h1{margin:0 0 20px;font-size:1.4rem}
        .item{padding:8px 0;border-bottom:1px solid #334155;display:flex;align-items:center;gap:8px}
        .item:last-child{border-bottom:none}
        .ok{color:#4ade80}
        .fail{color:#f87171}
        .summary{margin-top:20px;padding:12px;border-radius:8px;font-weight:500}
        .summary.ok{background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.3);color:#4ade80}
        .summary.fail{background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.3);color:#f87171}
        .btn{display:inline-block;margin-top:16px;padding:10px 24px;background:#6366f1;color:#fff;border:none;border-radius:8px;cursor:pointer;text-decoration:none;font-size:0.9rem}
        .btn:hover{background:#4f46e5}
    </style>
</head>
<body>
    <div class="card">
        <h1>CellVerse Database Migration</h1>
        <?php foreach ($results as $r): ?>
            <div class="item <?php echo $r[0]; ?>">
                <?php echo $r[0] === 'ok' ? '&#10003;' : '&#10007;'; ?>
                <?php echo htmlspecialchars($r[1]); ?>
            </div>
        <?php endforeach; ?>
        <div class="summary <?php echo $allOk ? 'ok' : 'fail'; ?>">
            <?php echo $allOk ? "All {$okCount} migrations completed successfully!" : "{$okCount} OK, {$failCount} failed."; ?>
        </div>
        <a href="login.php" class="btn">Go to Login</a>
    </div>
</body>
</html>
<?php
