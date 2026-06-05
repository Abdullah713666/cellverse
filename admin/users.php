<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_csrf_or_die();
    $action = $_POST['action'];
    $id = clamp_int($_POST['id'] ?? 0, 1);

    if ($action === 'toggle_status' && $id > 0) {
        $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'User status updated.';
        $message_type = 'success';
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'User deleted.';
        $message_type = 'success';
    }
}

// Stats
$total_users = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active_users = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$inactive_users = $total_users - $active_users;

// Fetch users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .usr-stats { display: flex; gap: 16px; margin-bottom: 24px; }
        .usr-stat { background: #111827; border: 1px solid #1e293b; border-radius: 10px; padding: 14px 20px; display: flex; align-items: center; gap: 12px; }
        .usr-stat-num { font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; font-weight: 700; }
        .usr-stat-label { color: #94a3b8; font-size: 0.85rem; }
        .usr-name { font-weight: 600; color: #e2e8f0; }
        .usr-email { color: #64748b; font-size: 0.85rem; }
        .usr-company { color: #94a3b8; font-size: 0.85rem; }
        .usr-date { color: #64748b; font-size: 0.85rem; white-space: nowrap; }
        .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #334155; border-radius: 24px; transition: 0.3s; }
        .toggle-slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #e2e8f0; border-radius: 50%; transition: 0.3s; }
        .toggle-switch input:checked + .toggle-slider { background: #00d4aa; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
        .empty-state { text-align: center; padding: 60px 20px; color: #64748b; }
        .empty-state svg { margin-bottom: 16px; opacity: 0.4; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1>Users</h1>
                <p>Manage registered users</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="usr-stats">
                <div class="usr-stat">
                    <div class="usr-stat-num" style="color:#00d4aa;"><?php echo $total_users; ?></div>
                    <div class="usr-stat-label">Total Users</div>
                </div>
                <div class="usr-stat">
                    <div class="usr-stat-num" style="color:#34d399;"><?php echo $active_users; ?></div>
                    <div class="usr-stat-label">Active</div>
                </div>
                <div class="usr-stat">
                    <div class="usr-stat-num" style="color:#ef4444;"><?php echo $inactive_users; ?></div>
                    <div class="usr-stat-label">Inactive</div>
                </div>
            </div>

            <div class="admin-card">
                <h3>All Users</h3>
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        <p>No registered users yet.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Company</th>
                                    <th>Phone</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="usr-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                            <div class="usr-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                            <div style="color:#64748b; font-size:0.8rem;">@<?php echo htmlspecialchars($user['username']); ?></div>
                                        </td>
                                        <td class="usr-company"><?php echo htmlspecialchars($user['company'] ?: 'N/A'); ?></td>
                                        <td style="color:#94a3b8; font-size:0.85rem;"><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                        <td class="usr-date"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline-flex; align-items:center; gap:8px;">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                                <label class="toggle-switch">
                                                    <input type="checkbox" <?php echo $user['is_active'] ? 'checked' : ''; ?> onchange="this.form.submit();">
                                                    <span class="toggle-slider"></span>
                                                </label>
                                                <span style="color:<?php echo $user['is_active'] ? '#00d4aa' : '#ef4444'; ?>; font-size:0.8rem;">
                                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete user <?php echo htmlspecialchars($user['username']); ?>? This cannot be undone.');">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
