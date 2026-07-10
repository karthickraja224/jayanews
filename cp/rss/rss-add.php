<?php
// rss/rss-add.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('add_rss_title');
$breadcrumbs = [__('nav_rss') => BASE_URL . 'rss/rss-list.php', __('add_rss_title') => null];

$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language    = trim($_POST['language'] ?? 'Tamil');
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
    $feed_name   = trim($_POST['feed_name'] ?? '');
    $feed_url    = trim($_POST['feed_url'] ?? '');
    $auto_update = isset($_POST['auto_update']) ? 1 : 0;

    if (empty($feed_name) || empty($feed_url)) {
        flashError(__('err_feed_req'));
    } elseif (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
        flashError(__('err_feed_url_invalid'));
    } else {
        db()->insert(
            "INSERT INTO tbl_rss_feeds (language, category_id, feed_name, feed_url, auto_update) VALUES (?,?,?,?,?)",
            'sissi', $language, $category_id, $feed_name, $feed_url, $auto_update
        );
        logActivity('create', 'rss', __('msg_rss_added') . ": $feed_name");
        flashSuccess(__('msg_rss_added'));
        header('Location: rss-list.php'); exit();
    }
}
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left"><h1><i class="fas fa-plus-circle"></i> <?= __('add_rss_title') ?></h1></div>
    <div class="page-header-right"><a href="rss-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <form method="POST">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-rss"></i> <?= __('card_rss_details') ?></span></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('col_language') ?></label>
                            <select name="language" class="form-control">
                                <option value="Tamil" <?= ($_POST['language'] ?? 'Tamil') === 'Tamil' ? 'selected' : '' ?>><?= __('opt_tamil') ?></option>
                                <option value="English" <?= ($_POST['language'] ?? '') === 'English' ? 'selected' : '' ?>><?= __('opt_english') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('col_category') ?></label>
                            <select name="category_id" class="form-control">
                                <option value=""><?= __('opt_select_category') ?></option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (int)($_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name_english']) ?><?= $cat['name_tamil'] ? ' / ' . htmlspecialchars($cat['name_tamil']) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_feed_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="feed_name" class="form-control" value="<?= htmlspecialchars($_POST['feed_name'] ?? '') ?>" placeholder="e.g. oneindia" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_feed_url') ?> <span class="text-danger">*</span></label>
                        <input type="url" name="feed_url" class="form-control" value="<?= htmlspecialchars($_POST['feed_url'] ?? '') ?>" placeholder="https://tamil.oneindia.com/rss/feeds/oneindia-tamil-fb.xml" required>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" name="auto_update" <?= !isset($_POST['feed_name']) || isset($_POST['auto_update']) ? 'checked' : '' ?>>
                            <?= __('label_auto_update') ?>
                        </label>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
                <a href="rss-list.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
