<?php
// subcategory/subcategory-delete.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();
requireRole(['superadmin', 'admin']);

$id  = (int)($_GET['id'] ?? 0);
$sub = db()->fetchOne("SELECT * FROM tbl_subcategories WHERE id=?", 'i', $id);
if (!$sub) { flashError(__('msg_subcat_not_found')); header('Location: subcategory-list.php'); exit(); }

$count = db()->fetchOne("SELECT COUNT(*) c FROM tbl_news WHERE subcategory_id=?", 'i', $id)['c'];
if ($count > 0) {
    flashError(sprintf(__('err_subcat_has_news'), $count));
    header('Location: subcategory-list.php'); exit();
}

db()->query("DELETE FROM tbl_subcategories WHERE id=?", 'i', $id);
logActivity('delete', 'subcategory', "Sub-category deleted: {$sub['name_english']}");
flashSuccess(__('msg_subcat_deleted'));
header('Location: subcategory-list.php'); exit();
