<?php
/**
 * @todo REFACTOR CODE!!! make it more friedly
 *
 */
class Dispatcher {
	
	private $defaultController = 'index';
	private $defaultAction = 'index';
	private $notFoundController = null;

	/**
	 * Call controller specifed by URI
	 * @param string $uri request URI
	 */
	public function process($uri = null) {
		// prepare URI
		$uri = $this->prepareUri($uri);
		// try to find out controller and action names, and also external request params
		$actionInformation = $this->dispatch($uri);
		
		// load controller
		$className = $this->loadController($actionInformation['controller']);
		$class = new $className;
		$class->action = $actionInformation['action'];
		$class->controller = $actionInformation['controller'];
		$class->request = new Request($actionInformation['external_request_params']);
		$class->view = new View();
		
		if (is_callable(array($class, $actionInformation['action'])))
		{
			if (is_callable(array($class, 'beforeAction')))
				call_user_func(array($class, 'beforeAction'));
			
			call_user_func_array(array($class, $actionInformation['action']), array());
			$isRendered = call_user_func_array(array($class, 'isRendered'), array());
			
			if (!$isRendered)
				call_user_func_array(array($class, 'render'), array());

			if (is_callable(array($class, 'afterAction')))
				call_user_func(array($class, 'afterAction'));
		} else {
			throw new Exception('Undefined action "'.$actionInformation['action'].'"');
		}
	}

	public function setNotFoundController($controller)
	{
		$this->notFoundController = $controller;
	}

	/**
	 * Prepare URI for parsing
	 * 1. remove GET-params
	 * 2. remove tralling slash
	 * 3. remove base http path
	 * @param string $uri
	 * @return string prepared URI
	 */
	private function prepareUri($uri = null)
	{
		if (empty($uri))
			$uri = $_SERVER['REQUEST_URI'];
		// get base http path
		$basePath = Application::getHttpRoot();
		// skip tralling slash in base http path
		$basePath = preg_replace('#/$#is','',$basePath);
		// skip base http path
		$uri = preg_replace('#^'.$basePath.'#','',$uri);
		// skip GET params
		$uri = preg_replace('/\?.*?$/','',$uri);
		// skip tralling slash
		$uri = preg_replace('#/$#is','',$uri);

		return $uri;
	}

	/**
	 * Load controller file by controller name
 	 * @param string $controller
	 * @return string controller class name
	 */
	private function loadController($controller)
	{
		$controllerClassName = $controller.'Controller';
		$path = Application::getControllerPath($controller);
		if (empty($path)) {
			// for generator
			$path = Application::getClassDir($this->notFoundController);
			$controllerClassName = $this->notFoundController;
		}
		if (!empty($path))
		{
			include_once($path);
			return $controllerClassName;
		} else {
			throw new Exception('Counld\'t load controller '.$controller);
		}
	}

	/**
	 * Parse URI
	 * Return array like:
	 * array(
	 * 		'controller' => 'controller-name',
	 * 		'action' => 'action-name',
	 * 		'external_request_params' => array('param1'=>'value1' ,'param2'=>'value2')
	 * );
	 * 
	 * @param string $uri
	 * @return array
	 */
	private function dispatch($uri)
	{
		$result = $this->mapDispatch($uri);
		// if path not defined in map configuration
		if (empty($result))
		{
			// try to parse url manually,
			// for example:
			// if uri look like /article/list/page/2/order/name
			// then this parsing should return
			// * controller - article
			// * action - list
			// * external_request_params - array('page' => 2, 'order'=> 'name')
			$uriData =  explode('/',$uri);
			
			$controller = (!empty($uriData[1])) ? $uriData[1] : $this->defaultController;
			$action = (!empty($uriData[2])) ? $uriData[2] : $this->defaultAction;

			$externalRequestParams = null;
			if (count($uriData) > 3)
			{
				for($i = 3; $i < count($uriData); $i=$i+2)
				{
					if (!empty($uriData[$i+1]))
					{
						$externalRequestParams[$uriData[$i]] = $uriData[$i+1];
					}
				}
			}

			$result = array(
				'controller' => $controller,
				'action' => $action,
				'external_request_params' => $externalRequestParams,
			);
		}
		return $result;
	}

	/**
	 * Try to find URI  in map config and parse it useing rule specifed by map
	 * Return array like:
	 * array(
	 * 		'controller' => 'controller-name',
	 * 		'action' => 'action-name',
	 * 		'external_request_params' => array('param1'=>'value1' ,'param2'=>'value2')
	 * );
	 *
	 * @param string $uri
	 * @return array
	 */
	private function mapDispatch($uri)
	{
		$map = Register::get('map');
		$controller = null;
		$action = null;
		$mapRequestParams = null;
		foreach ($map as $mapData)
		{
			$mapUrl = preg_replace('/(\:\w+)/','([^\/]+?)', $mapData['url']);
			// if match map 
			if (preg_match('#^'.$mapUrl.'$#', $uri, $aMatches))
			{
				$controller = $mapData['controller'];
				$action = $mapData['action'];
				// bind defined in map request params
				if (preg_match_all('/:(\w+)/', $mapData['url'], $aId))
				{
					unset($aId[0]);
					$mapRequestParams = array();
					foreach ($aId[1] as $key=>$id) {
						$mapRequestParams[$id] = $aMatches[$key + 1];
					}
				}

				return array(
					'controller' => $controller,
					'action' => $action,
					'external_request_params' => $mapRequestParams,
				);
			}
		}
		return null;
	}
}
?>