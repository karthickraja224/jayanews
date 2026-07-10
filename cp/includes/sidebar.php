<?php
// includes/sidebar.php
$user = currentUser();
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

function isActive($dirs = [], $files = []) {
    global $current_dir, $current_file;
    if (in_array($current_dir, (array)$dirs)) return 'active';
    if (in_array($current_file, (array)$files)) return 'active';
    return '';
}
function isOpen($dirs = [], $files = []) {
    return isActive($dirs, $files) ? 'open' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="" style="width: 50px;">
            <span class="brand-j"><img src="../images/logo.png"/></span>
        </div>
        <div class="brand-text">
            <span class="brand-name">Jaya Plus</span>
            <span class="brand-sub">Admin Panel</span>
        </div>
        <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></span>
            <span class="user-role"><?= ucfirst($user['role'] ?? 'admin') ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label"><?= __('nav_main') ?></div>

        <a href="<?= BASE_URL ?>dashboard/dashboard.php" class="nav-item <?= isActive('dashboard', 'dashboard.php') ?>">
            <i class="fas fa-tachometer-alt"></i><span><?= __('nav_dashboard') ?></span>
        </a>

        <!-- NEWS -->
        <div class="nav-group <?= isOpen('news') ?>">
            <div class="nav-group-header">
                <i class="fas fa-newspaper"></i><span><?= __('nav_news') ?></span><i class="fas fa-chevron-down arrow"></i>
            </div>
            <div class="nav-group-items">
                <a href="<?= BASE_URL ?>news/news-list.php" class="nav-item sub <?= isActive('', 'news-list.php') ?>">
                    <i class="fas fa-list"></i><span><?= __('nav_all_news') ?></span>
                </a>
                <a href="<?= BASE_URL ?>news/news-add.php" class="nav-item sub <?= isActive('', 'news-add.php') ?>">
                    <i class="fas fa-plus-circle"></i><span><?= __('nav_add_news') ?></span>
                </a>
                <a href="<?= BASE_URL ?>news/news-draft.php" class="nav-item sub <?= isActive('', 'news-draft.php') ?>">
                    <i class="fas fa-file-alt"></i><span><?= __('nav_drafts') ?></span>
                </a>
                <a href="<?= BASE_URL ?>news/news-published.php" class="nav-item sub <?= isActive('', 'news-published.php') ?>">
                    <i class="fas fa-check-circle"></i><span><?= __('nav_published') ?></span>
                </a>
                <a href="<?= BASE_URL ?>news/news-trash.php" class="nav-item sub <?= isActive('', 'news-trash.php') ?>">
                    <i class="fas fa-trash"></i><span><?= __('nav_trash') ?></span>
                </a>
            </div>
        </div>

        <!-- CATEGORIES -->
        <div class="nav-group <?= isOpen('category', 'subcategory') ?>">
            <div class="nav-group-header">
                <i class="fas fa-tags"></i><span><?= __('nav_categories') ?></span><i class="fas fa-chevron-down arrow"></i>
            </div>
            <div class="nav-group-items">
                <a href="<?= BASE_URL ?>category/category-list.php" class="nav-item sub <?= isActive('category') ?>">
                    <i class="fas fa-folder"></i><span><?= __('nav_category') ?></span>
                </a>
                <a href="<?= BASE_URL ?>subcategory/subcategory-list.php" class="nav-item sub <?= isActive('subcategory') ?>">
                    <i class="fas fa-folder-open"></i><span><?= __('nav_subcategory') ?></span>
                </a>
            </div>
        </div>

        <div class="nav-section-label">Media &amp; Design</div>

        <a href="<?= BASE_URL ?>breaking/breaking-news.php" class="nav-item <?= isActive('breaking') ?>">
            <i class="fas fa-bolt"></i><span><?= __('nav_breaking') ?></span>
        </a>

        <!-- SLIDER -->
        <div class="nav-group <?= isOpen('slider') ?>">
            <div class="nav-group-header">
                <i class="fas fa-images"></i><span><?= __('nav_slider') ?></span><i class="fas fa-chevron-down arrow"></i>
            </div>
            <div class="nav-group-items">
                <a href="<?= BASE_URL ?>slider/slider-list.php" class="nav-item sub <?= isActive('slider', 'slider-list.php') ?>">
                    <i class="fas fa-th-large"></i><span><?= ($_SESSION['lang'] ?? 'en') === 'ta' ? 'அனைத்து ஸ்லைடுகள்' : 'All Slides' ?></span>
                </a>
                <a href="<?= BASE_URL ?>slider/slider-add.php" class="nav-item sub <?= isActive('', 'slider-add.php') ?>">
                    <i class="fas fa-plus"></i><span><?= ($_SESSION['lang'] ?? 'en') === 'ta' ? 'புதிய ஸ்லைடு' : 'Add Slide' ?></span>
                </a>
            </div>
        </div>

        <a href="<?= BASE_URL ?>media/media.php" class="nav-item <?= isActive('media') ?>">
            <i class="fas fa-photo-video"></i><span><?= ($_SESSION['lang'] ?? 'en') === 'ta' ? 'மீடியா லைப்ரரி' : 'Media Library' ?></span>
        </a>

        <!-- ADS -->
        <div class="nav-group <?= isOpen('ads') ?>">
            <div class="nav-group-header">
                <i class="fas fa-ad"></i><span><?= __('nav_ads') ?></span><i class="fas fa-chevron-down arrow"></i>
            </div>
            <div class="nav-group-items">
                <a href="<?= BASE_URL ?>ads/ads-list.php" class="nav-item sub <?= isActive('ads', 'ads-list.php') ?>">
                    <i class="fas fa-list"></i><span><?= ($_SESSION['lang'] ?? 'en') === 'ta' ? 'அனைத்து விளம்பரங்கள்' : 'All Ads' ?></span>
                </a>
                <a href="<?= BASE_URL ?>ads/ads-add.php" class="nav-item sub <?= isActive('', 'ads-add.php') ?>">
                    <i class="fas fa-plus"></i><span><?= ($_SESSION['lang'] ?? 'en') === 'ta' ? 'புதிய விளம்பரம்' : 'Add Ad' ?></span>
                </a>
            </div>
        </div>

        <!-- RSS FEEDS -->
        <div class="nav-group <?= isOpen('rss') ?>">
            <div class="nav-group-header">
                <i class="fas fa-rss"></i><span><?= __('nav_rss') ?></span><i class="fas fa-chevron-down arrow"></i>
            </div>
            <div class="nav-group-items">
                <a href="<?= BASE_URL ?>rss/rss-list.php" class="nav-item sub <?= isActive('rss', 'rss-list.php') ?>">
                    <i class="fas fa-list"></i><span><?= ($_SESSION['lang'] ?? 'en') === 'ta' ? 'அனைத்து RSS' : 'All RSS Feeds' ?></span>
                </a>
                <a href="<?= BASE_URL ?>rss/rss-add.php" class="nav-item sub <?= isActive('', 'rss-add.php') ?>">
                    <i class="fas fa-plus"></i><span><?= ($_SESSION['lang'] ?? 'en') === 'ta' ? 'புதிய RSS' : 'Import RSS Feeds' ?></span>
                </a>
            </div>
        </div>

        <!-- LINKS -->
        <a href="<?= BASE_URL ?>link/link-list.php" class="nav-item <?= isActive('link') ?>">
            <i class="fas fa-link"></i><span><?= __('nav_links') ?></span>
        </a>

        <div class="nav-section-label"><?= __('nav_admin') ?></div>

        <a href="<?= BASE_URL ?>comments/comments.php" class="nav-item <?= isActive('comments') ?>">
            <i class="fas fa-comments"></i><span><?= __('nav_comments') ?></span>
            <?php $pc = db()->fetchOne("SELECT COUNT(*) c FROM tbl_comments WHERE status='pending'")['c']; ?>
            <?php if ($pc > 0): ?><span class="nav-badge"><?= $pc ?></span><?php endif; ?>
        </a>

        <!-- PAGES -->
        <div class="nav-group <?= isOpen('pages') ?>">
            <div class="nav-group-header">
                <i class="fas fa-file-alt"></i><span><?= __('nav_pages') ?></span><i class="fas fa-chevron-down arrow"></i>
            </div>
            <div class="nav-group-items">
                <a href="<?= BASE_URL ?>pages/about.php" class="nav-item sub <?= isActive('', 'about.php') ?>"><i class="fas fa-info-circle"></i><span><?= __('nav_about') ?></span></a>
                <a href="<?= BASE_URL ?>pages/contact.php" class="nav-item sub <?= isActive('', 'contact.php') ?>"><i class="fas fa-envelope"></i><span><?= __('nav_contact') ?></span></a>
                <a href="<?= BASE_URL ?>pages/privacy.php" class="nav-item sub <?= isActive('', 'privacy.php') ?>"><i class="fas fa-shield-alt"></i><span><?= __('nav_privacy') ?></span></a>
                <a href="<?= BASE_URL ?>pages/terms.php" class="nav-item sub <?= isActive('', 'terms.php') ?>"><i class="fas fa-scroll"></i><span><?= __('nav_terms') ?></span></a>
            </div>
        </div>

        <?php if (hasRole(['superadmin', 'admin'])): ?>
        <a href="<?= BASE_URL ?>users/users.php" class="nav-item <?= isActive('users') ?>">
            <i class="fas fa-users"></i><span><?= __('nav_users') ?></span>
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>reports/reports.php" class="nav-item <?= isActive('reports') ?>">
            <i class="fas fa-chart-bar"></i><span><?= __('nav_reports') ?></span>
        </a>

        <!-- SETTINGS -->
        <div class="nav-group <?= isOpen('settings') ?>">
            <div class="nav-group-header">
                <i class="fas fa-cog"></i><span><?= __('nav_settings') ?></span><i class="fas fa-chevron-down arrow"></i>
            </div>
            <div class="nav-group-items">
                <a href="<?= BASE_URL ?>settings/settings.php" class="nav-item sub <?= isActive('', 'settings.php') ?>"><i class="fas fa-sliders-h"></i><span><?= __('nav_general_settings') ?></span></a>
                <a href="<?= BASE_URL ?>settings/seo.php" class="nav-item sub <?= isActive('', 'seo.php') ?>"><i class="fas fa-search"></i><span><?= __('nav_seo') ?></span></a>
                <a href="<?= BASE_URL ?>settings/social.php" class="nav-item sub <?= isActive('', 'social.php') ?>"><i class="fas fa-share-alt"></i><span><?= __('nav_social') ?></span></a>
               <a href="<?= BASE_URL ?>settings/youtube-live.php" class="nav-item sub <?= isActive('', 'youtube-live.php') ?>">
    <i class="fab fa-youtube text-danger"></i>
    <span><?= __('youtube live') ?></span>
</a>
            </div>
        </div>

        <a href="<?= BASE_URL ?>logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i><span><?= __('nav_logout') ?></span>
        </a>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
