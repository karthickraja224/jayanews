<?php
// slider/slider-list.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('slider_title');
$breadcrumbs = [__('nav_slider') => null, __('nav_all_slides') => null];

if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['id'];
    db()->query("UPDATE tbl_slider SET status=IF(status=1,0,1) WHERE id=?", 'i', $tid);
    flashSuccess(__('msg_status_changed'));
    header('Location: slider-list.php'); exit();
}
if (isset($_GET['delete'])) {
    $did    = (int)$_GET['id'];
    $slider = db()->fetchOne("SELECT image FROM tbl_slider WHERE id=?", 'i', $did);
    if ($slider && $slider['image']) deleteFile($slider['image']);
    db()->query("DELETE FROM tbl_slider WHERE id=?", 'i', $did);
    logActivity('delete', 'slider', __('msg_slide_deleted'));
    flashSuccess(__('msg_slide_deleted'));
    header('Location: slider-list.php'); exit();
}

$sliders = db()->fetchAll("SELECT * FROM tbl_slider ORDER BY sort_order, id DESC");
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-images"></i> <?= __('slider_title') ?></h1>
        <p><?= __('slider_count') ?> <?= count($sliders) ?></p>
    </div>
    <div class="page-header-right">
        <a href="slider-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('add_slider_title') ?></a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th><?= __('label_image_required') ?></th>
                    <th><?= __('label_title') ?></th>
                    <th><?= __('label_link_url') ?></th>
                    <th><?= __('col_order') ?></th>
                    <th><?= __('col_status') ?></th>
                    <th><?= __('col_actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($sliders): foreach ($sliders as $i => $s): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
                    <?php if ($s['image']): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($s['image']) ?>" style="width:80px;height:50px;object-fit:cover;border-radius:5px;border:1px solid var(--border)">
                    <?php else: ?>
                    <div style="width:80px;height:50px;background:var(--bg-hover);border-radius:5px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border)"><i class="fas fa-image" style="color:var(--text-muted)"></i></div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($s['title'] ?? '—') ?></strong>
                    <?php if ($s['subtitle']): ?><div class="text-muted" style="font-size:12px"><?= htmlspecialchars($s['subtitle']) ?></div><?php endif; ?>
                </td>
                <td>
                    <?php if ($s['link_url']): ?>
                    <a href="<?= htmlspecialchars($s['link_url']) ?>" target="_blank" class="text-muted" style="font-size:12px"><i class="fas fa-external-link-alt"></i> <?= __('label_link_url') ?></a>
                    <?php else: ?>
                    <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td><?= (int)$s['sort_order'] ?></td>
                <td>
                    <a href="slider-list.php?toggle=1&id=<?= $s['id'] ?>" class="badge <?= $s['status'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer">
                        <?= $s['status'] ? __('status_active') : __('status_inactive') ?>
                    </a>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="slider-edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="slider-list.php?delete=1&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger btn-icon" title="<?= __('btn_delete') ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7"><div class="empty-state"><i class="fas fa-images"></i><h3><?= __('no_slides') ?></h3><p><a href="slider-add.php" class="btn btn-primary btn-sm"><?= __('add_slider_title') ?></a></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>