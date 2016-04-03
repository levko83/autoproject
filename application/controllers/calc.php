<?php

class CalcController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		
		$this->breadcrumbs ['Шинный калькулятор']= '#';
	}
	
	function beforeAction(){
		parent::beforeAction();
	}

	function beforeRender() {
		parent::beforeRender();
	}
}