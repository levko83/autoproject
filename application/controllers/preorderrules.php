<?php

class PreorderrulesController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		
		if (isset($_REQUEST['ajax']))
			$this->layout = "ajax";
		
		$page = SettingsModel::get('preorderrules');
		$this->view->page = $page;
		
		$translates = Register::get('translates');
		$this->breadcrumbs ['Условия покупки под заказ']= HTTP_ROOT.'/pre-order-rules/';
	}
	
	function beforeAction(){
		parent::beforeAction();
	}

	function beforeRender() {
		parent::beforeRender();
	}
}