<?php
// category/category-edit.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$id  = (int)($_GET['id'] ?? 0);
$cat = db()->fetchOne("SELECT * FROM tbl_categories WHERE id=?", 'i', $id);
if (!$cat) { flashError(__('msg_cat_not_found')); header('Location: category-list.php'); exit(); }

$page_title  = __('edit_cat_title');
$breadcrumbs = [__('cat_title') => BASE_URL . 'category/category-list.php', __('btn_edit') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_en = trim($_POST['name_english'] ?? '');
    $name_ta = trim($_POST['name_tamil'] ?? '');
    $slug    = trim($_POST['slug'] ?? '');
    $icon    = trim($_POST['icon'] ?? '');
    $color   = trim($_POST['color'] ?? '#e63946');
    $sort    = (int)($_POST['sort_order'] ?? 0);
    $status  = isset($_POST['status']) ? 1 : 0;
    $meta_t  = trim($_POST['meta_title'] ?? '');
    $meta_d  = trim($_POST['meta_description'] ?? '');

    if (empty($name_en)) { flashError(__('err_name_required')); }
    else {
        if (empty($slug)) $slug = slugify($name_en);
        $slug = uniqueSlug('tbl_categories', $slug, $id);
        db()->query(
            "UPDATE tbl_categories SET name_english=?,name_tamil=?,slug=?,icon=?,color=?,sort_order=?,status=?,meta_title=?,meta_description=? WHERE id=?",
            'sssssisisi', $name_en, $name_ta, $slug, $icon, $color, $sort, $status, $meta_t, $meta_d, $id
        );
        logActivity('update', 'category', "Category updated: $name_en");
        flashSuccess(__('msg_cat_updated'));
        $cat = db()->fetchOne("SELECT * FROM tbl_categories WHERE id=?", 'i', $id);
    }
}
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-edit"></i> <?= __('edit_cat_title') ?></h1>
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
                        <input type="text" name="name_english" class="form-control" value="<?= htmlspecialchars($cat['name_english']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_name_ta') ?></label>
                        <input type="text" name="name_tamil" class="form-control" value="<?= htmlspecialchars($cat['name_tamil'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($cat['slug']) ?>">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label">Font Awesome Icon</label>
                            <div style="display:flex;gap:8px;align-items:center">
                                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($cat['icon'] ?? '') ?>" id="iconInput">
                                <i class="fas fa-<?= htmlspecialchars($cat['icon'] ?? 'tag') ?>" id="iconPreview" style="font-size:20px;color:var(--primary-red);min-width:24px"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('label_color') ?></label>
                            <input type="color" name="color" class="form-control" value="<?= htmlspecialchars($cat['color'] ?? '#e63946') ?>" style="height:42px;padding:4px">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('label_order') ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)$cat['sort_order'] ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('label_active') ?></label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                                <input type="checkbox" name="status" <?= $cat['status'] ? 'checked' : '' ?>> <?= __('label_active') ?>
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
                        <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($cat['meta_title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($cat['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_update') ?></button>
                <a href="category-list.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('iconInput').addEventListener('input', function() {
    document.getElementById('iconPreview').className = 'fas fa-' + (this.value || 'tag');
});
</script>
<?php include '../includes/footer.php'; ?>
