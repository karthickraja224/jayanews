<?php
// link/link-add.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('add_link_title');
$breadcrumbs = [__('nav_links') => BASE_URL . 'link/link-list.php', __('add_link_title') => null];

// Generic document uploader (PDF/DOC/XLS etc.) -- separate from uploadImage() which is images-only.
function uploadDocument($file, $folder = 'links') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload error.'];
    if ($file['size'] > MAX_FILE_SIZE) return ['error' => 'File too large.'];

    $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) return ['error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed_exts)];

    $dir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $safe_title = preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
    $name = time() . ($safe_title ? '_' . $safe_title : '') . '.' . $ext;

    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return 'uploads/' . $folder . '/' . $name;
    }
    return ['error' => 'Upload failed.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $sort  = (int)($_POST['sort_order'] ?? 0);
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($title)) {
        flashError(__('err_title_required'));
    } elseif (empty($_FILES['file']['name'])) {
        flashError(__('err_file_required'));
    } else {
        $up = uploadDocument($_FILES['file']);
        if (isset($up['error'])) {
            flashError($up['error']);
        } else {
            db()->insert(
                "INSERT INTO tbl_links (title, file, sort_order, status) VALUES (?,?,?,?)",
                'ssii', $title, $up, $sort, $status
            );
            logActivity('create', 'links', __('msg_link_added') . ": $title");
            flashSuccess(__('msg_link_added'));
            header('Location: link-list.php'); exit();
        }
    }
}
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left"><h1><i class="fas fa-plus-circle"></i> <?= __('add_link_title') ?></h1></div>
    <div class="page-header-right"><a href="link-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-link"></i> <?= __('card_link_details') ?></span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label"><?= __('label_title') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required placeholder="e.g. IPTV RIO WEF MAR 2025">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('label_upload_file') ?> <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                        <p style="color:var(--text-muted);font-size:12px;margin-top:5px"><?= __('hint_link_file') ?></p>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                        <div class="form-group">
                            <label class="form-label"><?= __('col_order') ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)($_POST['sort_order'] ?? 0) ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('col_status') ?></label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                                <input type="checkbox" name="status" <?= !isset($_POST['title']) || isset($_POST['status']) ? 'checked' : '' ?>> <?= __('status_active') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?></button>
                <a href="link-list.php" class="btn btn-secondary"><?= __('btn_cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
