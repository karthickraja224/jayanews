<?php
// slider/slider-edit.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$id     = (int)($_GET['id'] ?? 0);
$slider = db()->fetchOne("SELECT * FROM tbl_slider WHERE id=?", 'i', $id);
if (!$slider) { flashError(__('msg_slide_deleted')); header('Location: slider-list.php'); exit(); }

$page_title  = __('edit_news_title');
$breadcrumbs = [__('nav_slider') => BASE_URL . 'slider/slider-list.php', __('edit_news_title') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $link     = trim($_POST['link'] ?? '');
    $sort     = (int)($_POST['sort_order'] ?? 0);
    $status   = isset($_POST['status']) ? 1 : 0;
    $image    = $slider['image'];
    $slug     = $slider['slug'] ?: ($title ? uniqueSlug('tbl_slider', slugify($title), $id) : null);

    if (!empty($_FILES['image']['name'])) {
        $up = uploadImage($_FILES['image'], 'slider');
        if (isset($up['error'])) { flashError($up['error']); }
        else {
            if ($image) deleteFile($image);
            $image = $up;
        }
    }
    if (isset($_POST['remove_image']) && $_POST['remove_image'] && $image) {
        deleteFile($image); $image = null;
    }

    db()->query(
        "UPDATE tbl_slider SET title=?,subtitle=?,slug=?,image=?,content=?,link_url=?,sort_order=?,status=? WHERE id=?",
        'ssssssiii', $title, $subtitle, $slug, $image, $content, $link, $sort, $status, $id
    );
    logActivity('update', 'slider', __('msg_slide_added'));
    flashSuccess(__('msg_slide_added'));
    $slider = db()->fetchOne("SELECT * FROM tbl_slider WHERE id=?", 'i', $id);
}
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left"><h1><i class="fas fa-edit"></i> <?= __('edit_news_title') ?></h1></div>
    <div class="page-header-right"><a href="slider-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-image"></i> <?= __('card_slider_details') ?></span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_image_required') ?></label>
                        <?php if ($slider['image']): ?>
                        <div style="margin-bottom:10px">
                            <img src="<?= BASE_URL . htmlspecialchars($slider['image']) ?>" style="max-height:160px;border-radius:6px;border:1px solid var(--border)">
                            <label style="display:flex;align-items:center;gap:6px;margin-top:8px;cursor:pointer;color:var(--text-muted);font-size:13px">
                                <input type="checkbox" name="remove_image" value="1"> <?= __('img_replace') ?>
                            </label>
                        </div>
                        <?php endif; ?>
                        <div class="image-upload-area" onclick="document.getElementById('imageInput').click()" style="cursor:pointer">
                            <div id="imagePreviewWrap" style="display:none"><img id="imagePreview" style="max-height:140px;border-radius:6px"></div>
                            <div id="imageUploadPlaceholder" style="text-align:center;padding:20px">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:var(--text-muted)"></i>
                                <p style="color:var(--text-muted);font-size:13px;margin-top:6px"><?= __('img_replace') ?></p>
                            </div>
                        </div>
                        <input type="file" name="image" id="imageInput" accept="image/*" style="display:none" onchange="previewImg(this)">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_title') ?></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($slider['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_subtitle') ?></label>
                        <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($slider['subtitle'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">செய்தி விவரம் / Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="8" placeholder="Full content shown when someone clicks this slide..."><?= htmlspecialchars($slider['content'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_link_url') ?> (Optional — external link override)</label>
                        <input type="url" name="link" class="form-control" value="<?= htmlspecialchars($slider['link_url'] ?? '') ?>" placeholder="https://... (leave blank to show the content above instead)">
                    </div>
                    <?php if (!empty($slider['slug'])): ?>
                    <div class="form-group">
                        <label class="form-label">Slide URL (slug)</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($slider['slug']) ?>" readonly onclick="this.select()">
                        <small style="color:var(--text-muted)">Frontend link: /slide/<?= htmlspecialchars($slider['slug']) ?> (or /slide.php?slug=... if not using pretty URLs)</small>
                    </div>
                    <?php endif; ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('col_order') ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)$slider['sort_order'] ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('col_status') ?></label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                                <input type="checkbox" name="status" <?= $slider['status']?'checked':'' ?>> <?= __('status_active') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_update') ?></button>
                <a href="slider-list.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewWrap').style.display = 'block';
            document.getElementById('imageUploadPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php include '../includes/footer.php'; ?>