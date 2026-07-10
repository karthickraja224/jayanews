<?php
// ads/ads-add.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('add_ad_title');
$breadcrumbs = [__('nav_ads') => BASE_URL . 'ads/ads-list.php', __('add_ad_title') => null];

$positions = ['header', 'sidebar-top', 'sidebar-bottom', 'content-top', 'content-bottom', 'footer', 'popup'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $type     = in_array($_POST['type'] ?? '', ['image','code']) ? $_POST['type'] : 'image';
    $position = in_array($_POST['position'] ?? '', $positions) ? $_POST['position'] : 'sidebar-top';
    $link     = trim($_POST['link'] ?? '');
    $code     = trim($_POST['ad_code'] ?? '');
    $sort     = (int)($_POST['sort_order'] ?? 0);
    $status   = isset($_POST['status']) ? 1 : 0;
    $image    = null;

    if (empty($title)) { flashError(__('err_title_req_ad')); }
    else {
        if ($type === 'image' && !empty($_FILES['image']['name'])) {
            $up = uploadImage($_FILES['image'], 'ads');
            if (isset($up['error'])) { flashError($up['error']); goto done; }
            $image = $up;
        }
       db()->insert(
    "INSERT INTO tbl_ads 
    (title, ad_type, position, image, link_url, ad_code, sort_order, status) 
    VALUES (?,?,?,?,?,?,?,?)",
    'ssssssii',
    $title,
    $type,
    $position,
    $image,
    $link,
    $code,
    $sort,
    $status
);
        logActivity('create', 'ads', __('msg_ad_added') . ": $title");
        flashSuccess(__('msg_ad_added'));
        header('Location: ads-list.php'); exit();
    }
    done:
}
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left"><h1><i class="fas fa-plus-circle"></i> <?= __('add_ad_title') ?></h1></div>
    <div class="page-header-right"><a href="ads-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-ad"></i> <?= __('card_ad_details') ?></span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_title') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required placeholder="<?= __('label_title') ?>">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('label_ad_type') ?></label>
                            <select name="type" class="form-control" id="adType" onchange="toggleAdType(this.value)">
                                <option value="image" <?= ($_POST['type']??'image')==='image'?'selected':'' ?>><?= __('opt_image') ?></option>
                                <option value="code" <?= ($_POST['type']??'')==='code'?'selected':'' ?>>HTML/JS Code</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('label_position') ?></label>
                            <select name="position" class="form-control">
                                <?php foreach ($positions as $p): ?>
                                <option value="<?= $p ?>" <?= ($_POST['position']??'sidebar-top')===$p?'selected':'' ?>><?= ucfirst(str_replace('-', ' ', $p)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="imageSection" class="form-group">
                        <label class="form-label"><?= __('label_ad_image') ?></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div id="codeSection" class="form-group" style="display:none">
                        <label class="form-label">HTML/Script Code</label>
                        <textarea name="ad_code" class="form-control" rows="6" placeholder="<!-- Google AdSense or custom ad code -->"><?= htmlspecialchars($_POST['ad_code'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_link_on_click') ?></label>
                        <input type="url" name="link" class="form-control" value="<?= htmlspecialchars($_POST['link'] ?? '') ?>" placeholder="https://...">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('col_order') ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)($_POST['sort_order']??0) ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('col_status') ?></label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                                <input type="checkbox" name="status" <?= !isset($_POST['title'])?'checked':(isset($_POST['status'])?'checked':'') ?>> <?= __('status_active') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
                <a href="ads-list.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a>
            </div>
        </form>
    </div>
</div>
<script>
function toggleAdType(v) {
    document.getElementById('imageSection').style.display = v === 'image' ? 'block' : 'none';
    document.getElementById('codeSection').style.display  = v === 'code'  ? 'block' : 'none';
}
toggleAdType('<?= htmlspecialchars($_POST['type'] ?? 'image') ?>');
</script>
<?php include '../includes/footer.php'; ?>