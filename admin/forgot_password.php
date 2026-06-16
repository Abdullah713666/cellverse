<?php
require_once 'auth.php';
require_once __DIR__ . '/includes/mailer.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_or_die();
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if (empty($username) || empty($email)) {
        $message = 'Please enter both username and email.';
        $message_type = 'error';
    } else {
        $db = getDB();

        // Rate limit: max 3 requests per IP per 15 minutes
        $recentAttempts = 0;
        if (tableExists('password_reset_attempts')) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM password_reset_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            $stmt->execute([$ipAddress]);
            $recentAttempts = (int)$stmt->fetchColumn();
        }

        if ($recentAttempts >= 3) {
            $message = 'Too many reset requests. Please try again in 15 minutes.';
            $message_type = 'error';
        } else {
            // Record this attempt
            if (tableExists('password_reset_attempts')) {
                $stmt = $db->prepare("INSERT INTO password_reset_attempts (ip_address, attempted_at) VALUES (?, NOW())");
                $stmt->execute([$ipAddress]);
            }

            $stmt = $db->prepare("SELECT id, username, email FROM admin_users WHERE username = ? AND email = ?");
            $stmt->execute([$username, $email]);
            $admin = $stmt->fetch();

            if ($admin) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $db->prepare("DELETE FROM password_resets WHERE admin_id = ?");
                $stmt->execute([$admin['id']]);

                $stmt = $db->prepare("INSERT INTO password_resets (admin_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$admin['id'], $token, $expires]);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $resetUrl = $scheme . '://' . $host . BASE_URL . '/admin/reset_password.php?token=' . $token;

                $emailResult = sendPasswordResetEmail($admin['email'], $admin['username'], $resetUrl);

                if ($emailResult['success']) {
                    $message = 'A password reset link has been sent to your email address.';
                    $message_type = 'success';
                } else {
                    $message = $emailResult['message'];
                    $message_type = 'error';
                }
            } else {
                // Don't reveal whether username or email is wrong
                $message = 'If the username and email match an account, a reset link has been sent.';
                $message_type = 'success';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CellVerse Admin</title>
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
                <h1>Reset Password</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required maxlength="50" autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input type="email" id="email" name="email" required maxlength="200" autocomplete="email">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Send Reset Link</button>
            </form>
            <p style="text-align:center;margin-top:16px;"><a href="login.php" style="color:#94a3b8;text-decoration:underline;font-size:0.9rem;">Back to Login</a></p>
        </div>
    </div>
</body>
</html>
