<?php
/**
 * CellVerse - Database Installer
 * Run this once to set up the database, then delete this file.
 */

// Check if already installed
if (file_exists(__DIR__ . '/.installed')) {
    die('CellVerse is already installed. Please delete install.php for security.');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'cellverse_db';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';

    try {
        // Connect to MySQL without selecting database
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");

        // Read and execute SQL file
        $sql = file_get_contents(__DIR__ . '/database.sql');
        
        // Remove CREATE DATABASE and USE statements (already executed)
        $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s', '', $sql);
        $sql = preg_replace('/USE\s+.*?;/s', '', $sql);
        
        // Split by semicolons and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }

        // Create .installed marker
        file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

        $success = 'Database installed successfully! You can now access the site.';
    } catch (PDOException $e) {
        $error = 'Installation failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CellVerse - Installation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0f1a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .installer {
            background: #111827;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #00d4aa;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #94a3b8;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #94a3b8;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 16px;
        }
        input:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.2);
        }
        button {
            width: 100%;
            padding: 14px;
            background: #00d4aa;
            color: #0a0f1a;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            background: #00b894;
            box-shadow: 0 0 20px rgba(0, 212, 170, 0.4);
        }
        .success {
            background: rgba(0, 212, 170, 0.1);
            border: 1px solid #00d4aa;
            color: #00d4aa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid #f59e0b;
            color: #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="installer">
        <h1>CellVerse Installer</h1>
        <p class="subtitle">Set up your database to get started</p>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <p style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="color: #00d4aa;">Go to Homepage</a> |
                <a href="admin/" style="color: #00d4aa;">Admin Panel</a>
            </p>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="warning">
                <strong>Warning:</strong> Delete this file after installation for security reasons.
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="dbname" value="cellverse_db" required>
                </div>
                <div class="form-group">
                    <label>Database Username</label>
                    <input type="text" name="username" value="root" required>
                </div>
                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="password" value="">
                </div>
                <button type="submit">Install Database</button>
            </form>

        <?php endif; ?>
    </div>
</body>
</html>
