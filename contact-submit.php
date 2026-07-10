<?php
// contact-submit.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/lang-strings.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_page('contact'));
    exit;
}

$redirectBase = url_page('contact');

if (isSpamBot('website')) {
    header('Location: ' . $redirectBase);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    feFlashSet('error', t('message_error'));
    header('Location: ' . $redirectBase);
    exit;
}

// tbl_contact_messages is created by extra_tables.sql if it doesn't already exist.
db()->query(
    "INSERT INTO tbl_contact_messages (name, email, subject, message, created_at) VALUES (?,?,?,?, NOW())",
    'ssss', $name, $email, $subject, $message
);

header('Location: ' . addQueryParam($redirectBase, 'sent', 1));
exit;
