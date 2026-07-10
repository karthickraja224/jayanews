<?php

echo "<pre>";
echo "upload_max_filesize = ".ini_get('upload_max_filesize')."\n";
echo "post_max_size = ".ini_get('post_max_size')."\n";
echo "max_file_uploads = ".ini_get('max_file_uploads')."\n";
echo "</pre>";

?>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="files">

<button type="submit" name="upload">
UPLOAD TEST
</button>

</form>


<?php

if(isset($_POST['upload'])){

echo "<pre>";
print_r($_FILES);
echo "</pre>";

}

?>