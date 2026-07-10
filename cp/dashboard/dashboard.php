<?php
// dashboard/dashboard.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title = __('dashboard_title');
$breadcrumbs = [__('dashboard_title') => null];
$stats = getDashboardStats();

// Recent news
$recent_news = db()->fetchAll("
    SELECT n.*, c.name_english cat_name, u.name author_name
    FROM tbl_news n
    LEFT JOIN tbl_categories c ON c.id = n.category_id
    LEFT JOIN tbl_users u ON u.id = n.author_id
    ORDER BY n.created_at DESC LIMIT 8
");

// Recent comments
$recent_comments = db()->fetchAll("
    SELECT cm.*, n.title news_title
    FROM tbl_comments cm
    LEFT JOIN tbl_news n ON n.id = cm.news_id
    ORDER BY cm.created_at DESC LIMIT 5
");

// Views per day (last 7 days) — simplified
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $row = db()->fetchOne("SELECT COUNT(*) c FROM tbl_news WHERE DATE(published_at) = ?", 's', $day);
    $chart_data[] = ['date' => date('d M', strtotime($day)), 'count' => (int)$row['c']];
}

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-tachometer-alt"></i> <?= __('dashboard_title') ?></h1>
        <p><?= __('welcome_msg') ?> <?= htmlspecialchars(currentUser()['name']) ?>! <?= __('dashboard_welcome') ?></p>
    </div>
    <div class="page-header-right">
        <a href="<?= BASE_URL ?>news/news-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('btn_add_news') ?></a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card" style="--stat-color:#e63946;--stat-bg:rgba(230,57,70,.12)">
        <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['total_news']) ?></div>
            <div class="stat-label"><?= __('stat_total_news') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#2ecc71;--stat-bg:rgba(46,204,113,.12)">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['published']) ?></div>
            <div class="stat-label"><?= __('stat_published') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#f4a261;--stat-bg:rgba(244,162,97,.12)">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['drafts']) ?></div>
            <div class="stat-label"><?= __('stat_drafts') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#3a86ff;--stat-bg:rgba(58,134,255,.12)">
        <div class="stat-icon"><i class="fas fa-eye"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['total_views']) ?></div>
            <div class="stat-label"><?= __('stat_views') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#2ec4b6;--stat-bg:rgba(46,196,182,.12)">
        <div class="stat-icon"><i class="fas fa-comments"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['total_comments']) ?></div>
            <div class="stat-label"><?= __('stat_comments') ?></div>
            <?php if ($stats['pending_comments'] > 0): ?>
            <div class="stat-trend"><span style="color:var(--warning)"><?= $stats['pending_comments'] ?> <?= __('stat_pending') ?></span></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#f4c542;--stat-bg:rgba(244,197,66,.12)">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
            <div class="stat-label"><?= __('stat_users') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#6a4c93;--stat-bg:rgba(106,76,147,.12)">
        <div class="stat-icon"><i class="fas fa-tags"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['categories']) ?></div>
            <div class="stat-label"><?= __('stat_categories') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#e76f51;--stat-bg:rgba(231,111,81,.12)">
        <div class="stat-icon"><i class="fas fa-bolt"></i></div>
        <div class="stat-info">
            <?php $bn = db()->fetchOne("SELECT COUNT(*) c FROM tbl_breaking_news WHERE status=1")['c']; ?>
            <div class="stat-value"><?= $bn ?></div>
            <div class="stat-label"><?= __('nav_breaking') ?></div>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px">
    <!-- Recent news table -->
    <div class="card" style="grid-column:1/-1">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-newspaper"></i> <?= __('recent_news') ?></span>
            <a href="<?= BASE_URL ?>news/news-list.php" class="btn btn-secondary btn-sm"><?= __('nav_all_news') ?></a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th><?= __('col_news') ?></th>
                        <th><?= __('col_category') ?></th>
                        <th><?= __('col_author') ?></th>
                        <th><?= __('col_status') ?></th>
                        <th><?= __('col_views') ?></th>
                        <th><?= __('col_date') ?></th>
                        <th><?= __('col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recent_news): foreach ($recent_news as $i => $n): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?php if ($n['featured_image']): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($n['featured_image']) ?>" class="news-thumb" style="float:left;margin-right:8px;">
                        <?php endif; ?>
                        <strong><?= mb_substr(htmlspecialchars($n['title']), 0, 55) ?><?= mb_strlen($n['title']) > 55 ? '…' : '' ?></strong>
                        <?php if ($n['is_breaking']): ?><span class="badge badge-danger" style="font-size:9px;margin-left:4px">Breaking</span><?php endif; ?>
                    </td>
                    <td><span class="text-muted"><?= htmlspecialchars($n['cat_name'] ?? '—') ?></span></td>
                    <td><span class="text-muted"><?= htmlspecialchars($n['author_name'] ?? '—') ?></span></td>
                    <td><?= statusBadge($n['status']) ?></td>
                    <td><span class="text-muted"><?= number_format($n['views']) ?></span></td>
                    <td><span class="text-muted"><?= formatDate($n['created_at'], 'd M Y') ?></span></td>
                    <td>
                        <div class="action-btns">
                            <a href="<?= BASE_URL ?>news/news-edit.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-secondary btn-icon" title="<?= __('btn_edit') ?>"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-newspaper"></i><h3><?= __('no_news') ?></h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart + recent comments -->
<div class="grid-2" style="margin-top:20px">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-chart-bar"></i> <?= __('daily_published') ?></span>
        </div>
        <div class="chart-wrap">
            <canvas id="newsChart" height="200"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-comments"></i> <?= __('recent_comments') ?></span>
            <a href="<?= BASE_URL ?>comments/comments.php" class="btn btn-secondary btn-sm"><?= __('nav_all_news') ?></a>
        </div>
        <div style="padding:4px 0">
        <?php if ($recent_comments): foreach ($recent_comments as $c): ?>
        <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;gap:12px;align-items:flex-start;">
            <div style="width:30px;height:30px;background:var(--bg-hover);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--text-muted);font-size:12px;font-weight:700;">
                <?= strtoupper(substr($c['name'], 0, 1)) ?>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;color:var(--text-primary);font-weight:500"><?= htmlspecialchars($c['name']) ?> <span style="font-weight:400;color:var(--text-muted);font-size:11px"><?= statusBadge($c['status']) ?></span></div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><?= mb_substr(htmlspecialchars($c['comment']), 0, 70) ?>…</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px"><?= timeAgo($c['created_at']) ?></div>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="empty-state" style="padding:30px"><i class="fas fa-comment"></i><h3><?= __('no_notifications') ?></h3></div>
        <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode(array_column($chart_data, 'date')) ?>;
const counts = <?= json_encode(array_column($chart_data, 'count')) ?>;
const ctx = document.getElementById('newsChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: '<?= __('stat_total_news') ?>',
                data: counts,
                backgroundColor: 'rgba(230,57,70,.6)',
                borderColor: '#e63946',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8b91b0' } },
                y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8b91b0', precision: 0 }, beginAtZero: true }
            }
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>