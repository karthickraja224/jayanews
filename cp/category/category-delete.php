<?php
// category/category-delete.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();
requireRole(['superadmin', 'admin']);

$id  = (int)($_GET['id'] ?? 0);
$cat = db()->fetchOne("SELECT * FROM tbl_categories WHERE id=?", 'i', $id);
if (!$cat) { flashError(__('msg_cat_not_found')); header('Location: category-list.php'); exit(); }

$news_count = db()->fetchOne("SELECT COUNT(*) c FROM tbl_news WHERE category_id=?", 'i', $id)['c'];
if ($news_count > 0) {
    flashError(sprintf(__('err_cat_has_news'), $news_count));
    header('Location: category-list.php'); exit();
}

db()->query("DELETE FROM tbl_subcategories WHERE category_id=?", 'i', $id);
db()->query("DELETE FROM tbl_categories WHERE id=?", 'i', $id);
logActivity('delete', 'category', "Category deleted: {$cat['name_english']}");
flashSuccess(__('msg_cat_deleted'));
header('Location: category-list.php'); exit();
