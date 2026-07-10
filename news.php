<?php
// news.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/lang-strings.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$news = $slug ? getNewsBySlug($slug) : null;

// Fall back to a static page if no article matches (keeps /{slug} links working
// for things like /about if pretty URLs are used without the /page/ prefix).
if (!$news) {
    $maybePage = $slug ? getPageByKey($slug) : null;
    if ($maybePage) {
        header('Location: ' . url_page($slug));
        exit;
    }
    show404();
}

trackView((int)$news['id']);

$is_article = true;
$current_cat_slug = $news['cat_slug'] ?? null;
$page_title = $news['meta_title'] ?: $news['title'];
$meta_description = $news['meta_description'] ?: excerpt($news['summary'] ?: $news['content'], 200);
$meta_image = $news['featured_image'];
$canonical_path = preg_replace('#^https?://[^/]+#i', '', url_news($news['slug']));
$body_class = 'page-article';

if (isset($_GET['commented'])) feFlashSet('success', t('comment_pending'));

$tags = array_filter(array_map('trim', explode(',', (string)($news['tags'] ?? ''))));
$comments = getApprovedComments($news['id']);
$related = getRelatedNews($news['category_id'], $news['id'], RELATED_LIMIT);
$catColor = categoryColor($news);
$shareUrl = rawurlencode(rtrim(getSetting('site_url', ''), '/') . url_news($news['slug']));
$shareTitle = rawurlencode($news['title']);

include __DIR__ . '/includes/header.php';
?>

<div class="container article-wrap">
    <article class="article">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= h(url_home()) ?>"><?= h(t('nav_home')) ?></a>
            <?php if ($news['cat_slug']): ?> <span>/</span> <a href="<?= h(url_category($news['cat_slug'])) ?>"><?= h(catLabel($news)) ?></a><?php endif; ?>
        </nav>

        <div class="article__eyebrow">
            <?php if ($news['cat_slug']): ?>
            <a href="<?= h(url_category($news['cat_slug'])) ?>" class="article__cat" style="background:<?= h($catColor) ?>"><?= h(catLabel($news)) ?></a>
            <?php endif; ?>
            <?php if ($news['is_breaking']): ?><span class="tag-breaking"><i class="fas fa-bolt"></i> <?= h(t('breaking')) ?></span><?php endif; ?>
        </div>

        <h1 class="article__title"><?= h($news['title']) ?></h1>
        <?php if ($news['summary']): ?><p class="article__standfirst"><?= h($news['summary']) ?></p><?php endif; ?>

        <div class="article__meta">
            <?php if ($news['author_name']): ?><span><i class="far fa-user"></i> <?= h(t('by')) ?> <?= h($news['author_name']) ?></span><?php endif; ?>
            <span><i class="far fa-clock"></i> <?= h(formatDateFront($news['published_at'] ?? $news['created_at'], 'd M Y, h:i A')) ?></span>
            <span><i class="far fa-hourglass"></i> <?= readingTime($news['content']) ?> <?= h(t('min_read')) ?></span>
            <span><i class="far fa-eye"></i> <?= number_format((int)$news['views']) ?> <?= h(t('views')) ?></span>
        </div>

        <?php if ($news['featured_image']): ?>
        <figure class="article__media">
            <img src="<?= h(newsImageUrl($news['featured_image'])) ?>" alt="<?= h($news['title']) ?>">
            <?php if ($news['image_caption']): ?><figcaption><?= h($news['image_caption']) ?></figcaption><?php endif; ?>
        </figure>
        <?php endif; ?>

        <div class="share-row share-row--top">
            <span><?= h(t('share')) ?></span>
            <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener" aria-label="X"><i class="fab fa-x-twitter"></i></a>
            <button type="button" class="copy-link" data-url="<?= h(rtrim(getSetting('site_url', ''), '/') . url_news($news['slug'])) ?>" aria-label="<?= h(t('copy_link')) ?>"><i class="fas fa-link"></i></button>
        </div>

        <div class="article__body drop-cap"><?= $news['content'] ?></div>

        <?php if ($tags): ?>
        <div class="tag-row">
            <span><?= h(t('tags')) ?>:</span>
            <?php foreach ($tags as $tag): ?><a href="<?= h(url_search($tag)) ?>" class="pill pill--sm"><?= h($tag) ?></a><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($news['source']): ?><p class="article__source"><?= h(t('source')) ?>: <?= h($news['source']) ?></p><?php endif; ?>

        <?php if ($news['author_name']): ?>
        <div class="author-box">
            <span class="author-box__avatar"><?= h(mb_substr($news['author_name'], 0, 1)) ?></span>
            <div><span class="author-box__label"><?= h(t('by')) ?></span><strong><?= h($news['author_name']) ?></strong></div>
        </div>
        <?php endif; ?>

        <?= renderAdSlot('content-bottom') ?>

        <?php if ($related): ?>
        <section class="section">
            <div class="section-head"><h2><i class="fas fa-layer-group"></i> <?= h(t('related')) ?></h2></div>
            <div class="story-grid">
                <?php foreach ($related as $r) echo renderStoryCard($r, 'grid'); ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="comments" id="comments">
            <h2><i class="far fa-comments"></i> <?= h(t('comments')) ?> (<?= count($comments) ?>)</h2>

            <?php if ($comments): ?>
            <ul class="comment-list">
                <?php foreach ($comments as $c): ?>
                <li class="comment">
                    <span class="comment__avatar"><?= h(mb_substr($c['name'], 0, 1)) ?></span>
                    <div class="comment__body">
                        <div class="comment__head"><strong><?= h($c['name']) ?></strong><span><?= h(timeAgoFront($c['created_at'])) ?></span></div>
                        <p><?= h($c['comment']) ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="comments__empty"><?= h(t('no_comments')) ?></p>
            <?php endif; ?>

            <form class="comment-form" action="<?= h(SITE_URL) ?>comment-submit.php" method="post">
                <h3><?= h(t('leave_comment')) ?></h3>
                <input type="hidden" name="news_id" value="<?= (int)$news['id'] ?>">
                <input type="hidden" name="slug" value="<?= h($news['slug']) ?>">
                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div class="form-row">
                    <input type="text" name="name" placeholder="<?= h(t('your_name')) ?>" required>
                    <input type="email" name="email" placeholder="<?= h(t('your_email')) ?>">
                </div>
                <textarea name="comment" rows="4" placeholder="<?= h(t('your_comment')) ?>" required></textarea>
                <button type="submit" class="btn btn--primary"><?= h(t('submit_comment')) ?></button>
            </form>
        </section>
    </article>

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
