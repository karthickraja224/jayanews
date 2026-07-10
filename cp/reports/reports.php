<?php
// reports/reports.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title  = __('reports_title');
$breadcrumbs = [__('reports_title') => null];

// Date range (default: last 30 days)
$date_to   = $_GET['to']   ?? date('Y-m-d');
$date_from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
// keep range sane
if (strtotime($date_from) > strtotime($date_to)) { $tmp = $date_from; $date_from = $date_to; $date_to = $tmp; }
$range_end_inclusive = date('Y-m-d', strtotime($date_to . ' +1 day'));

// ---------- CSV export of top news in range ----------
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = db()->fetchAll(
        "SELECT n.title, c.name_english category, n.views, n.status, n.created_at
         FROM tbl_news n LEFT JOIN tbl_categories c ON c.id = n.category_id
         WHERE n.created_at >= ? AND n.created_at < ? AND n.status != 'trash'
         ORDER BY n.views DESC",
        'ss', $date_from, $range_end_inclusive
    );
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="jaya-plus-report-' . $date_from . '-to-' . $date_to . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, [__('label_title'), __('col_category'), __('col_views'), __('col_status'), __('col_date')]);
    foreach ($rows as $r) {
        fputcsv($out, [$r['title'], $r['category'] ?? '—', $r['views'], $r['status'], $r['created_at']]);
    }
    fclose($out);
    exit();
}

// ---------- Stats within range ----------
$news_in_range = db()->fetchOne(
    "SELECT COUNT(*) c FROM tbl_news WHERE created_at >= ? AND created_at < ? AND status != 'trash'",
    'ss', $date_from, $range_end_inclusive
)['c'];

$published_in_range = db()->fetchOne(
    "SELECT COUNT(*) c FROM tbl_news WHERE status='published' AND published_at >= ? AND published_at < ?",
    'ss', $date_from, $range_end_inclusive
)['c'];

$views_in_range = db()->fetchOne(
    "SELECT COALESCE(SUM(views),0) c FROM tbl_news WHERE created_at >= ? AND created_at < ? AND status != 'trash'",
    'ss', $date_from, $range_end_inclusive
)['c'];

$comments_in_range = db()->fetchOne(
    "SELECT COUNT(*) c FROM tbl_comments WHERE created_at >= ? AND created_at < ?",
    'ss', $date_from, $range_end_inclusive
)['c'];

$avg_views = $news_in_range > 0 ? round($views_in_range / $news_in_range, 1) : 0;

// ---------- Top categories ----------
$top_categories = db()->fetchAll(
    "SELECT c.name_english, c.name_tamil, COUNT(n.id) cnt, COALESCE(SUM(n.views),0) total_views
     FROM tbl_categories c
     LEFT JOIN tbl_news n ON n.category_id = c.id AND n.created_at >= ? AND n.created_at < ? AND n.status != 'trash'
     GROUP BY c.id ORDER BY cnt DESC LIMIT 8",
    'ss', $date_from, $range_end_inclusive
);

// ---------- Top viewed news ----------
$top_news = db()->fetchAll(
    "SELECT n.id, n.title, n.views, n.status, c.name_english cat_name
     FROM tbl_news n LEFT JOIN tbl_categories c ON c.id = n.category_id
     WHERE n.created_at >= ? AND n.created_at < ? AND n.status != 'trash'
     ORDER BY n.views DESC LIMIT 10",
    'ss', $date_from, $range_end_inclusive
);

// ---------- Comments breakdown ----------
$comment_breakdown = db()->fetchAll(
    "SELECT status, COUNT(*) c FROM tbl_comments WHERE created_at >= ? AND created_at < ? GROUP BY status",
    'ss', $date_from, $range_end_inclusive
);
$cb = ['pending' => 0, 'approved' => 0, 'spam' => 0];
foreach ($comment_breakdown as $row) { $cb[$row['status']] = (int)$row['c']; }

// ---------- Daily news chart ----------
$days = max(1, min(60, (strtotime($date_to) - strtotime($date_from)) / 86400 + 1));
$chart_data = [];
for ($i = $days - 1; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime($date_to . " -$i days"));
    $row = db()->fetchOne("SELECT COUNT(*) c FROM tbl_news WHERE DATE(created_at) = ?", 's', $day);
    $chart_data[] = ['date' => date('d M', strtotime($day)), 'count' => (int)$row['c']];
}

// ---------- Recent activity ----------
$activity = db()->fetchAll(
    "SELECT a.*, u.name user_name FROM tbl_activity_log a
     LEFT JOIN tbl_users u ON u.id = a.user_id
     ORDER BY a.created_at DESC LIMIT 15"
);

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-chart-bar"></i> <?= __('reports_title') ?></h1>
        <p><?= __('period_label') ?> <?= formatDate($date_from, 'd M Y') ?> — <?= formatDate($date_to, 'd M Y') ?></p>
    </div>
    <div class="page-header-right">
        <a href="reports.php?from=<?= $date_from ?>&to=<?= $date_to ?>&export=csv" class="btn btn-secondary"><i class="fas fa-file-csv"></i> <?= __('btn_export') ?></a>
        <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> <?= __('btn_print') ?></button>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex:1;flex-wrap:wrap;align-items:center">
            <label class="text-muted" style="font-size:13px"><?= __('label_from_date') ?></label>
            <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($date_from) ?>" style="max-width:170px">
            <label class="text-muted" style="font-size:13px"><?= __('label_to_date') ?></label>
            <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($date_to) ?>" style="max-width:170px">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> <?= __('btn_filter') ?></button>
            <a href="reports.php" class="btn btn-secondary"><i class="fas fa-undo"></i> <?= __('btn_last30') ?></a>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card" style="--stat-color:#e63946;--stat-bg:rgba(230,57,70,.12)">
        <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($news_in_range) ?></div>
            <div class="stat-label"><?= __('stat_period_news') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#2ecc71;--stat-bg:rgba(46,204,113,.12)">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($published_in_range) ?></div>
            <div class="stat-label"><?= __('stat_period_published') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#3a86ff;--stat-bg:rgba(58,134,255,.12)">
        <div class="stat-icon"><i class="fas fa-eye"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($views_in_range) ?></div>
            <div class="stat-label"><?= __('stat_period_views') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#f4c542;--stat-bg:rgba(244,197,66,.12)">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $avg_views ?></div>
            <div class="stat-label"><?= __('stat_avg_views') ?></div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#2ec4b6;--stat-bg:rgba(46,196,182,.12)">
        <div class="stat-icon"><i class="fas fa-comments"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($comments_in_range) ?></div>
            <div class="stat-label"><?= __('stat_period_comments') ?></div>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px;margin-top:20px">
    <!-- Chart -->
    <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-chart-bar"></i> <?= __('card_daily_news') ?></span></div>
        <div class="chart-wrap"><canvas id="reportChart" height="220"></canvas></div>
    </div>

    <!-- Comments breakdown -->
    <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-comments"></i> <?= __('card_comments_status') ?></span></div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:14px">
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                        <span><i class="fas fa-clock" style="color:var(--warning)"></i> <?= __('label_pending') ?></span><strong><?= $cb['pending'] ?></strong>
                    </div>
                    <div style="height:8px;background:var(--bg-hover);border-radius:4px;overflow:hidden"><div style="height:100%;background:var(--warning);width:<?= $comments_in_range ? round($cb['pending']/$comments_in_range*100) : 0 ?>%"></div></div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                        <span><i class="fas fa-check-circle" style="color:var(--success)"></i> <?= __('label_approved') ?></span><strong><?= $cb['approved'] ?></strong>
                    </div>
                    <div style="height:8px;background:var(--bg-hover);border-radius:4px;overflow:hidden"><div style="height:100%;background:var(--success);width:<?= $comments_in_range ? round($cb['approved']/$comments_in_range*100) : 0 ?>%"></div></div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                        <span><i class="fas fa-ban" style="color:var(--danger)"></i> <?= __('label_spam') ?></span><strong><?= $cb['spam'] ?></strong>
                    </div>
                    <div style="height:8px;background:var(--bg-hover);border-radius:4px;overflow:hidden"><div style="height:100%;background:var(--danger);width:<?= $comments_in_range ? round($cb['spam']/$comments_in_range*100) : 0 ?>%"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px;margin-top:20px">
    <!-- Top categories -->
    <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-tags"></i> <?= __('card_top_cats') ?></span></div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th><?= __('col_cat2') ?></th><th><?= __('stat_total_news') ?></th><th><?= __('stat_views') ?></th></tr></thead>
                <tbody>
                <?php if ($top_categories): foreach ($top_categories as $tc): ?>
                <tr>
                    <td><?= htmlspecialchars($tc['name_english']) ?> <span class="text-muted">/ <?= htmlspecialchars($tc['name_tamil'] ?? '') ?></span></td>
                    <td><span class="badge badge-secondary"><?= $tc['cnt'] ?></span></td>
                    <td><span class="text-muted"><?= number_format($tc['total_views']) ?></span></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="3"><div class="empty-state" style="padding:24px"><i class="fas fa-tags"></i><h3><?= __('no_data') ?></h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top news -->
    <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-fire"></i> <?= __('card_top_news') ?></span></div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th><?= __('col_news') ?></th><th><?= __('col_cat2') ?></th><th><?= __('col_views') ?></th></tr></thead>
                <tbody>
                <?php if ($top_news): foreach ($top_news as $n): ?>
                <tr>
                    <td>
                        <a href="<?= BASE_URL ?>news/news-edit.php?id=<?= $n['id'] ?>"><?= mb_substr(htmlspecialchars($n['title']), 0, 45) ?><?= mb_strlen($n['title']) > 45 ? '…' : '' ?></a>
                    </td>
                    <td><span class="text-muted"><?= htmlspecialchars($n['cat_name'] ?? '—') ?></span></td>
                    <td><strong><?= number_format($n['views']) ?></strong></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="3"><div class="empty-state" style="padding:24px"><i class="fas fa-newspaper"></i><h3><?= __('no_data') ?></h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent activity -->
<div class="card" style="margin-top:20px">
    <div class="card-header"><span class="card-title"><i class="fas fa-history"></i> <?= __('recent_comments') ?></span></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th><?= __('col_author') ?></th><th><?= __('col_actions') ?></th><th><?= __('col_category') ?></th><th><?= __('label_content') ?></th><th><?= __('col_date') ?></th></tr></thead>
            <tbody>
            <?php if ($activity): foreach ($activity as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['user_name'] ?? 'System') ?></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($a['action']) ?></span></td>
                <td><span class="text-muted"><?= htmlspecialchars($a['module']) ?></span></td>
                <td><span class="text-muted"><?= htmlspecialchars($a['description'] ?? '') ?></span></td>
                <td><span class="text-muted"><?= timeAgo($a['created_at']) ?></span></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5"><div class="empty-state" style="padding:24px"><i class="fas fa-history"></i><h3><?= __('no_data') ?></h3></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode(array_column($chart_data, 'date')) ?>;
const counts = <?= json_encode(array_column($chart_data, 'count')) ?>;
const ctx = document.getElementById('reportChart');
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
                x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8b91b0', maxRotation: 0, autoSkip: true } },
                y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8b91b0', precision: 0 }, beginAtZero: true }
            }
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>