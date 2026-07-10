<?php
// category/category-add.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('add_cat_title');
$breadcrumbs = [__('cat_title') => BASE_URL . 'category/category-list.php', __('add_cat_title') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_en   = trim($_POST['name_english'] ?? '');
    $name_ta   = trim($_POST['name_tamil'] ?? '');
    $slug      = trim($_POST['slug'] ?? '');
    $icon      = trim($_POST['icon'] ?? '');
    $color     = trim($_POST['color'] ?? '#e63946');
    $sort      = (int)($_POST['sort_order'] ?? 0);
    $status    = isset($_POST['status']) ? 1 : 0;
    $meta_t    = trim($_POST['meta_title'] ?? '');
    $meta_d    = trim($_POST['meta_description'] ?? '');

    if (empty($name_en)) { flashError(__('err_name_required')); }
    else {
        if (empty($slug)) $slug = slugify($name_en);
        $slug = uniqueSlug('tbl_categories', $slug);
        $id = db()->insert(
            "INSERT INTO tbl_categories (name_english, name_tamil, slug, icon, color, sort_order, status, meta_title, meta_description) VALUES (?,?,?,?,?,?,?,?,?)",
            'sssssisis', $name_en, $name_ta, $slug, $icon, $color, $sort, $status, $meta_t, $meta_d
        );
        logActivity('create', 'category', "Category added: $name_en");
        flashSuccess(__('msg_cat_added'));
        header('Location: category-list.php'); exit();
    }
}
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-plus-circle"></i> <?= __('add_cat_title') ?></h1>
    </div>
    <div class="page-header-right">
        <a href="category-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a>
    </div>
</div>

<div class="form-layout">
    <div class="form-main">
        <form method="POST">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-info-circle"></i> <?= __('card_cat_details') ?></span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_name_en') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name_english" class="form-control" value="<?= htmlspecialchars($_POST['name_english'] ?? '') ?>" placeholder="Category name in English" required id="nameEn">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_name_ta') ?></label>
                        <input type="text" name="name_tamil" class="form-control" value="<?= htmlspecialchars($_POST['name_tamil'] ?? '') ?>" placeholder="வகை பெயர் தமிழில்">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" placeholder="category-slug" id="slugField">
                        <small class="text-muted"><?= __('hint_slug_auto') ?></small>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label">Font Awesome Icon</label>
                            <div style="display:flex;gap:8px;align-items:center">
                                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($_POST['icon'] ?? '') ?>" placeholder="newspaper" id="iconInput">
                                <i class="fas fa-newspaper" id="iconPreview" style="font-size:20px;color:var(--primary-red);min-width:24px"></i>
                            </div>
                            <small class="text-muted"><?= __('hint_icon') ?></small>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('label_color') ?></label>
                            <input type="color" name="color" class="form-control" value="<?= htmlspecialchars($_POST['color'] ?? '#e63946') ?>" style="height:42px;padding:4px">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('label_order') ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)($_POST['sort_order'] ?? 0) ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('label_active') ?></label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                                <input type="checkbox" name="status" <?= isset($_POST['status']) || !isset($_POST['name_english']) ? 'checked' : '' ?>> <?= __('label_active') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:16px">
                <div class="card-header"><span class="card-title"><i class="fas fa-search"></i> SEO</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
                <a href="category-list.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('nameEn').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');
    document.getElementById('slugField').placeholder = slug || 'category-slug';
});
document.getElementById('iconInput').addEventListener('input', function() {
    document.getElementById('iconPreview').className = 'fas fa-' + (this.value || 'tag');
});
</script>
<?php include '../includes/footer.php'; ?>
