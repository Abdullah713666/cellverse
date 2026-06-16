<?php
require_once 'auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$timeout = '';

if (isset($_GET['timeout'])) {
    $timeout = 'Your session has expired. Please log in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_or_die();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $result = loginAdmin($username, $password);
        if ($result['success']) {
            header('Location: index.php?tab=' . $result['tab_token']);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CellVerse Admin - Login</title>
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
                <h1>Admin Panel</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($timeout): ?>
                <div class="alert alert-warning"><?php echo htmlspecialchars($timeout); ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required maxlength="50" autocomplete="username" autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required maxlength="200" autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
            </form>
            <p style="text-align:center;margin-top:16px;"><a href="forgot_password.php" style="color:#94a3b8;text-decoration:underline;font-size:0.9rem;">Forgot password?</a></p>
        </div>
    </div>

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
    </script>
</body>
</html>
