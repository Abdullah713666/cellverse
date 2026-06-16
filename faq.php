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

<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div style="max-width:800px;">
            <span class="kicker">Help Center</span>
            <h1 data-reveal>Common Questions, Answered.</h1>
            <p style="font-size:var(--text-lg);color:var(--text-secondary);">Search by keyword or browse by topic. Can't find what you need? Our team is one click away.</p>
        </div>
    </div>
</section>

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
            <span class="kicker" style="color:var(--accent-gold-bright);">Still curious</span>
            <h2>Still Have Questions?</h2>
            <p>Our team is here to help. Reach out and we'll get back to you quickly.</p>
            <div class="cta-actions">
                <a href="contact.php" class="btn btn-primary btn-lg btn-arrow">
                    <span>Contact Us</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3l5 5-5 5"/></svg>
                </a>
                <a href="bulk-order.php" class="btn btn-ghost btn-lg">Request a Quote</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
