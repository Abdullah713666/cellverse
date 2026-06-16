<?php
require_once 'auth.php';

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

    if (empty($username) || empty($email)) {
        $message = 'Please enter both username and email.';
        $message_type = 'error';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, email FROM admin_users WHERE username = ? AND email = ?");
        $stmt->execute([$username, $email]);
        $admin = $stmt->fetch();

        if ($admin) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Delete any old tokens for this admin
            $stmt = $db->prepare("DELETE FROM password_resets WHERE admin_id = ?");
            $stmt->execute([$admin['id']]);

            // Insert new token
            $stmt = $db->prepare("INSERT INTO password_resets (admin_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$admin['id'], $token, $expires]);

            // In production, send email here. For now, show the link.
            $reset_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/cellverse/admin/reset_password.php?token=' . $token;

            $message = 'Password reset link generated. In production, this would be emailed. For now: <a href="' . htmlspecialchars($reset_url) . '" style="color:#00d4aa;text-decoration:underline;">Reset Password</a>';
            $message_type = 'success';
        } else {
            // Don't reveal whether username or email is wrong
            $message = 'If the username and email match an account, a reset link has been generated.';
            $message_type = 'success';
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
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>"><?php echo $message; ?></div>
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
                <button type="submit" class="btn btn-primary" style="width:100%;">Generate Reset Link</button>
            </form>
            <p style="text-align:center;margin-top:16px;"><a href="login.php" style="color:#94a3b8;text-decoration:underline;font-size:0.9rem;">Back to Login</a></p>
        </div>
    </div>
</body>
</html>
