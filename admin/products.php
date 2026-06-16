<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_or_die();
    // Delete product
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product && $product['image_path'] && file_exists('../' . $product['image_path'])) {
            unlink('../' . $product['image_path']);
        }

        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Product deleted successfully.';
        $message_type = 'success';
    }

    // Add/Edit product
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $category_id = clamp_int($_POST['category_id'] ?? 0, 1);
        $price_per_unit = max(0.0, min(1000000.0, (float)$_POST['price_per_unit']));
        $moq = clamp_int($_POST['moq'] ?? 0, 1, 1000000);
        $stock_qty = clamp_int($_POST['stock_qty'] ?? 0, 0, 10000000);
        $sku = sanitize_input($_POST['sku']);
        $status = sanitize_input($_POST['status']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $errors = [];
        if (empty($name) || strlen($name) > 200) $errors[] = 'Product name is required (max 200 chars)';
        if (strlen($description) > 5000) $errors[] = 'Description must be under 5000 characters';
        if (strlen($sku) > 100) $errors[] = 'SKU must be under 100 characters';
        if (!in_array($status, ['available', 'low_stock', 'out_of_stock', 'discontinued'])) $errors[] = 'Invalid status';

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        if ($slug === '' || strlen($slug) > 200) $errors[] = 'Generated slug is empty or too long';

        if (!empty($errors)) {
            $message = implode('. ', $errors);
            $message_type = 'error';
        }

        $image_path = null;
        if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $max_size = 5 * 1024 * 1024;
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_exts)) {
                $message = 'Invalid file extension. Only JPG, PNG, GIF, and WebP are allowed.';
                $message_type = 'error';
            } elseif ($_FILES['image']['size'] > $max_size) {
                $message = 'File size exceeds 5MB limit.';
                $message_type = 'error';
            } else {
                // Verify real MIME type to prevent spoofing
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $real_mime = $finfo->file($_FILES['image']['tmp_name']);
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($real_mime, $allowed_mimes)) {
                    $message = 'File content does not match an allowed image type.';
                    $message_type = 'error';
                } else {
                    $filename = 'uploads/products/' . uniqid('p_', true) . '.' . $ext;

                    if (!is_dir('../uploads/products')) {
                        mkdir('../uploads/products', 0755, true);
                    }

                    if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $filename)) {
                        $image_path = $filename;
                    } else {
                        $message = 'Failed to move uploaded file.';
                        $message_type = 'error';
                    }
                }
            }
        }

        if (empty($message)) {
            try {
                if ($_POST['action'] === 'edit') {
                    $id = clamp_int($_POST['id'] ?? 0, 1);
                    if ($image_path) {
                        $oldImage = $db->prepare("SELECT image_path FROM products WHERE id=?");
                        $oldImage->execute([$id]);
                        $oldRow = $oldImage->fetch();
                        if ($oldRow && $oldRow['image_path'] && file_exists('../' . $oldRow['image_path'])) {
                            unlink('../' . $oldRow['image_path']);
                        }
                        $stmt = $db->prepare("UPDATE products SET name=?, slug=?, description=?, category_id=?, image_path=?, price_per_unit=?, moq=?, stock_qty=?, sku=?, status=?, is_featured=? WHERE id=?");
                        $stmt->execute([$name, $slug, $description, $category_id, $image_path, $price_per_unit, $moq, $stock_qty, $sku, $status, $is_featured, $id]);
                    } else {
                        $stmt = $db->prepare("UPDATE products SET name=?, slug=?, description=?, category_id=?, price_per_unit=?, moq=?, stock_qty=?, sku=?, status=?, is_featured=? WHERE id=?");
                        $stmt->execute([$name, $slug, $description, $category_id, $price_per_unit, $moq, $stock_qty, $sku, $status, $is_featured, $id]);
                    }
                    $message = 'Product updated successfully.';
                    $message_type = 'success';
                } else {
                    $stmt = $db->prepare("INSERT INTO products (name, slug, description, category_id, image_path, price_per_unit, moq, stock_qty, sku, status, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$name, $slug, $description, $category_id, $image_path, $price_per_unit, $moq, $stock_qty, $sku, $status, $is_featured]);
                    $message = 'Product added successfully.';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                $message = 'Error saving product.';
                $message_type = 'error';
                error_log('Product save error: ' . $e->getMessage());
            }
        }
    }
}

// Get all products
$products_stmt = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $products_stmt->fetchAll();

// Get all categories
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 1.5rem; }
        .table-container { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #1e293b; }
        .data-table th { color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; background: #111827; }
        .data-table tbody tr:hover { background: rgba(0,212,170,0.03); }
        .table-image { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; }
        .no-image { width: 50px; height: 50px; border-radius: 8px; background: #1e293b; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 0.7rem; }
        .badge { padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .status-available { background: rgba(52,211,153,0.15); color: #34d399; }
        .status-low_stock { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .status-out_of_stock { background: rgba(248,113,113,0.15); color: #f87171; }
        .status-discontinued { background: rgba(100,116,139,0.15); color: #94a3b8; }
        .text-danger { color: #f87171; }
        .text-warning { color: #f59e0b; }
        .text-success { color: #34d399; }
        .text-center { text-align: center; }
        .actions-cell { display: flex; gap: 6px; align-items: center; }
        .inline-form { display: inline; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 28px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { font-family: 'Space Grotesk', sans-serif; font-size: 1.2rem; }
        .modal-close { background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .current-image { margin-top: 8px; }
        .current-image img { width: 80px; height: 80px; border-radius: 8px; object-fit: cover; }
        .current-image small { display: block; color: #64748b; font-size: 0.8rem; margin-top: 4px; }
        .checkbox-group label { display: flex; align-items: center; gap: 8px; color: #94a3b8; cursor: pointer; }
        .form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <div class="page-header" style="width:100%;">
                    <h1>Products</h1>
                    <button class="btn btn-primary" onclick="toggleModal('add-modal')">Add New Product</button>
                </div>
                <p>Manage your product inventory</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'confirmed' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>MOQ</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="8" class="text-center" style="padding:40px; color:#64748b;">No products found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image_path']): ?>
                                                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="table-image">
                                            <?php else: ?>
                                                <div class="no-image">No Image</div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight:600; color:#e2e8f0;">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                            <?php if ($product['is_featured']): ?>
                                                <span class="badge badge-warning">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color:#94a3b8;"><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                        <td style="color:#e2e8f0;">$<?php echo number_format($product['price_per_unit'], 2); ?></td>
                                        <td style="color:#94a3b8;"><?php echo $product['moq']; ?></td>
                                        <td>
                                            <?php if ($product['stock_qty'] <= 0): ?>
                                                <span class="text-danger">0</span>
                                            <?php elseif ($product['stock_qty'] <= 10): ?>
                                                <span class="text-warning"><?php echo $product['stock_qty']; ?></span>
                                            <?php else: ?>
                                                <span class="text-success"><?php echo $product['stock_qty']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $product['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $product['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="actions-cell">
                                            <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this product?');">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add/Edit Modal -->
            <div class="modal-overlay <?php echo $edit_product ? 'active' : ''; ?>" id="add-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
                        <button class="modal-close" onclick="toggleModal('add-modal')">&times;</button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="id" value="<?php echo (int)$edit_product['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required maxlength="200"
                                   value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" maxlength="5000"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category *</label>
                                <select id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo (int)$category['id']; ?>"
                                            <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="sku">SKU *</label>
                                <input type="text" id="sku" name="sku" required maxlength="100" pattern="[A-Za-z0-9._\-]{1,100}"
                                       value="<?php echo htmlspecialchars($edit_product['sku'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price_per_unit">Price per Unit ($) *</label>
                                <input type="number" id="price_per_unit" name="price_per_unit" step="0.01" min="0" max="1000000" required
                                       value="<?php echo $edit_product['price_per_unit'] ?? ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="moq">Minimum Order Quantity *</label>
                                <input type="number" id="moq" name="moq" min="1" max="1000000" required
                                       value="<?php echo $edit_product['moq'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="stock_qty">Stock Quantity *</label>
                                <input type="number" id="stock_qty" name="stock_qty" min="0" max="10000000" required
                                       value="<?php echo $edit_product['stock_qty'] ?? ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select id="status" name="status" required>
                                    <option value="available" <?php echo ($edit_product['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="low_stock" <?php echo ($edit_product['status'] ?? '') === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                                    <option value="out_of_stock" <?php echo ($edit_product['status'] ?? '') === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="discontinued" <?php echo ($edit_product['status'] ?? '') === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image">Product Image (Max 5MB)</label>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                            <?php if ($edit_product && $edit_product['image_path']): ?>
                                <div class="current-image">
                                    <img src="../<?php echo htmlspecialchars($edit_product['image_path']); ?>" alt="Current Image">
                                    <small>Current image</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="is_featured" value="1"
                                    <?php echo ($edit_product && $edit_product['is_featured']) ? 'checked' : ''; ?>>
                                Featured Product
                            </label>
                        </div>

                        <div class="form-actions">
                            <?php if ($edit_product): ?>
                                <a href="products.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleModal(modalId) {
            document.getElementById(modalId).classList.toggle('active');
        }

        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });

        document.getElementById('image')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 5 * 1024 * 1024;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (file) {
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
                    e.target.value = '';
                } else if (file.size > maxSize) {
                    alert('File size exceeds 5MB limit.');
                    e.target.value = '';
                }
            }
        });
    </script>
</body>
</html>
