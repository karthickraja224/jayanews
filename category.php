<?php
// category.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/lang-strings.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$cat  = $slug ? getCategoryBySlug($slug) : null;

if (!$cat) { show404(); }

$current_cat_slug = $cat['slug'];
$subSlug = trim($_GET['sub'] ?? '');
$subcategories = getSubcategories($cat['id']);
$activeSub = $subSlug ? getSubcategoryBySlug($cat['id'], $subSlug) : null;

$page = max(1, (int)($_GET['page'] ?? 1));
$filters = ['category_id' => $cat['id']];
if ($activeSub) $filters['subcategory_id'] = $activeSub['id'];

$result = getNewsList($filters, $page, NEWS_PER_PAGE);

$page_title = catLabelRow($cat);

$meta_description = !empty($cat['meta_description'])
    ? $cat['meta_description']
    : $page_title;

$body_class = 'page-category';

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-banner" style="--accent: <?= h($cat['color'] ?: '#7A1228') ?>">
        <span class="page-banner__icon"><i class="<?= h(catIconClass($cat)) ?>"></i></span>
        <div>
            <h1><?= h($page_title) ?></h1>
            <p><?= number_format($result['total']) ?> <?= h(t('latest')) ?></p>
        </div>
    </div>

    <?php if ($subcategories): ?>
    <div class="pill-row">
        <a href="<?= h(url_category($cat['slug'])) ?>" class="pill <?= !$activeSub ? 'is-active' : '' ?>"><?= h(t('view_all')) ?></a>
        <?php foreach ($subcategories as $sub): ?>
        <a href="<?= h(url_category($cat['slug'], $sub['slug'])) ?>" class="pill <?= ($activeSub && $activeSub['id'] === $sub['id']) ? 'is-active' : '' ?>"><?= h(subcatLabel($sub)) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div class="container layout-2col">
    <div class="layout-2col__main">
        <?php if ($result['items']): ?>
        <div class="story-grid">
            <?php foreach ($result['items'] as $n) echo renderStoryCard($n, 'grid'); ?>
        </div>
        <?= paginationLinks($result['total'], NEWS_PER_PAGE, $page, function ($p) use ($cat, $activeSub) {
            return addQueryParam(url_category($cat['slug'], $activeSub['slug'] ?? null), 'page', $p);
        }) ?>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-newspaper"></i>
            <p><?= h(t('no_news_yet')) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <aside class="layout-2col__sidebar">
        <?php $trending = getTrendingNews(TRENDING_LIMIT); if ($trending): ?>
        <div class="widget">
            <h3 class="widget__title"><i class="fas fa-fire"></i> <?= h(t('trending')) ?></h3>
            <ol class="trend-list">
                <?php foreach ($trending as $i => $n) echo renderTrendingItem($n, $i + 1); ?>
            </ol>
        </div>
        <?php endif; ?>
        <?= renderAdSlot('sidebar-top') ?>
        <?= renderAdSlot('sidebar-bottom') ?>
    </aside>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
