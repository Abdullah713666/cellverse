<?php
$page_title = 'Frequently Asked Questions - CellVerse Wholesale';
$page_description = 'Answers to common questions about CellVerse wholesale mobile accessories: ordering, MOQ, payment terms, delivery, returns, and bulk pricing in Pakistan.';
require_once 'includes/header.php';

try {
    $db = getDB();
    $faqs = $db->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY display_order, category")->fetchAll();
} catch (PDOException $e) {
    $faqs = [];
}

// Group by category
$grouped = [];
foreach ($faqs as $faq) {
    $cat = $faq['category'] ?? 'General';
    $grouped[$cat][] = $faq;
}
?>

<div class="page-hero">
    <div class="grid-overlay"></div>
    <div class="hero-orb hero-orb--a"></div>
    <div class="hero-orb hero-orb--b"></div>
    <div class="container">
        <div class="page-hero-content">
            <span class="kicker kicker--center">Help Center</span>
            <h1 class="hero-line">
                <span class="hero-word">Common</span>
                <span class="hero-word">Questions,</span>
                <span class="hero-word hero-word--accent">Answered.</span>
            </h1>
            <p class="page-hero-sub">Search by keyword or browse by topic. Can't find what you need? Our team is one click away.</p>
        </div>
    </div>
    <div class="page-hero-variant page-hero-variant--faq fade-up" aria-hidden="true">
        <span class="floating-q floating-q--a">?</span>
        <span class="floating-q floating-q--b">?</span>
        <span class="floating-q floating-q--c">?</span>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="faq-search">
            <div class="search-box">
                <label for="faqSearch" class="visually-hidden">Search frequently asked questions</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="faqSearch" placeholder="Search questions..." autocomplete="off" aria-label="Search frequently asked questions">
            </div>
        </div>

        <?php if (empty($faqs)): ?>
            <div style="text-align:center;padding:var(--spacing-xl);color:var(--text-muted);">
                <p>No FAQs available yet. Please run the installer or add FAQs from the admin panel.</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $category => $items): ?>
                <div class="faq-category">
                    <span class="faq-category-label"><?php echo htmlspecialchars($category); ?></span>
                    <span class="faq-category-count"><?php echo count($items); ?></span>
                </div>
                <div class="faq-list">
                    <?php foreach ($items as $faq): ?>
                        <div class="faq-item">
                            <button class="faq-question" aria-expanded="false">
                                <span><?php echo htmlspecialchars($faq['question']); ?></span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-banner">
            <span class="cta-orb cta-orb--a"></span>
            <span class="cta-orb cta-orb--b"></span>
            <span class="kicker kicker--center">Still curious</span>
            <h2>Still Have Questions?</h2>
            <p>Our team is here to help. Reach out and we'll get back to you quickly.</p>
            <div class="cta-actions">
                <a href="contact.php" class="btn btn-primary btn-lg magnetic">Contact Us <span class="btn-arrow">&rarr;</span></a>
                <a href="bulk-order.php" class="btn btn-ghost btn-lg magnetic">Request a Quote</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
