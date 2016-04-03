<? require 'config.php';?>
<html>
<head>
<title>FlyOut CMS: Фаловый менеджер</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript" type="text/javascript" src="/staffcp/media/js/tiny_mce/tiny_mce_popup.js"></script>
<script language="javascript" type="text/javascript" src="/staffcp/media/js/tiny_mce/filemanager/fo.js"></script>
<script language="javascript" type="text/javascript" src="/media-templates/js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript" src="/staffcp/media/js/jqueryFileTree.js"></script>
<link type="text/css" href="/staffcp/media/js/jquery_file__tree.css" rel="Stylesheet" />
<script language="javascript" type="text/javascript">
tinyMCEPopup.onInit.add(foFileManagerPopup.init, foFileManagerPopup);
var httpRoot = '<?=HTTP_UPLOAD_DIR;?>';
$(document).ready( function() {
	refresh();
});
function refresh(){
	$('#dir_container').fileTree({
	  root: '/',
	  script: '/staffcp/media/js/tiny_mce/filemanager/jqueryFileTree.php',
	  fileContainerId: 'file_container',
	  expandSpeed: 1000,
	  collapseSpeed: 1000, 
	  multiFolder: false
	},
	function(file){
	    foFileManagerPopup.select(httpRoot + file);
		return false;
	},
	function(dir){
	    $('#upload').get(0).contentWindow.uploadDir = dir;
		return false;
	});
}
function file_delete(file) {
	$.post('unlink.php?dir_file='+file, null,
	function(data) {
		alert(data);
		refresh();
	});
}
</script>

</head>
<body>
<div class="foFileManager">
<div id="dir_container" class="dirContainer"></div>
<div id="file_container" class="fileContainer"></div>
<div style="float: none; clear: both;"></div>
<div style="float: left; width: 450px;"><p>Для выбора файла используете двойной счелчок по имени файла.</p></div>
<div style="float: left; width: 340px;"><iframe frameborder="0" hspace="0" vspace="0" id="upload" src="/staffcp/media/js/tiny_mce/filemanager/upload.php" width="340" height="70" scrolling="no" style="border: 0; padding: 0; margin: 0;"></iframe></div>
</div>
</body>
</html>