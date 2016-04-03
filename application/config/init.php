<?php

define("setFolderTemplate","default");


require_once('core/classes/application.class.php');
function __appAutoload($className) {
	Application::loadClass($className);
}
spl_autoload_register('__appAutoload');

/* Application */
$application = new Application();
Application::setDefaultDirs();
Application::loadConfig('db');
Application::loadConfig('map');
Application::loadConfig('inc');

Register::add('db',new Db());
Register::add('utils',new Utils());
Register::add('translates',Langs::get(0));

// print_r(Langs::get(0));

// print_r($array);
function error404($exception = null) {
	if (debug) {
		echo '<html><head><style> * { font-family: Verdana, Tahoma, Arial; font-size: 13px; } body {} .error {width: 860px;text-align: left;} .error h1 {font-size: 18px;margin-left: 36px;} .error-num {float: left;width: 30px;background-color: #fff;padding: 3px;text-align: left;} .error-descr {float: left;background-color: #f5f5f5;margin-bottom: 10px;padding: 3px;width: 800px;text-align: left;} .error-descr span {font-weight: bold;}</style></head></body>
		';
		echo '<center><div class="error"><h1>'.$exception->getMessage().'</h1>';
		foreach ($exception->getTrace() as $key=>$item) {
			echo '<div class="error-item"><div class="error-num">'.($key + 1).'.</div>';
			echo '<div class="error-descr">';
			echo '<span>'.$item['class'].' '.$item['type'].' '.$item['function'].' '.'( '.join(', ',$item['args']).' )'.'</span><br>';
			echo $item['file'].' (Line: '.$item['line'].')<br>';
			echo '</div><br clear="all" /></div>';
		}
		echo '</div></center>';
		echo '</body></html>';
	}
}