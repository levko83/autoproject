<?php

class AboutController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		$lang = $_SESSION["setLang"];
		
		$curlang = SettingsModel::get('aboutus_'.$lang);
		if (!isset($curlang))
			$page = SettingsModel::get('aboutus');
		else
			$page = SettingsModel::get('aboutus_'.$lang);
		
		$this->view->page = $page;
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['front.about']]= HTTP_ROOT.'/about/';
	}
	
	function beforeAction(){
		parent::beforeAction();
	}

	function beforeRender() {
		parent::beforeRender();
	}
}