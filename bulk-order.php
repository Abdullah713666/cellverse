<?php
require_once 'config/init.php';
$page_title = 'Bulk Order Request - Wholesale Mobile Accessories';
$page_description = 'Submit your bulk mobile accessory order to CellVerse. Get custom pricing, minimum-order guidance, and delivery timelines for wholesale purchases across Pakistan.';
if (isset($_GET['product']) && ctype_digit((string)$_GET['product'])) {
    $absolute_base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $page_canonical = $absolute_base . BASE_URL . '/bulk-order.php';
    $page_robots = 'noindex, follow';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');

    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
        exit;
    }

    // Validate inputs
    $company_name = sanitize_input($_POST['company_name'] ?? '');
    $contact_person = sanitize_input($_POST['contact_person'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = sanitize_input($_POST['phone'] ?? '');
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $required_date = $_POST['required_date'] ?? '';
    $delivery_address = sanitize_input($_POST['delivery_address'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');

    $errors = [];
    if (empty($company_name) || strlen($company_name) > 150) $errors[] = 'Company name is required (max 150 chars)';
    if (empty($contact_person) || strlen($contact_person) > 100) $errors[] = 'Contact person is required (max 100 chars)';
    if (!$email) $errors[] = 'Valid email is required';
    if ($phone !== '' && !preg_match('/^[+0-9 ()\-]{7,20}$/', $phone)) $errors[] = 'Phone number format is invalid';
    if ($product_id <= 0) $errors[] = 'Please select a product';
    if ($quantity <= 0 || $quantity > 100000) $errors[] = 'Quantity must be between 1 and 100,000';
    if (empty($delivery_address) || strlen($delivery_address) > 500) $errors[] = 'Delivery address is required (max 500 chars)';
    if (strlen($notes) > 2000) $errors[] = 'Notes must be under 2000 characters';
    if ($required_date !== '') {
        $ts = strtotime($required_date);
        if (!$ts) { $errors[] = 'Required date is invalid'; }
        else {
            $today = strtotime('today');
            $maxFuture = strtotime('+2 years');
            if ($ts < $today) $errors[] = 'Required date must be today or later';
            if ($ts > $maxFuture) $errors[] = 'Required date must be within 2 years';
        }
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }

    // Verify product_id actually exists and is available
    try {
        $db = getDB();
        $product_check = $db->prepare("SELECT name FROM products WHERE id = ? AND status = 'available'");
        $product_check->execute([$product_id]);
        $product_row = $product_check->fetch();
        if (!$product_row) {
            echo json_encode(['success' => false, 'message' => 'Selected product is not available']);
            exit;
        }
        $product_name = $product_row['name'];

        $stmt = $db->prepare("INSERT INTO bulk_orders (company_name, contact_person, email, phone, product_id, product_name, quantity, required_date, delivery_address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$company_name, $contact_person, $email, $phone, $product_id, $product_name, $quantity, $required_date ?: null, $delivery_address, $notes]);

        echo json_encode(['success' => true, 'message' => 'Your bulk order request has been submitted! We will contact you within 24 hours.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
    }
    exit;
}

require_once 'includes/header.php';

// Fetch products for dropdown
try {
    $db = getDB();
    $products = $db->query("SELECT id, name, price_per_unit, moq, stock_qty FROM products WHERE status = 'available' ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$success = '';
$error = '';
?>

<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div style="max-width:800px;">
            <span class="kicker">Bulk Order</span>
            <h1 data-reveal>Stock Your Shelves.</h1>
            <p style="font-size:var(--text-lg);color:var(--text-secondary);">Send one request, get a tailored reply with pricing, delivery window, and stock holds within a business day.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header section-header--center">
            <span class="kicker">How it works</span>
            <h2 class="section-title">Four steps from request to delivery.</h2>
            <p class="section-sub">No back-and-forth, no waiting weeks for a callback.</p>
        </div>
        <div class="bulk-process">
            <div class="bulk-process-step">
                <span class="step-badge step-badge--teal">01</span>
                <h3 style="font-size:1.05rem;margin-bottom:6px;">Submit Request</h3>
                <p class="section-body" style="color:var(--text-secondary);font-size:0.9rem;">Fill out the form with products, quantities, and delivery city.</p>
            </div>
            <div class="bulk-process-step">
                <span class="step-badge step-badge--purple">02</span>
                <h3 style="font-size:1.05rem;margin-bottom:6px;">Get a Quote</h3>
                <p class="section-body" style="color:var(--text-secondary);font-size:0.9rem;">We send a price summary and dispatch window to your inbox within 24 hours.</p>
            </div>
            <div class="bulk-process-step">
                <span class="step-badge step-badge--amber">03</span>
                <h3 style="font-size:1.05rem;margin-bottom:6px;">Confirm &amp; Pay</h3>
                <p class="section-body" style="color:var(--text-secondary);font-size:0.9rem;">Approve the pricing, settle the invoice, and we hold the inventory for you.</p>
            </div>
            <div class="bulk-process-step">
                <span class="step-badge step-badge--teal">04</span>
                <h3 style="font-size:1.05rem;margin-bottom:6px;">Fast Dispatch</h3>
                <p class="section-body" style="color:var(--text-secondary);font-size:0.9rem;">48-hour dispatch nationwide. Tracking shared the moment the order ships.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="max-width:760px;margin:0 auto;">
            <div style="text-align:center;margin-bottom:var(--spacing-lg);">
                <span class="kicker kicker--center">Request a quote</span>
                <h2 class="section-title" style="margin-top:8px;">Tell us what you need.</h2>
                <p class="section-sub">All fields marked with * are required. We respond within one business day.</p>
            </div>
            <form id="bulkOrderForm" action="bulk-order.php" method="POST" class="contact-form">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="company_name">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" required maxlength="150" placeholder="Your company name">
                    </div>
                    <div class="form-group">
                        <label for="contact_person">Contact Person *</label>
                        <input type="text" id="contact_person" name="contact_person" required maxlength="100" pattern="[A-Za-zÀ-ɏ\s.\-']{2,100}" data-filter-alpha placeholder="Full name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required maxlength="254" placeholder="you@company.com">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" maxlength="20" pattern="[+0-9 ()\-]{7,20}" placeholder="0300 1234567">
                    </div>
                </div>

                <div class="form-group">
                    <label for="productSelect">Select Product *</label>
                    <select id="productSelect" name="product_id" required aria-label="Product selection">
                        <option value="">Choose a product...</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo (int)$product['id']; ?>"
                                    data-price="<?php echo htmlspecialchars($product['price_per_unit']); ?>"
                                    data-moq="<?php echo (int)$product['moq']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                                (Min. order: <?php echo (int)$product['moq']; ?>, available: <?php echo (int)$product['stock_qty']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="bulk-summary">
                    <div class="bulk-summary-card">
                        <div class="bulk-summary-label">Unit Price</div>
                        <div id="selectedPrice" class="bulk-summary-value">-</div>
                    </div>
                    <div class="bulk-summary-card">
                        <div class="bulk-summary-label">Min. Order Qty</div>
                        <div id="selectedMoq" class="bulk-summary-value">-</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="100000" required placeholder="Number of units">
                    </div>
                    <div class="form-group">
                        <label for="required_date">Required By</label>
                        <input type="date" id="required_date" name="required_date">
                    </div>
                </div>

                <div class="form-group">
                    <label for="delivery_address">Delivery Address *</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3" required maxlength="500" placeholder="Full delivery address with city and postal code"></textarea>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Any special requirements or questions..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Submit Bulk Order Request <span class="btn-arrow">&rarr;</span></button>
            </form>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-banner">
            <span class="kicker" style="color:var(--accent-gold-bright);">Prefer to talk</span>
            <h2>Have a custom catalog requirement?</h2>
            <p>For multi-product bundles, custom branding, or recurring weekly orders, our team is happy to scope it with you.</p>
            <div class="cta-actions">
                <a href="contact.php" class="btn btn-primary btn-lg btn-arrow">
                    <span>Talk to Wholesale</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3l5 5-5 5"/></svg>
                </a>
                <a href="products.php" class="btn btn-ghost btn-lg">Browse Catalog</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
