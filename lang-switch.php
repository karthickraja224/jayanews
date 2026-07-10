<?php
// lang-switch.php
require_once __DIR__ . '/config/constants.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lang = $_GET['lang'] ?? 'ta';
$_SESSION['lang'] = in_array($lang, ['en', 'ta']) ? $lang : 'ta';

$redirect = $_GET['redirect'] ?? SITE_URL;
// Only allow redirecting back within this site.
if (!is_string($redirect) || $redirect === '' || str_starts_with($redirect, '//') || str_contains($redirect, '://')) {
    $redirect = SITE_URL;
}

header('Location: ' . $redirect);
exit;
