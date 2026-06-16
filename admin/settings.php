<?php
require_once 'auth.php';
requireLogin();
requireRole(['super_admin']);

$db = getDB();
$message = '';
$message_type = '';

// Handle password/username change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_csrf_or_die();
    $action = $_POST['action'];

    if ($action === 'change_credentials') {
        $current_password = $_POST['current_password'] ?? '';
        $new_username = sanitize_input($_POST['new_username'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Verify current password
        $admin_id = $_SESSION['admin_id'];
        $stmt = $db->prepare("SELECT username, password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($current_password, $admin['password_hash'])) {
            $message = 'Current password is incorrect.';
            $message_type = 'error';
        } else {
            $updates = [];
            $params = [];

            // Username change
            if (!empty($new_username) && $new_username !== $admin['username']) {
                if (strlen($new_username) < 3 || strlen($new_username) > 50) {
                    $message = 'Username must be 3-50 characters.';
                    $message_type = 'error';
                } elseif (!preg_match('/^[A-Za-z0-9_.\-]+$/', $new_username)) {
                    $message = 'Username may only contain letters, numbers, dots, dashes, and underscores.';
                    $message_type = 'error';
                } else {
                    // Check if username is taken
                    $check = $db->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
                    $check->execute([$new_username, $admin_id]);
                    if ($check->fetch()) {
                        $message = 'Username is already taken.';
                        $message_type = 'error';
                    } else {
                        $updates[] = "username = ?";
                        $params[] = $new_username;
                    }
                }
            }

            // Password change
            if (!empty($new_password)) {
                if (strlen($new_password) < 8) {
                    $message = 'New password must be at least 8 characters.';
                    $message_type = 'error';
                } elseif (strlen($new_password) > 200) {
                    $message = 'New password is too long.';
                    $message_type = 'error';
                } elseif ($new_password !== $confirm_password) {
                    $message = 'New passwords do not match.';
                    $message_type = 'error';
                } else {
                    $updates[] = "password_hash = ?";
                    $params[] = password_hash($new_password, PASSWORD_BCRYPT);
                }
            }

            // Apply updates
            if (empty($message) && !empty($updates)) {
                $params[] = $admin_id;
                $sql = "UPDATE admin_users SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);

                if (!empty($new_username) && $new_username !== $admin['username']) {
                    $_SESSION['admin_username'] = $new_username;
                }

                $message = 'Credentials updated successfully.';
                $message_type = 'success';
            } elseif (empty($message) && empty($updates)) {
                $message = 'No changes to save.';
                $message_type = 'error';
            }
        }
    }

    // Handle SMTP settings
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_smtp') {
        require_csrf_or_die();
        $smtpKeys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_from_name'];
        foreach ($smtpKeys as $key) {
            $val = $_POST[$key] ?? '';
            $stmt = $db->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            if ($stmt->fetchColumn() > 0) {
                $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$val, $key]);
            } else {
                $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
                $stmt->execute([$key, $val]);
            }
        }
        $message = 'SMTP settings updated successfully.';
        $message_type = 'success';
    }
}

// Load current SMTP settings
$smtpSettings = [];
$smtpKeys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_from_name'];
foreach ($smtpKeys as $key) {
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    $smtpSettings[$key] = $row ? $row['setting_value'] : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .settings-card { background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 28px; margin-bottom: 24px; max-width: 600px; }
        .settings-card h3 { font-family: 'Space Grotesk', sans-serif; margin-bottom: 8px; }
        .settings-card .subtitle { color: #64748b; font-size: 0.85rem; margin-bottom: 24px; }
        .info-row { display: flex; gap: 12px; align-items: center; padding: 12px 0; border-bottom: 1px solid #1e293b; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-size: 0.85rem; min-width: 120px; }
        .info-value { color: #e2e8f0; font-weight: 500; }
        .section-divider { border: none; border-top: 1px solid #1e293b; margin: 24px 0; }
        .password-note { background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 8px; padding: 12px 16px; color: #f59e0b; font-size: 0.85rem; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1>Settings</h1>
                <p>Manage your admin account</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>" style="max-width:600px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="settings-card">
                <h3>Account Information</h3>
                <div class="info-row">
                    <span class="info-label">Username</span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Admin ID</span>
                    <span class="info-value">#<?php echo (int)$_SESSION['admin_id']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role</span>
                    <span class="info-value" style="text-transform:capitalize;"><?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'admin'); ?></span>
                </div>
            </div>

            <div class="settings-card">
                <h3>Change Credentials</h3>
                <p class="subtitle">Update your username and/or password</p>

                <div class="password-note">
                    You must enter your current password to make changes.
                </div>

                <form method="POST">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="change_credentials">

                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required maxlength="200">
                    </div>

                    <hr class="section-divider">

                    <div class="form-group">
                        <label for="new_username">New Username</label>
                        <input type="text" id="new_username" name="new_username" maxlength="50" pattern="[A-Za-z0-9_.\-]{3,50}"
                               value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>">
                        <small style="color:#64748b; font-size:0.8rem;">Leave unchanged to keep current username.</small>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" minlength="8" maxlength="200">
                        <small style="color:#64748b; font-size:0.8rem;">Minimum 8 characters. Leave blank to keep current password.</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" maxlength="200">
                    </div>

                    <button type="submit" class="btn btn-primary" onclick="return confirm('Update your credentials?');">Save Changes</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>SMTP Email Settings</h3>
                <p class="subtitle">Configure Gmail SMTP for password reset emails</p>

                <div class="password-note" style="background:rgba(99,102,241,0.08);border-color:rgba(99,102,241,0.2);color:#818cf8;">
                    Use a <strong>Gmail App Password</strong> (not your regular password).<br>
                    Generate one at: <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#c4b5fd;text-decoration:underline;">myaccount.google.com/apppasswords</a>
                </div>

                <form method="POST">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="change_smtp">

                    <div class="form-group">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($smtpSettings['smtp_host'] ?: 'smtp.gmail.com'); ?>" maxlength="200">
                    </div>

                    <div class="form-group">
                        <label for="smtp_port">SMTP Port</label>
                        <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($smtpSettings['smtp_port'] ?: '587'); ?>" maxlength="10">
                    </div>

                    <div class="form-group">
                        <label for="smtp_user">SMTP Username (Gmail address)</label>
                        <input type="email" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($smtpSettings['smtp_user']); ?>" maxlength="200" placeholder="you@gmail.com">
                    </div>

                    <div class="form-group">
                        <label for="smtp_pass">SMTP Password (App Password)</label>
                        <input type="password" id="smtp_pass" name="smtp_pass" value="<?php echo htmlspecialchars($smtpSettings['smtp_pass']); ?>" maxlength="200" placeholder="xxxx xxxx xxxx xxxx">
                    </div>

                    <div class="form-group">
                        <label for="smtp_from">From Email</label>
                        <input type="email" id="smtp_from" name="smtp_from" value="<?php echo htmlspecialchars($smtpSettings['smtp_from']); ?>" maxlength="200" placeholder="Same as username if empty">
                    </div>

                    <div class="form-group">
                        <label for="smtp_from_name">From Name</label>
                        <input type="text" id="smtp_from_name" name="smtp_from_name" value="<?php echo htmlspecialchars($smtpSettings['smtp_from_name'] ?: 'CellVerse Admin'); ?>" maxlength="200">
                    </div>

                    <button type="submit" class="btn btn-primary" onclick="return confirm('Update SMTP settings?');">Save SMTP Settings</button>
                </form>
            </div>
        </main>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        var newPass = document.getElementById('new_password').value;
        var confirmPass = document.getElementById('confirm_password').value;
        if (newPass && newPass !== confirmPass) {
            e.preventDefault();
            alert('New passwords do not match.');
        }
    });
    </script>
</body>
</html>
