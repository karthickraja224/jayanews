<?php
// lang-switch.php
// Toggles the admin UI language between 'en' and 'ta'
// Called via GET from the navbar toggle. Redirects back to referrer.

require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/auth.php';
requireLogin();

// Ensure a default before toggling — missing session key means English
$current = $_SESSION['lang'] ?? 'en';
$_SESSION['lang'] = ($current === 'en') ? 'ta' : 'en';

$back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard/dashboard.php';
header('Location: ' . $back);
exit;
