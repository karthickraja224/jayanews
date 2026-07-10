<?php
// sitemap.php — served as /sitemap.xml via .htaccess
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$siteUrl = rtrim(getSetting('site_url', ''), '/');

function abs_url($path) {
    // url_*() helpers already return full absolute URLs (built from SITE_URL),
    // so this just passes them through. Kept as a named wrapper in case a
    // future path needs the site_url setting prefixed instead.
    return $path;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

echo "  <url><loc>" . h(abs_url(url_home())) . "</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>\n";

foreach (getCategories() as $cat) {
    echo "  <url><loc>" . h(abs_url(url_category($cat['slug']))) . "</loc><changefreq>hourly</changefreq><priority>0.8</priority></url>\n";
}

foreach (['about', 'contact', 'privacy', 'terms'] as $slug) {
    echo "  <url><loc>" . h(abs_url(url_page($slug))) . "</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>\n";
}

$news = db()->fetchAll(
    "SELECT slug, published_at, created_at FROM tbl_news WHERE status = 'published' ORDER BY published_at DESC, created_at DESC LIMIT 1000"
);
foreach ($news as $n) {
    $lastmod = date('c', strtotime($n['published_at'] ?? $n['created_at']));
    echo "  <url><loc>" . h(abs_url(url_news($n['slug']))) . "</loc><lastmod>" . h($lastmod) . "</lastmod><changefreq>daily</changefreq><priority>0.6</priority></url>\n";
}

echo '</urlset>';
