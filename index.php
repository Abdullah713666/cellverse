<?php
$page_title = 'CellVerse - Bulk Mobile Accessories at Wholesale Prices';
$page_description = 'Pakistan\'s trusted wholesale supplier of premium mobile accessories. Cases, chargers, earphones, power banks at competitive bulk rates for retailers.';
require_once 'includes/header.php';

// Fetch featured products
$featured_products = [];
try {
    $db = getDB();
    if ($db) {
        $stmt = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 AND p.status = 'available' ORDER BY p.created_at DESC LIMIT 6");
        $featured_products = $stmt->fetchAll();
    }
} catch (Exception $e) {}

// Fetch testimonials
$testimonials = [];
try {
    $db = getDB();
    if ($db) {
        $stmt = $db->query("SELECT * FROM testimonials WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 3");
        $testimonials = $stmt->fetchAll();
    }
} catch (Exception $e) {}
?>

<section class="section hero-section" id="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <span class="kicker">Wholesale Mobile Accessories</span>
                <h1 data-reveal><em>Bulk</em> Mobile Accessories,<br><em>Wholesale</em> Pricing.</h1>
                <p class="hero-desc">Your trusted supplier for premium mobile accessories. Cases, chargers, earphones and more, delivered at competitive bulk rates for businesses that scale.</p>
                <div class="cta-actions cta-left">
                    <a href="products.php" class="btn btn-primary btn-lg btn-arrow">
                        <span>Browse Products</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                    <a href="bulk-order.php" class="btn btn-ghost btn-lg">Request Quote</a>
                </div>
                <div class="hero-trust">
                    <div class="hero-avatars">
                        <span class="hero-avatar" style="background:var(--accent-navy);">A</span>
                        <span class="hero-avatar" style="background:var(--accent-gold);">M</span>
                        <span class="hero-avatar" style="background:var(--accent-navy);">S</span>
                        <span class="hero-avatar" style="background:var(--accent-gold);">K</span>
                    </div>
                    <div class="hero-trust-text"><strong>2,000+</strong> <span>retailers stocked monthly</span></div>
                </div>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <canvas id="heroCanvas"></canvas>
            </div>
        </div>
    </div>
</section>

<div class="marquee" aria-hidden="true" style="padding:var(--space-8) 0;">
    <div class="marquee-track">
        <div class="marquee-group">
            <span class="marquee-item">Premium Quality</span>
            <span class="marquee-item">Wholesale Pricing</span>
            <span class="marquee-item">Bulk Supply</span>
            <span class="marquee-item">Fast Dispatch</span>
            <span class="marquee-item">Verified Suppliers</span>
            <span class="marquee-item">Wide Catalog</span>
            <span class="marquee-item">Nationwide Shipping</span>
            <span class="marquee-item">Trusted by Retailers</span>
        </div>
        <div class="marquee-group" aria-hidden="true">
            <span class="marquee-item">Premium Quality</span>
            <span class="marquee-item">Wholesale Pricing</span>
            <span class="marquee-item">Bulk Supply</span>
            <span class="marquee-item">Fast Dispatch</span>
            <span class="marquee-item">Verified Suppliers</span>
            <span class="marquee-item">Wide Catalog</span>
            <span class="marquee-item">Nationwide Shipping</span>
            <span class="marquee-item">Trusted by Retailers</span>
        </div>
    </div>
</div>

<div class="container"><div class="section-divider"></div></div>

<section class="section" id="stats">
    <div class="container">
        <div class="section-header">
            <span class="kicker" data-reveal>By the numbers</span>
            <h2 data-reveal>Built for scale, trusted for quality</h2>
        </div>
        <div class="grid-4">
            <div class="card stat-card" data-count="500" data-suffix="+">
                <div class="stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8 10-4-4"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-number"><span class="count-value">0</span><span class="count-suffix">+</span></div>
                    <div class="stat-label">Products stocked</div>
                </div>
            </div>
            <div class="card stat-card" data-count="2000" data-suffix="+">
                <div class="stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-number"><span class="count-value">0</span><span class="count-suffix">+</span></div>
                    <div class="stat-label">Active retailers</div>
                </div>
            </div>
            <div class="card stat-card" data-count="50" data-suffix="+">
                <div class="stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-number"><span class="count-value">0</span><span class="count-suffix">+</span></div>
                    <div class="stat-label">Top brands</div>
                </div>
            </div>
            <div class="card stat-card" data-count="10" data-suffix="+">
                <div class="stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-number"><span class="count-value">0</span><span class="count-suffix">+</span></div>
                    <div class="stat-label">Years operating</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featured_products)): ?>
<section class="section" id="featured">
    <div class="container">
        <div class="section-header">
            <span class="kicker" data-reveal>Featured catalog</span>
            <h2 data-reveal>Most popular this season</h2>
            <p data-reveal>Discover the wholesale accessories moving fastest through our retail network right now.</p>
        </div>
        <div class="grid-auto" data-reveal>
            <?php foreach ($featured_products as $product): ?>
            <article class="card card-hover product-card" data-product>
                <div class="product-card-image">
                    <?php if ($product['image_path']): ?>
                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy" data-product-img>
                    <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-surface-high);color:var(--text-muted);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        </div>
                    <?php endif; ?>
                    <span class="product-badge">Featured</span>
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
                        <span class="product-moq">Min. order <?php echo (int)$product['moq']; ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="section-cta" data-reveal>
            <a href="products.php" class="btn btn-ghost btn-arrow">
                <span>View all products</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="container"><div class="section-divider"></div></div>

<section class="section" id="how">
    <div class="container">
        <div class="section-header">
            <span class="kicker" data-reveal>How it works</span>
            <h2 data-reveal>From catalog to doorstep in three steps</h2>
            <p data-reveal>A short, predictable flow. We handle the logistics so you can focus on your retail floor.</p>
        </div>
        <div class="grid-3" data-reveal>
            <div class="step-card">
                <div class="step-number">01</div>
                <div class="step-content">
                    <h3>Browse the catalog</h3>
                    <p>Filter by category, brand, or minimum order. Every product page shows live wholesale pricing.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-number">02</div>
                <div class="step-content">
                    <h3>Request your quote</h3>
                    <p>Send quantities for the items you need. We respond within four business hours with tiered pricing and shipping options.</p>
                </div>
            </div>
            <div class="step-card">
                <div class="step-number">03</div>
                <div class="step-content">
                    <h3>Bulk delivery</h3>
                    <p>Confirm, pay, and we dispatch within 24 to 48 hours. Track the shipment from our warehouse to your store.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container"><div class="section-divider"></div></div>

<?php if (!empty($testimonials)): ?>
<section class="section" id="testimonials">
    <div class="container">
        <div class="section-header">
            <span class="kicker" data-reveal>Client voices</span>
            <h2 data-reveal>What retailers say about CellVerse</h2>
        </div>
        <div class="testimonial-carousel" data-carousel data-reveal>
            <div class="testimonial-track" data-carousel-track>
                <?php foreach ($testimonials as $i => $testimonial): ?>
                <article class="testimonial-card" data-slide="<?php echo $i; ?>">
                    <div class="testimonial-stars" aria-label="5 out of 5 stars">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
                    </div>
                    <blockquote><?php echo htmlspecialchars($testimonial['quote']); ?></blockquote>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">
                            <?php echo strtoupper(substr($testimonial['client_name'], 0, 1)); ?>
                        </div>
                        <div class="testimonial-info">
                            <h4><?php echo htmlspecialchars($testimonial['client_name']); ?></h4>
                            <p><?php echo htmlspecialchars($testimonial['client_title'] . ($testimonial['company'] ? ' at ' . $testimonial['company'] : '')); ?></p>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-controls" style="display:flex;align-items:center;justify-content:center;gap:var(--space-4);margin-top:var(--space-6);">
                <button class="testimonial-dot<?php echo count($testimonials) > 1 ? '' : ''; ?>" data-carousel-prev aria-label="Previous testimonial" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);border-radius:var(--radius-sm);color:var(--text-secondary);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
                <div class="testimonial-dots" data-carousel-dots style="display:flex;gap:var(--space-2);">
                    <?php foreach ($testimonials as $i => $t): ?>
                    <button class="testimonial-dot<?php echo $i === 0 ? ' active' : ''; ?>" data-carousel-dot="<?php echo $i; ?>" aria-label="Go to testimonial <?php echo $i + 1; ?>" style="width:10px;height:10px;border-radius:50%;border:1px solid var(--border-color);background:transparent;padding:0;cursor:pointer;transition:all var(--duration-fast) var(--ease-smooth);"></button>
                    <?php endforeach; ?>
                </div>
                <button class="testimonial-dot" data-carousel-next aria-label="Next testimonial" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);border-radius:var(--radius-sm);color:var(--text-secondary);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="container"><div class="section-divider"></div></div>

<section class="section" data-reveal>
    <div class="container">
        <div class="cta-banner">
            <span class="kicker" style="color:var(--accent-gold-bright);filter:brightness(1.2);">Ready when you are</span>
            <h2>Place a bulk order today</h2>
            <p>Competitive wholesale pricing across the full catalog. Submit a request and our team responds within hours.</p>
            <div class="cta-actions">
                <a href="bulk-order.php" class="btn btn-primary btn-lg btn-arrow">
                    <span>Request bulk quote</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                <a href="contact.php" class="btn btn-ghost btn-lg">Talk to sales</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
