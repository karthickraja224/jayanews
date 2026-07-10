<?php
// settings/seo.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();
requireRole(['superadmin', 'admin']);

$page_title  = __('nav_seo');
$breadcrumbs = [__('settings') => null, __('nav_seo') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'default_meta_title', 'default_meta_description', 'default_meta_keywords',
        'google_analytics_id', 'google_search_console', 'google_tag_manager_id',
        'facebook_pixel_id', 'robots_txt',
    ];
    foreach ($fields as $f) {
        saveSetting($f, trim($_POST[$f] ?? ''));
    }

    if (!empty($_FILES['og_image']['name'])) {
        $up = uploadImage($_FILES['og_image'], 'settings');
        if (isset($up['error'])) {
            flashError($up['error']);
        } else {
            $old = getSetting('og_image');
            if ($old) deleteFile($old);
            saveSetting('og_image', $up);
        }
    }

    logActivity('update', 'settings', __('msg_seo_updated'));
    flashSuccess(__('msg_seo_updated'));
    header('Location: seo.php'); exit();
}

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-search"></i> <?= __('nav_seo') ?></h1>
        <p><?= __('seo_subtitle') ?></p>
    </div>
</div>

<!-- Settings sub-nav -->
<div class="tabs-wrapper">
    <div class="tabs">
        <button class="tab-btn" onclick="window.location='settings.php'"><i class="fas fa-sliders-h"></i> <?= __('tab_general') ?></button>
        <button class="tab-btn active" onclick="window.location='seo.php'"><i class="fas fa-search"></i> <?= __('tab_seo') ?></button>
        <button class="tab-btn" onclick="window.location='social.php'"><i class="fas fa-share-alt"></i> <?= __('tab_social') ?></button>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
<div class="form-layout">
    <div class="form-main">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-tag"></i> <?= __('card_meta') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_title_def') ?></label>
                    <input type="text" name="default_meta_title" class="form-control" value="<?= htmlspecialchars(getSetting('default_meta_title')) ?>" placeholder="Jaya Plus — தமிழ் செய்திகள்">
                    <small class="text-muted"><?= __('hint_meta_title') ?></small>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_desc_def') ?></label>
                    <textarea name="default_meta_description" class="form-control" rows="3" placeholder="150-160 எழுத்துகளில் தள விளக்கம்..."><?= htmlspecialchars(getSetting('default_meta_description')) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_kw2') ?></label>
                    <input type="text" name="default_meta_keywords" class="form-control" value="<?= htmlspecialchars(getSetting('default_meta_keywords')) ?>" placeholder="தமிழ் செய்தி, breaking news, ...">
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-share-square"></i> <?= __('card_og_image') ?></span></div>
            <div class="card-body">
                <?php $og = getSetting('og_image'); ?>
                <?php if ($og): ?><img src="<?= BASE_URL . htmlspecialchars($og) ?>" style="max-height:120px;display:block;margin-bottom:8px;border-radius:6px"><?php endif; ?>
                <input type="file" name="og_image" class="form-control" accept="image/*">
                <small class="text-muted"><?= __('hint_og_image') ?></small>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-chart-line"></i> <?= __('card_analytics') ?></span></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                    <div class="form-group">
                        <label class="form-label">Google Analytics ID</label>
                        <input type="text" name="google_analytics_id" class="form-control" value="<?= htmlspecialchars(getSetting('google_analytics_id')) ?>" placeholder="G-XXXXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Google Tag Manager ID</label>
                        <input type="text" name="google_tag_manager_id" class="form-control" value="<?= htmlspecialchars(getSetting('google_tag_manager_id')) ?>" placeholder="GTM-XXXXXXX">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                    <div class="form-group">
                        <label class="form-label">Google Search Console Verification</label>
                        <input type="text" name="google_search_console" class="form-control" value="<?= htmlspecialchars(getSetting('google_search_console')) ?>" placeholder="verification meta content">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Facebook Pixel ID</label>
                        <input type="text" name="facebook_pixel_id" class="form-control" value="<?= htmlspecialchars(getSetting('facebook_pixel_id')) ?>" placeholder="000000000000000">
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-robot"></i> <?= __('card_robots') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <textarea name="robots_txt" class="form-control" rows="6" placeholder="User-agent: *&#10;Allow: /&#10;Sitemap: https://jayanewslive.com/sitemap.xml"><?= htmlspecialchars(getSetting('robots_txt', "User-agent: *\nAllow: /")) ?></textarea>
                </div>
                <small class="text-muted"><?= __('hint_robots') ?></small>
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
        </div>
    </div>
</div>
</form>

<?php include '../includes/footer.php'; ?>