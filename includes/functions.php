<?php
// includes/functions.php

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

/* ---------------- Settings ---------------- */

function getSetting($key, $default = '') {
    static $cache = [];
    if (!isset($cache[$key])) {
        $row = db()->fetchOne("SELECT setting_value FROM tbl_settings WHERE setting_key = ?", 's', $key);
        $cache[$key] = ($row && $row['setting_value'] !== null && $row['setting_value'] !== '') ? $row['setting_value'] : $default;
    }
    return $cache[$key];
}

/* ---------------- URL builders ---------------- */

function url_home() {
    return PRETTY_URLS ? SITE_URL : SITE_URL . 'index.php';
}
function url_category($slug, $sub = null) {
    if (PRETTY_URLS) {
        return SITE_URL . 'category/' . rawurlencode($slug) . ($sub ? '/' . rawurlencode($sub) : '');
    }
    return SITE_URL . 'category.php?slug=' . rawurlencode($slug) . ($sub ? '&sub=' . rawurlencode($sub) : '');
}
function url_news($slug) {
    return PRETTY_URLS ? SITE_URL . rawurlencode($slug) : SITE_URL . 'news.php?slug=' . rawurlencode($slug);
}
function url_page($slug) {
    return PRETTY_URLS ? SITE_URL . 'page/' . rawurlencode($slug) : SITE_URL . 'page.php?slug=' . rawurlencode($slug);
}
function url_search($q = null) {
    $base = PRETTY_URLS ? SITE_URL . 'search' : SITE_URL . 'search.php';
    return $q !== null ? $base . '?q=' . rawurlencode($q) : $base;
}
function url_slide($slug) {
    return PRETTY_URLS ? SITE_URL . 'slide/' . rawurlencode($slug) : SITE_URL . 'slide.php?slug=' . rawurlencode($slug);
}
function addQueryParam($url, $key, $value) {
    $sep = str_contains($url, '?') ? '&' : '?';
    return $url . $sep . $key . '=' . rawurlencode((string)$value);
}

function newsImageUrl($path) {
    if (empty($path)) return '';
    if (preg_match('#^https?://#i', $path)) return $path;
    return rtrim(ADMIN_UPLOAD_URL, '/') . '/' . ltrim($path, '/');
}

/* ---------------- Formatting ---------------- */

function excerpt($html, $len = null) {
    $len = $len ?: EXCERPT_LENGTH;
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$html)));
    if ($text === '') return '';
    if (mb_strlen($text, 'UTF-8') <= $len) return $text;
    $words = preg_split('/\s+/u', $text);
    $out = '';
    foreach ($words as $w) {
        $candidate = $out === '' ? $w : $out . ' ' . $w;
        if (mb_strlen($candidate, 'UTF-8') > $len) break;
        $out = $candidate;
    }
    if ($out === '') $out = mb_substr($text, 0, $len, 'UTF-8');
    return $out . '…';
}

function readingTime($content) {
    $words = preg_split('/\s+/u', trim(strip_tags((string)$content)));
    $count = count(array_filter($words));
    return max(1, (int)ceil($count / 200));
}

function formatDateFront($datetime, $format = 'd M Y') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

function timeAgoFront($datetime) {
    if (!$datetime) return '';
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return t('just_now');
    if ($diff < 3600) return sprintf(t('min_ago'), (int)floor($diff / 60));
    if ($diff < 86400) return sprintf(t('hr_ago'), (int)floor($diff / 3600));
    if ($diff < 86400 * 7) return sprintf(t('days_ago'), (int)floor($diff / 86400));
    return formatDateFront($datetime, 'd M Y');
}

function catLabel($n) {
    if (currentLang() === 'ta' && !empty($n['cat_name_ta'])) return $n['cat_name_ta'];
    return $n['cat_name'] ?? '';
}
function subcatLabel($s) {
    if (currentLang() === 'ta' && !empty($s['name_tamil'])) return $s['name_tamil'];
    return $s['name_english'] ?? '';
}
function catLabelRow($c) {
    if (currentLang() === 'ta' && !empty($c['name_tamil'])) return $c['name_tamil'];
    return $c['name_english'] ?? '';
}

function categoryColor($cat) {
    if (!empty($cat['cat_color'])) return $cat['cat_color'];
    if (!empty($cat['color'])) return $cat['color'];
    $palette = ['#7A1228', '#20304D', '#9C5B14', '#2F4D3A', '#5B3758', '#3A4750'];
    $id = (int)($cat['category_id'] ?? $cat['id'] ?? 0);
    return $palette[$id % count($palette)];
}

function catIconClass($cat) {
    $icon = trim($cat['cat_icon'] ?? $cat['icon'] ?? '');
    return $icon !== '' ? 'fas fa-' . $icon : 'fas fa-newspaper';
}

/* ---------------- Image / placeholder ---------------- */

function storyImageTag($n, $alt = '') {
    $alt = $alt ?: ($n['title'] ?? '');
    if (!empty($n['featured_image'])) {
        return '<img src="' . h(newsImageUrl($n['featured_image'])) . '" alt="' . h($alt) . '" loading="lazy">';
    }
    $color = categoryColor($n);
    return '<div class="img-placeholder" style="--ph-color:' . h($color) . '"><i class="fas fa-newspaper"></i></div>';
}

/* ---------------- Data: categories / subcategories ---------------- */

function getCategories() {
    static $cats = null;
    if ($cats === null) {
        $cats = db()->fetchAll("SELECT * FROM tbl_categories WHERE status = 1 ORDER BY sort_order, name_english");
    }
    return $cats;
}
function getCategoryBySlug($slug) {
    return db()->fetchOne("SELECT * FROM tbl_categories WHERE slug = ? AND status = 1", 's', $slug);
}
function getSubcategories($category_id) {
    return db()->fetchAll("SELECT * FROM tbl_subcategories WHERE category_id = ? AND status = 1 ORDER BY sort_order, name_english", 'i', $category_id);
}
function getSubcategoryBySlug($category_id, $slug) {
    return db()->fetchOne("SELECT * FROM tbl_subcategories WHERE category_id = ? AND slug = ? AND status = 1", 'is', $category_id, $slug);
}

/* ---------------- Data: news ---------------- */

const NEWS_SELECT = "n.*, c.name_english cat_name, c.name_tamil cat_name_ta, c.slug cat_slug, c.color cat_color, c.icon cat_icon, u.name author_name";
const NEWS_JOIN = "FROM tbl_news n
     LEFT JOIN tbl_categories c ON c.id = n.category_id
     LEFT JOIN tbl_users u ON u.id = n.author_id";

function getHeroStories($limit = 4) {
    return db()->fetchAll(
        "SELECT " . NEWS_SELECT . " " . NEWS_JOIN . "
         WHERE n.status = 'published'
         ORDER BY n.is_featured DESC, n.is_breaking DESC, n.published_at DESC, n.created_at DESC
         LIMIT ?", 'i', $limit
    );
}

function getCategoryLatest($category_id, $limit = 4, $excludeIds = []) {
    $exclude_sql = '';
    if ($excludeIds) {
        $ids = implode(',', array_map('intval', $excludeIds));
        $exclude_sql = "AND n.id NOT IN ($ids)";
    }
    return db()->fetchAll(
        "SELECT " . NEWS_SELECT . " " . NEWS_JOIN . "
         WHERE n.status = 'published' AND n.category_id = ? $exclude_sql
         ORDER BY n.published_at DESC, n.created_at DESC LIMIT ?", 'ii', $category_id, $limit
    );
}

function getTrendingNews($limit = 6) {
    return db()->fetchAll(
        "SELECT " . NEWS_SELECT . " " . NEWS_JOIN . "
         WHERE n.status = 'published'
         ORDER BY n.views DESC, n.published_at DESC LIMIT ?", 'i', $limit
    );
}

function getRelatedNews($category_id, $exclude_id, $limit = 4) {
    return db()->fetchAll(
        "SELECT " . NEWS_SELECT . " " . NEWS_JOIN . "
         WHERE n.status = 'published' AND n.category_id = ? AND n.id != ?
         ORDER BY n.published_at DESC LIMIT ?", 'iii', $category_id, $exclude_id, $limit
    );
}

function getNewsBySlug($slug) {
    return db()->fetchOne(
        "SELECT " . NEWS_SELECT . " " . NEWS_JOIN . "
         WHERE n.slug = ? AND n.status = 'published'", 's', $slug
    );
}

/**
 * General listing with pagination, used by category.php / search.php.
 * $filters: ['category_id'=>int, 'subcategory_id'=>int, 'q'=>string]
 */
function getNewsList($filters, $page = 1, $perPage = NEWS_PER_PAGE) {
    $where = ["n.status = 'published'"];
    $types = '';
    $params = [];

    if (!empty($filters['category_id'])) {
        $where[] = 'n.category_id = ?'; $types .= 'i'; $params[] = (int)$filters['category_id'];
    }
    if (!empty($filters['subcategory_id'])) {
        $where[] = 'n.subcategory_id = ?'; $types .= 'i'; $params[] = (int)$filters['subcategory_id'];
    }
    if (!empty($filters['q'])) {
        $where[] = '(n.title LIKE ? OR n.summary LIKE ? OR n.tags LIKE ?)';
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['q']);
        $like = '%' . $escaped . '%';
        $types .= 'sss'; $params[] = $like; $params[] = $like; $params[] = $like;
    }
    $where_sql = 'WHERE ' . implode(' AND ', $where);

    $total = (int)(db()->fetchOne("SELECT COUNT(*) c " . NEWS_JOIN . " $where_sql", $types, ...$params)['c'] ?? 0);

    $offset = max(0, ($page - 1) * $perPage);
    $items = db()->fetchAll(
        "SELECT " . NEWS_SELECT . " " . NEWS_JOIN . " $where_sql
         ORDER BY n.published_at DESC, n.created_at DESC LIMIT ? OFFSET ?",
        $types . 'ii', ...[...$params, $perPage, $offset]
    );

    return ['items' => $items, 'total' => $total, 'pages' => max(1, (int)ceil($total / $perPage))];
}

function trackView($news_id) {
    if (!isset($_SESSION['viewed_news'])) $_SESSION['viewed_news'] = [];
    if (!in_array($news_id, $_SESSION['viewed_news'], true)) {
        db()->query("UPDATE tbl_news SET views = views + 1 WHERE id = ?", 'i', $news_id);
        $_SESSION['viewed_news'][] = $news_id;
        if (count($_SESSION['viewed_news']) > 200) {
            $_SESSION['viewed_news'] = array_slice($_SESSION['viewed_news'], -100);
        }
    }
}

/* ---------------- Data: slider / breaking / ads / comments / pages ---------------- */

function getActiveSlides($limit = 8) {
    // Manually-curated promotional slides from the admin's Slider module.
    $manual = db()->fetchAll("SELECT * FROM tbl_slider WHERE status = 1 ORDER BY sort_order, id DESC");
    // Each slide has its own slug + content (added via slider-add.php / slider-edit.php),
    // rendered by slide.php. link_url is kept only as an optional external override.

    // Articles the editor flagged "Show in homepage slider" (tbl_news.is_slider)
    // when writing/editing a story. The admin exposes this checkbox, but until
    // now nothing on the frontend ever read it.
    $newsSlides = db()->fetchAll(
        "SELECT n.id, n.title, n.slug, n.featured_image,
                c.name_english cat_name, c.name_tamil cat_name_ta
         FROM tbl_news n LEFT JOIN tbl_categories c ON c.id = n.category_id
         WHERE n.status = 'published' AND n.is_slider = 1
         ORDER BY n.published_at DESC, n.created_at DESC
         LIMIT ?", 'i', $limit
    );

    $slides = [];
    foreach ($manual as $m) {
        $slides[] = [
            'title'    => $m['title'],
            'subtitle' => $m['subtitle'] ?? '',
            'image'    => $m['image'],
            'link_url' => $m['link_url'] ?: (!empty($m['slug']) ? url_slide($m['slug']) : null),
        ];
    }
    foreach ($newsSlides as $n) {
        $slides[] = [
            'title'    => $n['title'],
            'subtitle' => currentLang() === 'ta' && !empty($n['cat_name_ta']) ? $n['cat_name_ta'] : ($n['cat_name'] ?? ''),
            'image'    => $n['featured_image'],
            'link_url' => url_news($n['slug']),
        ];
    }
    return array_slice($slides, 0, $limit);
}
function getActiveBreaking() {
    return db()->fetchAll("SELECT * FROM tbl_breaking_news WHERE status = 1 ORDER BY sort_order, id DESC LIMIT 15");
}

/**
 * A handful of breaking-news rows for use as homepage hero side-cards.
 * tbl_breaking_news only has `text` + optional `link_url` — no image/category,
 * so these render with a lightweight card, not renderStoryCard().
 */
function getBreakingNewsCards($limit = 3) {
    return db()->fetchAll(
        "SELECT * FROM tbl_breaking_news WHERE status = 1 ORDER BY sort_order, id DESC LIMIT ?",
        'i', $limit
    );
}

function renderBreakingCard($b) {
    $hasLink  = !empty($b['link_url']);
    $tag      = $hasLink ? 'a' : 'div';
    $hasImage = !empty($b['image']);
    $mediaClass = $hasImage ? 'story-card__media' : 'story-card__media story-card__media--text-only';
    ob_start(); ?>
    <article class="story-card story-card--secondary story-card--breaking">
        <<?= $tag ?><?= $hasLink ? ' href="' . h($b['link_url']) . '"' : '' ?> class="<?= $mediaClass ?>">
            <?php if ($hasImage): ?>
            <img src="<?= h(newsImageUrl($b['image'])) ?>" alt="<?= h(mb_substr($b['text'], 0, 60)) ?>" loading="lazy">
            <?php else: ?>
            <div class="img-placeholder" style="--ph-color:#e63946"><i class="fas fa-bolt"></i></div>
            <?php endif; ?>
        </<?= $tag ?>>
        <div class="story-card__body">
            <div class="story-card__eyebrow">
                <span class="tag-breaking"><i class="fas fa-bolt"></i> <?= h(t('breaking')) ?></span>
            </div>
            <h3 class="story-card__title">
                <<?= $tag ?><?= $hasLink ? ' href="' . h($b['link_url']) . '"' : '' ?>><?= h($b['text']) ?></<?= $tag ?>>
            </h3>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

function getActiveAds($position, $limit = 2) {
    return db()->fetchAll("SELECT * FROM tbl_ads WHERE position = ? AND status = 1 ORDER BY sort_order, id DESC LIMIT ?", 'si', $position, $limit);
}
function getApprovedComments($news_id) {
    return db()->fetchAll("SELECT * FROM tbl_comments WHERE news_id = ? AND status = 'approved' ORDER BY created_at DESC", 'i', $news_id);
}
function getPageByKey($key) {
    // Supports installs where tbl_pages uses either `page_key` or `slug` as the lookup column.
    // mysqli throws exceptions on SQL errors by default (PHP 8.1+), so each attempt needs
    // its own try/catch rather than relying on @ suppression.
    $row = null;
    try {
        $row = db()->fetchOne("SELECT * FROM tbl_pages WHERE page_key = ?", 's', $key);
    } catch (\Throwable $e) {
        $row = null;
    }
    if (!$row) {
        try {
            $row = db()->fetchOne("SELECT * FROM tbl_pages WHERE slug = ?", 's', $key);
        } catch (\Throwable $e) {
            $row = null;
        }
    }
    return $row ?: null;
}

function renderAdSlot($position)
{
    $ad = db()->fetchOne(
        "SELECT * FROM tbl_ads 
         WHERE position=? AND status=1 
         ORDER BY sort_order ASC, id DESC 
         LIMIT 1",
        's',
        $position
    );

    if (!$ad) {
        return '';
    }

    ob_start();

    // IMAGE AD
    if (($ad['ad_type'] ?? '') === 'image' && !empty($ad['image'])) {

        $image = ADMIN_UPLOAD_URL . ltrim($ad['image'], '/');

        echo '<div class="ad-slot ad-image">';
        
        if (!empty($ad['link_url'])) {
            echo '<a href="' . h($ad['link_url']) . '" target="_blank" rel="noopener">';
        }

        echo '<img src="' . h($image) . '" 
              alt="' . h($ad['title']) . '"
              loading="lazy">';

        if (!empty($ad['link_url'])) {
            echo '</a>';
        }

        echo '</div>';

    }

    // HTML / SCRIPT CODE AD
    elseif (($ad['ad_type'] ?? '') === 'code' && !empty($ad['ad_code'])) {

        echo '<div class="ad-slot ad-code">';
        echo $ad['ad_code'];
        echo '</div>';

    }

    return ob_get_clean();
}
/* ---------------- Story card renderer ---------------- */

function renderStoryCard($n, $variant = 'grid') {
    $url = url_news($n['slug']);
    $cat = catLabel($n);
    $catUrl = !empty($n['cat_slug']) ? url_category($n['cat_slug']) : null;
    $color = categoryColor($n);
    $img = storyImageTag($n);
    $breaking = !empty($n['is_breaking'])
        ? '<span class="tag-breaking"><i class="fas fa-bolt"></i> ' . h(t('breaking')) . '</span>' : '';
    // $badge = $catUrl ? '<a href="' . h($catUrl) . '" class="story-card__badge" style="background:' . h($color) . '">' . h($cat) . '</a>' : '';

    ob_start(); ?>
    <article class="story-card story-card--<?= h($variant) ?>">
        <a href="<?= h($url) ?>" class="story-card__media"><?= $img ?><?= $badge ?></a>
        <div class="story-card__body">
            <?php if ($variant !== 'list' && $variant !== 'trending'): ?>
            <div class="story-card__eyebrow">
                <?php if ($catUrl): ?><a href="<?= h($catUrl) ?>" style="color:<?= h($color) ?>"><?= h($cat) ?></a><?php endif; ?>
                <span class="dot">•</span><span><?= h(timeAgoFront($n['published_at'] ?? $n['created_at'])) ?></span>
                <?= $breaking ?>
            </div>
            <?php endif; ?>
            <h3 class="story-card__title"><a href="<?= h($url) ?>"><?= h($n['title']) ?></a></h3>
            <?php if (in_array($variant, ['lead', 'secondary', 'grid'])): ?>
            <p class="story-card__excerpt"><?= h(excerpt($n['summary'] ?: $n['content'], $variant === 'lead' ? 180 : 110)) ?></p>
            <?php endif; ?>
            <?php if ($variant === 'list'): ?>
            <div class="story-card__meta"><span><?= h(timeAgoFront($n['published_at'] ?? $n['created_at'])) ?></span></div>
            <?php endif; ?>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

function renderTrendingItem($n, $rank) {
    $url = url_news($n['slug']);
    ob_start(); ?>
    <li class="trend-item">
        <span class="trend-item__rank"><?= (int)$rank ?></span>
        <a href="<?= h($url) ?>" class="trend-item__thumb"><?= storyImageTag($n) ?></a>
        <div class="trend-item__body">
            <a href="<?= h($url) ?>" class="trend-item__title"><?= h($n['title']) ?></a>
            <span class="trend-item__meta"><i class="fas fa-eye"></i> <?= number_format((int)$n['views']) ?> <?= h(t('views')) ?></span>
        </div>
    </li>
    <?php
    return ob_get_clean();
}

/* ---------------- Pagination ---------------- */

function paginationLinks($total, $perPage, $currentPage, callable $urlFor) {
    $pages = max(1, (int)ceil($total / $perPage));
    if ($pages <= 1) return '';
    $currentPage = max(1, min($pages, $currentPage));
    $html = '<nav class="pagination" aria-label="Pagination">';
    if ($currentPage > 1) {
        $html .= '<a href="' . h($urlFor($currentPage - 1)) . '" class="pagination__btn"><i class="fas fa-chevron-left"></i> ' . h(t('prev')) . '</a>';
    }
    $html .= '<span class="pagination__status">' . h(sprintf(t('page_of'), $currentPage, $pages)) . '</span>';
    if ($currentPage < $pages) {
        $html .= '<a href="' . h($urlFor($currentPage + 1)) . '" class="pagination__btn">' . h(t('next')) . ' <i class="fas fa-chevron-right"></i></a>';
    }
    $html .= '</nav>';
    return $html;
}

/* ---------------- Misc ---------------- */

function isSpamBot($field = 'website') {
    return !empty($_POST[$field]);
}

function feFlashSet($type, $msg) { $_SESSION['fe_flash_' . $type] = $msg; }
function feFlashGet($type) {
    $msg = $_SESSION['fe_flash_' . $type] ?? null;
    unset($_SESSION['fe_flash_' . $type]);
    return $msg;
}

function show404() {
    http_response_code(404);
    $page_title = t('page_not_found');
    include __DIR__ . '/header.php';
    ?>
    <div class="container not-found">
        <div class="not-found__mark">404</div>
        <h1><?= h(t('page_not_found')) ?></h1>
        <p><?= h(t('not_found_msg')) ?></p>
        <a href="<?= h(url_home()) ?>" class="btn btn--primary"><?= h(t('back_home')) ?></a>
    </div>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}