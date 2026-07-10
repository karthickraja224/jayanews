<?php
// comments/comments.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('comments_title');
$breadcrumbs = [__('comments_title') => null];

// Quick actions: approve / spam / delete
if (isset($_GET['action']) && isset($_GET['id'])) {
    $cid    = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($action === 'approve') {
        db()->query("UPDATE tbl_comments SET status='approved' WHERE id=?", 'i', $cid);
        flashSuccess(__('msg_comment_approved'));
    } elseif ($action === 'pending') {
        db()->query("UPDATE tbl_comments SET status='pending' WHERE id=?", 'i', $cid);
        flashSuccess(__('msg_comment_pending'));
    } elseif ($action === 'spam') {
        db()->query("UPDATE tbl_comments SET status='spam' WHERE id=?", 'i', $cid);
        flashSuccess(__('msg_comment_spam'));
    } elseif ($action === 'delete') {
        db()->query("DELETE FROM tbl_comments WHERE id=?", 'i', $cid);
        flashSuccess(__('msg_comment_deleted'));
    }
    logActivity($action, 'comments', __('msg_comment_deleted') . " #$cid " . __('col_actions') . " $action");
    $back_status = $_GET['status'] ?? 'all';
    $back_q      = $_GET['q'] ?? '';
    header('Location: comments.php?status=' . urlencode($back_status) . '&q=' . urlencode($back_q)); exit();
}

$status   = $_GET['status'] ?? 'all';
$search   = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = defined('COMMENTS_PER_PAGE') ? COMMENTS_PER_PAGE : 20;

$where  = []; $params = []; $types = '';
if ($status !== 'all') { $where[] = "cm.status = ?"; $params[] = $status; $types .= 's'; }
if ($search) { $where[] = "(cm.name LIKE ? OR cm.comment LIKE ? OR n.title LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'sss'; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = db()->fetchOne(
    "SELECT COUNT(*) c FROM tbl_comments cm LEFT JOIN tbl_news n ON n.id = cm.news_id $where_sql",
    $types, ...$params
)['c'];

$offset   = ($page - 1) * $per_page;
$comments = db()->fetchAll(
    "SELECT cm.*, n.title news_title, n.slug news_slug
     FROM tbl_comments cm LEFT JOIN tbl_news n ON n.id = cm.news_id
     $where_sql ORDER BY cm.created_at DESC LIMIT $per_page OFFSET $offset",
    $types, ...$params
);

$base_url = "comments.php?status={$status}&q=" . urlencode($search);
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-comments"></i> <?= __('comments_title') ?></h1>
        <p><?= __('comments_count') ?> <?= number_format($total) ?></p>
    </div>
</div>

<!-- Status tabs -->
<div class="tabs-wrapper">
    <div class="tabs">
        <?php
        $tab_items = [
            'all'      => ['label' => __('tab_all_comments'), 'icon' => 'list'],
            'pending'  => ['label' => __('tab_pending'), 'icon' => 'clock'],
            'approved' => ['label' => __('tab_approved'), 'icon' => 'check-circle'],
            'spam'     => ['label' => __('tab_spam'), 'icon' => 'ban'],
        ];
        foreach ($tab_items as $key => $item):
            $cnt = db()->fetchOne("SELECT COUNT(*) c FROM tbl_comments " . ($key === 'all' ? '' : "WHERE status='$key'"))['c'];
        ?>
        <button class="tab-btn <?= $status === $key ? 'active' : '' ?>"
            onclick="window.location='comments.php?status=<?= $key ?>&q=<?= urlencode($search) ?>'">
            <i class="fas fa-<?= $item['icon'] ?>"></i> <?= $item['label'] ?>
            <span style="background:var(--bg-hover);padding:1px 7px;border-radius:10px;font-size:11px;margin-left:4px"><?= $cnt ?></span>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex:1">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <input type="text" name="q" class="form-control" placeholder="<?= __('search_comments') ?>" value="<?= htmlspecialchars($search) ?>" style="max-width:340px">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> <?= __('btn_search') ?></button>
            <?php if ($search): ?><a href="comments.php?status=<?= $status ?>" class="btn btn-secondary"><i class="fas fa-times"></i> <?= __('btn_reset') ?></a><?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th><?= __('col_comment') ?></th>
                    <th><?= __('col_news') ?></th>
                    <th><?= __('col_status') ?></th>
                    <th><?= __('col_date') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($comments): foreach ($comments as $i => $c): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td style="max-width:340px">
                    <strong><?= htmlspecialchars($c['name']) ?></strong>
                    <?php if (!empty($c['email'])): ?>
                    <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($c['email']) ?></div>
                    <?php endif; ?>
                    <div style="margin-top:4px;color:var(--text-secondary)">
                        <?= mb_substr(htmlspecialchars($c['comment']), 0, 120) ?><?= mb_strlen($c['comment']) > 120 ? '…' : '' ?>
                    </div>
                </td>
                <td>
                    <?php if ($c['news_title']): ?>
                    <span class="text-muted"><?= mb_substr(htmlspecialchars($c['news_title']), 0, 40) ?><?= mb_strlen($c['news_title']) > 40 ? '…' : '' ?></span>
                    <?php else: ?>
                    <span class="text-muted"><?= __('deleted_news') ?></span>
                    <?php endif; ?>
                </td>
                <td><?= statusBadge($c['status']) ?></td>
                <td><span class="text-muted"><?= timeAgo($c['created_at']) ?></span></td>
                <td>
                    <div class="action-btns">
                        <?php if ($c['status'] !== 'approved'): ?>
                        <a href="comments.php?action=approve&id=<?= $c['id'] ?>&status=<?= $status ?>&q=<?= urlencode($search) ?>" class="btn btn-sm btn-success btn-icon" title="<?= __('tab_approved') ?>"><i class="fas fa-check"></i></a>
                        <?php endif; ?>
                        <?php if ($c['status'] !== 'pending'): ?>
                        <a href="comments.php?action=pending&id=<?= $c['id'] ?>&status=<?= $status ?>&q=<?= urlencode($search) ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('tab_pending') ?>"><i class="fas fa-clock"></i></a>
                        <?php endif; ?>
                        <?php if ($c['status'] !== 'spam'): ?>
                        <a href="comments.php?action=spam&id=<?= $c['id'] ?>&status=<?= $status ?>&q=<?= urlencode($search) ?>" class="btn btn-sm btn-warning btn-icon" title="<?= __('tab_spam') ?>"><i class="fas fa-ban"></i></a>
                        <?php endif; ?>
                        <a href="comments.php?action=delete&id=<?= $c['id'] ?>&status=<?= $status ?>&q=<?= urlencode($search) ?>" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6">
                <div class="empty-state"><i class="fas fa-comments"></i><h3><?= __('no_notifications') ?></h3></div>
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_page, $page, $base_url) ?>
</div>

<?php include '../includes/footer.php'; ?>