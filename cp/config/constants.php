<?php
// config/constants.php

define('BASE_PATH', dirname(__DIR__) . '/');

define('BASE_URL', 'https://crb.cloud/jayaplus_uis/cp/');

define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

define('ASSETS_URL', BASE_URL . 'assets/');

// Upload limits
define('MAX_FILE_SIZE', 500 * 1024 * 1024); // 500 MB

define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif'
]);

define('ALLOWED_IMAGE_EXTS', [
    'jpg',
    'jpeg',
    'png',
    'webp',
    'gif'
]);

define('ALLOWED_VIDEO_EXTS', [
    'mp4',
    'mov',
    'webm',
    'avi',
    'mkv'
]);

// Pagination
define('NEWS_PER_PAGE', 15);
define('COMMENTS_PER_PAGE', 20);

// Session
define('SESSION_TIMEOUT', 3600); // 1 hour

// App version
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'Jaya Plus Admin');
