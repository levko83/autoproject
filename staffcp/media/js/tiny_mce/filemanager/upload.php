<?php
include 'config.php';

$msg = '<b>Загрузка файла</b>';
$uploaded = false;
$dir = '';
$upload = '';
if (!empty($_FILES['upload']['tmp_name']) && isset($_POST['dir'])) {
	
	$root = UPLOAD_DIR;
	$dir = $_POST['dir'];
	if (empty($dir))
		$dir = '/';
		
	$upload = $root.$dir.$_FILES['upload']['name'];
	if (move_uploaded_file($_FILES['upload']['tmp_name'], $upload)) {
		$uploaded = true;
		$msg = 'Файл загружен';
	} else {
		$msg = 'При загрузке произошла ошибка';
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript" type="text/javascript" src="/staffcp/media/js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript" src="/staffcp/media/js/jqueryFileTree.js"></script>
<link type="text/css" href="/staffcp/media/js/jqueryFileTree.css" rel="Stylesheet" />
<script>
	var uploadDir = '<?=$dir;?>';
	function startUpload(){
		$('#result').html('Загрузка файла началась...');
		setTimeout("$('#result').html('Подождите...');",2000);
	}
	function getUploadStatus(){
		$.get('status.php?path=<?=$upload?>',function(data){
			$('#result').html(data);
		});
		setTimeout("$('#result').html('<?=$msg?>');",7000);
	}
	function start(){
		$('#dir').val(uploadDir);
		startUpload();
	}
	<?if ($uploaded){?>
		getUploadStatus();
		<?if (empty($dir)||$dir=="/"){?>
		window.parent.refresh();
		<?}else{?>
		window.parent.$('a[rel="<?=$dir;?>"]').click();
		window.parent.$('a[rel="<?=$dir;?>"]').click();
		<?}?>
	<?}?>
</script>
<style>
	* { color:#333333; font-family:Verdana; font-size:10px; margin:0; padding: 0; }
	html { background:#F0F0EE none repeat scroll 0 0; }
	#result { padding: 2px 0 0 20px; height: 20px; margin: 0; }
	#upload_form {margin:0; padding: 0;}
</style>
<!--[if IE]>
<style>
#upload {height: 20px;}
</style>
<![endif]-->
</head>
<body>
<div id="result"><?=$msg;?></div>
<div id="upload_form">
	<form method="post"  enctype="multipart/form-data" onsubmit="start();"><!---->
		<input type="file" name="upload" id="upload">
		<input type="hidden" name="dir" id="dir" value="">
		<input type="submit" value="Загрузить файл" >
	</form>
</div>
</body>
</html>