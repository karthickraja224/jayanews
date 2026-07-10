<?php
// news/news-trash.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('nav_trash');
$breadcrumbs = [__('nav_news') => BASE_URL . 'news/news-list.php', __('nav_trash') => null];

$search   = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = NEWS_PER_PAGE;

$where  = ["n.status = 'trash'"];
$params = []; $types = '';
if ($search) { $where[] = "n.title LIKE ?"; $params[] = "%$search%"; $types .= 's'; }

$where_sql = 'WHERE ' . implode(' AND ', $where);
$total     = db()->fetchOne("SELECT COUNT(*) c FROM tbl_news n $where_sql", $types, ...$params)['c'];
$offset    = ($page - 1) * $per_page;

$news_list = db()->fetchAll(
    "SELECT n.*, c.name_english cat_name, u.name author_name
     FROM tbl_news n
     LEFT JOIN tbl_categories c ON c.id = n.category_id
     LEFT JOIN tbl_users u ON u.id = n.author_id
     $where_sql ORDER BY n.updated_at DESC LIMIT $per_page OFFSET $offset",
    $types, ...$params
);

$base_url = "news-trash.php?q=" . urlencode($search);

// Empty trash action
if (isset($_POST['empty_trash']) && hasRole(['superadmin', 'admin'])) {
    $trashed = db()->fetchAll("SELECT featured_image FROM tbl_news WHERE status='trash'");
    foreach ($trashed as $t) { if ($t['featured_image']) deleteFile($t['featured_image']); }
    db()->query("DELETE FROM tbl_news WHERE status='trash'");
    logActivity('delete', 'news', __('trash_empty'));
    flashSuccess(__('msg_trash_emptied'));
    header('Location: news-trash.php'); exit();
}

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-trash"></i> <?= __('nav_trash') ?></h1>
        <p><?= __('trash_count') ?> <?= number_format($total) ?></p>
    </div>
    <div class="page-header-right">
        <?php if ($total > 0 && hasRole(['superadmin', 'admin'])): ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('<?= __('confirm_empty_trash') ?>')">
            <button type="submit" name="empty_trash" class="btn btn-danger"><i class="fas fa-fire"></i> <?= __('btn_empty_trash') ?></button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex:1">
            <input type="text" name="q" class="form-control" placeholder="<?= __('search_trash') ?>" value="<?= htmlspecialchars($search) ?>" style="max-width:300px">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> <?= __('btn_search') ?></button>
            <?php if ($search): ?><a href="news-trash.php" class="btn btn-secondary"><i class="fas fa-times"></i> <?= __('btn_reset') ?></a><?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th><?= __('col_news') ?></th>
                    <th><?= __('col_category') ?></th>
                    <th><?= __('col_author') ?></th>
                    <th><?= __('col_delete_date') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($news_list): foreach ($news_list as $i => $n): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <?php if ($n['featured_image']): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($n['featured_image']) ?>" class="news-thumb" style="opacity:.5">
                        <?php else: ?>
                        <div class="news-thumb" style="display:flex;align-items:center;justify-content:center;background:var(--bg-hover);border-radius:5px;border:1px solid var(--border);opacity:.5"><i class="fas fa-image" style="color:var(--text-muted)"></i></div>
                        <?php endif; ?>
                        <strong style="color:var(--text-muted)"><?= mb_substr(htmlspecialchars($n['title']), 0, 60) ?><?= mb_strlen($n['title']) > 60 ? '…' : '' ?></strong>
                    </div>
                </td>
                <td><span class="text-muted"><?= htmlspecialchars($n['cat_name'] ?? '—') ?></span></td>
                <td><span class="text-muted"><?= htmlspecialchars($n['author_name'] ?? '—') ?></span></td>
                <td><span class="text-muted"><?= formatDate($n['updated_at'], 'd M Y') ?></span></td>
                <td>
                    <div class="action-btns">
                        <a href="news-delete.php?id=<?= $n['id'] ?>&action=restore" class="btn btn-sm btn-success btn-icon" title="<?= __('btn_reset') ?>"><i class="fas fa-undo"></i></a>
                        <a href="news-delete.php?id=<?= $n['id'] ?>&action=delete" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-times"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6">
                <div class="empty-state">
                    <i class="fas fa-trash"></i>
                    <h3><?= __('trash_empty') ?></h3>
                    <p><?= __('trash_empty_hint') ?></p>
                </div>
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_page, $page, $base_url) ?>
</div>

<?php include '../includes/footer.php'; ?>