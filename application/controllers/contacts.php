<?php

class ContactsController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		
		// $this->view->google = SettingsModel::get('google');
		
		$this->view->error_data = (isset($this->accountData)&&count($this->accountData)>0)?$this->accountData:array();
		if (isset($_SESSION['sendC'])) {
			if ($_SESSION['sendC']==1) {
				$this->view->send = 1;
			}
			elseif ($_SESSION['sendC']==2) {
				$this->view->send = 2;
				$this->view->error_data = $_SESSION['error_data'];
				unset($_SESSION['error_data']);
			}
			unset($_SESSION['sendC']);
		} else {
		  $this->view->send = 0;
		}
		
		$this->breadcrumbs ['Контакты']= '#';
	}
	
	function send() {
		$data = $this->request("form");
		$email = SettingsModel::get('contact_email');	
		$site = $_SERVER['SERVER_NAME'];
		$check = EmailsModel::get('contact',$data,$email,$data['email'],$data['name'].' ('.$site.')',true);
		$this->redirectUrl("/contacts/?success=1");
	}
	
	function beforeAction(){
		parent::beforeAction();
	}

	function beforeRender() {
		parent::beforeRender();
	}
}