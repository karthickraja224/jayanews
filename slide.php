<?php
// slide.php — displays a single slider slide's own content (title/subtitle/image/content)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/lang-strings.php';
require_once __DIR__ . '/includes/functions.php';

$slug  = trim($_GET['slug'] ?? '');
$slide = $slug ? db()->fetchOne("SELECT * FROM tbl_slider WHERE slug = ? AND status = 1", 's', $slug) : null;

if (!$slide) {
    show404(); // matches your existing 404 helper in functions.php
}

$page_title = $slide['title'];
$body_class = 'page-slide';

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:800px;padding:30px 0">
    <article class="slide-article">
        <h1 class="slide-article__title"><?= h($slide['title']) ?></h1>

        <?php if (!empty($slide['subtitle'])): ?>
        <p class="slide-article__subtitle" style="color:var(--text-muted);font-size:1.1em;margin-top:6px">
            <?= h($slide['subtitle']) ?>
        </p>
        <?php endif; ?>

        <?php if (!empty($slide['image'])): ?>
        <img src="<?= h(newsImageUrl($slide['image'])) ?>"
             alt="<?= h($slide['title']) ?>"
             style="width:100%;border-radius:8px;margin:20px 0">
        <?php endif; ?>

        <div class="slide-article__content" style="line-height:1.8;font-size:1.05em">
            <?= $slide['content'] // stored content is trusted admin input, same as tbl_news.content rendering ?>
        </div>
    </article>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
