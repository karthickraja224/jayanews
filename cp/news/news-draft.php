<?php
// news/news-draft.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('nav_drafts');
$breadcrumbs = [__('nav_news') => BASE_URL . 'news/news-list.php', __('nav_drafts') => null];

$search   = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = NEWS_PER_PAGE;

$where  = ["n.status = 'draft'"];
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

$base_url = "news-draft.php?q=" . urlencode($search);
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-file-alt"></i> <?= __('nav_drafts') ?></h1>
        <p><?= __('news_total') ?> <?= number_format($total) ?> <?= __('nav_drafts') ?></p>
    </div>
    <div class="page-header-right">
        <a href="<?= BASE_URL ?>news/news-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('btn_add_news') ?></a>
    </div>
</div>

<div class="card">
    <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex:1">
            <input type="text" name="q" class="form-control" placeholder="<?= __('search_news') ?>" value="<?= htmlspecialchars($search) ?>" style="max-width:300px">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> <?= __('btn_search') ?></button>
            <?php if ($search): ?>
            <a href="news-draft.php" class="btn btn-secondary"><i class="fas fa-times"></i> <?= __('btn_reset') ?></a>
            <?php endif; ?>
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
                    <th><?= __('col_last_edit') ?></th>
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
                        <img src="<?= BASE_URL . htmlspecialchars($n['featured_image']) ?>" class="news-thumb">
                        <?php else: ?>
                        <div class="news-thumb" style="display:flex;align-items:center;justify-content:center;background:var(--bg-hover);border-radius:5px;border:1px solid var(--border)"><i class="fas fa-file-alt" style="color:var(--text-muted)"></i></div>
                        <?php endif; ?>
                        <div>
                            <strong><?= mb_substr(htmlspecialchars($n['title']), 0, 60) ?><?= mb_strlen($n['title']) > 60 ? '…' : '' ?></strong>
                            <?php if ($n['summary']): ?>
                            <div class="text-muted" style="font-size:12px;margin-top:2px"><?= mb_substr(htmlspecialchars($n['summary']), 0, 80) ?>…</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td><span class="text-muted"><?= htmlspecialchars($n['cat_name'] ?? '—') ?></span></td>
                <td><span class="text-muted"><?= htmlspecialchars($n['author_name'] ?? '—') ?></span></td>
                <td><span class="text-muted"><?= formatDate($n['updated_at'], 'd M Y, h:i A') ?></span></td>
                <td>
                    <div class="action-btns">
                        <a href="<?= BASE_URL ?>news/news-edit.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-primary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="news-delete.php?id=<?= $n['id'] ?>&action=trash" class="btn btn-sm btn-warning btn-icon" title="<?= __('nav_trash') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash-alt"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6">
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h3><?= __('no_drafts') ?></h3>
                    <p><?= __('save_draft_hint') ?></p>
                </div>
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_page, $page, $base_url) ?>
</div>

<?php include '../includes/footer.php'; ?>