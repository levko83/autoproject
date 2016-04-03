<?php
require 'config.php';
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
$_REQUEST['dir'] = urldecode($_REQUEST['dir']);
$root = UPLOAD_DIR;
if( file_exists($root . $_REQUEST['dir']) ) {
	$files = scandir($root . $_REQUEST['dir']);
	if( count($files) >= 2 ) { /* The 2 accounts for . and .. */
		$data['dirs'] = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($root . $_REQUEST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_REQUEST['dir'] . $file) ) {
				$file = iconv('windows-1251', 'utf-8', $file);
				$data['dirs'] .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlspecialchars($_REQUEST['dir'] . $file) . "/\">" . htmlspecialchars($file) . "</a></li>";
			}
		}
		$data['dirs'] .= '</ul>';
		
		$data['files'] = '<div class="jqueryFileTree">';
		// All files
		
		$data['files'] .= '<table width="100%">';
		$i=$j=0;
		foreach( $files as $file ) {
			if( file_exists($root . $_REQUEST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_REQUEST['dir'] . $file) ) {
				$i++;$j++;
				$ext = preg_replace('/^.*\./', '', $file);
				$fileName = str_replace('.'.$ext, '', $file);
				$fileName = (strlen($fileName) > 17)? substr($fileName, 0, 17).'~':$fileName;
				$fileName .= '.'.$ext;
				$file = iconv('windows-1251', 'utf-8', $file);
				$fileName = iconv('windows-1251', 'utf-8', $fileName);
				
				if ($i==1) $data['files'] .= '<tr>';
				$data['files'] .= "<td>";
				$data['files'] .= "<div class=\"file ext_$ext\">";
				$data['files'] .= "<a href=\"#\" rel=\"" . htmlspecialchars($_REQUEST['dir'] . $file) . "\">" . htmlspecialchars($fileName) . "</a>";
				$data['files'] .= "</div>";
				$data['files'] .= "</td>";
				$data['files'] .= "<td>";
				$data['files'] .= "<a href=\"#\" onclick=\"file_delete('". htmlspecialchars($_REQUEST['dir'] . $file) ."');\"><img src=\"/staffcp/media/images/trash_16.png\"/></a>";
				$data['files'] .= "</td>";
				if ($i==2||$j==count($files)){ $data['files'] .= '</tr>'; $i=0; }
			}
		}
		
		$data['files'] .= '</table>';
		$data['files'] .= '</div>';
		echo json_encode($data);
	}
}

?>