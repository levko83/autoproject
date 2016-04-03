<?php

class BrandController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		$db = Register::get('db');
		
		// $page = (int)$this->request("page",1);
		// $per_page = 20;
		// $sql_page = ($page - 1)*$per_page;
		// $sql = "SELECT * FROM `".DB_PREFIX."brands` WHERE BRA_ACTIVE='1' ORDER BY BRA_BRAND LIMIT $sql_page,$per_page";	
		$sql = "SELECT * FROM `".DB_PREFIX."brands` WHERE BRA_ACTIVE='1' ORDER BY BRA_BRAND";	
		$this->view->data = $db->query($sql);
		
		// $sql = "SELECT COUNT(*) cc FROM `".DB_PREFIX."brands` WHERE BRA_ACTIVE='1';";	
		// $res = $db->get($sql);
		// $cc = $res['cc'];
		
		// $this->view->pages_num = (int)(($cc - 1) / $per_page) + 1;
		// $this->view->page = $page;
		
		$this->breadcrumbs ['Бренды']= '/brand/';
	}
	
	public function view(){
		$id = urldecode($this->request("name"));
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."brands` WHERE `BRA_BRAND`='".mysql_real_escape_string($id)."';";
		$res = $db->get($sql);
		$this->view->data = $res;
		$this->view->_seo = array(
			'title'		=>	$res['title'],
			'kwords'	=>	$res['kwords'],
			'descr'		=>	$res['descr'],
		);
		
		$this->breadcrumbs ['Бренды']= '/brand/';
		$this->breadcrumbs [$res['title']]= '#';
	}
	
	public function ajax(){
		
		$this->layout = "brand/ajax_home";
		
		$id = urldecode($this->request("id"));
		$id = str_replace("~plus~","+",$id);
		
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."brands` WHERE `BRA_BRAND`='".mysql_real_escape_string($id)."';";
		$res = $db->get($sql);
		
		$this->view->data = $res;
		$this->view->_seo = array(
			'title'		=>	$res['title'],
			'kwords'	=>	$res['kwords'],
			'descr'		=>	$res['descr'],
		);
	}
	
	function beforeAction(){
		parent::beforeAction();
	}
	function beforeRender(){
		parent::beforeRender();
	}
}