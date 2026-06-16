<?php
require_once 'auth.php';
requireLogin();

$db = getDB();

// Monthly orders (last 6 months)
$monthlyData = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
           DATE_FORMAT(created_at, '%b %Y') AS month_label,
           COUNT(*) AS order_count
    FROM bulk_orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_key, month_label
    ORDER BY month_key ASC
")->fetchAll();

// Orders by status
$statusData = $db->query("
    SELECT status, COUNT(*) AS count
    FROM bulk_orders
    GROUP BY status
    ORDER BY count DESC
")->fetchAll();

// Top 5 products by order quantity
$topProductsData = $db->query("
    SELECT product_name AS name, SUM(quantity) AS total_qty
    FROM bulk_orders
    WHERE product_name IS NOT NULL
    GROUP BY product_name
    ORDER BY total_qty DESC
    LIMIT 5
")->fetchAll();

// Messages over time (last 6 months)
$messagesData = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
           DATE_FORMAT(created_at, '%b %Y') AS month_label,
           COUNT(*) AS message_count
    FROM contact_submissions
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_key, month_label
    ORDER BY month_key ASC
")->fetchAll();

// Summary stats
$totalOrders = $db->query("SELECT COUNT(*) FROM bulk_orders")->fetchColumn();
$totalRevenue = $db->query("SELECT COALESCE(SUM(price_per_unit * quantity), 0) FROM bulk_orders bo JOIN products p ON bo.product_id = p.id")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();

// Prepare chart data
$monthLabels = array_column($monthlyData, 'month_label');
$monthCounts = array_column($monthlyData, 'order_count');

$statusLabels = array_column($statusData, 'status');
$statusCountsArr = array_column($statusData, 'count');
$statusColors = ['#00d4aa', '#7c3aed', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#10b981', '#6366f1'];

$productNames = array_column($topProductsData, 'name');
$productQty = array_column($topProductsData, 'total_qty');

$messLabels = array_column($messagesData, 'month_label');
$messCounts = array_column($messagesData, 'message_count');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CellVerse Admin</title>
    <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
        .stat-card {
            background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 20px;
            border-left: 4px solid #00d4aa;
        }
        .stat-card .label { font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
        .stat-card .value { font-size: 28px; font-weight: 700; margin-top: 4px; color: #f1f5f9; font-family: 'Space Grotesk', sans-serif; }
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .chart-card {
            background: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 24px;
        }
        .chart-card h3 { font-size: 16px; margin-bottom: 16px; color: #e2e8f0; font-family: 'Space Grotesk', sans-serif; }
        .chart-card canvas { width: 100% !important; }
        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1>Reports &amp; Analytics</h1>
                <p>Overview of your store performance</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Total Orders</div>
                    <div class="value"><?php echo number_format($totalOrders); ?></div>
                </div>
                <div class="stat-card" style="border-left-color:#7c3aed;">
                    <div class="label">Total Revenue</div>
                    <div class="value">$<?php echo number_format($totalRevenue, 2); ?></div>
                </div>
                <div class="stat-card" style="border-left-color:#f59e0b;">
                    <div class="label">Products</div>
                    <div class="value"><?php echo number_format($totalProducts); ?></div>
                </div>
                <div class="stat-card" style="border-left-color:#ef4444;">
                    <div class="label">Messages</div>
                    <div class="value"><?php echo number_format($totalMessages); ?></div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Monthly Orders</h3>
                    <canvas id="monthlyChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Orders by Status</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Top Products</h3>
                    <canvas id="productsChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Messages Over Time</h3>
                    <canvas id="messagesChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
    const colors = {
        primary: '#00d4aa',
        secondary: '#7c3aed',
        tertiary: '#f59e0b',
        danger: '#ef4444',
        blue: '#3b82f6',
        grid: 'rgba(148,163,184,.1)',
        text: '#94a3b8'
    };

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { labels: { color: colors.text, padding: 16 } }
        },
        scales: {
            x: { ticks: { color: colors.text }, grid: { color: colors.grid } },
            y: { ticks: { color: colors.text }, grid: { color: colors.grid } }
        }
    };

    // Monthly Orders
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($monthLabels, JSON_HEX_TAG); ?>,
            datasets: [{
                label: 'Orders',
                data: <?php echo json_encode($monthCounts, JSON_HEX_TAG); ?>,
                backgroundColor: colors.primary,
                borderRadius: 6
            }]
        },
        options: defaultOptions
    });

    // Orders by Status
    const statusColorsArr = <?php echo json_encode($statusColors, JSON_HEX_TAG); ?>;
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($statusLabels, JSON_HEX_TAG); ?>,
            datasets: [{
                data: <?php echo json_encode($statusCountsArr, JSON_HEX_TAG); ?>,
                backgroundColor: statusColorsArr.slice(0, <?php echo count($statusLabels); ?>),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'right', labels: { color: colors.text, padding: 12, usePointStyle: true } }
            },
            cutout: '60%'
        }
    });

    // Top Products
    new Chart(document.getElementById('productsChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($productNames, JSON_HEX_TAG); ?>,
            datasets: [{
                label: 'Quantity Ordered',
                data: <?php echo json_encode($productQty, JSON_HEX_TAG); ?>,
                backgroundColor: [colors.primary, colors.secondary, colors.tertiary, colors.danger, colors.blue],
                borderRadius: 6
            }]
        },
        options: {
            ...defaultOptions,
            indexAxis: 'y'
        }
    });

    // Messages Over Time
    new Chart(document.getElementById('messagesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($messLabels, JSON_HEX_TAG); ?>,
            datasets: [{
                label: 'Messages',
                data: <?php echo json_encode($messCounts, JSON_HEX_TAG); ?>,
                borderColor: colors.secondary,
                backgroundColor: 'rgba(124,58,237,.15)',
                fill: true,
                tension: .4,
                pointBackgroundColor: colors.secondary,
                pointRadius: 4
            }]
        },
        options: defaultOptions
    });
    </script>
</body>
</html>
