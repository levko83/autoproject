<?php
/**
 * Parent class for all application controllers
 */
class Controller {
	
	public $view;
	public $request;
	public $action;
	public $controller;
	public $layout = null;
	
	private $isRendered = false;
	
	/**
	 * Render template
	 * @param string $templateName template name, for example "mytpl","myfolder/mytpl"
	 */
	public function render($templateName = '')
	{
		$this->beforeRender();
		// you could set layout by $this->view->setLayout();
		if (!empty($this->layout))
			$this->view->setLayout($this->layout);
		$templatePath = $this->getTemplatePath($templateName);
		$this->view->render($templatePath);

		$this->isRendered = true;
	}
	
	public function request($name, $defaulValue = null)
	{
		return $this->request->getParam($name, $defaulValue);
	}

	/**
	 * Is any template already has been rendered by current action ?
	 * @return bool is any template already has been rendered by current action
	 */
	public function isRendered()
	{
		return $this->isRendered;
	}

	/**
	 * Redirect to custom URL
	 * @param string $url URL
	 */
	public function redirectUrl($url = '/')
	{
		header('Location: '.$url);
		die();
	}

	/**
	 * Redirect to URL specifed by controller, action and params
	 *
	 * @todo What would we do with mapping? Close eyes? No! We should parse params and put it to map rule
	 * @param string $action
	 * @param string $controller
	 * @param string $params
	 */
	public function redirect($action = 'index',$controller ='index',$params = '')
	{
		if ($params)
			$params = '?'.$params;
			
		header('location: '.Application::getHttpRoot().$controller.'/'.$action.'/'.$params);
		die();
	}

	/**
	 * Error 404 posting
	 *
	 * @todo depricate
	 */
	public function error404($exception = null) {
		if (debug)
		{
		echo '<html><head><style>';
		echo '
	* {
		font-family: Verdana, Tahoma, Arial;
		font-size: 13px;
	}
	
	body {
	}
	
	.error {
		width: 860px;
		text-align: left;
	}
	
	.error h1 {
		font-size: 18px;
		margin-left: 36px;
	}
	
	.error-num {
		float: left;
		width: 30px;
		background-color: #fff;
		padding: 3px;
		text-align: left;
	}
	
	.error-descr {
		float: left;
		background-color: #f5f5f5;
		margin-bottom: 10px;
		padding: 3px;
		width: 800px;
		text-align: left;
	}
	
	.error-descr span {
		font-weight: bold;
	}
	
	';
		echo '</style></head></body>';
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
		} else {
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			$controller = new Dispatcher();
			$controller->process('/error404');
			exit();
		}
		die();
	}


	public function beforeRender() {}

	/**
	 * Get path to template by template name
	 * @param string $templateName
	 * @return string template path
	 */
	private function getTemplatePath($templateName)
	{
		$path = null;
		if (preg_match('/\//',$templateName)) {
			$path = Application::getTemplatesDir(true). '/' . $templateName;;
		} elseif (strlen($templateName) != 0) {	
			$path = Application::getTemplatesDir(true) . '/' . $this->controller . '/' . $templateName;
		} else {
			$path = Application::getTemplatesDir(true) . '/' . $this->controller . '/' . $this->action;
		}
		return $path;
	}
}