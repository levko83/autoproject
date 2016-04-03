<?php
// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
ini_set('display_errors', 1);
$POST_MAX_SIZE = ini_get('post_max_size');
$unit = strtoupper(substr($POST_MAX_SIZE, -1));
$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

if ((int) $_SERVER['CONTENT_LENGTH'] > $multiplier * (int) $POST_MAX_SIZE && $POST_MAX_SIZE) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "POST exceeded maximum allowed size.";
    exit(0);
}

// Settings
//$save_path = realpath(dirname(__FILE__)) . "/media/files/products/";
#1steve_jobs.jpg
$save_path = "../../../../media/files/products/";
$upload_name = "Filedata";
// 2GB in bytes
$max_file_size_in_bytes = 2147483647;
// Allowed file extensions
$extension_whitelist = array("jpg", "gif", "png", "jpeg");
// Characters allowed in the file name (in a Regular Expression format)
$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';
// Other variables
$MAX_FILENAME_LENGTH = 260;
$file_name = "";
$file_extension = "";
$uploadErrors = array(
    0 => "There is no error, the file uploaded with success",
    1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
    2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
    3 => "The uploaded file was only partially uploaded",
    4 => "No file was uploaded",
    6 => "Missing a temporary folder"
);

// Validate the upload
if (!isset($_FILES[$upload_name])) {
    HandleError("No upload found in \$_FILES for " . $upload_name);
    exit(0);
} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
    HandleError($uploadErrors[$_FILES[$upload_name]["error"]]);
    exit(0);
} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
    HandleError("Upload failed is_uploaded_file test.");
    exit(0);
} else if (!isset($_FILES[$upload_name]['name'])) {
    HandleError("File has no name.");
    exit(0);
}

// Validate the file size (Warning the largest files supported by this code is 2GB)
$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
if (!$file_size || $file_size > $max_file_size_in_bytes) {
    HandleError("File exceeds the maximum allowed size");
    exit(0);
}

// Validate file name (for our purposes we'll just remove invalid characters)
$file_name = preg_replace('/[^' . $valid_chars_regex . ']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
    HandleError("Invalid file name");
    exit(0);
}

// Validate that we won't over-write an existing file
if (file_exists($save_path . $file_name)) {
    HandleError("File with this name already exists");
    exit(0);
}

// Validate file extention
$path_info = pathinfo($_FILES[$upload_name]['name']);
$file_extension = strtolower($path_info["extension"]);
$is_valid_extension = false;
foreach ($extension_whitelist as $extension) {
    if ($file_extension == $extension) {
        $is_valid_extension = true;
        break;
    }
}

if (!$is_valid_extension) {
    HandleError("Invalid file extension");
    exit(0);
}

if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path . 'inside-placeholder-' . $file_name)) {
	@chmod($save_path . $file_name,0755);
    HandleError("File could not be saved: " . $save_path . $file_name);
    exit(0);
}

createImageThumbs($save_path . 'inside-placeholder-' . $file_name, $save_path . 'normal-' . 'inside-placeholder-' . $file_name, 130, 100);

# запишим все в БД
/*$db = mysql_connect (DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $db);
$sql = "INSERT INTO `".DB_PREFIX."galleries__images` (`fk_gallery`, `name`, `img`, `sort`, `is_active`)
VALUES ('".(int)$_POST['catid']."','','".$file_name."','0','1');";
mysql_query($sql);*/

echo "File Received " . $save_path . $file_name;
exit(0);

function HandleError($message) {
    header("HTTP/1.1 500 Internal Server Error");
    echo $message;
}

function createImageThumbs($img_dir, $pathToThumbs, $thumbWidth, $thumbHeight, $rgb=0xffffff, $quality=100) {
	
	$src = $img_dir;
	$dest = $pathToThumbs;
	$width = $thumbWidth;
	$height = $thumbHeight;
	
	if (!file_exists($src)) return false;
	
	  $size = getimagesize($src);
	
	  if ($size === false) return false;

	  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
	  $icfunc = "imagecreatefrom" . $format;
	  if (!function_exists($icfunc)) return false;
	
	  $x_ratio = $width / $size[0];
	  $y_ratio = $height / $size[1];
	
	  $ratio = min($x_ratio, $y_ratio);
	  $use_x_ratio = ($x_ratio == $ratio);
	
	  $new_width = $use_x_ratio ? $width : floor($size[0] * $ratio);
	  $new_height = !$use_x_ratio ? $height : floor($size[1] * $ratio);
	  $new_left = $use_x_ratio ? 0 : floor(($width - $new_width) / 2);
	  $new_top = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
	
	  //var_dump($icfunc);
	  $isrc = $icfunc($src);
	  $idest = imagecreatetruecolor($width, $height);

	  imagefill($idest, 0, 0, $rgb);
	  imagecopyresampled($idest,$isrc,$new_left,$new_top,0,0, $new_width,$new_height, $size[0],$size[1] );
	  imagejpeg($idest, $dest);
	
	  imagedestroy($isrc);
	  imagedestroy($idest);
	
	return $dest .'.'. $format;
	
//    # parse path for the extension
//    $info = pathinfo($img_dir);
//    # continue only if this is a JPEG image
//    if (isset($info['extension'])) {
//        #echo "Creating thumbnail for {$fname} <br />";
//        #load image and get image size
//        $img_str = file_get_contents("{$img_dir}");
//        $isrc = imagecreatefromstring($img_str);
//
//        $width = $thumbWidth;
//        $height = $thumbHeight;
//        $width = $width ? $thumbWidth : 320;
//        $height = $height ? $thumbHeight : 180;
//        #pr($width);
//        #copy and resize old image into new image
//        #imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
//        $size[0] = imagesx($isrc);
//        $size[1] = imagesy($isrc);
//
//        if ($size === false)
//            return false;
//
//        $x_ratio = $width / $size[0];
//        $y_ratio = $height / $size[1];
//
//        $ratio = max($x_ratio, $y_ratio);
//        $use_x_ratio = ($x_ratio == $ratio);
//
//        if (( $size[0] <= $width ) && ( $size[1] <= $height)) {
//            $new_width = $size[0];
//            $new_height = $size[1];
//        } else {
//            $new_width = floor($size[0] * $ratio);
//            $new_height = floor($size[1] * $ratio);
//        }
//
//        $new_left = $use_x_ratio ? 0 : floor(($width - $new_width) / 2);
//        $new_top = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
//
//        #$isrc = $icfunc($src);
//        $idest = imagecreatetruecolor($width, $height);
//
//        $rgb = 0xFFFFFF;
//        imagefill($idest, 0, 0, $rgb);
//        imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
//                $new_width, $new_height, $size[0], $size[1]);
//
//        #imagejpeg( $idest,  $dest, $quality );
//        imagejpeg($idest, "{$pathToThumbs}", 85);
//
//        imagedestroy($isrc);
//        imagedestroy($idest);
//    }
}
?>