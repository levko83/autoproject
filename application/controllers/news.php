<?php

class NewsController  extends BaseController {
	
	public $layout = 'home';
	
	public function index(){
		
		$activeViewRssFeed = SettingsModel::get('rss_news_url_active');
		if ($activeViewRssFeed){
			
			$this->view->news = $this->rss();
		}
		else {
			
			$per_page = 5;
			$page = $this->request("page",1);
			
			$this->view->news = NewsModel::getAll($page,$per_page);
			
			$cc = NewsModel::getByPaging();
			$this->view->pages_num = (int)(($cc - 1) / $per_page) + 1;
			$this->view->page = $page;
		}
		
		$this->breadcrumbs ['Новости']= '/news/';
	}
	
	public function view(){
		
		$id = addslashes($this->request("code"));
		if (empty($id)) {
			$this->error404();
		}
		
		$data = NewsModel::getByCode($id);
		if (!$data) {
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			$controller = new Dispatcher();
			$controller->process('/error404');
			exit();
		}
		$this->view->page = $data;
		$this->view->_seo = $data;
		
		$this->breadcrumbs ['Новости']= '/news/';
		$this->breadcrumbs [$data['name']]= '/news/';
	}
	
	function rss($limit=false){
		
		$url = SettingsModel::get('rss_news_url');
		
		$reg_exp  = '#<item>.*?<title>(.*?)<\/title>.*?';
		$reg_exp .='<link>(.*?)<\/link>.*?<description>';
		$reg_exp .='(.*?)<\/description>.*?<\/item>#si';
		
		$xml_data = file_get_contents($url);
		
		preg_match_all($reg_exp, $xml_data, $temp);
		
		$res = array(
			'count'=>count($temp[0]),
			'title'=>$temp[1],
			'link'=>$temp[2],
			'desc'=>$temp[3]
		);
		
		$array = array();
		for ($i=0; $i<=(($limit)?$limit:($res['count']-1));$i++){
			if (isset($res['title'][$i]) && $res['title'][$i])
			$array []= array(
				'url' => $res['link'][$i],
				'name' => $res['title'][$i],
				'dt' => mktime(),
				'brief' => html_entity_decode($res['desc'][$i]),
			);
		}
		return $array;
	}
	
	public function rssfeed(){
		
		$this->layout = "ajax";
		$news = NewsModel::getByLimitAll(20);
		$this->view->news = $news;
	}

	function beforeAction(){
		parent::beforeAction();
	}
	function beforeRender(){
		parent::beforeRender();
	}
}