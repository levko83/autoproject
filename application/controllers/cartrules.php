<?php

class CartrulesController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		
		$this->view->page = SettingsModel::get('cart_rules');
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['rulestoinfocart']]= '#';
	}
	
	function beforeAction(){
		parent::beforeAction();
	}

	function beforeRender() {
		parent::beforeRender();
	}
}