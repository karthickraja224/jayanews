<?php // includes/navbar.php
$_lang = $_SESSION['lang'] ?? 'en';
$_is_tamil = ($_lang === 'ta');
?>
<header class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <nav class="breadcrumb-nav">
            <a href="<?= BASE_URL ?>dashboard/dashboard.php"><i class="fas fa-home"></i></a>
            <?php if (isset($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $label => $url): ?>
                    <span class="bc-sep">/</span>
                    <?php if ($url): ?>
                        <a href="<?= $url ?>"><?= $label ?></a>
                    <?php else: ?>
                        <span class="bc-current"><?= $label ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Theme Picker -->
    <div class="topbar-theme">
        <button class="theme-picker-btn topbar-btn" id="themePickerBtn" title="Change Theme" aria-label="Change theme">
            <i class="fas fa-palette"></i>
        </button>
        <div class="theme-picker-panel" id="themePickerPanel">
            <h6>Choose Theme</h6>
            <div class="theme-list">
                <button class="theme-option" data-theme="light">
                    <span class="theme-swatch" style="background:linear-gradient(135deg,#f4f6fb,#e63946)"></span>
                    Light
                </button>
                <button class="theme-option" data-theme="dark">
                    <span class="theme-swatch" style="background:linear-gradient(135deg,#0d0f1a,#e63946)"></span>
                    Dark
                </button>
                <button class="theme-option" data-theme="ocean">
                    <span class="theme-swatch" style="background:linear-gradient(135deg,#0a1628,#38bdf8)"></span>
                    Ocean
                </button>
                <button class="theme-option" data-theme="nature">
                    <span class="theme-swatch" style="background:linear-gradient(135deg,#f0faf4,#16a34a)"></span>
                    Nature
                </button>
                <button class="theme-option" data-theme="royal">
                    <span class="theme-swatch" style="background:linear-gradient(135deg,#0e0a1a,#a855f7)"></span>
                    Royal
                </button>
                <button class="theme-option" data-theme="contrast">
                    <span class="theme-swatch" style="background:linear-gradient(135deg,#000,#ffff00)"></span>
                    High Contrast
                </button>
            </div>
        </div>
    </div>

    <!-- Language Switcher -->
   <a href="<?= BASE_URL ?>lang-switch.php"
           class="lang-switcher-btn topbar-btn"
           title="<?= __('lang_switch_title') ?>">
            <span class="lang-flag"><?= $_is_tamil ? '' : '' ?></span>
            <span class="lang-label"><?= $_is_tamil ? 'TAM' : 'EN' ?></span>
            <span class="lang-toggle-track <?= $_is_tamil ? 'is-tamil' : '' ?>">
                <span class="lang-toggle-thumb"></span>
            </span>
        </a>

    <div class="topbar-right">
        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="<?= __('search_placeholder') ?>" id="quickSearch">
        </div>

        <a href="<?= getSetting('site_url', '#') ?>" target="_blank" class="topbar-btn" title="<?= __('view_site') ?>">
            <i class="fas fa-external-link-alt"></i>
        </a>
        <div class="topbar-notif">
            <button class="topbar-btn" id="notifBtn">
                <i class="fas fa-bell"></i>
                <?php $pending = db()->fetchOne("SELECT COUNT(*) c FROM tbl_comments WHERE status='pending'")['c']; ?>
                <?php if ($pending > 0): ?><span class="notif-dot"></span><?php endif; ?>
            </button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header"><?= __('notifications') ?></div>
                <?php if ($pending > 0): ?>
                <a href="<?= BASE_URL ?>comments/comments.php?status=pending" class="notif-item">
                    <i class="fas fa-comment-dots"></i>
                    <div><strong><?= $pending ?> <?= $_is_tamil ? 'கருத்துகள்' : 'comments' ?></strong> <?= __('pending_comments_msg') ?></div>
                </a>
                <?php else: ?>
                <div class="notif-empty"><i class="fas fa-check-circle"></i> <?= __('no_notifications') ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="topbar-user">
            <button class="user-btn" id="userBtn">
                <div class="user-avatar-sm"><?= strtoupper(substr(currentUser()['name'] ?? 'A', 0, 1)) ?></div>
                <span><?= htmlspecialchars(currentUser()['name'] ?? 'Admin') ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <a href="<?= BASE_URL ?>users/users.php"><i class="fas fa-user"></i> <?= __('profile') ?></a>
                <a href="<?= BASE_URL ?>settings/settings.php"><i class="fas fa-cog"></i> <?= __('settings') ?></a>
                <hr>
                <a href="<?= BASE_URL ?>logout.php"><i class="fas fa-sign-out-alt"></i> <?= __('nav_logout') ?></a>
            </div>
        </div>
    </div>
</header>
