<?php
// rss/rss-list.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('rss_title');
$breadcrumbs = [__('nav_rss') => null];

// Toggle auto-update
if (isset($_GET['toggle'])) {
    db()->query("UPDATE tbl_rss_feeds SET auto_update=IF(auto_update=1,0,1) WHERE id=?", 'i', (int)$_GET['id']);
    flashSuccess(__('msg_status_changed'));
    header('Location: rss-list.php'); exit();
}

// Delete
if (isset($_GET['delete'])) {
    db()->query("DELETE FROM tbl_rss_feeds WHERE id=?", 'i', (int)$_GET['id']);
    logActivity('delete', 'rss', __('msg_rss_deleted'));
    flashSuccess(__('msg_rss_deleted'));
    header('Location: rss-list.php'); exit();
}

$search = trim($_GET['search'] ?? '');
$where  = '';
$params = [];
$types  = '';
if ($search) {
    $where  = "WHERE r.feed_name LIKE ? OR r.feed_url LIKE ? OR r.language LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
    $types  = 'sss';
}

$feeds = db()->fetchAll(
    "SELECT r.*, c.name_english, c.name_tamil
     FROM tbl_rss_feeds r
     LEFT JOIN tbl_categories c ON c.id = r.category_id
     $where
     ORDER BY r.id DESC",
    $types, ...$params
);

$cron_url = BASE_URL . 'rss-feed/cronJob/Update.php';

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-rss"></i> <?= __('rss_title') ?></h1>
        <p><?= __('rss_count') ?> <?= count($feeds) ?></p>
    </div>
    <div class="page-header-right">
        <a href="rss-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('btn_add_feed') ?></a>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-body">
        <label class="form-label" style="text-align:center;display:block"><?= __('label_cron_url') ?></label>
        <div style="display:flex;gap:10px;align-items:center;max-width:700px;margin:0 auto">
            <input type="text" readonly id="cronUrl" class="form-control" value="<?= htmlspecialchars($cron_url) ?>" style="text-align:center">
            <button type="button" class="btn btn-secondary btn-icon" title="<?= __('btn_copy') ?>" onclick="copyCron()"><i class="fas fa-copy"></i></button>
        </div>
        <p style="text-align:center;color:var(--text-muted);font-size:13px;margin-top:8px"><?= __('hint_cron') ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
        <form method="GET" style="display:flex;gap:8px">
            <input type="text" name="search" class="form-control" placeholder="<?= __('placeholder_search') ?>" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= __('col_language') ?></th>
                    <th><?= __('col_category') ?></th>
                    <th><?= __('col_feed_name') ?></th>
                    <th><?= __('col_feed_url') ?></th>
                    <th><?= __('col_auto_update') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($feeds): foreach ($feeds as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['language']) ?></td>
                <td><?= htmlspecialchars((($_SESSION['lang'] ?? 'en') === 'ta' ? ($f['name_tamil'] ?: $f['name_english']) : $f['name_english']) ?? '—') ?></td>
                <td><strong><?= htmlspecialchars($f['feed_name']) ?></strong></td>
                <td>
                    <a href="<?= htmlspecialchars($f['feed_url']) ?>" target="_blank" rel="noopener" style="word-break:break-all">
                        <?= htmlspecialchars($f['feed_url']) ?>
                    </a>
                </td>
                <td>
                    <a href="rss-list.php?toggle=1&id=<?= $f['id'] ?>" class="badge <?= $f['auto_update'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer">
                        <?= $f['auto_update'] ? __('status_active') : __('status_inactive') ?>
                    </a>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="rss-edit.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="rss-list.php?delete=1&id=<?= $f['id'] ?>" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6"><div class="empty-state"><i class="fas fa-rss"></i><h3><?= __('no_rss') ?></h3><p><a href="rss-add.php" class="btn btn-primary btn-sm"><?= __('btn_add_feed') ?></a></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyCron() {
    const el = document.getElementById('cronUrl');
    el.select();
    navigator.clipboard.writeText(el.value);
    el.nextElementSibling.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => { el.nextElementSibling.innerHTML = '<i class="fas fa-copy"></i>'; }, 1500);
}
</script>

<?php include '../includes/footer.php'; ?>
