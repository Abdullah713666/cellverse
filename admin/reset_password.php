<?php
require_once 'auth.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$valid_token = false;

if (empty($token)) {
    $message = 'Invalid reset link.';
    $message_type = 'error';
} else {
    $db = getDB();
    $stmt = $db->prepare("SELECT pr.id, pr.admin_id, pr.expires_at, pr.used, au.username FROM password_resets pr JOIN admin_users au ON pr.admin_id = au.id WHERE pr.token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $message = 'Invalid reset link.';
        $message_type = 'error';
    } elseif ($reset['used']) {
        $message = 'This reset link has already been used.';
        $message_type = 'error';
    } elseif (strtotime($reset['expires_at']) < time()) {
        $message = 'This reset link has expired. Please request a new one.';
        $message_type = 'error';
    } else {
        $valid_token = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    require_csrf_or_die();
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 8) {
        $message = 'Password must be at least 8 characters.';
        $message_type = 'error';
    } elseif (strlen($new_password) > 200) {
        $message = 'Password is too long.';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'error';
    } else {
        // Update password
        $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
        $stmt->execute([password_hash($new_password, PASSWORD_BCRYPT), $reset['admin_id']]);

        // Mark token as used
        $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        $stmt->execute([$reset['id']]);

        // Delete all other tokens for this admin
        $stmt = $db->prepare("DELETE FROM password_resets WHERE admin_id = ? AND id != ?");
        $stmt->execute([$reset['admin_id'], $reset['id']]);

        $message = 'Password updated successfully. You can now log in.';
        $message_type = 'success';
        $valid_token = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <a href="../" class="logo">
                    <svg class="logo-icon" width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <rect x="8" y="2" width="16" height="28" rx="3" stroke="currentColor" stroke-width="2"/>
                        <circle cx="16" cy="26" r="2" fill="currentColor"/>
                        <rect x="12" y="6" width="8" height="14" rx="1" fill="currentColor" opacity="0.3"/>
                    </svg>
                    <span class="logo-text">Cell<span class="logo-accent">Verse</span></span>
                </a>
                <h1>Set New Password</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($valid_token): ?>
                <p style="color:#94a3b8;font-size:0.9rem;margin-bottom:20px;">Username: <strong style="color:#e2e8f0;"><?php echo htmlspecialchars($reset['username']); ?></strong></p>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8" maxlength="200" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="200">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Password</button>
                </form>
            <?php else: ?>
                <p style="text-align:center;"><a href="login.php" style="color:#00d4aa;text-decoration:underline;">Go to Login</a></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($valid_token): ?>
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        var p = document.getElementById('new_password').value;
        var c = document.getElementById('confirm_password').value;
        if (p !== c) {
            e.preventDefault();
            alert('Passwords do not match.');
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
