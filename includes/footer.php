<?php
// includes/footer.php
$footer_text = getSetting('footer_text', t('tagline_fallback'));
$copyright   = getSetting('copyright_text', '© ' . date('Y') . ' ' . getSetting('site_name', 'Jaya Plus') . '. ' . t('all_rights'));
$wa = getSetting('whatsapp_number');
$site_logo   = getSetting('site_logo', '');
?>
</main>
<?= renderAdSlot('footer') ?>
<footer class="site-footer">
    <div class="container footer__grid">
        <div class="footer__col footer__col--brand">
            <?php if ($site_logo): ?>
                <img src="<?= h(newsImageUrl($site_logo)) ?>" alt="<?= h(getSetting('site_name', 'Jaya Plus')) ?>" class="footer__logo" style="width:100px;">
            <?php else: ?>
                <span class="footer__brand"><?= h(getSetting('site_name', 'Jaya Plus')) ?></span>
            <?php endif; ?>
            <p><?= h($footer_text) ?></p>
            <?php if (getSetting('show_social_icons', '1') === '1'): ?>
            <div class="footer__social">
                <?php
                $socials = [
                    'facebook_url' => 'fab fa-facebook-f', 'twitter_url' => 'fab fa-x-twitter',
                    'instagram_url' => 'fab fa-instagram', 'youtube_url' => 'fab fa-youtube',
                    'telegram_url' => 'fab fa-telegram', 'linkedin_url' => 'fab fa-linkedin-in',
                    'pinterest_url' => 'fab fa-pinterest-p',
                ];
                foreach ($socials as $key => $icon) {
                    $val = getSetting($key);
                    if ($val) echo '<a href="' . h($val) . '" target="_blank" rel="noopener" aria-label="' . h($key) . '"><i class="' . $icon . '"></i></a>';
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="footer__col">
            <h4><?= h(t('nav_categories')) ?></h4>
            <ul>
                <?php foreach (array_slice(getCategories(), 0, 8) as $cat): ?>
                <li><a href="<?= h(url_category($cat['slug'])) ?>"><?= h(catLabelRow($cat)) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="footer__col">
            <h4><?= h(t('quick_links')) ?></h4>
            <ul>
                <li><a href="<?= h(url_page('about')) ?>"><?= h(t('about_us')) ?></a></li>
                <li><a href="<?= h(url_page('contact')) ?>"><?= h(t('contact_us')) ?></a></li>
                <li><a href="<?= h(url_page('privacy')) ?>"><?= h(t('privacy_policy')) ?></a></li>
                <li><a href="<?= h(url_page('terms')) ?>"><?= h(t('terms')) ?></a></li>
            </ul>
        </div>
        <div class="footer__col">
            <h4><?= h(t('get_in_touch')) ?></h4>
            <ul class="footer__contact">
                <?php if (getSetting('site_address')): ?><li><i class="fas fa-location-dot"></i> <?= h(getSetting('site_address')) ?></li><?php endif; ?>
                <?php if (getSetting('site_phone')): ?><li><i class="fas fa-phone"></i> <a href="tel:<?= h(getSetting('site_phone')) ?>"><?= h(getSetting('site_phone')) ?></a></li><?php endif; ?>
                <?php if (getSetting('site_email')): ?><li><i class="fas fa-envelope"></i> <a href="mailto:<?= h(getSetting('site_email')) ?>"><?= h(getSetting('site_email')) ?></a></li><?php endif; ?>
            </ul>
            <?php if ($wa): ?>
            <a class="btn btn--whatsapp" href="https://wa.me/<?= h(preg_replace('/\D/', '', $wa)) ?>" target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i> <?= h(t('whatsapp_channel')) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <span><?= h($copyright) ?></span>
        </div>
    </div>
</footer>
<button id="backToTop" class="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>
<script src="<?= h(ASSETS_URL) ?>js/main.js?v=<?= h(APP_VERSION) ?>"></script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>