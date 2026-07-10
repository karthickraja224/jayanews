<?php
// news/news-add.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';
requireLogin();

$page_title = __('add_news_title');
$breadcrumbs = [__('nav_news') => BASE_URL . 'news/news-list.php', __('add_news_title') => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $slug         = trim($_POST['slug'] ?? '');
    $summary      = trim($_POST['summary'] ?? '');
    $content      = $_POST['content'] ?? '';
    $category_id  = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $tags         = trim($_POST['tags'] ?? '');
    $source       = trim($_POST['source'] ?? '');
    $status       = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';
    $is_breaking  = isset($_POST['is_breaking']) ? 1 : 0;
    $is_featured  = isset($_POST['is_featured']) ? 1 : 0;
    $is_slider    = isset($_POST['is_slider']) ? 1 : 0;
    $meta_title   = trim($_POST['meta_title'] ?? '');
    $meta_desc    = trim($_POST['meta_description'] ?? '');
    $meta_kw      = trim($_POST['meta_keywords'] ?? '');
    $img_caption  = trim($_POST['image_caption'] ?? '');

    if (empty($title)) {
        flashError(__('err_title_required'));
    } else {
        if (empty($slug)) $slug = slugify($title);
        $slug = uniqueSlug('tbl_news', $slug);

        // Image upload
        $featured_image = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = uploadImage($_FILES['featured_image'], 'news');
            if (isset($upload['error'])) { flashError($upload['error']); goto form_end; }
            $featured_image = $upload;
        }

        $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
        $author_id    = $_SESSION['admin_id'];

        // CORRECTED: 19 placeholders, 19 type characters
        // Fields: title(s), slug(s), summary(s), content(s), 
        // category_id(i), subcategory_id(i), author_id(i), 
        // featured_image(s), image_caption(s), tags(s), source(s), 
        // is_breaking(i), is_featured(i), is_slider(i), 
        // status(s), meta_title(s), meta_description(s), meta_keywords(s), published_at(s)
        $id = db()->insert(
            "INSERT INTO tbl_news (title, slug, summary, content, category_id, subcategory_id, author_id,
             featured_image, image_caption, tags, source, is_breaking, is_featured, is_slider,
             status, meta_title, meta_description, meta_keywords, published_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            'ssssiiissssiiisssss',  // 19 chars: ssss + iiii + sss + iiii + ssss
            $title, $slug, $summary, $content,
            $category_id ?: null, $subcategory_id ?: null, $author_id,
            $featured_image, $img_caption, $tags, $source,
            $is_breaking, $is_featured, $is_slider,
            $status, $meta_title, $meta_desc, $meta_kw, $published_at
        );

        logActivity('create', 'news', __('msg_news_added') . ": $title");
        flashSuccess(__('msg_news_added'));
        // header('Location: ' . BASE_URL . 'news/news-edit.php?id=' . $id);
        header('Location: ' . BASE_URL . 'news/news-list.php');
        exit();
    }
}
form_end:
$categories   = getCategories();
$subcategories = db()->fetchAll("SELECT * FROM tbl_subcategories WHERE status = 1 ORDER BY name_english");

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-plus-circle"></i> <?= __('add_news_title') ?></h1>
    </div>
    <div class="page-header-right">
        <a href="<?= BASE_URL ?>news/news-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= __('btn_back') ?></a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" id="newsForm">
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

    <!-- Left: main content -->
    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><span class="card-title"><i class="fas fa-pen"></i> <?= __('card_news_details') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_title') ?> <span class="required">*</span></label>
                    <input type="text" id="news_title" name="title" class="form-control" placeholder="<?= __('label_title') ?>..." value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug (URL)</label>
                    <input type="text" id="news_slug" name="slug" class="form-control" placeholder="news-slug-here" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" readonly>
                    <div class="form-hint"><?= __('hint_slug') ?></div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_summary') ?></label>
                    <textarea name="summary" class="form-control" rows="3" placeholder="<?= __('label_summary') ?>..."><?= htmlspecialchars($_POST['summary'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_content') ?> <span class="required">*</span></label>
                    <textarea name="content" id="newsContent" class="form-control" rows="15"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- SEO -->
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-search"></i> <?= __('card_seo') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_title') ?></label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>" placeholder="<?= __('label_meta_title') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_desc') ?></label>
                    <textarea name="meta_description" class="form-control" rows="2" placeholder="<?= __('label_meta_desc') ?> (150-160 characters)"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_meta_kw') ?></label>
                    <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($_POST['meta_keywords'] ?? '') ?>" placeholder="keyword1, keyword2, keyword3">
                </div>
            </div>
        </div>
    </div>

    <!-- Right: sidebar options -->
    <div style="display:flex;flex-direction:column;gap:16px">

        <!-- Publish box -->
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-paper-plane"></i> <?= __('card_publish') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_status') ?></label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= __('opt_draft') ?></option>
                      
                        <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>><?= __('btn_publish') ?></option>
                    </select>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
                    <label class="form-check">
                        <input type="checkbox" name="is_breaking" <?= !empty($_POST['is_breaking']) ? 'checked' : '' ?>>
                        <span class="form-check-label"><i class="fas fa-bolt" style="color:var(--accent)"></i> <?= __('chk_breaking') ?></span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_featured" <?= !empty($_POST['is_featured']) ? 'checked' : '' ?>>
                        <span class="form-check-label"><i class="fas fa-star" style="color:var(--gold)"></i> <?= __('chk_featured') ?></span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_slider" <?= !empty($_POST['is_slider']) ? 'checked' : '' ?>>
                        <span class="form-check-label"><i class="fas fa-images" style="color:var(--teal)"></i> <?= __('chk_slider') ?></span>
                    </label>
                </div>
               
            </div>
        </div>

        <!-- Category -->
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-tag"></i> <?= __('card_category') ?></span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label"><?= __('label_category') ?></label>
                    <select name="category_id" id="categorySelect" class="form-control select2">
                        <option value=""><?= __('select_category') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name_english']) ?> / <?= htmlspecialchars($cat['name_tamil']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_subcategory') ?></label>
                    <select name="subcategory_id" id="subcategorySelect" class="form-control">
                        <option value=""><?= __('select_subcategory') ?></option>
                        <?php foreach ($subcategories as $sub): ?>
                        <option value="<?= $sub['id'] ?>" data-cat="<?= $sub['category_id'] ?>" <?= ($_POST['subcategory_id'] ?? '') == $sub['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sub['name_english']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_tags') ?></label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>" placeholder="tag1, tag2, tag3">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('label_source') ?></label>
                    <input type="text" name="source" class="form-control" value="<?= htmlspecialchars($_POST['source'] ?? '') ?>" placeholder="<?= __('label_source') ?>">
                </div>
            </div>
        </div>

        <!-- Featured image -->
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-image"></i> <?= __('card_image') ?></span></div>
            <div class="card-body">
                <div id="imgPreviewWrap" style="display:none" class="img-preview-wrap">
                    <img id="imgPreview" class="img-preview" src="#" alt="Preview">
                    <button type="button" class="img-preview-remove" onclick="removeImage()">×</button>
                </div>
                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('featured_image').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><span><?= __('img_select') ?></span> <?= __('img_or_drop') ?></p>
                    <p style="font-size:11px;color:var(--text-muted);margin-top:4px"><?= __('img_types') ?></p>
                </div>
                <input type="file" id="featured_image" name="featured_image" accept="image/*" class="img-input" data-preview="imgPreview" style="display:none">
                <div class="form-group" style="margin-top:10px">
                    <label class="form-label"><?= __('label_image_caption') ?></label>
                    <input type="text" name="image_caption" class="form-control" value="<?= htmlspecialchars($_POST['image_caption'] ?? '') ?>" placeholder="<?= __('label_image_caption') ?>...">
                </div>
            </div>
        </div>

    </div>
</div>
  <div style="margin-top:16px; display:flex; justify-content:center; margin-left:100px;">
                    <button type="submit" name="status" value="draft" class="btn btn-secondary" style="justify-content:center; "><?= __('btn_save_draft') ?></button>
                    <button type="submit" name="status" value="published" class="btn btn-primary" style="justify-content:center;margin-left:30px;"><?= __('btn_publish') ?></button>
                </div>
</form>

<script src="https://cdn.tiny.cloud/1/fdnmpjrl01lldr548jirvuqkhtz7gpqbf5yt03jm7334xeno/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#newsContent',
    plugins: 'lists link image table code fullscreen media',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | table | code fullscreen',
    skin: 'oxide-dark', content_css: 'dark',
    height: 450,
    menubar: false,
    body_class: 'content-body',
    content_style: 'body { font-family: Inter, Noto Sans Tamil, sans-serif; font-size: 14px; color: #eaedf5; background: #0d0f1a; padding: 12px; }'
});

// Dynamic subcategory filter
document.getElementById('categorySelect')?.addEventListener('change', function() {
    const catId = this.value;
    const subSelect = document.getElementById('subcategorySelect');
    Array.from(subSelect.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!catId || opt.dataset.cat === catId) ? '' : 'none';
    });
    subSelect.value = '';
});

// Image preview
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
    document.getElementById('imgPreviewWrap').style.display = 'none';
    document.getElementById('uploadZone').style.display = 'block';
}

// Drag-drop upload zone
const zone = document.getElementById('uploadZone');
zone?.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone?.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone?.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('featured_image');
        const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include '../includes/footer.php'; ?>