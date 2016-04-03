<?php

class ProductController  extends BaseController {
	
	public $layout = 'home';
	
	function __construct(){
		if (isset($_SESSION['simpleview']) || isset($_GET['simpleview'])) {
			$this->layout = "simple";
		}
	}
	
	public function index() {
		
		$ui = $this->request("ui",false);
		$url = $this->request("id",false);
		
		$exp = explode("-",$url);
		$id = array_pop($exp);
		$alias = substr($url, 0, (strlen($url) - strlen(("-".$id))));
		
		$data = ProductsModel::getById($id);
		$this->view->data = $data;
		
		if (AliasViewHelper::doTraslit($data['name']).'-'.$id != $url || $data['set_isset'] == 0){
			if (count($data)>0 && $data['set_isset'] == 1){
				header ('HTTP/1.1 301 Moved Permanently');
				header ('location: /product/'.AliasViewHelper::doTraslit($data['name']).'-'.$data['id'].'/'.($ui?'?ui=mobile':''));
				exit();
			}
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			$controller = new Dispatcher();
			$controller->process('/error404');
			exit();
		}
		
		$this->add_testimonial($alias,$data['id']);
		if (isset($_SESSION['testim']['_err']) && count($_SESSION['testim']['_err'])>0){
			$this->view->_err = $_SESSION['testim']['_err'];
			unset($_SESSION['testim']['_err']);
		}
		$this->view->testimonials = TestimonialsModel::getByProductId($data['id']);
		$this->view->total = TestimonialsModel::getRating($data['id']);
		$this->view->params = FiltersModel::getParamsView($data['id']);
		
		$ProductsModel = new ProductsModel();
		$this->view->pnodes = $ProductsModel->getPNodes($data['id']);
		
		$catcat = CatModel::getById($data['fk']);
		$this->view->catalogue = $catcat;
		
		$prices = ProductsModel::getAllPrices($data['id'],false,true);
		$this->view->prices = $prices;
				
		$use=new CatModel();
		$use->getLevelsBack($data['fk']);
		$ids = CatModel::$tree;
		$this->view->home_select = $ids;
		$arr = CatModel::$bread_crumbs;
		$arr = @array_reverse($arr);
		$this->view->bread_crumbs = $arr;
		
		$setSEO = array(
			'title'=>($data['title']?$data['title']:$data['name']),
			'kwords'=>($data['kwords']?$data['kwords']:$data['name']),
			'descr'=>($data['descr']?$data['descr']:$data['name'])
		);
		$this->view->_seo = $setSEO;
		
		if (isset($catcat['background_image']) && $catcat['background_image']){
			$this->view->background_img_static = $catcat['background_image'];
		} else {
			if (isset($arr[0]['id']) && $arr[0]['id']){
				$bg = CatModel::getBackgroundCategory($arr[0]['id']);
				if (isset($bg['background_image']) && $bg['background_image'])
					$this->view->background_img_static = $bg['background_image'];
			}
		}
		
		$this->view->yatestimonials = $this->getYandexMartketTestimonials($data['yandexmarket_model_id']);
	}
	
	private function add_testimonial($alias='',$product_id=0){
		$testim = $this->request("testim",false);
		if ($testim){
			if (md5($testim['code'])==$_SESSION['captcha_keystring']) {
				$db = Register::get('db');
				$sql = "
					INSERT INTO ".DB_PREFIX."testimonials
					(`name`,`phone`,`email`,`message`,`dt`,`is_active`,`product_id`,`raiting`)
					VALUES
					(
						'".mysql_real_escape_string(strip_tags($testim['name']))."',
						'".mysql_real_escape_string(strip_tags($testim['phone']))."',
						'".mysql_real_escape_string(strip_tags($testim['email']))."',
						'".mysql_real_escape_string(strip_tags($testim['message']))."',
						'".mktime()."',
						'0',
						'".(int)$product_id."',
						'".(int)$testim['raiting']."'
					);
				";
				$db->post($sql);
				$this->redirectUrl('/product/'.$alias.'-'.$product_id.'/?accept#tabs-raiting');
			}
			else {
				$_SESSION['testim']['_err'] = $testim;
				$this->redirectUrl('/product/'.$alias.'-'.$product_id.'/?deny&captcha#tabs-raiting');
			}
		}
		/* */
	}

	private function getYandexMartketTestimonials($model_id=FALSE){
		
		if ($model_id){
		
			if (isset($_SESSION['__testimonials'][$model_id]) && count($_SESSION['__testimonials'][$model_id]))
				return $_SESSION['__testimonials'][$model_id];
			
			$url = "https://api.content.market.yandex.ru/v1/model/".$model_id."/opinion.json?sort=date&count=30";
			$data = file_get_contents($url);
			$json = json_decode($data);
			
			$list = array();
			if (isset($json->modelOpinions->opinion) && count($json->modelOpinions->opinion)>0){
				foreach ($json->modelOpinions->opinion as $testimon){
					
					$rate = 0;
					switch ($testimon->grade){
						case -2: $rate = 1; break;
						case -1: $rate = 2; break;
						case 0: $rate = 3; break;
						case 1: $rate = 4; break;
						case 2: $rate = 5; break;
					}
					
					$list []= array(
						'dt' => $testimon->date,
						'rate' => $rate,					
						'name' => $testimon->author,
						'text' => $testimon->text,
						'plus' => $testimon->pro,
						'minus' => $testimon->contra,
					);
				}
			}
			
			if (count($list)>0)
				$_SESSION['__testimonials'][$model_id]= $list;
			
			return $list;
		}
		else {
			return array();
		}
	}
	
	function beforeAction(){
		parent::beforeAction();
	}
	function beforeRender(){
		parent::beforeRender();
	}
}