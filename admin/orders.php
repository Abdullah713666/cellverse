<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_csrf_or_die();
    $action = $_POST['action'];

    if ($action === 'update_status') {
        $orderId = clamp_int($_POST['order_id'] ?? 0, 1);
        $newStatus = sanitize_input($_POST['status'] ?? '');
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

        if ($orderId > 0 && in_array($newStatus, $validStatuses, true)) {
            $stmt = $db->prepare("UPDATE bulk_orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            $success = "Order #$orderId status updated to $newStatus.";
        } else {
            $error = "Invalid status or order ID.";
        }
    } elseif ($action === 'update_notes') {
        $orderId = clamp_int($_POST['order_id'] ?? 0, 1);
        $adminNotes = sanitize_input($_POST['admin_notes'] ?? '');

        if ($orderId > 0) {
            if (strlen($adminNotes) > 2000) {
                $error = "Admin notes must be under 2000 characters.";
            } else {
                $stmt = $db->prepare("UPDATE bulk_orders SET admin_notes = ? WHERE id = ?");
                $stmt->execute([$adminNotes, $orderId]);
                $success = "Admin notes for order #$orderId updated.";
            }
        }
    } elseif ($action === 'delete') {
        $orderId = clamp_int($_POST['order_id'] ?? 0, 1);
        if ($orderId > 0) {
            $stmt = $db->prepare("DELETE FROM bulk_orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $success = "Order #$orderId deleted.";
        }
    }
}

// Filter by status
$statusFilter = $_GET['status'] ?? '';
$validFilters = ['', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($statusFilter, $validFilters)) {
    $statusFilter = '';
}

// Fetch orders
if ($statusFilter !== '') {
    $stmt = $db->prepare("SELECT * FROM bulk_orders WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$statusFilter]);
    $orders = $stmt->fetchAll();
} else {
    $orders = $db->query("SELECT * FROM bulk_orders ORDER BY created_at DESC")->fetchAll();
}

// Count per status for filter badges
$statusCounts = [];
$countResult = $db->query("SELECT status, COUNT(*) as cnt FROM bulk_orders GROUP BY status");
while ($row = $countResult->fetch()) {
    $statusCounts[$row['status']] = $row['cnt'];
}
$totalCount = array_sum($statusCounts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-bar { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .filter-btn { padding: 8px 16px; border-radius: 20px; border: 1px solid #334155; background: #1e293b; color: #94a3b8; cursor: pointer; text-decoration: none; font-size: 0.85rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
        .filter-btn:hover { border-color: #00d4aa; color: #00d4aa; }
        .filter-btn.active { background: #00d4aa; color: #0a0f1a; border-color: #00d4aa; font-weight: 600; }
        .filter-btn .count { background: rgba(0,0,0,0.2); padding: 1px 7px; border-radius: 10px; font-size: 0.8rem; }
        .status-select { background: #1e293b; border: 1px solid #334155; color: #e0e0e0; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; }
        .status-select:focus { outline: none; border-color: #00d4aa; }
        .status-badge { padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; display: inline-block; }
        .status-pending { background: rgba(251,191,36,0.15); color: #fbbf24; }
        .status-confirmed { background: rgba(96,165,250,0.15); color: #60a5fa; }
        .status-processing { background: rgba(168,85,247,0.15); color: #a855f7; }
        .status-shipped { background: rgba(56,189,248,0.15); color: #38bdf8; }
        .status-delivered { background: rgba(52,211,153,0.15); color: #34d399; }
        .status-cancelled { background: rgba(248,113,113,0.15); color: #f87171; }
        .company-cell { font-weight: 600; color: #e2e8f0; }
        .contact-cell { color: #94a3b8; font-size: 0.85rem; }
        .product-cell { color: #cbd5e1; }
        .qty-cell { font-weight: 600; color: #00d4aa; text-align: center; }
        .date-cell { color: #94a3b8; font-size: 0.85rem; white-space: nowrap; }
        .actions-cell { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .btn-sm { padding: 4px 10px; font-size: 0.75rem; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 30px; width: 90%; max-width: 500px; }
        .modal h3 { color: #00d4aa; margin-bottom: 20px; font-family: 'Space Grotesk', sans-serif; }
        .modal textarea { width: 100%; background: #1e293b; border: 1px solid #334155; color: #e0e0e0; padding: 12px; border-radius: 8px; font-family: inherit; resize: vertical; min-height: 100px; }
        .modal textarea:focus { outline: none; border-color: #00d4aa; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .notes-preview { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #64748b; font-size: 0.85rem; font-style: italic; cursor: pointer; }
        .notes-preview:hover { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1>Orders</h1>
                <p>Manage and track all bulk orders</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-confirmed"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="filter-bar">
                <a href="orders.php" class="filter-btn <?php echo $statusFilter === '' ? 'active' : ''; ?>">
                    All <span class="count"><?php echo $totalCount; ?></span>
                </a>
                <a href="orders.php?status=pending" class="filter-btn <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                    Pending <span class="count"><?php echo $statusCounts['pending'] ?? 0; ?></span>
                </a>
                <a href="orders.php?status=confirmed" class="filter-btn <?php echo $statusFilter === 'confirmed' ? 'active' : ''; ?>">
                    Confirmed <span class="count"><?php echo $statusCounts['confirmed'] ?? 0; ?></span>
                </a>
                <a href="orders.php?status=processing" class="filter-btn <?php echo $statusFilter === 'processing' ? 'active' : ''; ?>">
                    Processing <span class="count"><?php echo $statusCounts['processing'] ?? 0; ?></span>
                </a>
                <a href="orders.php?status=shipped" class="filter-btn <?php echo $statusFilter === 'shipped' ? 'active' : ''; ?>">
                    Shipped <span class="count"><?php echo $statusCounts['shipped'] ?? 0; ?></span>
                </a>
                <a href="orders.php?status=delivered" class="filter-btn <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">
                    Delivered <span class="count"><?php echo $statusCounts['delivered'] ?? 0; ?></span>
                </a>
                <a href="orders.php?status=cancelled" class="filter-btn <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">
                    Cancelled <span class="count"><?php echo $statusCounts['cancelled'] ?? 0; ?></span>
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="admin-card">
                    <div style="text-align:center; padding:60px 20px; color:#64748b;">
                        <p>No orders found<?php echo $statusFilter ? " with status \"$statusFilter\"" : ""; ?>.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="admin-card">
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Contact</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="company-cell"><?php echo htmlspecialchars($order['company_name']); ?></td>
                                        <td>
                                            <div class="company-cell"><?php echo htmlspecialchars($order['contact_person']); ?></div>
                                            <div class="contact-cell"><?php echo htmlspecialchars($order['email']); ?></div>
                                            <div class="contact-cell"><?php echo htmlspecialchars($order['phone']); ?></div>
                                        </td>
                                        <td class="product-cell"><?php echo htmlspecialchars($order['product_name']); ?></td>
                                        <td class="qty-cell"><?php echo (int)$order['quantity']; ?></td>
                                        <td class="date-cell">
                                            <?php echo date('M d, Y', strtotime($order['required_date'])); ?>
                                            <div class="contact-cell">Created: <?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline-flex; align-items:center; gap:6px;">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                                <select name="status" class="status-select" onchange="this.form.submit()">
                                                    <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                                                        <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if (!empty($order['admin_notes'])): ?>
                                                <span class="notes-preview" title="<?php echo htmlspecialchars($order['admin_notes']); ?>"><?php echo htmlspecialchars($order['admin_notes']); ?></span>
                                            <?php else: ?>
                                                <span class="notes-preview">No notes</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <button class="btn btn-secondary btn-sm" onclick="openNotesModal(<?php echo $order['id']; ?>, `<?php echo htmlspecialchars($order['admin_notes'] ?? '', ENT_QUOTES); ?>`)">
                                                    Notes
                                                </button>
                                                <button class="btn btn-secondary btn-sm" onclick="openDetailsModal(<?php echo htmlspecialchars(json_encode($order), ENT_QUOTES); ?>)">
                                                    View
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete order #<?php echo (int)$order['id']; ?>?');">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Notes Modal -->
    <div class="modal-overlay" id="notesModal">
        <div class="modal">
            <h3>Admin Notes</h3>
            <form method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="update_notes">
                <input type="hidden" name="order_id" id="notesOrderId">
                <textarea name="admin_notes" id="notesTextarea" maxlength="2000" placeholder="Add notes about this order..."></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Notes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal-overlay" id="detailsModal">
        <div class="modal" style="max-width:600px;">
            <h3>Order Details</h3>
            <div id="detailsContent" style="line-height:2; font-size:0.95rem;"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModals()">Close</button>
            </div>
        </div>
    </div>

    <script>
    function openNotesModal(orderId, notes) {
        document.getElementById('notesOrderId').value = orderId;
        document.getElementById('notesTextarea').value = notes || '';
        document.getElementById('notesModal').classList.add('active');
    }

    function openDetailsModal(order) {
        const html = `
            <p><strong>Company:</strong> ${order.company_name}</p>
            <p><strong>Contact:</strong> ${order.contact_person}</p>
            <p><strong>Email:</strong> ${order.email}</p>
            <p><strong>Phone:</strong> ${order.phone}</p>
            <p><strong>Product:</strong> ${order.product_name}</p>
            <p><strong>Quantity:</strong> ${order.quantity}</p>
            <p><strong>Required Date:</strong> ${new Date(order.required_date).toLocaleDateString()}</p>
            <p><strong>Delivery Address:</strong> ${order.delivery_address || 'N/A'}</p>
            <p><strong>Customer Notes:</strong> ${order.notes || 'None'}</p>
            <p><strong>Admin Notes:</strong> ${order.admin_notes || 'None'}</p>
            <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status}</span></p>
            <p><strong>Created:</strong> ${new Date(order.created_at).toLocaleString()}</p>
        `;
        document.getElementById('detailsContent').innerHTML = html;
        document.getElementById('detailsModal').classList.add('active');
    }

    function closeModals() {
        document.getElementById('notesModal').classList.remove('active');
        document.getElementById('detailsModal').classList.remove('active');
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) closeModals();
        });
    });
    </script>
</body>
</html>
