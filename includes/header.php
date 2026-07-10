<?php
// includes/header.php
// Expects (optionally, set by the calling page before include):
//   $page_title, $meta_description, $meta_image, $canonical_path, $body_class

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/lang-strings.php';
require_once __DIR__ . '/functions.php';

$site_name    = getSetting('site_name', 'Jaya Plus');
$site_tagline = getSetting('site_tagline', t('tagline_fallback'));
$html_lang    = currentLang() === 'ta' ? 'ta' : 'en';

$meta_title       = $page_title ?? $site_name;
$meta_description = $meta_description ?? getSetting('default_meta_description', $site_tagline);
$meta_image       = $meta_image ?? getSetting('og_image', '');
$ga_id            = getSetting('google_analytics_id', '');
$gtm_id           = getSetting('google_tag_manager_id', '');
$fb_pixel         = getSetting('facebook_pixel_id', '');
$favicon          = getSetting('site_favicon', '');
$site_logo        = getSetting('site_logo', '');

$categories = getCategories();
$breaking_items = getActiveBreaking();
?>
<!DOCTYPE html>
<html lang="<?= h($html_lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($meta_title) ?><?= isset($page_title) ? ' — ' . h($site_name) : '' ?></title>
<meta name="description" content="<?= h($meta_description) ?>">
<link rel="canonical" href="<?= h(rtrim(getSetting('site_url', ''), '/') . ($canonical_path ?? $_SERVER['REQUEST_URI'])) ?>">
<?php if ($favicon): ?><link rel="icon" href="<?= h(newsImageUrl($favicon)) ?>"><?php endif; ?>

<meta property="og:site_name" content="<?= h($site_name) ?>">
<meta property="og:title" content="<?= h($meta_title) ?>">
<meta property="og:description" content="<?= h($meta_description) ?>">
<meta property="og:type" content="<?= isset($is_article) ? 'article' : 'website' ?>">
<?php if ($meta_image): ?><meta property="og:image" content="<?= h(newsImageUrl($meta_image)) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700;8..60,900&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Tamil:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= h(ASSETS_URL) ?>css/style.css?v=<?= h(APP_VERSION) ?>">

<?php if ($gtm_id): ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= h($gtm_id) ?>');</script>
<?php elseif ($ga_id): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= h($ga_id) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= h($ga_id) ?>');</script>
<?php endif; ?>
</head>
<body class="<?= h($body_class ?? '') ?>" data-lang="<?= h($html_lang) ?>">
<?php if ($gtm_id): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= h($gtm_id) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>

<a class="skip-link" href="#main"><?= h(t('skip_content')) ?></a>

<header class="site-header">

    <div class="topbar">
        <div class="container topbar__inner">
            <div class="topbar__date"><i class="far fa-calendar"></i> <span id="todayDate"><?= h(formatDateFront(date('Y-m-d'), 'l, d M Y')) ?></span></div>
            <div class="topbar__right">
                <?php if (getSetting('show_social_icons', '1') === '1'): ?>
                <div class="topbar__social">
                    <?php
                    $socials = [
                        'facebook_url' => 'fab fa-facebook-f', 'twitter_url' => 'fab fa-x-twitter',
                        'instagram_url' => 'fab fa-instagram', 'youtube_url' => 'fab fa-youtube',
                        'telegram_url' => 'fab fa-telegram', 'linkedin_url' => 'fab fa-linkedin-in',
                        'pinterest_url' => 'fab fa-pinterest-p',
                    ];
                    foreach ($socials as $key => $icon) {
                        $val = getSetting($key);
                        if ($val) echo '<a href="' . h($val) . '" target="_blank" rel="noopener" aria-label="' . h($key) . '"><i class="' . $icon . '"></i></a>';
                    }
                    $wa = getSetting('whatsapp_number');
                    if ($wa) echo '<a href="https://wa.me/' . h(preg_replace('/\D/', '', $wa)) . '" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>';
                    ?>
                </div>
                <?php endif; ?>
                <form class="lang-switch" action="<?= h(SITE_URL) ?>lang-switch.php" method="get">
                    <input type="hidden" name="redirect" value="<?= h($_SERVER['REQUEST_URI'] ?? '/') ?>">
                    <button type="submit" name="lang" value="en" class="<?= $html_lang === 'en' ? 'active' : '' ?>">EN</button>
                    <span>/</span>
                    <button type="submit" name="lang" value="ta" class="<?= $html_lang === 'ta' ? 'active' : '' ?>">தமிழ்</button>
                </form>
            </div>
        </div>
    </div>

    <div class="masthead">
        <div class="container masthead__inner">
            <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false" aria-controls="primaryNav">
                <span></span><span></span><span></span>
            </button>
            <a href="<?= h(url_home()) ?>" class="masthead__brand">
                <?php if ($site_logo): ?>
                    <img src="<?= h(newsImageUrl($site_logo)) ?>" alt="<?= h($site_name) ?>" class="masthead__logo" style="width:100px;">
                <?php else: ?>
                    <span class="masthead__title"><?= h($site_name) ?></span>
                    <span class="masthead__tagline"><?= h($site_tagline) ?></span>
                <?php endif; ?>
            </a>
            <form class="masthead__search" action="<?= h(url_search()) ?>" method="get" role="search">
                <input type="text" name="q" placeholder="<?= h(t('search_ph')) ?>" value="<?= h($_GET['q'] ?? '') ?>" aria-label="<?= h(t('search_ph')) ?>">
                <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <nav class="primary-nav" id="primaryNav">
        <div class="container primary-nav__inner">
            <ul class="primary-nav__list">
                <li><a href="<?= h(url_home()) ?>" class="<?= empty($current_cat_slug) && !isset($is_article) ? 'is-active' : '' ?>"><i class="fas fa-house"></i> <?= h(t('nav_home')) ?></a></li>
                <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="<?= h(url_category($cat['slug'])) ?>" style="--accent: <?= h($cat['color'] ?: '#7A1228') ?>" class="<?= (($current_cat_slug ?? '') === $cat['slug']) ? 'is-active' : '' ?>">
                        <i class="<?= h(catIconClass($cat)) ?>"></i> <?= h(catLabelRow($cat)) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <?php if ($breaking_items): ?>
    <div class="wire-ticker" role="region" aria-label="<?= h(t('breaking')) ?>">
        <div class="wire-ticker__label"><span class="live-dot"></span> <?= h(t('live')) ?></div>
        <div class="wire-ticker__track">
            <ul class="wire-ticker__list">
                <?php foreach ($breaking_items as $b): $blink = $b['link_url'] ?? $b['link'] ?? ''; ?>
                <li><span class="wire-ticker__src"><?= h(t('wire')) ?> •</span> <?php if (!empty($blink)): ?><a href="<?= h($blink) ?>"><?= h($b['text']) ?></a><?php else: ?><?= h($b['text']) ?><?php endif; ?></li>
                <?php endforeach; ?>
            </ul>
            <ul class="wire-ticker__list" aria-hidden="true">
                <?php foreach ($breaking_items as $b): $blink = $b['link_url'] ?? $b['link'] ?? ''; ?>
                <li><span class="wire-ticker__src"><?= h(t('wire')) ?> •</span> <?php if (!empty($blink)): ?><a href="<?= h($blink) ?>"><?= h($b['text']) ?></a><?php else: ?><?= h($b['text']) ?><?php endif; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?= renderAdSlot('header') ?>
</header>

<?php
$fe_success = feFlashGet('success');
$fe_error   = feFlashGet('error');
if ($fe_success || $fe_error):
?>
<div class="container" style="margin-top:16px">
    <?php if ($fe_success): ?><div class="flash flash--success"><i class="fas fa-circle-check"></i> <?= h($fe_success) ?></div><?php endif; ?>
    <?php if ($fe_error): ?><div class="flash flash--error"><i class="fas fa-circle-exclamation"></i> <?= h($fe_error) ?></div><?php endif; ?>
</div>
<?php endif; ?>

<main id="main">