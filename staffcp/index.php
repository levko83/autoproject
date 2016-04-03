<?php

define('debug',0);
if (!debug){
	error_reporting(0);
} else {
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 'On');
}

try {
	require('config/init.php');
	$controller = new Dispatcher();
	$controller->setNotFoundController('CmsGenerator');
	$controller->process();
} catch (Exception $exception) {
	$exp = new CmsGenerator();
	$exp->error404($exception);
	
}
?>