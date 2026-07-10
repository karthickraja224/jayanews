<?php
// media/media.php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/lang.php';

requireLogin();

$db = db();

$page_title = __('media_title');

/*
|--------------------------------------------------------------------------
| FOLDER
|--------------------------------------------------------------------------
*/

$folders = [
    'media',
    'news',
    'slider'
];

$folder = $_GET['folder'] ?? 'media';

if(!in_array($folder,$folders)){
    $folder='media';
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if(isset($_GET['delete'])){
    requireRole(['superadmin','admin']);
    
    $file = basename($_GET['file'] ?? '');
    
    if($file){
        $path = UPLOAD_PATH.$folder.'/'.$file;
        
        if(file_exists($path)){
            unlink($path);
        }
        
        $db->query(
            "DELETE FROM tbl_media WHERE filename=?",
            "s",
            $file
        );
    }
    
    flashSuccess(__('msg_file_deleted'));
    
    header(
        "Location: media.php?folder=".$folder
    );
    
    exit;
}

/*
|--------------------------------------------------------------------------
| UPLOAD
|--------------------------------------------------------------------------
*/

if(isset($_POST['upload'])){
    if(!isset($_FILES['files'])){
        flashError(__('err_file_select'));
        header("Location: media.php");
        exit;
    }

    $uploadDir = UPLOAD_PATH.$folder.'/';

    if(!is_dir($uploadDir)){
        mkdir($uploadDir,0777,true);
    }

    $count=0;

    foreach($_FILES['files']['name'] as $i=>$original){
        if(empty($original)){
            continue;
        }

        $error = $_FILES['files']['error'][$i];

        if($error !== UPLOAD_ERR_OK){
            die(
                "Upload failed. Error code: ".$error.
                "<br>".uploadError($error)
            );
        }

        $tmp = $_FILES['files']['tmp_name'][$i];
        $size = $_FILES['files']['size'][$i];

        if($size > MAX_FILE_SIZE){
            flashError(__('err_file_large'));
            continue;
        }

        $ext = strtolower(
            pathinfo(
                $original,
                PATHINFO_EXTENSION
            )
        );

        $imageExt = [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'gif'
        ];

        $videoExt = [
            'mp4',
            'mov',
            'webm',
            'avi',
            'mkv'
        ];

        if(in_array($ext,$videoExt)){
            $type="video";
        }
        elseif(in_array($ext,$imageExt)){
            $type="image";
        }
        else{
            continue;
        }

        $filename = uniqid("media_",true).".".$ext;
        $target = $uploadDir.$filename;

        if(move_uploaded_file($tmp,$target)){
            $filePath = "uploads/".$folder."/".$filename;

            $db->query(
                "INSERT INTO tbl_media
                (
                    filename,
                    original_name,
                    file_type,
                    file_size,
                    file_path,
                    uploaded_by,
                    created_at
                )
                VALUES
                (?,?,?,?,?,?,NOW())
                ",
                "sssisi",
                $filename,
                $original,
                $type,
                $size,
                $filePath,
                $_SESSION['user_id'] ?? 0
            );

            $count++;
        }
    }

    flashSuccess(
        $count." ".__('msg_file_deleted')
    );

    header(
        "Location: media.php?folder=".$folder
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| SCAN FILES
|--------------------------------------------------------------------------
*/
function uploadError($code){
    $errors = [
        UPLOAD_ERR_INI_SIZE =>
        "File exceeds php.ini upload_max_filesize",
        UPLOAD_ERR_FORM_SIZE =>
        "File exceeds form limit",
        UPLOAD_ERR_PARTIAL =>
        "File partially uploaded",
        UPLOAD_ERR_NO_FILE =>
        "No file received",
        UPLOAD_ERR_NO_TMP_DIR =>
        "Missing temp folder",
        UPLOAD_ERR_CANT_WRITE =>
        "Cannot write file",
        UPLOAD_ERR_EXTENSION =>
        "Blocked by extension"
    ];
    return $errors[$code] ?? "Unknown upload error";
}

function scanUploads($folder){
    $dir = UPLOAD_PATH.$folder.'/';
    $files=[];

    if(!is_dir($dir)){
        return [];
    }

    foreach(glob($dir."*") as $file){
        if(!is_file($file)){
            continue;
        }

        $ext = strtolower(
            pathinfo(
                $file,
                PATHINFO_EXTENSION
            )
        );

        $allowed=[
            'jpg',
            'jpeg',
            'png',
            'webp',
            'gif',
            'mp4',
            'mov',
            'webm',
            'avi',
            'mkv'
        ];

        if(!in_array($ext,$allowed)){
            continue;
        }

        $files[]=[
            "name"=>basename($file),
            "url"=>
            BASE_URL.
            "uploads/".$folder."/".basename($file),
            "size"=>filesize($file),
            "type"=>
            in_array(
                $ext,
                [
                    'mp4',
                    'mov',
                    'webm',
                    'avi',
                    'mkv'
                ]
            )
            ?
            "video"
            :
            "image"
        ];
    }

    return $files;
}

$files = scanUploads($folder);

include '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-photo-video"></i> <?= __('media_title') ?></h1>
        <p><?= count($files) ?> <?= __('media_title') ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <input 
                    type="file"
                    name="files[]"
                    multiple
                    required
                    accept="image/*,video/*"
                    class="form-control">
            </div>
            <br>
            <button 
                type="submit"
                name="upload"
                class="btn btn-primary">
                <i class="fas fa-upload"></i>
                <?= __('btn_save') ?>
            </button>
        </form>
    </div>
</div>

<div style="margin:15px;display:flex;gap:10px;flex-wrap:wrap">
    <?php foreach($folders as $f): ?>
    <a 
        href="media.php?folder=<?=$f?>"
        class="btn <?= $folder === $f ? 'btn-primary' : 'btn-secondary' ?>">
        <?= ucfirst($f) ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-body">
        <div style="
            display:grid;
            grid-template-columns:
            repeat(auto-fill,minmax(200px,1fr));
            gap:15px;">
            
            <?php if(empty($files)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:40px;">
                <i class="fas fa-folder-open" style="font-size:48px;color:var(--text-muted);"></i>
                <h3 style="color:var(--text-muted);margin-top:15px;"><?= __('no_data') ?></h3>
                <p style="color:var(--text-muted);"><?= __('err_file_select') ?></p>
            </div>
            <?php endif; ?>

            <?php foreach($files as $f): ?>
            <div class="card" style="overflow:hidden">
                <?php if($f['type']=="image"): ?>
                <img
                    src="<?=$f['url']?>"
                    style="
                    width:100%;
                    height:150px;
                    object-fit:cover;
                    border-bottom:1px solid var(--border);">
                <?php else: ?>
                <video
                    controls
                    src="<?=$f['url']?>"
                    style="
                    width:100%;
                    height:150px;
                    object-fit:cover;
                    border-bottom:1px solid var(--border);">
                </video>
                <?php endif; ?>

                <div style="padding:10px">
                    <small style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?=htmlspecialchars($f['name'])?>
                    </small>
                    <br>
                    <small style="color:var(--text-muted);">
                        <?=round($f['size']/1024)?> KB
                    </small>
                    <br><br>
                    <a
                        href="media.php?folder=<?=$folder?>&delete=1&file=<?=urlencode($f['name'])?>"
                        onclick="return confirm('<?= __('confirm_delete') ?>')"
                        class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> <?= __('btn_delete') ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>