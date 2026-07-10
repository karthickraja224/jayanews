<?php
// pages/contact.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('contact_title');
$breadcrumbs = [__('nav_pages') => null, __('contact_title') => null];

$page_key = 'contact';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $content    = $_POST['content'] ?? '';
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc  = trim($_POST['meta_description'] ?? '');

    $email      = trim($_POST['contact_email'] ?? '');
    $phone      = trim($_POST['contact_phone'] ?? '');
    $whatsapp   = trim($_POST['contact_whatsapp'] ?? '');
    $address    = trim($_POST['contact_address'] ?? '');
    $hours      = trim($_POST['contact_hours'] ?? '');
    $map_embed  = trim($_POST['contact_map_embed'] ?? '');

    if (empty($title)) {
        flashError(__('err_page_title'));
    } else {
      db()->query(
    "INSERT INTO tbl_pages 
    (page_key, title, content, meta_title, meta_description) 
    VALUES (?,?,?,?,?)
    ON DUPLICATE KEY UPDATE 
    title=?, 
    content=?, 
    meta_title=?, 
    meta_description=?",
    'sssssssss',
    $page_key,
    $title,
    $content,
    $meta_title,
    $meta_desc,
    $title,
    $content,
    $meta_title,
    $meta_desc
);
        saveSetting('contact_email', $email);
        saveSetting('contact_phone', $phone);
        saveSetting('contact_whatsapp', $whatsapp);
        saveSetting('contact_address', $address);
        saveSetting('contact_hours', $hours);
        saveSetting('contact_map_embed', $map_embed);

        logActivity('update', 'pages', __('contact_title') . ' ' . __('msg_page_saved'));
        flashSuccess(__('msg_page_saved'));
        header('Location: contact.php'); exit();
    }
}

$pg = db()->fetchOne(
    "SELECT * FROM tbl_pages WHERE page_key=?",
    's',
    $page_key
);
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-envelope"></i> <?= __('contact_title') ?></h1>
        <p><?= __('contact_subtitle') ?></p>
    </div>
    <?php if ($pg): ?><div class="page-header-right"><span class="text-muted" style="font-size:12px"><i class="fas fa-clock"></i> <?= __('col_last_edit') ?>: <?= formatDate($pg['updated_at']) ?></span></div><?php endif; ?>
</div>

<form method="POST" id="pageForm">
<div class="form-layout">
    <div class="form-main">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-heading"></i> <?= __('card_page_title') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_title') ?> <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($pg['title'] ?? __('contact_title')) ?>" required>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-address-book"></i> <?= __('card_contact_details') ?></span></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-envelope"></i> <?= __('label_email') ?></label>
                        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars(getSetting('contact_email')) ?>" placeholder="info@jayanewslive.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-phone"></i> <?= __('label_phone') ?></label>
                        <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars(getSetting('contact_phone')) ?>" placeholder="+91 98765 43210">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-whatsapp"></i> WhatsApp</label>
                        <input type="text" name="contact_whatsapp" class="form-control" value="<?= htmlspecialchars(getSetting('contact_whatsapp')) ?>" placeholder="+91 98765 43210">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-clock"></i> <?= __('label_office_hours') ?></label>
                        <input type="text" name="contact_hours" class="form-control" value="<?= htmlspecialchars(getSetting('contact_hours')) ?>" placeholder="<?= __('label_office_hours') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-map-marker-alt"></i> <?= __('label_address') ?></label>
                    <textarea name="contact_address" class="form-control" rows="2" placeholder="<?= __('label_address') ?>..."><?= htmlspecialchars(getSetting('contact_address')) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-map"></i> <?= __('label_map_embed') ?></label>
                    <textarea name="contact_map_embed" class="form-control" rows="3" placeholder="&lt;iframe src=&quot;https://www.google.com/maps/embed?...&quot;&gt;&lt;/iframe&gt;"><?= htmlspecialchars(getSetting('contact_map_embed')) ?></textarea>
                    <small class="text-muted"><?= __('hint_map') ?></small>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-align-left"></i> <?= __('card_extra_content') ?></span></div>
            <div class="card-body">
                <textarea name="content" id="pageContent" class="form-control" rows="10"><?= htmlspecialchars($pg['content'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-search"></i> <?= __('card_seo') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_title') ?></label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($pg['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_desc') ?></label>
                    <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($pg['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
        </div>
    </div>
</div>
</form>

<script src="https://cdn.tiny.cloud/1/fdnmpjrl01lldr548jirvuqkhtz7gpqbf5yt03jm7334xeno/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#pageContent',
    plugins: 'lists link table code fullscreen',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code fullscreen',
    skin: 'oxide-dark', content_css: 'dark',
    height: 280,
    menubar: false,
    body_class: 'content-body',
    content_style: 'body { font-family: Inter, Noto Sans Tamil, sans-serif; font-size: 14px; color: #eaedf5; background: #0d0f1a; padding: 12px; }'
});
</script>
<?php include '../includes/footer.php'; ?>