<?php
// settings/youtube-live.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = 'Live YouTube News';
$breadcrumbs = [__('nav_settings') ?: 'Settings' => BASE_URL . 'settings/', 'Live YouTube News' => null];

// Extract a bare YouTube video ID from a full URL, live URL, or an ID pasted directly.
function extractYoutubeId($raw) {
    $raw = trim($raw);
    if ($raw === '') return '';
    if (preg_match('~(?:youtu\.be/|v=|live/)([A-Za-z0-9_-]{6,})~', $raw, $m)) return $m[1];
    if (preg_match('~^[A-Za-z0-9_-]{6,}$~', $raw)) return $raw;
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['youtube_live'] ?? '');

    if ($input === '') {
        // Empty input = turn the live section off on the homepage.
        saveSetting('youtube_live_id', '');
        flashSuccess('Live News section turned off.');
    } else {
        $id = extractYoutubeId($input);
        if (!$id) {
            flashError('Could not read a valid YouTube video ID from that. Paste the video URL, the live URL, or the bare ID.');
        } else {
            saveSetting('youtube_live_id', $id);
            flashSuccess('Live News updated successfully!');
        }
    }
    header('Location: youtube-live.php'); exit();
}

$current = getSetting('youtube_live_id', '');
include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left"><h1><i class="fab fa-youtube"></i> <?= htmlspecialchars($page_title) ?></h1></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <?php showFlash(); ?>
        <form method="POST">
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-broadcast-tower"></i> Homepage Live Stream</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">YouTube Live Video</label>
                        <input type="text" name="youtube_live" class="form-control"
                               value="<?= htmlspecialchars($current) ?>"
                               placeholder="Paste video ID, watch URL, or live URL — e.g. https://www.youtube.com/watch?v=XXXXXXXXXXX">
                        <small style="color:var(--text-muted);display:block;margin-top:6px">
                            Leave this blank and save to hide the Live News section from the homepage.
                        </small>
                    </div>

                    <?php if ($current): ?>
                    <div class="form-group">
                        <label class="form-label">Current Preview</label>
                        <div style="position:relative;width:100%;max-width:480px;padding-top:27%;background:#000;border-radius:6px;overflow:hidden">
                            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($current) ?>?mute=1"
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen loading="lazy"></iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('btn_save') ?: 'Save' ?></button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>