<?php
require_once __DIR__ . '/../config/init.php';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = $page_title ?? 'CellVerse - Bulk Mobile Accessories, Wholesale Pricing';
$page_description = $page_description ?? 'Pakistan\'s leading wholesale supplier of premium mobile accessories. Cases, chargers, earphones and more, delivered at competitive bulk rates for businesses that scale.';
$page_image = $page_image ?? (BASE_URL . '/images/og-default.svg');
$absolute_base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$absolute_canonical = $absolute_base . BASE_URL . ($current_page === 'index' ? '/' : '/' . $current_page . '.php');
$page_canonical = $page_canonical ?? $absolute_canonical;
$site_name = 'CellVerse';
$twitter_handle = '@cellverse';
$absolute_og_image = $absolute_base . $page_image;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0f1a">
    <meta name="format-detection" content="telephone=no">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="robots" content="<?php echo htmlspecialchars($page_robots ?? 'index, follow, max-image-preview:large', ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="author" content="CellVerse">
    <link rel="canonical" href="<?php echo htmlspecialchars($page_canonical, ENT_QUOTES, 'UTF-8'); ?>">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($page_canonical, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($absolute_og_image, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:locale" content="en_US">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?php echo htmlspecialchars($twitter_handle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($page_image, ENT_QUOTES, 'UTF-8'); ?>">

    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/sprint1.css?v=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
    <noscript><style>body.is-loading .page-curtain { display: none; }</style></noscript>

    <?php if (empty($page_robots) || strpos($page_robots, 'noindex') === false): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "CellVerse",
        "url": "<?php echo $absolute_base . BASE_URL . '/'; ?>",
        "logo": "<?php echo $absolute_base . BASE_URL; ?>/images/logo.svg",
        "description": "Pakistan's leading wholesale supplier of premium mobile accessories.",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Shop 12, Tech Market",
            "addressLocality": "Lahore",
            "addressCountry": "PK"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+92-300-1234567",
            "contactType": "sales",
            "email": "info@cellverse.pk"
        }
    }
    </script>
    <?php endif; ?>
</head>
<body class="is-loading">
    <div class="page-curtain" aria-hidden="true"></div>
    <div class="scroll-progress" id="scrollProgress"></div>
    <a href="#main-content" class="skip-link">Skip to content</a>

    <header class="site-header" id="siteHeader">
        <div class="container header-inner">
            <a href="<?php echo BASE_URL; ?>/" class="logo" aria-label="CellVerse home">
                <svg class="logo-icon" width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                    <rect x="8" y="2" width="16" height="28" rx="3" stroke="currentColor" stroke-width="2"/>
                    <circle cx="16" cy="26" r="2" fill="currentColor"/>
                    <rect x="12" y="6" width="8" height="14" rx="1" fill="currentColor" opacity="0.3"/>
                    <path d="M10 24h12" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <span class="logo-text">Cell<span class="logo-accent">Verse</span></span>
            </a>

            <nav class="desktop-nav" id="desktopNav" aria-label="Main navigation">
                <a href="<?php echo BASE_URL; ?>/" class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>" <?php echo $current_page === 'index' ? 'aria-current="page"' : ''; ?>>Home</a>
                <a href="<?php echo BASE_URL; ?>/products.php" class="nav-link <?php echo $current_page === 'products' ? 'active' : ''; ?>" <?php echo $current_page === 'products' ? 'aria-current="page"' : ''; ?>>Products</a>
                <a href="<?php echo BASE_URL; ?>/bulk-order.php" class="nav-link <?php echo $current_page === 'bulk-order' ? 'active' : ''; ?>" <?php echo $current_page === 'bulk-order' ? 'aria-current="page"' : ''; ?>>Bulk Order</a>
                <a href="<?php echo BASE_URL; ?>/about.php" class="nav-link <?php echo $current_page === 'about' ? 'active' : ''; ?>" <?php echo $current_page === 'about' ? 'aria-current="page"' : ''; ?>>About</a>
                <a href="<?php echo BASE_URL; ?>/faq.php" class="nav-link <?php echo $current_page === 'faq' ? 'active' : ''; ?>" <?php echo $current_page === 'faq' ? 'aria-current="page"' : ''; ?>>FAQ</a>
                <a href="<?php echo BASE_URL; ?>/contact.php" class="nav-link <?php echo $current_page === 'contact' ? 'active' : ''; ?>" <?php echo $current_page === 'contact' ? 'aria-current="page"' : ''; ?>>Contact</a>
            </nav>

            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/bulk-order.php" class="btn btn-primary btn-sm pulse-glow">Get Quote</a>
                <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobileNav">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation" aria-hidden="true">
            <a href="<?php echo BASE_URL; ?>/" class="mobile-nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>" <?php echo $current_page === 'index' ? 'aria-current="page"' : ''; ?>>Home</a>
            <a href="<?php echo BASE_URL; ?>/products.php" class="mobile-nav-link <?php echo $current_page === 'products' ? 'active' : ''; ?>" <?php echo $current_page === 'products' ? 'aria-current="page"' : ''; ?>>Products</a>
            <a href="<?php echo BASE_URL; ?>/bulk-order.php" class="mobile-nav-link <?php echo $current_page === 'bulk-order' ? 'active' : ''; ?>" <?php echo $current_page === 'bulk-order' ? 'aria-current="page"' : ''; ?>>Bulk Order</a>
            <a href="<?php echo BASE_URL; ?>/about.php" class="mobile-nav-link <?php echo $current_page === 'about' ? 'active' : ''; ?>" <?php echo $current_page === 'about' ? 'aria-current="page"' : ''; ?>>About</a>
            <a href="<?php echo BASE_URL; ?>/faq.php" class="mobile-nav-link <?php echo $current_page === 'faq' ? 'active' : ''; ?>" <?php echo $current_page === 'faq' ? 'aria-current="page"' : ''; ?>>FAQ</a>
            <a href="<?php echo BASE_URL; ?>/contact.php" class="mobile-nav-link <?php echo $current_page === 'contact' ? 'active' : ''; ?>" <?php echo $current_page === 'contact' ? 'aria-current="page"' : ''; ?>>Contact</a>
        </nav>
    </header>

    <main id="main-content">
