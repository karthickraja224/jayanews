<?php
// rss-feed/cronJob/Update.php
// Public endpoint (no login) hit by an external cron job, e.g. every 15 minutes:
//   https://jayanewslive.com/rss-feed/cronJob/Update.php
// It reads every active feed in tbl_rss_feeds, parses the XML, and imports any
// article whose link/guid is not already stored (dedup via tbl_news.source_link).

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

function rss_fetch($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'JayaPlusRSSBot/1.0',
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($body === false) throw new Exception("cURL error: $err");
        return $body;
    }
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'header' => "User-Agent: JayaPlusRSSBot/1.0\r\n"]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) throw new Exception('file_get_contents failed');
    return $body;
}

function rss_clean_text($html) {
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $text));
}

$results = [];
$feeds = db()->fetchAll("SELECT * FROM tbl_rss_feeds WHERE auto_update = 1");

if (!$feeds) {
    echo json_encode(['status' => 'ok', 'message' => 'No active feeds.', 'feeds' => []]);
    exit;
}

// ensure dedup column exists (safe to ignore if it already does)
@db()->query("ALTER TABLE tbl_news ADD COLUMN source_link VARCHAR(500) NULL");
@db()->query("ALTER TABLE tbl_news ADD UNIQUE KEY uq_source_link (source_link)");

foreach ($feeds as $feed) {
    $imported = 0;
    $status   = 'success';
    $error    = null;

    try {
        $raw = rss_fetch($feed['feed_url']);
        $raw = preg_replace('/^[^<]+/', '', $raw); // strip stray BOM/whitespace before <?xml
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($raw);
        if ($xml === false) throw new Exception('Invalid XML / could not parse feed.');

        $items = $xml->channel->item ?? $xml->entry ?? [];

        foreach ($items as $item) {
            $title = rss_clean_text((string)$item->title);
            $link  = trim((string)$item->link);
            if (empty($title) || empty($link)) continue;

            // dedup check
            $exists = db()->fetchOne("SELECT id FROM tbl_news WHERE source_link = ?", 's', $link);
            if ($exists) continue;

            $description = (string)($item->description ?? '');
            $summary = mb_substr(rss_clean_text($description), 0, 300, 'UTF-8');
            $content = $description ?: $title;

            $pubDateRaw = (string)($item->pubDate ?? $item->published ?? '');
            $pubDate    = $pubDateRaw ? date('Y-m-d H:i:s', strtotime($pubDateRaw)) : date('Y-m-d H:i:s');

            $slug = slugify($title);
            $slug = uniqueSlug('tbl_news', $slug);

            db()->insert(
                "INSERT INTO tbl_news (title, slug, summary, content, category_id, author_id,
                 source, status, source_link, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?)",
                'ssssiissss', // s,s,s,s,i,i,s,s,s,s -> title,slug,summary,content,category_id,author_id,source,status,source_link,created_at
                $title, $slug, $summary, $content,
                $feed['category_id'], 1,
                $feed['feed_name'], 'draft', $link, $pubDate
            );

            $imported++;
        }
    } catch (Exception $e) {
        $status = 'failed';
        $error  = $e->getMessage();
    }

    db()->query(
        "UPDATE tbl_rss_feeds SET last_run_at = NOW(), last_status = ?, items_imported = ? WHERE id = ?",
        'sii', $status, $imported, $feed['id']
    );

    $results[] = [
        'feed'     => $feed['feed_name'],
        'status'   => $status,
        'imported' => $imported,
        'error'    => $error,
    ];
}

echo json_encode(['status' => 'ok', 'feeds' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
