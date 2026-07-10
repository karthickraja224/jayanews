<?php
// includes/functions.php

function flash($key, $value = null) {
    if ($value !== null) {
        $_SESSION["flash_$key"] = $value;
    } else {
        $msg = $_SESSION["flash_$key"] ?? null;
        unset($_SESSION["flash_$key"]);
        return $msg;
    }
}

function flashSuccess($msg) { flash('success', $msg); }
function flashError($msg)   { flash('error', $msg); }
function flashWarning($msg) { flash('warning', $msg); }

function showFlash() {
    $types = [
        'success' => ['bg' => '#1a3a2a', 'border' => '#2d6a4f', 'icon' => 'check-circle', 'color' => '#52b788'],
        'error'   => ['bg' => '#3a1a1a', 'border' => '#e63946', 'icon' => 'exclamation-circle', 'color' => '#e63946'],
        'warning' => ['bg' => '#3a2a1a', 'border' => '#f4a261', 'icon' => 'exclamation-triangle', 'color' => '#f4a261'],
    ];
    foreach ($types as $key => $style) {
        $msg = flash($key);
        if ($msg) {
            echo "<div class='alert-toast' style='background:{$style['bg']};border-left:4px solid {$style['border']};color:{$style['color']};padding:12px 18px;border-radius:6px;margin-bottom:16px;display:flex;align-items:center;gap:10px;'>
                <i class='fas fa-{$style['icon']}'></i> <span>{$msg}</span>
                <button onclick=\"this.parentElement.remove()\" style='margin-left:auto;background:none;border:none;color:{$style['color']};cursor:pointer;font-size:16px;'>×</button>
            </div>";
        }
    }
}

function slugify($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/[^\p{L}\p{M}\p{N}\-]/u', '', $text);
    $text = trim($text, '-');
    return $text ?: 'news-' . time();
}

function uniqueSlug($table, $slug, $exclude_id = 0) {
    $original = $slug;
    $i = 1;
    while (true) {
        $row = db()->fetchOne("SELECT id FROM $table WHERE slug = ?", 's', $slug);
        if (!$row || ($exclude_id && $row['id'] == $exclude_id)) break;
        $slug = $original . '-' . $i++;
    }
    return $slug;
}

function uploadImage($file, $folder = 'news') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_FILE_SIZE) return ['error' => 'File too large (max 5MB)'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) return ['error' => 'Invalid image type'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXTS)) return ['error' => 'Invalid extension'];
    $dir  = UPLOAD_PATH . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = uniqid('jp_', true) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return 'uploads/' . $folder . '/' . $name;
    }
    return ['error' => 'Upload failed'];
}

function deleteFile($path) {
    $full = BASE_PATH . ltrim($path, '/');
    if (file_exists($full)) unlink($full);
}

function timeAgo($datetime) {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    if ($diff->y > 0) return sprintf(__('time_years_ago'), $diff->y);
    if ($diff->m > 0) return sprintf(__('time_months_ago'), $diff->m);
    if ($diff->d > 0) return sprintf(__('time_days_ago'), $diff->d);
    if ($diff->h > 0) return sprintf(__('time_hours_ago'), $diff->h);
    if ($diff->i > 0) return sprintf(__('time_minutes_ago'), $diff->i);
    return __('time_just_now');
}

function formatDate($date, $format = 'd M Y, h:i A') {
    if (!$date) return '—';
    return date($format, strtotime($date));
}

function getSetting($key, $default = '') {
    static $cache = [];
    if (!isset($cache[$key])) {
        $row = db()->fetchOne("SELECT setting_value FROM tbl_settings WHERE setting_key = ?", 's', $key);
        $cache[$key] = $row['setting_value'] ?? $default;
    }
    return $cache[$key];
}

function saveSetting($key, $value) {
    db()->query("INSERT INTO tbl_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = ?", 'sss', $key, $value, $value);
}

function statusBadge($status) {
    $map = [
        'published' => ['label' => 'Published', 'class' => 'badge-success'],
        'draft'     => ['label' => 'Draft',     'class' => 'badge-warning'],
        'trash'     => ['label' => 'Trash',     'class' => 'badge-danger'],
        '1'         => ['label' => 'Active',    'class' => 'badge-success'],
        '0'         => ['label' => 'Inactive',  'class' => 'badge-danger'],
        'approved'  => ['label' => 'Approved',  'class' => 'badge-success'],
        'pending'   => ['label' => 'Pending',   'class' => 'badge-warning'],
        'spam'      => ['label' => 'Spam',      'class' => 'badge-danger'],
    ];
    $s = (string)$status;
    $b = $map[$s] ?? ['label' => ucfirst($s), 'class' => 'badge-secondary'];
    return "<span class='badge {$b['class']}'>{$b['label']}</span>";
}

function paginate($total, $per_page, $current_page, $url) {
    $pages = ceil($total / $per_page);
    if ($pages <= 1) return '';
    $html = "<div class='pagination-wrap'><ul class='pagination'>";
    $prev = max(1, $current_page - 1);
    $next = min($pages, $current_page + 1);
    if ($current_page > 1)
        $html .= "<li><a href='{$url}&page={$prev}'><i class='fas fa-chevron-left'></i></a></li>";
    for ($i = max(1, $current_page - 2); $i <= min($pages, $current_page + 2); $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= "<li class='$active'><a href='{$url}&page={$i}'>$i</a></li>";
    }
    if ($current_page < $pages)
        $html .= "<li><a href='{$url}&page={$next}'><i class='fas fa-chevron-right'></i></a></li>";
    $html .= "</ul></div>";
    return $html;
}

function getCategories() {
    return db()->fetchAll("SELECT * FROM tbl_categories WHERE status = 1 ORDER BY sort_order, name_english");
}

function getDashboardStats() {
    return [
        'total_news'       => db()->fetchOne("SELECT COUNT(*) c FROM tbl_news WHERE status != 'trash'")['c'],
        'published'        => db()->fetchOne("SELECT COUNT(*) c FROM tbl_news WHERE status = 'published'")['c'],
        'drafts'           => db()->fetchOne("SELECT COUNT(*) c FROM tbl_news WHERE status = 'draft'")['c'],
        'total_views'      => db()->fetchOne("SELECT COALESCE(SUM(views),0) c FROM tbl_news")['c'],
        'total_comments'   => db()->fetchOne("SELECT COUNT(*) c FROM tbl_comments")['c'],
        'pending_comments' => db()->fetchOne("SELECT COUNT(*) c FROM tbl_comments WHERE status = 'pending'")['c'],
        'total_users'      => db()->fetchOne("SELECT COUNT(*) c FROM tbl_users WHERE status = 1")['c'],
        'categories'       => db()->fetchOne("SELECT COUNT(*) c FROM tbl_categories WHERE status = 1")['c'],
    ];
}