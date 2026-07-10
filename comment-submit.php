<?php
// comment-submit.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/lang-strings.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_home());
    exit;
}

$slug    = trim($_POST['slug'] ?? '');
$news_id = (int)($_POST['news_id'] ?? 0);
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$comment = trim($_POST['comment'] ?? '');

$redirect = $slug ? url_news($slug) . '#comments' : url_home();
$redirectBase = $slug ? url_news($slug) : url_home();

// Honeypot — silently drop bot submissions without revealing the check.
if (isSpamBot('website')) {
    header('Location: ' . $redirect);
    exit;
}

if ($name === '' || $comment === '' || $news_id <= 0) {
    feFlashSet('error', t('comment_error'));
    header('Location: ' . $redirect);
    exit;
}

// Make sure the news_id actually corresponds to a published article.
$valid = db()->fetchOne("SELECT id FROM tbl_news WHERE id = ? AND status = 'published'", 'i', $news_id);
if (!$valid) {
    header('Location: ' . url_home());
    exit;
}

db()->insert(
    "INSERT INTO tbl_comments (news_id, name, email, comment, status) VALUES (?,?,?,?, 'pending')",
    'isss', $news_id, $name, $email, $comment
);

header('Location: ' . addQueryParam($redirectBase, 'commented', 1) . '#comments');
exit;
