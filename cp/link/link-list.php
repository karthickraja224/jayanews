<?php
// link/link-list.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('links_title');
$breadcrumbs = [__('nav_links') => null];

if (isset($_GET['delete'])) {
    $row = db()->fetchOne("SELECT file FROM tbl_links WHERE id=?", 'i', (int)$_GET['id']);
    if ($row && $row['file']) deleteFile($row['file']);
    db()->query("DELETE FROM tbl_links WHERE id=?", 'i', (int)$_GET['id']);
    logActivity('delete', 'links', __('msg_link_deleted'));
    flashSuccess(__('msg_link_deleted'));
    header('Location: link-list.php'); exit();
}

$search = trim($_GET['search'] ?? '');
$where = ''; $params = []; $types = '';
if ($search) { $where = "WHERE title LIKE ?"; $params = ["%$search%"]; $types = 's'; }

$links = db()->fetchAll("SELECT * FROM tbl_links $where ORDER BY sort_order, id DESC", $types, ...$params);

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-link"></i> <?= __('links_title') ?></h1>
        <p><?= __('links_count') ?> <?= count($links) ?></p>
    </div>
    <div class="page-header-right">
        <a href="link-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('btn_add_link') ?></a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;justify-content:flex-end">
        <form method="GET" style="display:flex;gap:8px">
            <input type="text" name="search" class="form-control" placeholder="<?= __('placeholder_search') ?>" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= __('col_title') ?></th>
                    <th><?= __('col_file') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($links): foreach ($links as $l): ?>
            <tr>
                <td><strong><?= htmlspecialchars($l['title']) ?></strong></td>
                <td>
                    <a href="<?= BASE_URL . htmlspecialchars($l['file']) ?>" target="_blank" rel="noopener">
                        <?= htmlspecialchars(basename($l['file'])) ?>
                    </a>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="link-edit.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="link-list.php?delete=1&id=<?= $l['id'] ?>" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3"><div class="empty-state"><i class="fas fa-link"></i><h3><?= __('no_links') ?></h3><p><a href="link-add.php" class="btn btn-primary btn-sm"><?= __('btn_add_link') ?></a></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
