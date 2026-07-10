<?php
// slider/slider-add.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('add_slider_title');
$breadcrumbs = [__('nav_slider') => BASE_URL . 'slider/slider-list.php', __('add_slider_title') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $link     = trim($_POST['link'] ?? '');
    $sort     = (int)($_POST['sort_order'] ?? 0);
    $status   = isset($_POST['status']) ? 1 : 0;
    $slug     = $title ? uniqueSlug('tbl_slider', slugify($title)) : null;

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $up = uploadImage($_FILES['image'], 'slider');
        if (isset($up['error'])) { flashError($up['error']); }
        else { $image = $up; }
    }

    if (!$image) { flashError(__('err_image_required')); }
    else {
        db()->insert(
            "INSERT INTO tbl_slider (title, subtitle, slug, image, content, link_url, sort_order, status) VALUES (?,?,?,?,?,?,?,?)",
            'ssssssii', $title, $subtitle, $slug, $image, $content, $link, $sort, $status
        );
        logActivity('create', 'slider', __('msg_slide_added'));
        flashSuccess(__('msg_slide_added'));
        header('Location: slider-list.php'); exit();
    }
}
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left"><h1><i class="fas fa-plus-circle"></i> <?= __('add_slider_title') ?></h1></div>
    <div class="page-header-right"><a href="slider-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-image"></i> <?= __('card_slider_details') ?></span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_image_required') ?> <span class="text-danger">*</span></label>
                        <div class="image-upload-area" id="imageUploadArea" onclick="document.getElementById('imageInput').click()">
                            <div id="imagePreviewWrap" style="display:none"><img id="imagePreview" style="max-height:200px;border-radius:6px"></div>
                            <div id="imageUploadPlaceholder">
                                <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--text-muted)"></i>
                                <p style="color:var(--text-muted);margin-top:8px"><?= __('img_select_hint') ?><br><small><?= __('img_types') ?></small></p>
                            </div>
                        </div>
                        <input type="file" name="image" id="imageInput" accept="image/*" style="display:none" required onchange="previewImg(this)">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_title') ?></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="<?= __('label_title') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_subtitle') ?></label>
                        <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($_POST['subtitle'] ?? '') ?>" placeholder="<?= __('label_subtitle') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">செய்தி விவரம் / Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="8" placeholder="Full content shown when someone clicks this slide..."><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_link_url') ?> (Optional — external link override)</label>
                        <input type="url" name="link" class="form-control" value="<?= htmlspecialchars($_POST['link'] ?? '') ?>" placeholder="https://... (leave blank to show the content above instead)">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('col_order') ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)($_POST['sort_order'] ?? 0) ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('col_status') ?></label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                                <input type="checkbox" name="status" checked> <?= __('status_active') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
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