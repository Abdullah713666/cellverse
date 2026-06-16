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
    <style>
        .password-wrapper { position: relative; }
        .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; padding: 4px; display: flex; align-items: center; }
        .password-toggle:hover { color: #94a3b8; }
        .password-toggle svg { width: 20px; height: 20px; }
    </style>
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
                        <div class="password-wrapper">
                            <input type="password" id="new_password" name="new_password" required minlength="8" maxlength="200" autofocus>
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)" aria-label="Toggle password visibility">
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="200">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)" aria-label="Toggle password visibility">
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
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
    function togglePassword(inputId, btn) {
        var input = document.getElementById(inputId);
        var eyeOpen = btn.querySelector('.eye-open');
        var eyeClosed = btn.querySelector('.eye-closed');
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
        } else {
            input.type = 'password';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        }
    }
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
