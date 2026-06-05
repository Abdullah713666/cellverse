<?php
$page_title = 'CellVerse - Bulk Mobile Accessories at Wholesale Prices';
$page_description = 'Pakistan\'s trusted wholesale supplier of premium mobile accessories. Cases, chargers, earphones, power banks at competitive bulk rates for retailers.';
require_once 'includes/header.php';

// Fetch featured products
try {
    $db = getDB();
    $stmt = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 AND p.status = 'available' ORDER BY p.created_at DESC LIMIT 6");
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
}

// Fetch testimonials
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM testimonials WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 3");
    $testimonials = $stmt->fetchAll();
} catch (PDOException $e) {
    $testimonials = [];
}
?>

<section class="hero" id="hero">
    <div class="hero-bg">
        <div class="grid-overlay"></div>
        <div class="gradient-mesh"></div>
        <div class="ambient-glow" aria-hidden="true"></div>
    </div>
    <div class="container hero-grid">
        <div class="hero-content">
            <span class="hero-tag reveal" data-reveal-delay="0">
                <span class="hero-tag-dot"></span>
                Wholesale Mobile Accessories
            </span>
            <h1 class="hero-title">
                <span class="hero-line"><span class="hero-word reveal" data-reveal-delay="1">Bulk</span> <span class="hero-word reveal" data-reveal-delay="2">Mobile</span></span>
                <span class="hero-line"><span class="hero-word reveal" data-reveal-delay="3">Accessories,</span></span>
                <span class="hero-line"><span class="hero-word hero-word--accent reveal" data-reveal-delay="4">Wholesale</span> <span class="hero-word reveal" data-reveal-delay="5">Pricing.</span></span>
            </h1>
            <p class="reveal" data-reveal-delay="6">Your trusted supplier for premium mobile accessories. Cases, chargers, earphones and more, delivered at competitive bulk rates for businesses that scale.</p>
            <div class="hero-buttons reveal" data-reveal-delay="7">
                <a href="products.php" class="btn btn-primary btn-lg magnetic">
                    <span>Browse Products</span>
                    <svg class="btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                <a href="bulk-order.php" class="btn btn-ghost btn-lg">
                    <span>Request Quote</span>
                </a>
            </div>
            <div class="hero-trust reveal" data-reveal-delay="8">
                <div class="hero-trust-avatars">
                    <span class="hero-trust-avatar" style="--c:#00d4aa">A</span>
                    <span class="hero-trust-avatar" style="--c:#7c3aed">M</span>
                    <span class="hero-trust-avatar" style="--c:#f59e0b">S</span>
                    <span class="hero-trust-avatar" style="--c:#ef4444">K</span>
                </div>
                <div class="hero-trust-text">
                    <strong>2,000+</strong> retailers stocked monthly
                </div>
            </div>
        </div>
        <div class="hero-visual" data-parallax="0.08">
            <div class="hero-card hero-card--main border-beam">
                <div class="hero-card-glow"></div>
                <div class="hero-card-header">
                    <div class="hero-card-dots" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="hero-card-title">Live Order Feed</div>
                </div>
                <div class="ticker-viewport" role="region" aria-label="Live order distribution by category">
                    <ul class="ticker-track" id="ticker">
                        <li class="ticker-item">
                            <span class="hero-card-pill" style="--c:#00d4aa">Cases</span>
                            <span class="hero-card-bar"><span style="width:86%"></span></span>
                            <span class="hero-card-val">2,400</span>
                        </li>
                        <li class="ticker-item">
                            <span class="hero-card-pill" style="--c:#7c3aed">Chargers</span>
                            <span class="hero-card-bar"><span style="width:64%"></span></span>
                            <span class="hero-card-val">1,820</span>
                        </li>
                        <li class="ticker-item">
                            <span class="hero-card-pill" style="--c:#f59e0b">Earphones</span>
                            <span class="hero-card-bar"><span style="width:48%"></span></span>
                            <span class="hero-card-val">1,310</span>
                        </li>
                        <li class="ticker-item">
                            <span class="hero-card-pill" style="--c:#ef4444">Power</span>
                            <span class="hero-card-bar"><span style="width:32%"></span></span>
                            <span class="hero-card-val">910</span>
                        </li>
                        <li class="ticker-item" aria-hidden="true">
                            <span class="hero-card-pill" style="--c:#00d4aa">Cases</span>
                            <span class="hero-card-bar"><span style="width:86%"></span></span>
                            <span class="hero-card-val">2,400</span>
                        </li>
                        <li class="ticker-item" aria-hidden="true">
                            <span class="hero-card-pill" style="--c:#7c3aed">Chargers</span>
                            <span class="hero-card-bar"><span style="width:64%"></span></span>
                            <span class="hero-card-val">1,820</span>
                        </li>
                        <li class="ticker-item" aria-hidden="true">
                            <span class="hero-card-pill" style="--c:#f59e0b">Earphones</span>
                            <span class="hero-card-bar"><span style="width:48%"></span></span>
                            <span class="hero-card-val">1,310</span>
                        </li>
                        <li class="ticker-item" aria-hidden="true">
                            <span class="hero-card-pill" style="--c:#ef4444">Power</span>
                            <span class="hero-card-bar"><span style="width:32%"></span></span>
                            <span class="hero-card-val">910</span>
                        </li>
                    </ul>
                </div>
                <div class="hero-card-foot">
                    <span class="hero-card-dot live"></span>
                    <span>Real-time wholesale distribution</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container"><div class="section-divider"></div></div>

<div class="marquee" aria-hidden="true">
    <div class="marquee-track">
        <div class="marquee-group">
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg> Premium Quality</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg> Wholesale Pricing</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20"/></svg> Bulk Supply</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Fast Dispatch</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg> Verified Suppliers</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3zM3 9h18M9 21V9"/></svg> Wide Catalog</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg> Nationwide Shipping</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Trusted by Retailers</span>
        </div>
        <div class="marquee-group" aria-hidden="true">
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg> Premium Quality</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg> Wholesale Pricing</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20"/></svg> Bulk Supply</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Fast Dispatch</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg> Verified Suppliers</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3zM3 9h18M9 21V9"/></svg> Wide Catalog</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg> Nationwide Shipping</span>
            <span class="marquee-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Trusted by Retailers</span>
        </div>
    </div>
</div>

<div class="container"><div class="section-divider"></div></div>

<section class="section stats-section" id="stats">
    <div class="container">
        <div class="stats-header">
            <span class="kicker reveal" data-reveal-delay="0">By the numbers</span>
            <h2 class="reveal" data-reveal-delay="1">Built for scale, trusted for quality</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card border-beam reveal" data-count="500" data-suffix="+" data-reveal-delay="0">
                <div class="stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8 10-4-4"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-number"><span class="count-value">0</span><span class="count-suffix">+</span></div>
                    <div class="stat-label">Products stocked</div>
                </div>
            </div>
            <div class="stat-card border-beam--bright reveal" data-count="2000" data-suffix="+" data-reveal-delay="1">
                <div class="stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-number"><span class="count-value">0</span><span class="count-suffix">+</span></div>
                    <div class="stat-label">Active retailers</div>
                </div>
            </div>
            <div class="stat-card border-beam reveal" data-count="50" data-suffix="+" data-reveal-delay="2">
                <div class="stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-number"><span class="count-value">0</span><span class="count-suffix">+</span></div>
                    <div class="stat-label">Top brands</div>
                </div>
            </div>
            <div class="stat-card border-beam reveal" data-count="10" data-suffix="+" data-reveal-delay="3">
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

<div class="container"><div class="section-divider"></div></div>

<?php if (!empty($featured_products)): ?>
<section class="section" id="featured">
    <div class="container">
        <div class="section-header section-header--lg">
            <span class="kicker">Featured catalog</span>
            <h2>Most popular this season</h2>
            <p>Discover the wholesale accessories moving fastest through our retail network right now.</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <article class="product-card product-card" data-product>
                <div class="product-card-image">
                    <?php if ($product['image_path']): ?>
                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy" data-product-img>
                    <?php else: ?>
                        <div class="product-card-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        </div>
                    <?php endif; ?>
                    <span class="product-badge">Featured</span>
                    <div class="product-card-overlay">
                        <a href="bulk-order.php?product=<?php echo (int)$product['id']; ?>" class="product-quick-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                            Get a price
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
        <div class="section-cta">
            <a href="products.php" class="btn btn-ghost">
                <span>View all products</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="container"><div class="section-divider"></div></div>

<section class="section steps-section" id="how">
    <div class="container">
        <div class="section-header section-header--lg">
            <span class="kicker">How it works</span>
            <h2>From catalog to doorstep in three steps</h2>
            <p>A short, predictable flow. We handle the logistics so you can focus on your retail floor.</p>
        </div>
        <div class="steps-wrap">
            <div class="steps-rail" aria-hidden="true">
                <div class="steps-rail-fill" data-steps-fill></div>
            </div>
            <div class="steps-grid">
                <div class="step-card" data-step>
                    <div class="step-number"><span>01</span></div>
                    <div class="step-content">
                        <h3>Browse the catalog</h3>
                        <p>Filter by category, brand, or minimum order. Save items to a list as you go. Every product page shows live wholesale pricing.</p>
                    </div>
                </div>
                <div class="step-card" data-step>
                    <div class="step-number"><span>02</span></div>
                    <div class="step-content">
                        <h3>Request your quote</h3>
                        <p>Send quantities for the items you need. We respond within four business hours with tiered pricing and shipping options.</p>
                    </div>
                </div>
                <div class="step-card" data-step>
                    <div class="step-number"><span>03</span></div>
                    <div class="step-content">
                        <h3>Bulk delivery</h3>
                        <p>Confirm, pay, and we dispatch within 24 to 48 hours. Track the shipment from our warehouse to your store.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container"><div class="section-divider"></div></div>

<?php if (!empty($testimonials)): ?>
<section class="section testimonials-section" id="testimonials">
    <div class="container">
        <div class="section-header section-header--lg">
            <span class="kicker">Client voices</span>
            <h2>What retailers say about CellVerse</h2>
        </div>
        <div class="testimonial-carousel" data-carousel>
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
                    <p class="testimonial-quote"><?php echo htmlspecialchars($testimonial['quote']); ?></p>
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
            <div class="testimonial-controls">
                <button class="testimonial-arrow" data-carousel-prev aria-label="Previous testimonial">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
                <div class="testimonial-dots" data-carousel-dots>
                    <?php foreach ($testimonials as $i => $t): ?>
                    <button class="testimonial-dot<?php echo $i === 0 ? ' active' : ''; ?>" data-carousel-dot="<?php echo $i; ?>" aria-label="Go to testimonial <?php echo $i + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <button class="testimonial-arrow" data-carousel-next aria-label="Next testimonial">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="container"><div class="section-divider"></div></div>

<section class="section">
    <div class="container">
        <div class="cta-banner">
            <div class="cta-orb cta-orb--a"></div>
            <div class="cta-orb cta-orb--b"></div>
            <div class="cta-content">
                <span class="kicker">Ready when you are</span>
                <h2>Place a bulk order today</h2>
                <p>Competitive wholesale pricing across the full catalog. Submit a request and our team responds within hours.</p>
                <div class="cta-actions">
                    <a href="bulk-order.php" class="btn btn-primary btn-lg">
                        <span>Request bulk quote</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                    <a href="contact.php" class="btn btn-ghost btn-lg">Talk to sales</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
