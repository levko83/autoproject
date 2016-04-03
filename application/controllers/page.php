<?php

class PageController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		$code = $this->request("code");
		$code = addslashes($code);
		if (empty($code))
			$this->error404();
		
		$lang = $_SESSION["setLang"];
		
		$data = PageModel::getByCode($code);
		$this->view->page = $data;
		$this->view->curlang = $lang;
		if ($lang!='de') $data['title'] = $data['title_'.$lang];
		$this->view->_seo = $data;
		
		// if ($data['fk_gallery']){
			// $this->view->galleries_images = Galleries_imagesModel::getAllImagesByGalleryID($data['fk_gallery']);
		// }
		
		$this->breadcrumbs[$data['name']] = "/page/".$data['code'];
	}
	
	function beforeAction(){
		parent::beforeAction();
	}
	function beforeRender(){
		parent::beforeRender();
	}
}