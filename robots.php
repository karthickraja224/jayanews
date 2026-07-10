<?php
// robots.php — served as /robots.txt via .htaccess
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$custom = getSetting('robots_txt', '');
if (trim($custom) !== '') {
    echo $custom;
    exit;
}

$siteUrl = rtrim(getSetting('site_url', ''), '/');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /config/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /*.sql\n";
if ($siteUrl) {
    echo "Sitemap: {$siteUrl}/sitemap.xml\n";
}
