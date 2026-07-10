<?php
// logout.php
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
session_unset();
session_destroy();
header('Location: ' . BASE_URL . 'login.php');
exit();