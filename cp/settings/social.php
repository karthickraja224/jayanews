<?php
// settings/social.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();
requireRole(['superadmin', 'admin']);

$page_title  = __('social_title');
$breadcrumbs = [__('settings') => null, __('social_title') => null];

$social_fields = [
    'facebook_url'  => ['label' => 'Facebook',  'icon' => 'fab fa-facebook',  'placeholder' => 'https://facebook.com/jayaplus'],
    'twitter_url'   => ['label' => 'X (Twitter)','icon' => 'fab fa-twitter',   'placeholder' => 'https://x.com/jayaplus'],
    'instagram_url' => ['label' => 'Instagram',  'icon' => 'fab fa-instagram', 'placeholder' => 'https://instagram.com/jayaplus'],
    'youtube_url'   => ['label' => 'YouTube',    'icon' => 'fab fa-youtube',   'placeholder' => 'https://youtube.com/@jayaplus'],
    'telegram_url'  => ['label' => 'Telegram',   'icon' => 'fab fa-telegram',  'placeholder' => 'https://t.me/jayaplus'],
    'linkedin_url'  => ['label' => 'LinkedIn',   'icon' => 'fab fa-linkedin',  'placeholder' => 'https://linkedin.com/company/jayaplus'],
    'pinterest_url' => ['label' => 'Pinterest',  'icon' => 'fab fa-pinterest', 'placeholder' => 'https://pinterest.com/jayaplus'],
    'whatsapp_number' => ['label' => 'WhatsApp Channel/Number', 'icon' => 'fab fa-whatsapp', 'placeholder' => '+91 98765 43210 அல்லது channel link'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($social_fields as $key => $meta) {
        saveSetting($key, trim($_POST[$key] ?? ''));
    }
    saveSetting('show_social_icons', isset($_POST['show_social_icons']) ? '1' : '0');

    logActivity('update', 'settings', __('msg_social_updated'));
    flashSuccess(__('msg_social_updated'));
    header('Location: social.php'); exit();
}

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-share-alt"></i> <?= __('social_title') ?></h1>
        <p><?= __('social_subtitle') ?></p>
    </div>
</div>

<!-- Settings sub-nav -->
<div class="tabs-wrapper">
    <div class="tabs">
        <button class="tab-btn" onclick="window.location='settings.php'"><i class="fas fa-sliders-h"></i> <?= __('tab_general') ?></button>
        <button class="tab-btn" onclick="window.location='seo.php'"><i class="fas fa-search"></i> <?= __('tab_seo') ?></button>
        <button class="tab-btn active" onclick="window.location='social.php'"><i class="fas fa-share-alt"></i> <?= __('tab_social') ?></button>
    </div>
</div>

<form method="POST">
<div class="form-layout">
    <div class="form-main">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-link"></i> <?= __('card_social_links') ?></span></div>
            <div class="card-body">
                <?php foreach ($social_fields as $key => $meta): ?>
                <div class="form-group">
                    <label class="form-label"><i class="<?= $meta['icon'] ?>"></i> <?= $meta['label'] ?></label>
                    <input type="text" name="<?= $key ?>" class="form-control" value="<?= htmlspecialchars(getSetting($key)) ?>" placeholder="<?= htmlspecialchars($meta['placeholder']) ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-eye"></i> <?= __('card_display') ?></span></div>
            <div class="card-body">
                <label class="form-check">
                    <input type="checkbox" name="show_social_icons" <?= getSetting('show_social_icons', '1') === '1' ? 'checked' : '' ?>>
                    <span class="form-check-label"><i class="fas fa-icons" style="color:var(--teal)"></i> <?= __('label_show_icons') ?></span>
                </label>
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
        </div>
    </div>
</div>
</form>

<?php include '../includes/footer.php'; ?>