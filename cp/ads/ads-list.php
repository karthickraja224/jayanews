<?php
// ads/ads-list.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('ads_title');
$breadcrumbs = [__('nav_ads') => null];

if (isset($_GET['toggle'])) {
    db()->query("UPDATE tbl_ads SET status=IF(status=1,0,1) WHERE id=?", 'i', (int)$_GET['id']);
    flashSuccess(__('msg_status_changed'));
    header('Location: ads-list.php'); exit();
}
if (isset($_GET['delete'])) {
    $ad = db()->fetchOne("SELECT image FROM tbl_ads WHERE id=?", 'i', (int)$_GET['id']);
    if ($ad && $ad['image']) deleteFile($ad['image']);
    db()->query("DELETE FROM tbl_ads WHERE id=?", 'i', (int)$_GET['id']);
    logActivity('delete', 'ads', __('msg_ad_deleted'));
    flashSuccess(__('msg_ad_deleted'));
    header('Location: ads-list.php'); exit();
}

$ads = db()->fetchAll("SELECT * FROM tbl_ads ORDER BY position, sort_order, id DESC");
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-ad"></i> <?= __('ads_title') ?></h1>
        <p><?= __('ads_count') ?> <?= count($ads) ?></p>
    </div>
    <div class="page-header-right">
        <a href="ads-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('btn_add_ad') ?></a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th><?= __('col_ad') ?></th>
                    <th><?= __('col_position') ?></th>
                    <th><?= __('col_type') ?></th>
                    <th><?= __('col_clicks') ?></th>
                    <th><?= __('col_status') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($ads): foreach ($ads as $i => $ad): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <?php if ($ad['ad_type'] === 'image' && $ad['image']): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($ad['image']) ?>" style="width:70px;height:40px;object-fit:cover;border-radius:4px;border:1px solid var(--border)">
                        <?php elseif ($ad['ad_type'] === 'code'): ?>
                        <div style="width:70px;height:40px;background:var(--bg-hover);border-radius:4px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center"><i class="fas fa-code" style="color:var(--text-muted)"></i></div>
                        <?php endif; ?>
                        <strong><?= htmlspecialchars($ad['title']) ?></strong>
                    </div>
                </td>
                <td><span class="badge badge-secondary"><?= ucfirst(str_replace('-', ' ', htmlspecialchars($ad['position'] ?? '—'))) ?></span></td>
                <td><?= $ad['ad_type'] === 'code' ? '<span class="badge badge-warning">Code</span>' : '<span class="badge badge-secondary">' . __('opt_image') . '</span>' ?></td>
                <td><?= number_format($ad['clicks'] ?? 0) ?></td>
                <td>
                    <a href="ads-list.php?toggle=1&id=<?= $ad['id'] ?>" class="badge <?= $ad['status'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer">
                        <?= $ad['status'] ? __('status_active') : __('status_inactive') ?>
                    </a>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="ads-edit.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="ads-list.php?delete=1&id=<?= $ad['id'] ?>" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7"><div class="empty-state"><i class="fas fa-ad"></i><h3><?= __('no_ads') ?></h3><p><a href="ads-add.php" class="btn btn-primary btn-sm"><?= __('btn_add_ad') ?></a></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>