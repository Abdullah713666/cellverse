<?php
require_once 'config/init.php';
$page_title = 'Wholesale Mobile Accessories Catalog - CellVerse';
$page_description = 'Browse CellVerse\'s complete wholesale catalog: phone cases, chargers, earphones, power banks, cables, and screen protectors. Bulk pricing for every category.';
if (isset($_GET['category']) && preg_match('/^[a-z0-9\-]{1,40}$/', (string)$_GET['category'])) {
    $absolute_base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $page_canonical = $absolute_base . BASE_URL . '/products.php';
    $page_robots = 'noindex, follow';
}
require_once 'includes/header.php';

// Fetch categories
try {
    $db = getDB();
    $categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Fetch products
try {
    $db = getDB();
    $products = $db->query("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status != 'discontinued' 
        ORDER BY p.is_featured DESC, p.created_at DESC
    ")->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>

<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div style="max-width:800px;">
            <span class="kicker">Catalog</span>
            <h1 data-reveal>The Complete <em>Wholesale</em><br>Catalog.</h1>
            <p style="font-size:var(--text-lg);color:var(--text-secondary);">500+ mobile accessories, one supplier. Filter by category, search by name, quote by the case.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="pill-group" data-reveal>
            <button class="pill active" data-filter="all">All</button>
            <?php foreach ($categories as $cat): ?>
                <button class="pill" data-filter="<?php echo htmlspecialchars($cat['slug']); ?>"><?php echo htmlspecialchars($cat['name']); ?></button>
            <?php endforeach; ?>
        </div>
        <div style="position:relative;max-width:400px;margin:var(--space-6) 0;" data-reveal>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--text-muted);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="productSearch" placeholder="Search products..." autocomplete="off" aria-label="Search products" style="width:100%;padding:0.75rem 1rem 0.75rem 2.5rem;background:var(--bg-surface);border:1.5px solid var(--border-color);border-radius:var(--radius-sm);color:var(--text-primary);">
        </div>

        <?php if (empty($products)): ?>
            <div style="text-align:center;padding:var(--space-16);color:var(--text-muted);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:16px;opacity:0.5;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <p>No products found. Please run the installer first.</p>
                <a href="install.php" class="btn btn-ghost" style="margin-top:16px;">Run Installer</a>
            </div>
        <?php else: ?>
            <div class="grid-auto">
                <?php foreach ($products as $product): ?>
                    <article class="card card-hover product-card" data-product data-category="<?php echo htmlspecialchars($product['category_slug'] ?? ''); ?>">
                        <div class="product-card-image" data-lightbox="<?php echo $product['image_path'] ? BASE_URL . '/' . htmlspecialchars($product['image_path']) : ''; ?>">
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy" data-product-img>
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-surface-high);color:var(--text-muted);">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                </div>
                            <?php endif; ?>
                            <?php if ($product['is_featured']): ?>
                                <span class="product-badge" style="background:var(--accent-warning);">Featured</span>
                            <?php endif; ?>
                            <?php if ($product['stock_qty'] <= 0): ?>
                                <span class="product-badge product-badge--sold" style="left:auto;right:var(--space-3);">Out of Stock</span>
                            <?php elseif ($product['stock_qty'] < 20): ?>
                                <span class="product-badge" style="left:auto;right:var(--space-3);background:var(--accent-warning);">Low Stock</span>
                            <?php endif; ?>
                            <div class="product-overlay">
                                <a href="bulk-order.php?product=<?php echo (int)$product['id']; ?>" class="btn btn-sm btn-gold">
                                    <span>Get a price</span>
                                </a>
                            </div>
                        </div>
                        <div class="product-card-body">
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'General'); ?></span>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-meta">
                                <span class="product-price">$<?php echo number_format($product['price_per_unit'], 2); ?></span>
                                <span class="product-moq">MOQ <?php echo (int)$product['moq']; ?> pcs</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-banner">
            <h2>Can't Find What You Need?</h2>
            <p>Contact us for custom orders and special pricing on bulk purchases</p>
            <div class="cta-actions">
                <a href="contact.php" class="btn btn-primary btn-lg btn-arrow">
                    <span>Contact Us</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
