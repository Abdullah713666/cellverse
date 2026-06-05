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

    if ($action === 'toggle_read' && $id > 0) {
        $stmt = $db->prepare("UPDATE contact_submissions SET is_read = NOT is_read WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Message status updated.';
        $message_type = 'success';
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = $db->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Message deleted.';
        $message_type = 'success';
    }

    if ($action === 'mark_all_read') {
        $db->exec("UPDATE contact_submissions SET is_read = 1 WHERE is_read = 0");
        $message = 'All messages marked as read.';
        $message_type = 'success';
    }
}

// View a single message
$view_msg = null;
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    $stmt = $db->prepare("SELECT * FROM contact_submissions WHERE id = ?");
    $stmt->execute([$view_id]);
    $view_msg = $stmt->fetch();

    if ($view_msg && !$view_msg['is_read']) {
        $db->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?")->execute([$view_id]);
        $view_msg['is_read'] = 1;
    }
}

// Stats
$unread_count = (int)$db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();
$total_count = (int)$db->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();

// Fetch all messages
$messages = $db->query("SELECT * FROM contact_submissions ORDER BY is_read ASC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .msg-header-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
        .msg-stats { display: flex; gap: 16px; }
        .msg-stat { background: #111827; border: 1px solid #1e293b; border-radius: 10px; padding: 14px 20px; display: flex; align-items: center; gap: 12px; }
        .msg-stat-num { font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; font-weight: 700; }
        .msg-stat-label { color: #94a3b8; font-size: 0.85rem; }
        .unread-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block; }
        .msg-row-unread { background: rgba(239, 68, 68, 0.04); }
        .msg-row-unread td:first-child { border-left: 3px solid #ef4444; }
        .msg-sender { font-weight: 600; color: #e2e8f0; }
        .msg-subject { color: #cbd5e1; }
        .msg-preview { color: #64748b; font-size: 0.85rem; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .msg-date { color: #64748b; font-size: 0.85rem; white-space: nowrap; }
        .msg-actions { display: flex; gap: 6px; }
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border-radius: 6px; }
        .view-panel { background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 28px; margin-bottom: 24px; }
        .view-panel h2 { font-family: 'Space Grotesk', sans-serif; color: #00d4aa; margin-bottom: 20px; font-size: 1.2rem; }
        .view-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #1e293b; }
        .view-meta dt { color: #64748b; font-size: 0.85rem; margin-bottom: 2px; }
        .view-meta dd { color: #e2e8f0; font-size: 0.95rem; }
        .view-body { color: #cbd5e1; line-height: 1.7; white-space: pre-wrap; }
        .empty-state { text-align: center; padding: 60px 20px; color: #64748b; }
        .empty-state svg { margin-bottom: 16px; opacity: 0.4; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1>Messages</h1>
                <p>Manage contact form submissions</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="msg-stats">
                <div class="msg-stat">
                    <div class="msg-stat-num" style="color:#ef4444;"><?php echo $unread_count; ?></div>
                    <div class="msg-stat-label">Unread</div>
                </div>
                <div class="msg-stat">
                    <div class="msg-stat-num" style="color:#00d4aa;"><?php echo $total_count; ?></div>
                    <div class="msg-stat-label">Total Messages</div>
                </div>
            </div>

            <?php if (isset($_GET['view']) && $view_msg): ?>
                <div class="view-panel">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h2>Message #<?php echo $view_msg['id']; ?></h2>
                        <a href="messages.php" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                    <dl class="view-meta">
                        <div><dt>Name</dt><dd><?php echo htmlspecialchars($view_msg['name']); ?></dd></div>
                        <div><dt>Email</dt><dd><?php echo htmlspecialchars($view_msg['email']); ?></dd></div>
                        <div><dt>Phone</dt><dd><?php echo htmlspecialchars($view_msg['phone'] ?: 'N/A'); ?></dd></div>
                        <div><dt>Subject</dt><dd><?php echo htmlspecialchars($view_msg['subject'] ?: 'N/A'); ?></dd></div>
                        <div><dt>Date</dt><dd><?php echo date('M d, Y \a\t h:i A', strtotime($view_msg['created_at'])); ?></dd></div>
                        <div><dt>Status</dt><dd><?php echo $view_msg['is_read'] ? '<span style="color:#00d4aa;">Read</span>' : '<span style="color:#ef4444;">Unread</span>'; ?></dd></div>
                    </dl>
                    <div class="view-body"><?php echo htmlspecialchars($view_msg['message']); ?></div>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin-bottom:0;">All Messages</h3>
                    <?php if ($unread_count > 0): ?>
                        <form method="POST" style="display:inline;">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="btn btn-secondary btn-sm">Mark All Read</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <p>No messages yet.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                    <tr class="<?php echo !$msg['is_read'] ? 'msg-row-unread' : ''; ?>">
                                        <td><?php if (!$msg['is_read']): ?><span class="unread-dot"></span><?php endif; ?></td>
                                        <td>
                                            <div class="msg-sender"><?php echo htmlspecialchars($msg['name']); ?></div>
                                            <div style="color:#64748b; font-size:0.8rem;"><?php echo htmlspecialchars($msg['email']); ?></div>
                                        </td>
                                        <td class="msg-subject"><?php echo htmlspecialchars($msg['subject'] ?: 'No subject'); ?></td>
                                        <td class="msg-preview"><?php echo htmlspecialchars($msg['message']); ?></td>
                                        <td class="msg-date"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                        <td>
                                            <div class="msg-actions">
                                                <a href="?view=<?php echo $msg['id']; ?>" class="btn btn-secondary btn-sm btn-icon" title="View">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </a>
                                                <form method="POST" style="display:inline;">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="action" value="toggle_read">
                                                    <input type="hidden" name="id" value="<?php echo (int)$msg['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm btn-icon" title="<?php echo $msg['is_read'] ? 'Mark Unread' : 'Mark Read'; ?>">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                    </button>
                                                </form>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo (int)$msg['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                                    </button>
                                                </form>
                                            </div>
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
