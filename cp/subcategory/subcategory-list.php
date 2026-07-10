<?php
// subcategory/subcategory-list.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('subcat_title');
$breadcrumbs = [__('cat_title') => BASE_URL . 'category/category-list.php', __('subcat_title') => null];

if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $tid = (int)$_GET['id'];
    db()->query("UPDATE tbl_subcategories SET status = IF(status=1,0,1) WHERE id=?", 'i', $tid);
    flashSuccess(__('msg_status_changed'));
    header('Location: subcategory-list.php'); exit();
}

$cat_id = (int)($_GET['cat_id'] ?? 0);
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per_pg = 20;

$where  = []; $params = []; $types = '';
if ($cat_id) { $where[] = "s.category_id=?"; $params[] = $cat_id; $types .= 'i'; }
if ($search) { $where[] = "(s.name_english LIKE ? OR s.name_tamil LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total  = db()->fetchOne("SELECT COUNT(*) c FROM tbl_subcategories s $where_sql", $types, ...$params)['c'];
$offset = ($page - 1) * $per_pg;
$subs   = db()->fetchAll(
    "SELECT s.*, c.name_english cat_name,
     (SELECT COUNT(*) FROM tbl_news n WHERE n.subcategory_id=s.id AND n.status!='trash') news_count
     FROM tbl_subcategories s
     LEFT JOIN tbl_categories c ON c.id=s.category_id
     $where_sql ORDER BY c.name_english, s.sort_order, s.name_english LIMIT $per_pg OFFSET $offset",
    $types, ...$params
);
$categories = getCategories();
$base_url   = "subcategory-list.php?cat_id={$cat_id}&q=" . urlencode($search);
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-folder-open"></i> <?= __('subcat_title') ?></h1>
        <p><?= __('news_total') ?> <?= number_format($total) ?> <?= __('subcat_count') ?></p>
    </div>
    <div class="page-header-right">
        <a href="subcategory-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('btn_add_subcat') ?></a>
    </div>
</div>

<div class="card">
    <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex:1;flex-wrap:wrap">
            <select name="cat_id" class="form-control" style="max-width:200px">
                <option value=""><?= __('all_categories') ?></option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat_id==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name_english']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="q" class="form-control" placeholder="<?= __('search_subcat') ?>" value="<?= htmlspecialchars($search) ?>" style="max-width:250px">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> <?= __('btn_search') ?></button>
            <?php if ($search || $cat_id): ?><a href="subcategory-list.php" class="btn btn-secondary"><i class="fas fa-times"></i> <?= __('btn_reset') ?></a><?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th><?= __('col_subcat_en') ?></th>
                    <th><?= __('col_subcat_ta') ?></th>
                    <th><?= __('col_category') ?></th>
                    <th>Slug</th>
                    <th><?= __('col_news_count') ?></th>
                    <th><?= __('col_status') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($subs): foreach ($subs as $i => $s): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($s['name_english']) ?></strong></td>
                <td><?= htmlspecialchars($s['name_tamil'] ?? '—') ?></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($s['cat_name'] ?? '—') ?></span></td>
                <td><code style="font-size:12px;background:var(--bg-hover);padding:2px 6px;border-radius:4px"><?= htmlspecialchars($s['slug']) ?></code></td>
                <td><span class="badge badge-secondary"><?= $s['news_count'] ?></span></td>
                <td>
                    <a href="subcategory-list.php?toggle=1&id=<?= $s['id'] ?>" class="badge <?= $s['status'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer">
                        <?= $s['status'] ? __('status_active') : __('status_inactive') ?>
                    </a>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="subcategory-edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="subcategory-delete.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="8"><div class="empty-state"><i class="fas fa-folder-open"></i><h3><?= __('no_subcategories') ?></h3><p><a href="subcategory-add.php" class="btn btn-primary btn-sm"><?= __('btn_add_subcat') ?></a></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_pg, $page, $base_url) ?>
</div>

<?php include '../includes/footer.php'; ?>
