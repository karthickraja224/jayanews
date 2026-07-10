<?php
// news/news-edit.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$id   = (int)($_GET['id'] ?? 0);
$news = db()->fetchOne("SELECT * FROM tbl_news WHERE id = ?", 'i', $id);
if (!$news) { flashError(__('msg_news_not_found')); header('Location: ' . BASE_URL . 'news/news-list.php'); exit(); }

$page_title = __('edit_news_title');
$breadcrumbs = [__('nav_news') => BASE_URL . 'news/news-list.php', __('edit_news_title') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $slug         = trim($_POST['slug'] ?? '');
    $summary      = trim($_POST['summary'] ?? '');
    $content      = $_POST['content'] ?? '';
    $category_id  = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $tags         = trim($_POST['tags'] ?? '');
    $source       = trim($_POST['source'] ?? '');
    $status       = in_array($_POST['status'] ?? '', ['draft', 'published', 'trash']) ? $_POST['status'] : 'draft';
    $is_breaking  = isset($_POST['is_breaking']) ? 1 : 0;
    $is_featured  = isset($_POST['is_featured']) ? 1 : 0;
    $is_slider    = isset($_POST['is_slider']) ? 1 : 0;
    $meta_title   = trim($_POST['meta_title'] ?? '');
    $meta_desc    = trim($_POST['meta_description'] ?? '');
    $meta_kw      = trim($_POST['meta_keywords'] ?? '');
    $img_caption  = trim($_POST['image_caption'] ?? '');

    if (empty($title)) { flashError(__('err_title_req2')); }
    else {
        if (empty($slug)) $slug = slugify($title);
        $slug = uniqueSlug('tbl_news', $slug, $id);

        // Image upload
        $featured_image = $news['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = uploadImage($_FILES['featured_image'], 'news');
            if (!isset($upload['error'])) {
                if ($featured_image) deleteFile($featured_image);
                $featured_image = $upload;
            }
        }
        if (isset($_POST['remove_image']) && $_POST['remove_image']) {
            if ($featured_image) deleteFile($featured_image);
            $featured_image = null;
        }

        $published_at = $news['published_at'];
        if ($status === 'published' && !$published_at) $published_at = date('Y-m-d H:i:s');

        // CORRECTED: 20 placeholders (19 SET fields + 1 WHERE id)
        // Fields: title(s), slug(s), summary(s), content(s), 
        // category_id(i), subcategory_id(i), 
        // featured_image(s), image_caption(s), tags(s), source(s), 
        // is_breaking(i), is_featured(i), is_slider(i), 
        // status(s), meta_title(s), meta_description(s), meta_keywords(s), published_at(s), 
        // WHERE id(i)
        
       db()->query(
    "UPDATE tbl_news SET 
    title=?,
    slug=?,
    summary=?,
    content=?,
    category_id=?,
    subcategory_id=?,
    featured_image=?,
    image_caption=?,
    tags=?,
    source=?,
    is_breaking=?,
    is_featured=?,
    is_slider=?,
    status=?,
    meta_title=?,
    meta_description=?,
    meta_keywords=?,
    published_at=?
    WHERE id=?",
    'ssssiissssiiisssssi',// 20 chars: ssss + ii + ssss + iiii + ssss + i
            $title, $slug, $summary, $content,
            $category_id ?: null, $subcategory_id ?: null,
            $featured_image, $img_caption, $tags, $source,
            $is_breaking, $is_featured, $is_slider,
            $status, $meta_title, $meta_desc, $meta_kw, $published_at, $id
        );
       logActivity('update', 'news', __('msg_news_updated') . ": $title");

flashSuccess(__('msg_news_updated'));

header('Location: ' . BASE_URL . 'news/news-list.php');
exit();
    }
}

$categories    = getCategories();
$subcategories = db()->fetchAll("SELECT * FROM tbl_subcategories WHERE status = 1 ORDER BY name_english");
include '../includes/header.php';
$n = $news;
?>
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-edit"></i> <?= __('edit_news_title') ?></h1>
        <p>#<?= $n['id'] ?> — <?= htmlspecialchars(mb_substr($n['title'], 0, 60)) ?></p>
    </div>
    <div class="page-header-right">
        <a href="<?= BASE_URL ?>news/news-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a>
        <?php if ($n['status'] === 'published'): ?>
        <a href="<?= getSetting('site_url') ?>/<?= $n['slug'] ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-external-link-alt"></i> <?= __('view_site') ?></a>
        <?php endif; ?>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">
    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><span class="card-title"><i class="fas fa-pen"></i> <?= __('card_news_details') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_title') ?> <span class="required">*</span></label>
                    <input type="text" id="news_title" name="title" class="form-control" value="<?= htmlspecialchars($n['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" id="news_slug" name="slug" class="form-control" data-manual="1" value="<?= htmlspecialchars($n['slug']) ?>">
                    <div class="form-hint"><?= __('hint_slug') ?></div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_summary') ?></label>
                    <textarea name="summary" class="form-control" rows="3"><?= htmlspecialchars($n['summary']) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_content') ?></label>
                    <textarea name="content" id="newsContent" class="form-control" rows="15"><?= htmlspecialchars($n['content']) ?></textarea>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-search"></i> <?= __('card_seo') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_title') ?></label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($n['meta_title']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_desc') ?></label>
                    <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($n['meta_description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_kw') ?></label>
                    <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($n['meta_keywords']) ?>">
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-paper-plane"></i> <?= __('card_publish') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_status') ?></label>
                    <select name="status" class="form-control">
                        <option value="trash" <?= $n['status'] === 'trash' ? 'selected' : '' ?>>
Trash
</option>
                        <option value="draft"     <?= $n['status'] === 'draft'     ? 'selected' : '' ?>><?= __('opt_draft') ?></option>
                        <option value="published" <?= $n['status'] === 'published' ? 'selected' : '' ?>><?= __('opt_published') ?></option>
                    </select>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px">
                    <label class="form-check"><input type="checkbox" name="is_breaking" <?= $n['is_breaking'] ? 'checked' : '' ?>><span class="form-check-label"><i class="fas fa-bolt" style="color:var(--accent)"></i> <?= __('chk_breaking') ?></span></label>
                    <label class="form-check"><input type="checkbox" name="is_featured" <?= $n['is_featured'] ? 'checked' : '' ?>><span class="form-check-label"><i class="fas fa-star" style="color:var(--gold)"></i> <?= __('chk_featured') ?></span></label>
                    <label class="form-check"><input type="checkbox" name="is_slider"   <?= $n['is_slider']   ? 'checked' : '' ?>><span class="form-check-label"><i class="fas fa-images" style="color:var(--teal)"></i> <?= __('chk_slider') ?></span></label>
                </div>
              
                <div style="margin-top:10px;font-size:11px;color:var(--text-muted);text-align:center">
                    <i class="fas fa-clock"></i> <?= formatDate($n['created_at']) ?>
                    <?php if ($n['published_at']): ?>
                    <br><i class="fas fa-check"></i> <?= __('published_on') ?> <?= formatDate($n['published_at']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-tag"></i> <?= __('card_category') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_category') ?></label>
                    <select name="category_id" id="categorySelect" class="form-control">
                        <option value=""><?= __('select_category') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $n['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name_english']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_subcategory') ?></label>
                    <select name="subcategory_id" id="subcategorySelect" class="form-control">
                        <option value=""><?= __('select_subcategory') ?></option>
                        <?php foreach ($subcategories as $sub): ?>
                        <option value="<?= $sub['id'] ?>" data-cat="<?= $sub['category_id'] ?>" <?= $n['subcategory_id'] == $sub['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sub['name_english']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_tags') ?></label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($n['tags']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_source') ?></label>
                    <input type="text" name="source" class="form-control" value="<?= htmlspecialchars($n['source']) ?>">
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-image"></i> <?= __('card_image') ?></span></div>
            <div class="card-body">
                <?php if ($n['featured_image']): ?>
                <div class="img-preview-wrap" id="imgPreviewWrap" style="display:inline-block;margin-bottom:10px">
                    <img src="<?= BASE_URL . htmlspecialchars($n['featured_image']) ?>" class="img-preview" id="imgPreview">
                    <button type="button" class="img-preview-remove" onclick="removeImage()">×</button>
                </div>
                <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                <?php else: ?>
                <div id="imgPreviewWrap" style="display:none" class="img-preview-wrap">
                    <img id="imgPreview" class="img-preview" src="#">
                    <button type="button" class="img-preview-remove" onclick="removeImage()">×</button>
                </div>
                <?php endif; ?>
                <div class="upload-zone" id="uploadZone" style="<?= $n['featured_image'] ? 'display:none' : '' ?>" onclick="document.getElementById('featured_image').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><span><?= __('img_replace') ?></span></p>
                </div>
                <input type="file" id="featured_image" name="featured_image" accept="image/*" style="display:none">
                <div class="form-group" style="margin-top:10px">
                    <label class="form-label"><?= __('label_image_caption') ?></label>
                    <input type="text" name="image_caption" class="form-control" value="<?= htmlspecialchars($n['image_caption']) ?>">
                </div>
            </div>
        </div>
    </div>
    <div style="margin-top:16px; display:flex; justify-content:center; margin-left:360px;">
    <button 
        type="submit" 
        class="btn btn-primary"
        style="display:flex; align-items:center; justify-content:center; gap:8px; width:50% ;">
        <i class="fas fa-save"></i> <?= __('btn_save') ?>
    </button>
</div>
</div>
</form>

<script src="https://cdn.tiny.cloud/1/fdnmpjrl01lldr548jirvuqkhtz7gpqbf5yt03jm7334xeno/tinymce/6/tinymce.min.js"></script>
<script>
tinymce.init({ selector: '#newsContent', plugins: 'lists link image table code fullscreen media',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | code fullscreen',
    skin: 'oxide-dark', content_css: 'dark', height: 450, menubar: false,
    content_style: 'body{font-family:Inter,Noto Sans Tamil,sans-serif;font-size:14px;color:#eaedf5;background:#0d0f1a;padding:12px}' });
document.getElementById('categorySelect')?.addEventListener('change', function() {
    const catId = this.value;
    Array.from(document.getElementById('subcategorySelect').options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!catId || opt.dataset.cat === catId) ? '' : 'none';
    });
});
document.getElementById('featured_image')?.addEventListener('change', function() {
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreviewWrap').style.display = 'inline-block';
            document.getElementById('uploadZone').style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    }
});
function removeImage() {
    document.getElementById('featured_image').value = '';
    const flag = document.getElementById('removeImageFlag');
    if (flag) flag.value = '1';
    document.getElementById('imgPreviewWrap').style.display = 'none';
    document.getElementById('uploadZone').style.display = 'block';
}
</script>
<?php include '../includes/footer.php'; ?>