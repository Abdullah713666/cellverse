<?php
/**
 * One-time migration script.
 * Visit /admin/migrate.php once to apply schema changes, then delete this file.
 */
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

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

    // 2. Set default email for admin user
    $stmt = $db->prepare("UPDATE admin_users SET email = ? WHERE username = ? AND (email IS NULL OR email = '')");
    $stmt->execute(['admin@cellverse.com', 'admin']);
    $migrations[] = ['Set default email for admin user', true];

    // 3. Create password_resets table if missing
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
            <p style="color:#4ade80;margin-top:20px;">All migrations completed successfully.</p>
        <?php endif; ?>

        <a href="index.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
