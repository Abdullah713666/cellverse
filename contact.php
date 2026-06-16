<?php
$page_title = 'Contact CellVerse - Wholesale Mobile Accessories Inquiries';
$page_description = 'Get in touch with CellVerse for wholesale mobile accessory orders, custom branding, bulk pricing, and distribution partnerships. We respond within 24 hours.';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    require_once 'config/init.php';
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
        exit;
    }

    $name = sanitize_input($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = sanitize_input($_POST['phone'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    $errors = [];
    if (empty($name) || strlen($name) < 2) $errors[] = 'Valid name is required';
    if (strlen($name) > 100) $errors[] = 'Name must be under 100 characters';
    if (!$email) $errors[] = 'Valid email is required';
    if (empty($message) || strlen($message) < 10) $errors[] = 'Message must be at least 10 characters';
    if (strlen($message) > 5000) $errors[] = 'Message must be under 5000 characters';

    if ($phone !== '' && !preg_match('/^[+0-9 ()\-]{7,20}$/', $phone)) {
        $errors[] = 'Phone number format is invalid';
    }
    if ($subject !== '' && strlen($subject) > 200) {
        $errors[] = 'Subject must be under 200 characters';
    }

    $wordCount = str_word_count($message);
    if ($wordCount > 250) $errors[] = 'Message must be under 250 words';

    if (!verifyRecaptcha($_POST['g-recaptcha-response'] ?? '')) {
        $errors[] = 'Please complete the CAPTCHA verification';
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contact_submissions (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent. We will reply within 24 hours.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    exit;
}

require_once 'includes/header.php';

$map_lat = getSetting('map_latitude', '31.5204');
$map_lon = getSetting('map_longitude', '74.3587');
$map_embed_url = getSetting('map_embed_url', '');
if ($map_embed_url === '') {
    $map_embed_url = "https://www.openstreetmap.org/export/embed.html?bbox=" . ($map_lon - 0.01) . "%2C" . ($map_lat - 0.01) . "%2C" . ($map_lon + 0.01) . "%2C" . ($map_lat + 0.01) . "&layer=mapnik&marker=" . $map_lat . "%2C" . $map_lon;
}
$recaptcha_site_key = getRecaptchaSiteKey();
?>

<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div style="max-width:800px;">
            <span class="kicker">Get in touch</span>
            <h1 data-reveal>Let's Talk Wholesale.</h1>
            <p style="font-size:var(--text-lg);color:var(--text-secondary);">Questions, quotes, custom catalogs, or just a quick sanity check — we answer every message within one business day.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-form">
                <div style="margin-bottom:var(--spacing-md);">
                    <span class="kicker">Message</span>
                    <h3 style="margin-top:8px;">Send us a message</h3>
                    <p class="section-body" style="color:var(--text-secondary);font-size:0.95rem;margin:8px 0 0;">Fill out the form and our team will get back to you within 24 hours.</p>
                </div>
                <form id="contactForm" action="contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" required maxlength="100" pattern="[A-Za-zÀ-ɏ\s.\-']{2,100}" data-filter-alpha placeholder="Full name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required maxlength="254" placeholder="you@example.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" maxlength="20" pattern="[+0-9 ()\-]{7,20}" placeholder="0300 1234567">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" maxlength="200" placeholder="How can we help?">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message * <small style="color:var(--text-muted);">(max 250 words / 5000 chars)</small></label>
                        <textarea id="message" name="message" rows="5" required maxlength="5000" placeholder="Tell us about your inquiry..."></textarea>
                    </div>

                    <?php if ($recaptcha_site_key): ?>
                    <div class="form-group" style="margin-bottom:var(--spacing-md);">
                        <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptcha_site_key); ?>" data-theme="<?php echo (isset($_COOKIE['cellverse-theme']) && $_COOKIE['cellverse-theme'] === 'dark') ? 'dark' : 'light'; ?>"></div>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Send Message <span class="btn-arrow">&rarr;</span></button>
                </form>
            </div>

            <div class="contact-info">
                <div>
                    <span class="kicker">Reach us</span>
                    <h3 style="margin-top:8px;margin-bottom:var(--spacing-md);">Three ways to talk to a human.</h3>
                </div>
                <div class="contact-info-card">
                    <div class="contact-info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                    </div>
                    <div>
                        <h4>Call Us</h4>
                        <p><?php echo htmlspecialchars(getSetting('contact_phone', '+92 300 1234567')); ?></p>
                    </div>
                </div>

                <div class="map-container">
                    <iframe width="600" height="400" src="<?php echo htmlspecialchars($map_embed_url); ?>" loading="lazy" title="Office location map" style="border:0;" referrerpolicy="no-referrer"></iframe>
                    <div class="map-overlay" aria-hidden="true">
                        <svg viewBox="0 0 200 120" preserveAspectRatio="none">
                            <defs>
                                <pattern id="mapGrid" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(0,212,170,0.18)" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="200" height="120" fill="url(#mapGrid)"/>
                            <path d="M0,70 Q50,40 100,55 T200,30" stroke="rgba(0,212,170,0.4)" stroke-width="0.8" fill="none"/>
                            <path d="M0,90 Q60,70 120,85 T200,60" stroke="rgba(124,58,237,0.35)" stroke-width="0.8" fill="none"/>
                            <circle cx="100" cy="55" r="6" fill="rgba(0,212,170,0.2)"/>
                            <circle cx="100" cy="55" r="3" fill="var(--color-primary)"/>
                        </svg>
                        <div class="map-pulse"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header section-header--center">
            <span class="kicker kicker--center">How we work</span>
            <h2 class="section-title">From your first message to your first shipment.</h2>
            <p class="section-body">Every wholesale inquiry goes through the same streamlined path so you know exactly what happens next.</p>
        </div>
        <div class="contact-paths">
            <div class="bulk-process-step">
                <div class="step-badge step-badge--teal">01</div>
                <h3>Send your inquiry</h3>
                <p>Use the form, call us, or message on WhatsApp with your product list and delivery city.</p>
            </div>
            <div class="bulk-process-step">
                <div class="step-badge step-badge--purple">02</div>
                <h3>Receive a tailored quote</h3>
                <p>Within 24 hours we reply with stock, tiered pricing, and freight options.</p>
            </div>
            <div class="bulk-process-step">
                <div class="step-badge step-badge--amber">03</div>
                <h3>Confirm and pay</h3>
                <p>Approve the pricing and settle via bank transfer, cheque, or Letter of Credit.</p>
            </div>
            <div class="bulk-process-step">
                <div class="step-badge step-badge--teal">04</div>
                <h3>Stock and ship</h3>
                <p>We QC every unit, pack to spec, and dispatch within 48 hours with tracking.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-banner">
            <span class="kicker" style="color:var(--accent-gold-bright);">In a hurry</span>
            <h2>Skip the form. Request a quote.</h2>
            <p>For volume pricing, custom catalogs, and stock holds, jump straight to our bulk order desk.</p>
            <div class="cta-actions">
                <a href="bulk-order.php" class="btn btn-primary btn-lg btn-arrow">
                    <span>Open Bulk Order</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3l5 5-5 5"/></svg>
                </a>
                <a href="faq.php" class="btn btn-ghost btn-lg">Read the FAQ</a>
            </div>
        </div>
    </div>
</section>

<?php if ($recaptcha_site_key): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>
