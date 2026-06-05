<?php
/**
 * Dynamic XML sitemap generator.
 * Outputs sitemap of all indexable pages with lastmod dates.
 */
require_once __DIR__ . '/config/init.php';
header('Content-Type: application/xml; charset=UTF-8');

$base = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . BASE_URL;

$pages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => '/products.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/bulk-order.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/about.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/faq.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/contact.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/privacy.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/terms.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

$categories = ['phone-cases', 'screen-protectors', 'chargers', 'cables', 'earphones', 'power-banks', 'stands-holders', 'other-accessories'];
foreach ($categories as $cat) {
    $pages[] = ['loc' => '/products.php?category=' . $cat, 'priority' => '0.7', 'changefreq' => 'weekly'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $p) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base . $p['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    echo "    <changefreq>" . $p['changefreq'] . "</changefreq>\n";
    echo "    <priority>" . $p['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
