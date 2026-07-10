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
