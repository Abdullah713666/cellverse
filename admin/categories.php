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
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $display_order = clamp_int($_POST['display_order'] ?? 0, 0, 10000);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        if (empty($name) || strlen($name) > 100) {
            $message = 'Category name is required (max 100 chars).';
            $message_type = 'error';
        } elseif (strlen($description) > 500) {
            $message = 'Description must be under 500 characters.';
            $message_type = 'error';
        } elseif (strlen($slug) > 100) {
            $message = 'Generated slug is too long.';
            $message_type = 'error';
        } else {
            try {
                if ($action === 'edit') {
                    $id = clamp_int($_POST['id'] ?? 0, 1);
                    $stmt = $db->prepare("UPDATE categories SET name=?, slug=?, description=?, display_order=?, is_active=? WHERE id=?");
                    $stmt->execute([$name, $slug, $description, $display_order, $is_active, $id]);
                    $message = 'Category updated successfully.';
                } else {
                    $stmt = $db->prepare("INSERT INTO categories (name, slug, description, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $description, $display_order, $is_active]);
                    $message = 'Category added successfully.';
                }
                $message_type = 'success';
            } catch (PDOException $e) {
                error_log('Category save error: ' . $e->getMessage());
                $message = 'Error saving category. Please try again.';
                $message_type = 'error';
            }
        }
    }

    if ($action === 'delete') {
        $id = clamp_int($_POST['id'] ?? 0, 1);
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
            $stmt->execute([$id]);
            $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Category deleted.';
            $message_type = 'success';
        }
    }
}

// Edit mode
$edit_cat = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_cat = $stmt->fetch();
}

// Fetch categories with product counts
$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.display_order ASC, c.name ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .cat-header-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
        .form-card { background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .form-card h3 { font-family: 'Space Grotesk', sans-serif; margin-bottom: 20px; }
        .slug-preview { background: #0a0f1a; border: 1px solid #1e293b; border-radius: 6px; padding: 8px 12px; color: #64748b; font-size: 0.85rem; margin-top: 6px; }
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
                <h1>Categories</h1>
                <p>Manage product categories</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <h3><?php echo $edit_cat ? 'Edit Category' : 'Add New Category'; ?></h3>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="<?php echo $edit_cat ? 'edit' : 'add'; ?>">
                    <?php if ($edit_cat): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$edit_cat['id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Category Name *</label>
                            <input type="text" id="name" name="name" required maxlength="100"
                                   value="<?php echo htmlspecialchars($edit_cat['name'] ?? ''); ?>"
                                   oninput="document.getElementById('slug-preview').textContent = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'auto-generated';">
                            <div class="slug-preview">Slug: <span id="slug-preview"><?php echo $edit_cat ? htmlspecialchars($edit_cat['slug']) : 'auto-generated'; ?></span></div>
                        </div>
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" id="display_order" name="display_order" min="0" max="10000"
                                   value="<?php echo $edit_cat['display_order'] ?? 0; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" maxlength="500"><?php echo htmlspecialchars($edit_cat['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group" style="display:flex; align-items:center; gap:12px;">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_active" value="1" <?php echo ($edit_cat && !$edit_cat['is_active']) ? '' : 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="color:#94a3b8; font-size:0.9rem;">Active</span>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <?php if ($edit_cat): ?>
                            <a href="categories.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary"><?php echo $edit_cat ? 'Update Category' : 'Add Category'; ?></button>
                    </div>
                </form>
            </div>

            <div class="admin-card">
                <h3>All Categories</h3>
                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <p>No categories yet.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Products</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?php echo $cat['display_order']; ?></td>
                                        <td style="font-weight:600; color:#e2e8f0;"><?php echo htmlspecialchars($cat['name']); ?></td>
                                        <td style="color:#64748b; font-size:0.85rem;"><?php echo htmlspecialchars($cat['slug']); ?></td>
                                        <td>
                                            <span class="badge badge-confirmed"><?php echo $cat['product_count']; ?> products</span>
                                        </td>
                                        <td>
                                            <?php if ($cat['is_active']): ?>
                                                <span class="badge badge-delivered">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-cancelled">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:6px;">
                                                <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category? Products will be unlinked.');">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
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
