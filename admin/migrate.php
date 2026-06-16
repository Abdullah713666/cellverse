<?php
/**
 * One-time migration script.
 * Visit /admin/migrate.php once to apply schema changes, then delete this file.
 */
require_once 'auth.php';
requireLogin();
requireRole(['super_admin']);

$db = getDB();
$migrations = [];
$allOk = true;

try {
    // 1. Add email column to admin_users if missing
    $colCheck = $db->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'admin_users' AND column_name = 'email'")->fetchColumn();
    if ($colCheck == 0) {
        $db->exec("ALTER TABLE admin_users ADD COLUMN email VARCHAR(200) AFTER password_hash");
        $migrations[] = ['Added email column to admin_users', true];
    } else {
        $migrations[] = ['email column already exists', true];
    }

    // 2. Add role column to admin_users if missing
    $colCheck = $db->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'admin_users' AND column_name = 'role'")->fetchColumn();
    if ($colCheck == 0) {
        $db->exec("ALTER TABLE admin_users ADD COLUMN role ENUM('super_admin','admin') DEFAULT 'admin' AFTER email");
        $migrations[] = ['Added role column to admin_users', true];
    } else {
        $migrations[] = ['role column already exists', true];
    }

    // 3. Set default email for admin user
    $stmt = $db->prepare("UPDATE admin_users SET email = ? WHERE username = ? AND (email IS NULL OR email = '')");
    $stmt->execute(['admin@cellverse.com', 'admin']);
    $migrations[] = ['Set default email for admin user', true];

    // 4. Set admin role to super_admin
    $stmt = $db->prepare("UPDATE admin_users SET role = 'super_admin' WHERE username = ? AND (role IS NULL OR role = '')");
    $stmt->execute(['admin']);
    $migrations[] = ['Set admin role to super_admin', true];

    // 5. Create password_resets table if missing
    $tableCheck = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'password_resets'")->fetchColumn();
    if ($tableCheck == 0) {
        $db->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            token VARCHAR(64) UNIQUE NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");
        $migrations[] = ['Created password_resets table', true];
    } else {
        $migrations[] = ['password_resets table already exists', true];
    }

    // 6. Create login_attempts table if missing
    $tableCheck = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'login_attempts'")->fetchColumn();
    if ($tableCheck == 0) {
        $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at DATETIME NOT NULL,
            INDEX idx_lookup (username, ip_address, attempted_at)
        ) ENGINE=InnoDB");
        $migrations[] = ['Created login_attempts table', true];
    } else {
        $migrations[] = ['login_attempts table already exists', true];
    }

    // 7. Create password_reset_attempts table if missing
    $tableCheck = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'password_reset_attempts'")->fetchColumn();
    if ($tableCheck == 0) {
        $db->exec("CREATE TABLE IF NOT EXISTS password_reset_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at DATETIME NOT NULL,
            INDEX idx_lookup (ip_address, attempted_at)
        ) ENGINE=InnoDB");
        $migrations[] = ['Created password_reset_attempts table', true];
    } else {
        $migrations[] = ['password_reset_attempts table already exists', true];
    }

    // 8. Add SMTP settings if missing
    $stmt = $db->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
    $smtpKeys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_from_name'];
    $smtpDefaults = ['smtp_host' => '', 'smtp_port' => '587', 'smtp_user' => '', 'smtp_pass' => '', 'smtp_from' => '', 'smtp_from_name' => 'CellVerse Admin'];
    foreach ($smtpKeys as $key) {
        $stmt->execute([$key]);
        if ($stmt->fetchColumn() == 0) {
            $ins = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            $ins->execute([$key, $smtpDefaults[$key]]);
        }
    }
    $migrations[] = ['SMTP settings in site_settings', true];

} catch (Exception $e) {
    $migrations[] = ['Error: ' . $e->getMessage(), false];
    $allOk = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e293b; border-radius: 12px; padding: 32px; max-width: 500px; width: 100%; }
        h1 { margin: 0 0 24px; font-size: 1.5rem; }
        .item { padding: 8px 0; border-bottom: 1px solid #334155; display: flex; align-items: center; gap: 8px; }
        .item:last-child { border-bottom: none; }
        .ok { color: #4ade80; }
        .fail { color: #f87171; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 24px; background: #6366f1; color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; font-size: 0.9rem; }
        .btn:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Database Migration</h1>
        <?php foreach ($migrations as $m): ?>
            <div class="item <?php echo $m[1] ? 'ok' : 'fail'; ?>">
                <?php echo $m[1] ? '&#10003;' : '&#10007;'; ?> <?php echo htmlspecialchars($m[0]); ?>
            </div>
        <?php endforeach; ?>

        <?php if ($allOk): ?>
            <p style="color:#4ade80;margin-top:20px;">All migrations completed successfully. You can now delete this file.</p>
        <?php endif; ?>

        <a href="index.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
