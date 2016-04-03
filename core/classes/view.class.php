<?php

class View extends Dynamic {
	
	public $sufix = 'ViewHelper';
	
	private $__values;
	
	private $__extension = '.tpl';
	private $__extension_phtml = '.phtml';
	
	private $__layout;
	
	private $__template;
	
	public function __construct()
	{
		$this->__values = array();
	}
	
	public function __set($varName, $value)
	{
		$this->__values[$varName] = $value;
	}
	
	public function __get($varName)
	{
		if (!empty($this->__values[$varName]))
			return $this->__values[$varName];
		else 
			return null;
	}
	
	public function render($templateName)
	{
		extract($this->__values);
		
		$__template = $templateName . $this->__extension_phtml;
		if (file_exists($__template)){
			$__template = $__template;
		}
		else {
			$__template = $templateName . $this->__extension;
		}
		$this->__template = $__template;
		
		if (is_file($this->__layout)){
			include($this->__layout);
		}
		else 
			include($this->__template);
	}
	
	public function block($templateName)
	{
		extract($this->__values);
		$file = Application::getTemplatesDir(true) . '/' . $templateName . $this->__extension_phtml;
		if (file_exists($file)){
			$file = $file;
		}
		else {
			$file = Application::getTemplatesDir(true) . '/' . $templateName . $this->__extension;
		}
		include($file);
	}
	
	public function content()
	{
		extract($this->__values);
		include($this->__template);
	}
	
	public function clear()
	{
		$this->__values = array();
	}
	
	public function setLayout($templatePath)
	{
		$__layout = Application::getTemplatesDir(true) . '/' . $templatePath . $this->__extension_phtml;
		if (file_exists($__layout)){
			$__layout = $__layout;
		}
		else {
			$__layout = Application::getTemplatesDir(true) . '/' . $templatePath . $this->__extension;
		}
		$this->__layout = $__layout;
	}
	
	public function getLayout()
	{
		return $this->__layout;
	}
}