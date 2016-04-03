<?php

class ArticlesController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() 
	{	
		$page = $this->request("page",1);
		
		$this->view->news = ArticlesModel::getAll($page,$per_page=5);
		$cc = ArticlesModel::getByPaging();
		$this->view->pages_num = (int)(($cc - 1) / $per_page) + 1;
		$this->view->page = $page;
			
		$this->breadcrumbs ['Статьи']= '/articles/';
	}
	
	public function view() 
	{
		$id = addslashes($this->request("code"));
		if (empty($id)) {
			$this->error404();
		}
		$data = ArticlesModel::getByCode($id);
		if (!$data) {
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			$controller = new Dispatcher();
			$controller->process('/error404');
			exit();
		}
		$this->view->page = $data;
		$this->view->_seo = $data;
		
		$this->breadcrumbs ['Статьи']= '/articles/';
		$this->breadcrumbs [$data['name']]= '#';
	}

	function beforeAction()
	{
		parent::beforeAction();
	}

}