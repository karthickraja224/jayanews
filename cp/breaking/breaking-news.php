<?php
// breaking/breaking-news.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('breaking_title');
$breadcrumbs = [__('breaking_title') => null];

// Add / Edit / Delete / Toggle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $text      = trim($_POST['text'] ?? '');
        $link      = trim($_POST['link'] ?? '');
        $sort      = (int)($_POST['sort_order'] ?? 0);
        $status    = isset($_POST['status']) ? 1 : 0;

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $up = uploadImage($_FILES['image'], 'breaking');
            if (isset($up['error'])) { flashError($up['error']); }
            else { $image = $up; }
        }

        if ($text) {
            db()->insert(
                "INSERT INTO tbl_breaking_news (text, image, link_url, sort_order, status) VALUES (?,?,?,?,?)",
                'sssii', $text, $image, $link, $sort, $status
            );
            logActivity('create', 'breaking_news', __('msg_breaking_added'));
            flashSuccess(__('msg_breaking_added'));
        } else { flashError(__('err_text_required')); }
    }

    if ($action === 'edit') {
        $id   = (int)($_POST['id'] ?? 0);
        $text = trim($_POST['text'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $sort = (int)($_POST['sort_order'] ?? 0);
        $st   = isset($_POST['status']) ? 1 : 0;

        $existing = db()->fetchOne("SELECT image FROM tbl_breaking_news WHERE id=?", 'i', $id);
        $image    = $existing['image'] ?? null;

       if (isset($_POST['remove_image']) && $_POST['remove_image'] && $image) {
    deleteFile($image);
    $image = null;
}
if (!empty($_FILES['image']['name'])) {
    $up = uploadImage($_FILES['image'], 'breaking');
    if (isset($up['error'])) { flashError($up['error']); }
    else {
        if ($image) deleteFile($image);
        $image = $up;
    }
}

        if ($text) {
            db()->query(
                "UPDATE tbl_breaking_news SET text=?,image=?,link_url=?,sort_order=?,status=? WHERE id=?",
                'sssiii', $text, $image, $link, $sort, $st, $id
            );
            logActivity('update', 'breaking_news', __('msg_breaking_updated'));
            flashSuccess(__('msg_breaking_updated'));
        }
    }

    if ($action === 'delete') {
        $id  = (int)($_POST['id'] ?? 0);
        $row = db()->fetchOne("SELECT image FROM tbl_breaking_news WHERE id=?", 'i', $id);
        if ($row && !empty($row['image'])) deleteFile($row['image']);
        db()->query("DELETE FROM tbl_breaking_news WHERE id=?", 'i', $id);
        logActivity('delete', 'breaking_news', __('msg_breaking_deleted'));
        flashSuccess(__('msg_breaking_deleted'));
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        db()->query("UPDATE tbl_breaking_news SET status=IF(status=1,0,1) WHERE id=?", 'i', $id);
        flashSuccess(__('msg_status_changed'));
    }

    header('Location: breaking-news.php'); exit();
}

$breaking_list = db()->fetchAll("SELECT * FROM tbl_breaking_news ORDER BY sort_order, id DESC");
$edit_item     = null;
if (isset($_GET['edit'])) {
    $edit_item = db()->fetchOne("SELECT * FROM tbl_breaking_news WHERE id=?", 'i', (int)$_GET['edit']);
}

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-bolt"></i> <?= __('breaking_title') ?></h1>
        <p><?= __('breaking_subtitle') ?></p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;align-items:start">

    <!-- Add / Edit Form -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-<?= $edit_item ? 'edit' : 'plus-circle' ?>"></i> <?= $edit_item ? __('btn_edit') : __('btn_add_breaking') ?></span>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $edit_item ? 'edit' : 'add' ?>">
                <?php if ($edit_item): ?><input type="hidden" name="id" value="<?= $edit_item['id'] ?>"><?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Image (Optional)</label>
                    <?php if (!empty($edit_item['image'])): ?>
                    <div style="margin-bottom:10px">
                        <img src="<?= BASE_URL . htmlspecialchars($edit_item['image']) ?>" style="max-height:120px;border-radius:6px;border:1px solid var(--border)">
                        <label style="display:flex;align-items:center;gap:6px;margin-top:8px;cursor:pointer;color:var(--text-muted);font-size:13px">
                            <input type="checkbox" name="remove_image" value="1"> Remove current image
                        </label>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label"><?= __('label_news_text') ?> <span class="text-danger">*</span></label>
                    <textarea name="text" class="form-control" rows="3" required><?= htmlspecialchars($edit_item['text'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_link_optional') ?></label>
                    <input type="url" name="link" class="form-control" value="<?= htmlspecialchars($edit_item['link_url'] ?? '') ?>" placeholder="https://...">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="form-group">
                        <label class="form-label"><?= __('col_order') ?></label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($edit_item['sort_order'] ?? 0) ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('col_status') ?></label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                            <input type="checkbox" name="status" <?= (!$edit_item || $edit_item['status']) ? 'checked' : '' ?>> <?= __('status_active') ?>
                        </label>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $edit_item ? __('btn_update_breaking') : __('btn_add_breaking') ?></button>
                    <?php if ($edit_item): ?><a href="breaking-news.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-list"></i> <?= __('card_breaking_list') ?></span></div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Image</th>
                        <th><?= __('label_news_text') ?></th>
                        <th><?= __('col_order') ?></th>
                        <th><?= __('col_status') ?></th>
                        <th><?= __('col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($breaking_list): foreach ($breaking_list as $i => $b): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?php if (!empty($b['image'])): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($b['image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px">
                        <?php else: ?>
                        <span class="text-muted" style="font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div><?= mb_substr(htmlspecialchars($b['text']), 0, 60) ?><?= mb_strlen($b['text']) > 60 ? '…' : '' ?></div>
                        <?php if ($b['link_url']): ?><small class="text-muted"><i class="fas fa-link" style="font-size:10px"></i> <?= __('label_link_optional') ?></small><?php endif; ?>
                    </td>
                    <td><?= $b['sort_order'] ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                            <button type="submit" class="badge <?= $b['status'] ? 'badge-success' : 'badge-danger' ?>" style="border:none;cursor:pointer">
                                <?= $b['status'] ? __('status_active') : __('status_inactive') ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="breaking-news.php?edit=<?= $b['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('<?= __('confirm_delete') ?>')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6"><div class="empty-state"><i class="fas fa-bolt"></i><h3><?= __('no_news') ?></h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>