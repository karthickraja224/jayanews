    <?php
    // index.php
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/constants.php';
    require_once __DIR__ . '/includes/lang-strings.php';
    require_once __DIR__ . '/includes/functions.php';
    include __DIR__ . '/includes/header.php';
    $page_title = null; // homepage uses site name as title
    $body_class = 'page-home';
    
    $hero = getHeroStories(1);
    $heroIds = array_map(fn($n) => (int)$n['id'], $hero);
    $lead = $hero[0] ?? null;
    $secondary = getBreakingNewsCards(3);
    
    $slides = getActiveSlides();
    $trending = getTrendingNews(TRENDING_LIMIT);
    // $sections = array_slice(getCategories(), 0, HOMEPAGE_SECTIONS);
    $sections = getCategories(); // show every category that has news
    
    // Live YouTube news stream — configured via tbl_settings (key: youtube_live_id).
    // Accepts either a bare video ID (dQw4w9WgXcQ) or a full watch/live URL; both work.
    $ytLiveRaw = getSetting('youtube_live_id');
    $ytLiveId  = '';
    if ($ytLiveRaw) {
        if (preg_match('~(?:youtu\.be/|v=|live/)([A-Za-z0-9_-]{6,})~', $ytLiveRaw, $m)) {
            $ytLiveId = $m[1];
        } elseif (preg_match('~^[A-Za-z0-9_-]{6,}$~', $ytLiveRaw)) {
            $ytLiveId = $ytLiveRaw; // already a bare video ID
        }
    }
    ?>
    
    <?php if ($ytLiveId): ?>
    <section class="container live-tv">
        <div class="section-head">
            <h2><span class="live-tv__dot"></span> <?= h(t('Live News') ?: 'Live News') ?></h2>
        </div>
        <div class="live-tv__frame">
            <iframe
                src="https://www.youtube.com/embed/<?= h($ytLiveId) ?>?autoplay=0&mute=1"
                title="Live News"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy"></iframe>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if ($lead): ?>
    <section class="container hero">
        <div class="hero__lead"><?= renderStoryCard($lead, 'lead') ?></div>
        <?php if ($secondary): ?>
        <div class="hero__secondary">
            <?php foreach ($secondary as $b) echo renderBreakingCard($b); ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
    
    <?php if ($slides): ?>
    <section class="container">
        <div class="section-head">
            <h2><i class="fas fa-star"></i> <?= h(t('editors_picks')) ?></h2>
        </div>
        <div class="picks-slider" id="picksSlider" data-autoplay="6000">
            <div class="picks-slider__track">
                <?php foreach ($slides as $s):
                    $hasLink = !empty($s['link_url']);
                    $tag = $hasLink ? 'a' : 'div';
                ?>
                <div class="picks-slider__slide">
                    <<?= $tag ?><?= $hasLink ? ' href="' . h($s['link_url']) . '"' : '' ?> class="picks-slider__media">
                        <?php if ($s['image']): ?><img src="<?= h(newsImageUrl($s['image'])) ?>" alt="<?= h($s['title']) ?>" loading="lazy">
                        <?php else: ?><div class="img-placeholder" style="--ph-color:#7A1228"><i class="fas fa-newspaper"></i></div><?php endif; ?>
                    </<?= $tag ?>>
                    <div class="picks-slider__caption">
                        <h3><?= h($s['title']) ?></h3>
                        <?php if ($s['subtitle']): ?><p><?= h($s['subtitle']) ?></p><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($slides) > 1): ?>
            <button class="picks-slider__nav picks-slider__nav--prev" aria-label="<?= h(t('prev')) ?>"><i class="fas fa-chevron-left"></i></button>
            <button class="picks-slider__nav picks-slider__nav--next" aria-label="<?= h(t('next')) ?>"><i class="fas fa-chevron-right"></i></button>
            <div class="picks-slider__dots"></div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <div class="container layout-2col">
        <div class="layout-2col__main">
            <?php foreach ($sections as $cat):
                $items = getCategoryLatest($cat['id'], HOMEPAGE_SECTION_ITEMS, $heroIds);
                if (!$items) continue;
            ?>
            <section class="section">
                <div class="section-head" style="--accent: <?= h($cat['color'] ?: '#7A1228') ?>">
                    <h2><i class="<?= h(catIconClass($cat)) ?>"></i> <?= h(catLabelRow($cat)) ?></h2>
                    <a href="<?= h(url_category($cat['slug'])) ?>" class="section-head__more"><?= h(t('view_all')) ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="story-grid">
                    <?php foreach ($items as $n) echo renderStoryCard($n, 'grid'); ?>
                </div>
            </section>
            <?php endforeach; ?>
        </div>
    
        <aside class="layout-2col__sidebar">
            <?php if ($trending): ?>
            <div class="widget">
                <h3 class="widget__title"><i class="fas fa-fire"></i> <?= h(t('trending')) ?></h3>
                <ol class="trend-list">
                    <?php foreach ($trending as $i => $n) echo renderTrendingItem($n, $i + 1); ?>
                </ol>
            </div>
            <?php endif; ?>
    
            <?= renderAdSlot('sidebar-top') ?>
    
            <?php $wa = getSetting('whatsapp_number'); if ($wa): ?>
            <div class="widget widget--whatsapp">
                <i class="fab fa-whatsapp"></i>
                <p><?= h(t('whatsapp_channel')) ?></p>
                <a href="https://wa.me/<?= h(preg_replace('/\D/', '', $wa)) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp"><?= h(t('whatsapp_channel')) ?></a>
            </div>
            <?php endif; ?>
    
            <?= renderAdSlot('sidebar-bottom') ?>
        </aside>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>