<?php require 'config.php';?>
<?php

$file = (isset($_REQUEST['dir_file']))?$_REQUEST['dir_file']:'';
$root = UPLOAD_DIR;
$get = $root.$file;
if (file_exists($get)) {
	if (unlink($get))
		echo 'File delete!';
	else 
		echo 'File operation error!';
}

?>
