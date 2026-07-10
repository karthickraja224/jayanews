<?php
// pages/privacy.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('privacy_title');
$breadcrumbs = [__('nav_pages') => null, __('privacy_title') => null];

$page_key = 'privacy';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $content    = $_POST['content'] ?? '';
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc  = trim($_POST['meta_description'] ?? '');

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
        logActivity('update', 'pages', __('privacy_title') . ' ' . __('msg_page_saved'));
        flashSuccess(__('msg_page_saved'));
        header('Location: privacy.php'); exit();
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
        <h1><i class="fas fa-shield-alt"></i> <?= __('privacy_title') ?></h1>
        <p><?= __('privacy_title') ?></p>
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
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($pg['title'] ?? __('privacy_title')) ?>" required>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-header"><span class="card-title"><i class="fas fa-align-left"></i> <?= __('card_content') ?></span></div>
            <div class="card-body">
                <textarea name="content" id="pageContent" class="form-control" rows="15"><?= htmlspecialchars($pg['content'] ?? '') ?></textarea>
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
    plugins: 'lists link image table code fullscreen media',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | table | code fullscreen',
    skin: 'oxide-dark', content_css: 'dark',
    height: 420,
    menubar: false,
    body_class: 'content-body',
    content_style: 'body { font-family: Inter, Noto Sans Tamil, sans-serif; font-size: 14px; color: #eaedf5; background: #0d0f1a; padding: 12px; }'
});
</script>
<?php include '../includes/footer.php'; ?>