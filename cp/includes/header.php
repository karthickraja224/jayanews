<?php
// includes/header.php
require_once BASE_PATH . 'includes/lang.php';
$current_user = currentUser();
$site_name = getSetting('site_name', 'Jaya Plus');
$html_lang = ($_SESSION['lang'] ?? 'en') === 'ta' ? 'ta' : 'en';
?>
<!DOCTYPE html>
<html lang="<?= $html_lang ?>" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title ?? 'Dashboard') ?> — <?= $site_name ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Tamil:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/admin.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/lang-switcher.css">
</head>
<body>
<div class="admin-wrapper">
<?php include BASE_PATH . 'includes/sidebar.php'; ?>
<div class="main-content">
<?php include BASE_PATH . 'includes/navbar.php'; ?>
<div class="page-content">
<?php showFlash(); ?>
