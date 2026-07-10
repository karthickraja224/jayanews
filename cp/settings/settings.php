<?php
// settings/settings.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();
requireRole(['superadmin', 'admin']);

$page_title  = __('settings_title');
$breadcrumbs = [__('settings') => null, __('settings_title') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_name', 'site_tagline', 'site_url', 'site_email', 'site_phone',
        'site_address', 'footer_text', 'copyright_text', 'items_per_page',
    ];
    foreach ($fields as $f) {
        saveSetting($f, trim($_POST[$f] ?? ''));
    }
    saveSetting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');

    // Logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $up = uploadImage($_FILES['site_logo'], 'settings');
        if (isset($up['error'])) {
            flashError($up['error']);
        } else {
            $old = getSetting('site_logo');
            if ($old) deleteFile($old);
            saveSetting('site_logo', $up);
        }
    }
    // Favicon upload
    if (!empty($_FILES['site_favicon']['name'])) {
        $up = uploadImage($_FILES['site_favicon'], 'settings');
        if (isset($up['error'])) {
            flashError($up['error']);
        } else {
            $old = getSetting('site_favicon');
            if ($old) deleteFile($old);
            saveSetting('site_favicon', $up);
        }
    }

    logActivity('update', 'settings', __('msg_settings_updated'));
    flashSuccess(__('msg_settings_saved'));
    header('Location: settings.php'); exit();
}

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-cog"></i> <?= __('settings_title') ?></h1>
        <p><?= __('settings_subtitle') ?></p>
    </div>
</div>

<!-- Settings sub-nav -->
<div class="tabs-wrapper">
    <div class="tabs">
        <button class="tab-btn active" onclick="window.location='settings.php'"><i class="fas fa-sliders-h"></i> <?= __('tab_general') ?></button>
        <button class="tab-btn" onclick="window.location='seo.php'"><i class="fas fa-search"></i> <?= __('tab_seo') ?></button>
        <button class="tab-btn" onclick="window.location='social.php'"><i class="fas fa-share-alt"></i> <?= __('tab_social') ?></button>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
<div class="form-layout">
    <div class="form-main">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-globe"></i> <?= __('card_site_details') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_site_name') ?></label>
                    <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(getSetting('site_name', 'Jaya Plus')) ?>" placeholder="Jaya Plus">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_tagline') ?></label>
                    <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars(getSetting('site_tagline')) ?>" placeholder="உங்கள் நம்பகமான செய்தி தளம்">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_site_url') ?></label>
                    <input type="url" name="site_url" class="form-control" value="<?= htmlspecialchars(getSetting('site_url')) ?>" placeholder="https://jayanewslive.com">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_email') ?></label>
                        <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars(getSetting('site_email')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_phone') ?></label>
                        <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars(getSetting('site_phone')) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_address') ?></label>
                    <textarea name="site_address" class="form-control" rows="2"><?= htmlspecialchars(getSetting('site_address')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-image"></i> <?= __('card_logo') ?></span></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <label class="form-label"><?= __('label_logo') ?></label>
                        <?php $logo = getSetting('site_logo'); ?>
                        <?php if ($logo): ?><img src="<?= BASE_URL . htmlspecialchars($logo) ?>" style="max-height:60px;display:block;margin-bottom:8px;background:var(--bg-hover);border-radius:6px;padding:6px"><?php endif; ?>
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                        <small class="text-muted"><?= __('hint_logo') ?></small>
                    </div>
                    <div>
                        <label class="form-label">Favicon</label>
                        <?php $favicon = getSetting('site_favicon'); ?>
                        <?php if ($favicon): ?><img src="<?= BASE_URL . htmlspecialchars($favicon) ?>" style="max-height:40px;display:block;margin-bottom:8px;background:var(--bg-hover);border-radius:6px;padding:6px"><?php endif; ?>
                        <input type="file" name="site_favicon" class="form-control" accept="image/*">
                        <small class="text-muted"><?= __('hint_favicon') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-shoe-prints"></i> Footer</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_footer_text') ?></label>
                    <textarea name="footer_text" class="form-control" rows="2" placeholder="தமிழகத்தின் நம்பகமான செய்தி தளம்..."><?= htmlspecialchars(getSetting('footer_text')) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_copyright') ?></label>
                    <input type="text" name="copyright_text" class="form-control" value="<?= htmlspecialchars(getSetting('copyright_text', '© ' . date('Y') . ' Jaya Plus. அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை.')) ?>">
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-tools"></i> <?= __('settings_title') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('stat_total_news') ?></label>
                    <input type="number" name="items_per_page" class="form-control" value="<?= htmlspecialchars(getSetting('items_per_page', '15')) ?>" min="5" max="100" style="max-width:150px">
                </div>
                <label class="form-check">
                    <input type="checkbox" name="maintenance_mode" <?= getSetting('maintenance_mode') === '1' ? 'checked' : '' ?>>
                    <span class="form-check-label"><i class="fas fa-wrench" style="color:var(--warning)"></i> பராமரிப்பு பயன்முறை (Maintenance Mode)</span>
                </label>
                <small class="text-muted" style="display:block;margin-top:4px">இயக்கப்பட்டால், பொது தளம் பார்வையாளர்களுக்கு "பராமரிப்பு" பக்கம் காட்டப்படும்.</small>
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
        </div>
    </div>
</div>
</form>

<?php include '../includes/footer.php'; ?>