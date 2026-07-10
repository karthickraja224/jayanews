<?php
// page.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/lang-strings.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$pageRow = $slug ? getPageByKey($slug) : null;

if (!$pageRow) { show404(); }

$page_title = $pageRow['title'];
$meta_description = $pageRow['meta_description'] ?: excerpt($pageRow['content'], 200);
$body_class = 'page-static page-static--' . preg_replace('/[^a-z0-9\-]/', '', $slug);

if (isset($_GET['sent'])) feFlashSet('success', t('message_sent'));

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-banner" style="--accent:#7A1228">
        <span class="page-banner__icon"><i class="fas fa-file-lines"></i></span>
        <div><h1><?= h($pageRow['title']) ?></h1></div>
    </div>
</div>

<div class="container static-page">
    <div class="static-page__content">
        <?= $pageRow['content'] ?>
    </div>

    <?php if ($slug === 'contact'): ?>
    <div class="contact-grid">
        <div class="contact-info">
            <h3><?= h(t('get_in_touch')) ?></h3>
            <?php if (getSetting('site_address')): ?>
            <p><i class="fas fa-location-dot"></i><br><strong><?= h(t('address')) ?></strong><br><?= h(getSetting('site_address')) ?></p>
            <?php endif; ?>
            <?php if (getSetting('site_phone')): ?>
            <p><i class="fas fa-phone"></i><br><strong><?= h(t('phone')) ?></strong><br><a href="tel:<?= h(getSetting('site_phone')) ?>"><?= h(getSetting('site_phone')) ?></a></p>
            <?php endif; ?>
            <?php if (getSetting('site_email')): ?>
            <p><i class="fas fa-envelope"></i><br><strong><?= h(t('email')) ?></strong><br><a href="mailto:<?= h(getSetting('site_email')) ?>"><?= h(getSetting('site_email')) ?></a></p>
            <?php endif; ?>
        </div>
        <form class="contact-form" action="<?= h(SITE_URL) ?>contact-submit.php" method="post">
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div class="form-row">
                <input type="text" name="name" placeholder="<?= h(t('your_name')) ?>" required>
                <input type="email" name="email" placeholder="<?= h(t('contact_email_ph')) ?>" required>
            </div>
            <input type="text" name="subject" placeholder="<?= h(t('subject')) ?>">
            <textarea name="message" rows="5" placeholder="<?= h(t('your_message')) ?>" required></textarea>
            <button type="submit" class="btn btn--primary"><?= h(t('send_message')) ?></button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
