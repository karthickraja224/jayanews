<?php
// subcategory/subcategory-edit.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$id  = (int)($_GET['id'] ?? 0);
$sub = db()->fetchOne("SELECT * FROM tbl_subcategories WHERE id=?", 'i', $id);
if (!$sub) { flashError(__('msg_subcat_not_found')); header('Location: subcategory-list.php'); exit(); }

$page_title  = __('edit_subcat_title');
$breadcrumbs = [__('subcat_title') => BASE_URL . 'subcategory/subcategory-list.php', __('btn_edit') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_en = trim($_POST['name_english'] ?? '');
    $name_ta = trim($_POST['name_tamil'] ?? '');
    $slug    = trim($_POST['slug'] ?? '');
    $cat_id  = (int)($_POST['category_id'] ?? 0);
    $sort    = (int)($_POST['sort_order'] ?? 0);
    $status  = isset($_POST['status']) ? 1 : 0;

    if (empty($name_en)) { flashError(__('err_name_required')); }
    elseif (!$cat_id)    { flashError(__('err_cat_required')); }
    else {
        if (empty($slug)) $slug = slugify($name_en);
        $slug = uniqueSlug('tbl_subcategories', $slug, $id);
        db()->query(
            "UPDATE tbl_subcategories SET category_id=?,name_english=?,name_tamil=?,slug=?,sort_order=?,status=? WHERE id=?",
            'isssiii', $cat_id, $name_en, $name_ta, $slug, $sort, $status, $id
        );
        logActivity('update', 'subcategory', "Sub-category updated: $name_en");
        flashSuccess(__('msg_subcat_updated'));
        $sub = db()->fetchOne("SELECT * FROM tbl_subcategories WHERE id=?", 'i', $id);
    }
}
$categories = getCategories();
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left"><h1><i class="fas fa-edit"></i> <?= __('edit_subcat_title') ?></h1></div>
    <div class="page-header-right"><a href="subcategory-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <form method="POST">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-folder-open"></i> <?= __('card_subcat_details') ?></span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_category') ?> <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value=""><?= __('select_cat_required') ?></option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $sub['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name_english']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_name_en') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name_english" class="form-control" value="<?= htmlspecialchars($sub['name_english']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_name_ta') ?></label>
                        <input type="text" name="name_tamil" class="form-control" value="<?= htmlspecialchars($sub['name_tamil'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($sub['slug']) ?>">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('label_order') ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)$sub['sort_order'] ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('label_active') ?></label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                                <input type="checkbox" name="status" <?= $sub['status']?'checked':'' ?>> <?= __('label_active') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_update') ?></button>
                <a href="subcategory-list.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
