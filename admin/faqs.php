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

    if ($action === 'add' || $action === 'edit') {
        $question = sanitize_input($_POST['question'] ?? '');
        $answer = sanitize_input($_POST['answer'] ?? '');
        $category = sanitize_input($_POST['category'] ?? '');
        $display_order = clamp_int($_POST['display_order'] ?? 0, 0, 10000);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($question) || strlen($question) > 500) {
            $message = 'Question is required (max 500 chars).';
            $message_type = 'error';
        } elseif (empty($answer) || strlen($answer) > 5000) {
            $message = 'Answer is required (max 5000 chars).';
            $message_type = 'error';
        } elseif (strlen($category) > 100) {
            $message = 'Category must be under 100 characters.';
            $message_type = 'error';
        } else {
            try {
                if ($action === 'edit') {
                    $id = clamp_int($_POST['id'] ?? 0, 1);
                    $stmt = $db->prepare("UPDATE faqs SET question=?, answer=?, category=?, display_order=?, is_active=? WHERE id=?");
                    $stmt->execute([$question, $answer, $category, $display_order, $is_active, $id]);
                    $message = 'FAQ updated successfully.';
                } else {
                    $stmt = $db->prepare("INSERT INTO faqs (question, answer, category, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$question, $answer, $category, $display_order, $is_active]);
                    $message = 'FAQ added successfully.';
                }
                $message_type = 'success';
            } catch (PDOException $e) {
                error_log('FAQ save error: ' . $e->getMessage());
                $message = 'Error saving FAQ. Please try again.';
                $message_type = 'error';
            }
        }
    }

    if ($action === 'delete') {
        $id = clamp_int($_POST['id'] ?? 0, 1);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'FAQ deleted.';
            $message_type = 'success';
        }
    }
}

// Edit mode
$edit_faq = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_faq = $stmt->fetch();
}

// Fetch FAQs
$faqs = $db->query("SELECT * FROM faqs ORDER BY display_order ASC, id ASC")->fetchAll();

// Get unique categories for the dropdown
$faq_categories = $db->query("SELECT DISTINCT category FROM faqs ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .faq-header-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
        .form-card { background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .form-card h3 { font-family: 'Space Grotesk', sans-serif; margin-bottom: 20px; }
        .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #334155; border-radius: 24px; transition: 0.3s; }
        .toggle-slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #e2e8f0; border-radius: 50%; transition: 0.3s; }
        .toggle-switch input:checked + .toggle-slider { background: #00d4aa; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
        .faq-question { font-weight: 600; color: #e2e8f0; margin-bottom: 4px; }
        .faq-answer { color: #94a3b8; font-size: 0.85rem; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .faq-cat-badge { background: rgba(124, 58, 237, 0.15); color: #7c3aed; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .empty-state { text-align: center; padding: 60px 20px; color: #64748b; }
        .empty-state svg { margin-bottom: 16px; opacity: 0.4; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1>FAQs</h1>
                <p>Manage frequently asked questions</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <h3><?php echo $edit_faq ? 'Edit FAQ' : 'Add New FAQ'; ?></h3>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="<?php echo $edit_faq ? 'edit' : 'add'; ?>">
                    <?php if ($edit_faq): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$edit_faq['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="question">Question *</label>
                        <input type="text" id="question" name="question" required maxlength="500"
                               value="<?php echo htmlspecialchars($edit_faq['question'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="answer">Answer *</label>
                        <textarea id="answer" name="answer" rows="5" required maxlength="5000"><?php echo htmlspecialchars($edit_faq['answer'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" list="faq-categories" maxlength="100"
                                   value="<?php echo htmlspecialchars($edit_faq['category'] ?? 'General'); ?>">
                            <datalist id="faq-categories">
                                <?php foreach ($faq_categories as $fc): ?>
                                    <option value="<?php echo htmlspecialchars($fc); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" id="display_order" name="display_order" min="0" max="10000"
                                   value="<?php echo $edit_faq['display_order'] ?? 0; ?>">
                        </div>
                    </div>

                    <div class="form-group" style="display:flex; align-items:center; gap:12px;">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_active" value="1" <?php echo ($edit_faq && !$edit_faq['is_active']) ? '' : 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="color:#94a3b8; font-size:0.9rem;">Active</span>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <?php if ($edit_faq): ?>
                            <a href="faqs.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary"><?php echo $edit_faq ? 'Update FAQ' : 'Add FAQ'; ?></button>
                    </div>
                </form>
            </div>

            <div class="admin-card">
                <h3>All FAQs</h3>
                <?php if (empty($faqs)): ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <p>No FAQs yet.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Question</th>
                                    <th>Answer</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faqs as $faq): ?>
                                    <tr>
                                        <td><?php echo $faq['display_order']; ?></td>
                                        <td class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></td>
                                        <td class="faq-answer"><?php echo htmlspecialchars($faq['answer']); ?></td>
                                        <td><span class="faq-cat-badge"><?php echo htmlspecialchars($faq['category']); ?></span></td>
                                        <td>
                                            <?php if ($faq['is_active']): ?>
                                                <span class="badge badge-delivered">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-cancelled">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:6px;">
                                                <a href="?edit=<?php echo $faq['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this FAQ?');">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo (int)$faq['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
