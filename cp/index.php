<?php
// index.php
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/auth.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'dashboard/dashboard.php');
} else {
    header('Location: ' . BASE_URL . 'login.php');
}
exit();