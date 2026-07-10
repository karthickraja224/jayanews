<?php
// news/news-delete.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$id     = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$news   = db()->fetchOne("SELECT * FROM tbl_news WHERE id = ?", 'i', $id);

if (!$news) {
    flashError(__('msg_news_not_found'));
    header('Location: ' . BASE_URL . 'news/news-list.php'); exit();
}

switch ($action) {
    case 'trash':
        db()->query("UPDATE tbl_news SET status='trash' WHERE id=?", 'i', $id);
        logActivity('trash', 'news', __('trash_title') . ": {$news['title']}");
        flashSuccess(__('trash_title') . ' ' . __('btn_delete'));
        header('Location: ' . BASE_URL . 'news/news-list.php'); break;

    case 'restore':
        db()->query("UPDATE tbl_news SET status='draft' WHERE id=?", 'i', $id);
        logActivity('restore', 'news', __('btn_reset') . ": {$news['title']}");
        flashSuccess(__('btn_reset') . ' ' . __('btn_save_draft'));
        header('Location: ' . BASE_URL . 'news/news-trash.php'); break;

    case 'delete':
        requireRole(['superadmin', 'admin']);
        if ($news['featured_image']) deleteFile($news['featured_image']);
        db()->query("DELETE FROM tbl_news WHERE id=?", 'i', $id);
        logActivity('delete', 'news', __('btn_delete') . ": {$news['title']}");
        flashSuccess(__('btn_delete') . ' ' . __('trash_empty'));
        header('Location: ' . BASE_URL . 'news/news-trash.php'); break;

    default:
        header('Location: ' . BASE_URL . 'news/news-list.php'); break;
}
exit();
?>