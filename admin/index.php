<?php
require_once 'auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></h1>
                <p>Here's what's happening with your store today.</p>
            </div>

            <?php
            try {
                $db = getDB();
                $stats = [
                    'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
                    'orders' => $db->query("SELECT COUNT(*) FROM bulk_orders")->fetchColumn(),
                    'pending' => $db->query("SELECT COUNT(*) FROM bulk_orders WHERE status='pending'")->fetchColumn(),
                    'messages' => $db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read=0")->fetchColumn(),
                    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                    'revenue' => $db->query("SELECT COALESCE(SUM(price_per_unit * quantity), 0) FROM bulk_orders bo JOIN products p ON bo.product_id = p.id WHERE bo.status='completed'")->fetchColumn(),
                ];
            } catch (PDOException $e) {
                $stats = ['products'=>0, 'orders'=>0, 'pending'=>0, 'messages'=>0, 'users'=>0, 'revenue'=>0];
            }
            ?>

            <div class="stat-cards">
                <a href="products.php" class="stat-card-link">
                    <div class="admin-stat-card">
                        <div class="stat-icon" style="background:rgba(0,212,170,0.1);color:#00d4aa;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo $stats['products']; ?></span>
                            <span class="stat-label">Products</span>
                        </div>
                    </div>
                </a>

                <a href="orders.php" class="stat-card-link">
                    <div class="admin-stat-card">
                        <div class="stat-icon" style="background:rgba(124,58,237,0.1);color:#7c3aed;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo $stats['orders']; ?></span>
                            <span class="stat-label">Total Orders</span>
                        </div>
                    </div>
                </a>

                <a href="orders.php?status=pending" class="stat-card-link">
                    <div class="admin-stat-card">
                        <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo $stats['pending']; ?></span>
                            <span class="stat-label">Pending Orders</span>
                        </div>
                    </div>
                </a>

                <a href="messages.php" class="stat-card-link">
                    <div class="admin-stat-card">
                        <div class="stat-icon" style="background:rgba(239,68,68,0.1);color:#ef4444;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo $stats['messages']; ?></span>
                            <span class="stat-label">Unread Messages</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Quick Actions</h3>
                    <div class="quick-actions">
                        <a href="products.php" class="btn btn-primary btn-sm">Add Product</a>
                        <a href="orders.php" class="btn btn-secondary btn-sm">View Orders</a>
                        <a href="messages.php" class="btn btn-secondary btn-sm">Read Messages</a>
                        <a href="reports.php" class="btn btn-secondary btn-sm">View Reports</a>
                    </div>
                </div>

                <div class="admin-card">
                    <h3>Recent Orders</h3>
                    <?php
                    try {
                        $recent = $db->query("SELECT * FROM bulk_orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
                        if (empty($recent)): ?>
                            <p style="color:var(--text-muted);">No orders yet.</p>
                        <?php else: ?>
                            <div class="admin-table-wrapper">
                                <table class="admin-table">
                                    <thead><tr><th>Company</th><th>Product</th><th>Status</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($recent as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                            <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif;
                    } catch (PDOException $e) { ?>
                        <p style="color:var(--text-muted);">No orders yet.</p>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Tab-scoped session check
    (function() {
        const params = new URLSearchParams(window.location.search);
        const tabToken = params.get('tab');
        if (tabToken) {
            sessionStorage.setItem('adminTabToken', tabToken);
            history.replaceState(null, '', 'index.php');
        }
        const stored = sessionStorage.getItem('adminTabToken');
        if (!stored) {
            window.location.href = 'logout.php';
        }
    })();
    </script>
</body>
</html>
