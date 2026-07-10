<?php
// search.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/lang-strings.php';
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$result = $q ? getNewsList(['q' => $q], $page, SEARCH_PER_PAGE) : ['items' => [], 'total' => 0, 'pages' => 1];

$page_title = $q ? (t('search_title') . ': ' . $q) : t('search_title');
$body_class = 'page-search';

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-banner" style="--accent:#7A1228">
        <span class="page-banner__icon"><i class="fas fa-search"></i></span>
        <div>
            <h1><?= h(t('search_title')) ?><?= $q ? ': "' . h($q) . '"' : '' ?></h1>
            <?php if ($q): ?><p><?= number_format($result['total']) ?> <?= h(t('latest')) ?></p><?php endif; ?>
        </div>
    </div>
</div>

<div class="container layout-2col">
    <div class="layout-2col__main">
        <?php if ($q && $result['items']): ?>
        <div class="story-grid">
            <?php foreach ($result['items'] as $n) echo renderStoryCard($n, 'grid'); ?>
        </div>
        <?= paginationLinks($result['total'], SEARCH_PER_PAGE, $page, function ($p) use ($q) {
            return addQueryParam(url_search($q), 'page', $p);
        }) ?>
        <?php elseif ($q): ?>
        <div class="empty-state">
            <i class="fas fa-magnifying-glass"></i>
            <p><?= h(t('search_empty')) ?></p>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-magnifying-glass"></i>
            <p><?= h(t('search_ph')) ?></p>
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
    </aside>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
