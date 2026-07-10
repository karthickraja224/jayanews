<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

function currentUser() {
    if (!isLoggedIn()) return null;
    if (isset($_SESSION['admin_data'])) return $_SESSION['admin_data'];
    $user = db()->fetchOne("SELECT * FROM tbl_users WHERE id = ? AND status = 1", 'i', $_SESSION['admin_id']);
    if ($user) {
        unset($user['password']);
        $_SESSION['admin_data'] = $user;
    }
    return $user;
}

function hasRole($roles) {
    $user = currentUser();
    if (!$user) return false;
    if (is_string($roles)) $roles = [$roles];
    return in_array($user['role'], $roles);
}

function requireRole($roles) {
    if (!hasRole($roles)) {
        $_SESSION['flash_error'] = 'இந்த செயலுக்கு அனுமதி இல்லை.';
        header('Location: ' . BASE_URL . 'dashboard/dashboard.php');
        exit();
    }
}

function logActivity($action, $module, $description = '') {
    if (!isLoggedIn()) return;
    $uid = $_SESSION['admin_id'];
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    db()->insert(
        "INSERT INTO tbl_activity_log (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)",
        'issss', $uid, $action, $module, $description, $ip
    );
}