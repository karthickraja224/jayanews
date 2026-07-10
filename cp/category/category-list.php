<?php
// category/category-list.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('cat_title');
$breadcrumbs = [__('cat_title') => null];

// Handle quick toggle status
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $tid = (int)$_GET['id'];
    db()->query("UPDATE tbl_categories SET status = IF(status=1,0,1) WHERE id=?", 'i', $tid);
    flashSuccess(__('msg_status_changed'));
    header('Location: category-list.php'); exit();
}

$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per_pg = 20;

$where  = []; $params = []; $types = '';
if ($search) { $where[] = "(name_english LIKE ? OR name_tamil LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total  = db()->fetchOne("SELECT COUNT(*) c FROM tbl_categories $where_sql", $types, ...$params)['c'];
$offset = ($page - 1) * $per_pg;
$cats   = db()->fetchAll(
    "SELECT c.*, (SELECT COUNT(*) FROM tbl_news n WHERE n.category_id=c.id AND n.status!='trash') news_count
     FROM tbl_categories c $where_sql ORDER BY c.sort_order, c.name_english LIMIT $per_pg OFFSET $offset",
    $types, ...$params
);
$base_url = "category-list.php?q=" . urlencode($search);
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-tags"></i> <?= __('cat_title') ?></h1>
        <p><?= __('news_total') ?> <?= number_format($total) ?> <?= __('cat_count') ?></p>
    </div>
    <div class="page-header-right">
        <a href="category-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('btn_add_cat') ?></a>
    </div>
</div>

<div class="card">
    <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex:1">
            <input type="text" name="q" class="form-control" placeholder="<?= __('search_cat') ?>" value="<?= htmlspecialchars($search) ?>" style="max-width:300px">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> <?= __('btn_search') ?></button>
            <?php if ($search): ?><a href="category-list.php" class="btn btn-secondary"><i class="fas fa-times"></i> <?= __('btn_reset') ?></a><?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th><?= __('col_cat_en') ?></th>
                    <th><?= __('col_cat_ta') ?></th>
                    <th>Slug</th>
                    <th><?= __('col_order') ?></th>
                    <th><?= __('col_news_count') ?></th>
                    <th><?= __('col_status') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($cats): foreach ($cats as $i => $cat): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <?php if ($cat['icon']): ?><i class="fas fa-<?= htmlspecialchars($cat['icon']) ?>" style="color:var(--primary-red)"></i><?php endif; ?>
                        <strong><?= htmlspecialchars($cat['name_english']) ?></strong>
                    </div>
                </td>
                <td><?= htmlspecialchars($cat['name_tamil'] ?? '—') ?></td>
                <td><code style="font-size:12px;background:var(--bg-hover);padding:2px 6px;border-radius:4px"><?= htmlspecialchars($cat['slug']) ?></code></td>
                <td><?= (int)$cat['sort_order'] ?></td>
                <td><span class="badge badge-secondary"><?= $cat['news_count'] ?></span></td>
                <td>
                    <a href="category-list.php?toggle=1&id=<?= $cat['id'] ?>" class="badge <?= $cat['status'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer">
                        <?= $cat['status'] ? __('status_active') : __('status_inactive') ?>
                    </a>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="category-edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="category-delete.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="8">
                <div class="empty-state"><i class="fas fa-tags"></i><h3><?= __('no_categories') ?></h3><p><a href="category-add.php" class="btn btn-primary btn-sm"><?= __('btn_add_cat') ?></a></p></div>
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_pg, $page, $base_url) ?>
</div>

<?php include '../includes/footer.php'; ?>
