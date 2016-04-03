<?php
class IndexController  extends BaseController {
	public $layout = 'home';
	public function index() {
		
		// $this->view->products = ProductsModel::set_index(16);
		//$this->view->articles = ArticlesModel::getByLimit(2);
		// $this->view->articles = ArticlesModel::getByLimit(4);
		$this->view->sliders = SliderModel::getAll();
		/*
		$activeViewRssFeed = SettingsModel::get('rss_news_url_active');
		if (!$activeViewRssFeed){
			//$this->view->news = NewsModel::getByLimit(2);
			$this->view->news = NewsModel::getByLimit(4);
		}
		else {
			require_once 'news.php';
			$news = new NewsController();
			//$this->view->news = $news->rss(3);
			$this->view->news = $news->rss(4);
		}
		*/
		$this->view->marks_letters = ManufacturersModel::getMarksLetters();
		
		// require_once 'wheels_tires.php';
		// $wheels_tires = new Wheels_tiresController();
		// $this->view->list_of_auto_brands = $wheels_tires->getWheelsTiresMarks();
		
		// $this->view->rememberCar = (isset($_SESSION['__remembercar']) && $_SESSION['__remembercar'])?$_SESSION['__remembercar']:false;
	}
	/*function sitemap(){
		$this->view->allcars = ManufacturersModel::All();
		
		$this->breadcrumbs ['Карта сайта']= '#';
	}*/
	function beforeAction(){
		parent::beforeAction();
	}
	function beforeRender(){
		parent::beforeRender();
		#$this->mailing_alert();
	}
	public function captcha() {
		$this->layout = "ajax";
	}
	public function notify() {
		$this->layout = "ajax";
		echo 'notify';
	}
	public function error404($e=''){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		
		$this->breadcrumbs ['404 Not Found']= '#';
	}
	/* ****************************************************************** */
	public function click() {
		$id = $this->request("id");
		BannernetworkModel::click($id);
		#$this->redirectUrl('/');
		exit();
	}
	public function mailing_add() {
		$data = $this->request("mailing");
		Mailing_emailsModel::add($data);
		$this->redirectUrl('/');
		exit();
	}
	public function mailing_allow() {
		$key = $this->request("key");
		Mailing_emailsModel::allow($key);
		$this->redirectUrl('/');
		exit();
	}
	public function mailing_deny() {
		$key = $this->request("key");
		Mailing_emailsModel::deny($key);
		$this->redirectUrl('/');
		exit();
	}
	protected function mailing_alert() {
		if (isset($_SESSION['mailing'])) {
			$this->view->mailing = $_SESSION['mailing'];
			unset($_SESSION['mailing']);
		} else {
		  	$this->view->mailing = 0;
		}
	}
}
?>