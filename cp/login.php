<?php
// login.php
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/lang.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Language switching via logo click
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'] === 'ta' ? 'ta' : 'en';
    $_SESSION['lang'] = $lang;
    // Redirect to remove query param
    header('Location: login.php');
    exit();
}

// Theme switching
if (isset($_GET['theme'])) {
    $allowed_themes = ['light', 'dark', 'blue', 'green', 'purple'];
    $theme = in_array($_GET['theme'], $allowed_themes) ? $_GET['theme'] : 'light';
    $_SESSION['theme'] = $theme;
    // Redirect to remove query param
    header('Location: login.php');
    exit();
}

// Default language if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Default theme if not set
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light'; // Default to white/light theme
}

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'dashboard/dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = __('err_login_required');
    } else {
        $user = db()->fetchOne("SELECT * FROM tbl_users WHERE email = ? AND status = 1", 's', $email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_data'] = array_diff_key($user, ['password' => '']);
            $_SESSION['last_activity'] = time();
            if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';
            // Update last login
            db()->query("UPDATE tbl_users SET last_login = NOW() WHERE id = ?", 'i', $user['id']);
            header('Location: ' . BASE_URL . 'dashboard/dashboard.php');
            exit();
        } else {
            $error = __('err_login_invalid');
        }
    }
}
$timeout = isset($_GET['timeout']);

// Get current language for toggle
$current_lang = $_SESSION['lang'] ?? 'en';
$toggle_lang = $current_lang === 'en' ? 'ta' : 'en';
$toggle_label = $current_lang === 'en' ? 'தமிழ்' : 'English';
$toggle_title = $current_lang === 'en' ? __('lang_switch_title') : __('lang_switch_title');

// Get current theme
$current_theme = $_SESSION['theme'] ?? 'light';
$theme_icon = [
    'light' => 'fa-sun',
    'dark' => 'fa-moon',
    'blue' => 'fa-water',
    'green' => 'fa-leaf',
    'purple' => 'fa-gem'
];
?>
<!DOCTYPE html>
<html lang="<?= $current_lang === 'ta' ? 'ta' : 'en' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('login_title') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/admin.css">

<!-- Theme Variables -->
<style>
:root {
    --bg-primary: #f5f7fa;
    --bg-card: #ffffff;
    --bg-hover: #f0f2f5;
    --bg-input: #f8f9fa;
    --text-primary: #1a1a2e;
    --text-secondary: #4a4a6a;
    --text-muted: #8b91b0;
    --border: #e2e8f0;
    --primary-red: #e63946;
    --primary-red-hover: #c62828;
    --success: #2ecc71;
    --warning: #f4a261;
    --danger: #e63946;
    --gold: #f4c542;
    --teal: #2ec4b6;
    --shadow: 0 20px 60px rgba(0,0,0,0.08);
    --radius: 16px;
    --transition: 0.3s ease;
}

/* Dark Theme */
[data-theme="dark"] {
    --bg-primary: #0d0f1a;
    --bg-card: #1a1d2e;
    --bg-hover: #2a2d3e;
    --bg-input: #1a1d2e;
    --text-primary: #eaedf5;
    --text-secondary: #b0b4cc;
    --text-muted: #8b91b0;
    --border: #2a2d3e;
    --shadow: 0 20px 60px rgba(0,0,0,0.4);
}

/* Blue Theme */
[data-theme="blue"] {
    --bg-primary: #e8f0fe;
    --bg-card: #ffffff;
    --bg-hover: #dce6f5;
    --bg-input: #f0f5ff;
    --text-primary: #0a1628;
    --text-secondary: #1a3a6a;
    --text-muted: #5a7a9a;
    --border: #b8cfe0;
    --primary-red: #1a73e8;
    --primary-red-hover: #1557b0;
    --shadow: 0 20px 60px rgba(26,115,232,0.1);
}

/* Green Theme */
[data-theme="green"] {
    --bg-primary: #e8f5e9;
    --bg-card: #ffffff;
    --bg-hover: #d4edda;
    --bg-input: #f0faf0;
    --text-primary: #0a1a0a;
    --text-secondary: #1a4a2a;
    --text-muted: #5a7a6a;
    --border: #b8d8c0;
    --primary-red: #2e7d32;
    --primary-red-hover: #1b5e20;
    --shadow: 0 20px 60px rgba(46,125,50,0.1);
}

/* Purple Theme */
[data-theme="purple"] {
    --bg-primary: #f3e8ff;
    --bg-card: #ffffff;
    --bg-hover: #e8d5f5;
    --bg-input: #f5f0ff;
    --text-primary: #1a0a2a;
    --text-secondary: #4a1a6a;
    --text-muted: #7a5a9a;
    --border: #d0b8e0;
    --primary-red: #7b1fa2;
    --primary-red-hover: #5c0e7a;
    --shadow: 0 20px 60px rgba(123,31,162,0.1);
}

/* Update card background in login */
.login-card {
    background: var(--bg-card);
    padding: 40px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.login-page {
    background: var(--bg-primary);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

/* Form elements inherit theme */
.form-control {
    background: var(--bg-input);
    color: var(--text-primary);
    border: 1px solid var(--border);
}
.form-control:focus {
    border-color: var(--primary-red);
}
.form-label {
    color: var(--text-secondary);
}
.text-muted {
    color: var(--text-muted);
}
.login-title {
    color: var(--text-primary);
}
.login-sub {
    color: var(--text-muted);
}

/* Animation */
@keyframes fadeUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.login-card {
    animation: fadeUp .4s ease;
    max-width: 420px;
    width: 100%;
}

/* Logo styling */
.login-logo {
    text-align: center;
    margin-bottom: 30px;
    cursor: pointer;
    transition: opacity 0.2s;
}
.login-logo:hover {
    opacity: 0.8;
}
.login-logo-mark {
    font-size: 48px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-red);
    width: 70px;
    height: 70px;
    border-radius: 16px;
    margin-bottom: 12px;
}

/* Top controls */
.top-controls {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 100;
    display: flex;
    gap: 10px;
}
.top-controls button,
.top-controls a button {
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-primary);
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.top-controls button:hover,
.top-controls a button:hover {
    background: var(--bg-hover);
    border-color: var(--primary-red);
}
.top-controls button i {
    font-size: 14px;
}

/* Theme dropdown */
.theme-dropdown {
    position: relative;
    display: inline-block;
}
.theme-dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 8px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    min-width: 160px;
    box-shadow: var(--shadow);
    padding: 6px;
    z-index: 101;
}
.theme-dropdown-content.show {
    display: block;
}
.theme-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    color: var(--text-primary);
    transition: background 0.2s;
    border: none;
    background: transparent;
    width: 100%;
    font-size: 13px;
}
.theme-option:hover {
    background: var(--bg-hover);
}
.theme-option.active {
    background: var(--bg-hover);
    border-left: 3px solid var(--primary-red);
}
.theme-color-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--border);
    flex-shrink: 0;
}
.theme-color-dot.light { background: #f5f7fa; border-color: #d0d0d0; }
.theme-color-dot.dark { background: #0d0f1a; border-color: #2a2d3e; }
.theme-color-dot.blue { background: #e8f0fe; border-color: #b8cfe0; }
.theme-color-dot.green { background: #e8f5e9; border-color: #b8d8c0; }
.theme-color-dot.purple { background: #f3e8ff; border-color: #d0b8e0; }

/* Error/Success messages */
.alert {
    padding: 10px 14px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 13px;
    border-left: 4px solid;
}
.alert-danger {
    background: rgba(230, 57, 70, 0.08);
    border-color: #e63946;
    color: #e63946;
}
.alert-warning {
    background: rgba(244, 162, 97, 0.08);
    border-color: #f4a261;
    color: #f4a261;
}
[data-theme="dark"] .alert-danger {
    background: rgba(230, 57, 70, 0.15);
}
[data-theme="dark"] .alert-warning {
    background: rgba(244, 162, 97, 0.15);
}

.logo-box {
    width: 100%;
    height: 120px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: transparent;
}

.logo {
    width: 100px;
    height: auto;
    animation: logoAnimation 2.5s ease-in-out infinite;
    transition: 0.4s;
}

/* Hover effect */
.logo:hover {
    transform: scale(1.15) rotate(5deg);
}


/* Floating + Glow Animation */
@keyframes logoAnimation {

    0% {
        transform: translateY(0px) scale(1);
        filter: drop-shadow(0 0 0px #fff);
    }

    50% {
        transform: translateY(-12px) scale(1.05);
        filter: drop-shadow(0 0 15px #fff);
    }

    100% {
        transform: translateY(0px) scale(1);
        filter: drop-shadow(0 0 0px #fff);
    }
}
</style>
</head>
<body data-theme="<?= $current_theme ?>">

<!-- Top Controls -->
<div class="top-controls">
    <!-- Language Toggle -->
    <a href="login.php?lang=<?= $toggle_lang ?>" title="<?= $toggle_title ?>">
        <button type="button">
            <i class="fas fa-language"></i> <?= $toggle_label ?>
        </button>
    </a>

    <!-- Theme Dropdown -->
    <div class="theme-dropdown">
        <button type="button" onclick="toggleThemeDropdown()">
            <i class="fas <?= $theme_icon[$current_theme] ?? 'fa-palette' ?>"></i>
            <span>Theme</span>
            <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i>
        </button>
        <div class="theme-dropdown-content" id="themeDropdown">
            <?php
            $themes = [
                'light' => ['label' => 'Light', 'icon' => 'fa-sun'],
                'dark' => ['label' => 'Dark', 'icon' => 'fa-moon'],
                'blue' => ['label' => 'Blue', 'icon' => 'fa-water'],
                'green' => ['label' => 'Green', 'icon' => 'fa-leaf'],
                'purple' => ['label' => 'Purple', 'icon' => 'fa-gem']
            ];
            foreach ($themes as $key => $theme):
            ?>
            <a href="login.php?theme=<?= $key ?>" style="text-decoration:none;display:block;">
                <button type="button" class="theme-option <?= $current_theme === $key ? 'active' : '' ?>">
                    <span class="theme-color-dot <?= $key ?>"></span>
                    <i class="fas <?= $theme['icon'] ?>" style="width:18px;"></i>
                    <?= $theme['label'] ?>
                    <?php if ($current_theme === $key): ?>
                    <i class="fas fa-check" style="margin-left:auto;color:var(--primary-red);font-size:12px;"></i>
                    <?php endif; ?>
                </button>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="login-page">
    <div class="login-card">
        <!-- Logo click toggles language -->
        <a href="login.php?lang=<?= $toggle_lang ?>" style="text-decoration:none;display:block;" title="<?= $toggle_title ?>">
            <div class="login-logo">
                <!-- <div class="login-logo-mark" style="display:none;"> -->
                  <div class="logo-box">
    <img src="./images/logo.png" class="logo" alt="Logo">
</div>

                <div class="login-title">Jaya Plus Admin</div>
                <div class="login-sub"><?= __('Admin Logn') ?></div>
            </div>
        </a>

        <?php if ($timeout): ?>
        <div class="alert alert-warning">
            <i class="fas fa-clock"></i> <?= __('login_timeout') ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label"><?= __('label_email') ?></label>
                <div style="position:relative">
                    <i class="fas fa-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;"></i>
                    <input type="email" name="email" class="form-control" style="padding-left:36px" placeholder="admin@jayanews.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('label_password') ?></label>
                <div style="position:relative">
                    <i class="fas fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;"></i>
                    <input type="password" name="password" id="passwordInput" class="form-control" style="padding-left:36px;padding-right:40px" placeholder="••••••••" required>
                    <button type="button" id="togglePwd" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px;font-size:15px;margin-top:8px;background:var(--primary-red);border-color:var(--primary-red);">
                <i class="fas fa-sign-in-alt"></i> <?= __('Login') ?>
            </button>
        </form>
        <div style="margin-top:20px;text-align:center;font-size:12px;color:var(--text-muted);">
            Default: admin@jayanews.com / password
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePwd')?.addEventListener('click', () => {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') { 
        input.type = 'text'; 
        icon.className = 'fas fa-eye-slash'; 
    } else { 
        input.type = 'password'; 
        icon.className = 'fas fa-eye'; 
    }
});

// Toggle theme dropdown
function toggleThemeDropdown() {
    const dropdown = document.getElementById('themeDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('themeDropdown');
    const themeBtn = document.querySelector('.theme-dropdown button');
    if (dropdown && !dropdown.contains(event.target) && event.target !== themeBtn) {
        dropdown.classList.remove('show');
    }
});

// Close dropdown on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const dropdown = document.getElementById('themeDropdown');
        if (dropdown) dropdown.classList.remove('show');
    }
});
</script>
</body>
</html>