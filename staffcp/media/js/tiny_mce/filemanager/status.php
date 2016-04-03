<?php
include 'config.php';

$filePath = $_REQUEST['path'];

if (!empty($filePath)) {
	
	$output = 'подождите...';
	
	if (is_file($filePath))
		$output = size2String(filesize($filePath));
	
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	
	die('Загружено: '.$output);
}
else {
	die('Нет данных');
}

function size2String($size)
{
	$aVal = array('b','Kb','Mb','Gb','Tb');
	$pow = intval(log($size,1024));
	return round($size/pow(1024,$pow),2).' '.$aVal[$pow];
}