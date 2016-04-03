<?php

class YmlController extends CmsGenerator {
	
	var $yml;
	
	function index(){}
	
	function export(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Сформирована выгрузка в yandex YML',URL_NOW);
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."seo WHERE id='index';";
		$seo = $db->get($sql);
		$this->yml = new Yml();
		
		$seo['title'] = iconv("utf8", "windows-1251", $seo['title']);
		$seo['title'] = iconv("utf8", "windows-1251", $seo['title']);
		
		$this->yml->set_shop($seo['title'], $seo['title'], $_SERVER['HTTP_HOST']);
		$this->yml->add_currency("RUR",1);
		$this->parseCat();
		file_put_contents("../cache/yml/yml.xml",$this->yml->get_xml());
		// Будем передавать
		header('Content-type: application/xml');
		// Который будет называться
		header('Content-Disposition: attachment; filename="yml.xml"');
		// Исходный
		readfile("../cache/yml/yml.xml");
		exit;
	}
	
	public function getLevelsBack($id) {
		$ids = $this->getCatName($id);
		if ($ids['id']){
			$this->tree []= (int)$ids['id'];
			if (!empty($ids['parent'])) {
				YmlController::getLevelsBack($ids['parent']);
			}
		}
	}
	
	public function getCatName($id){
		$db = Register::get('db');
		$sql = "select id,name,parent from ".DB_PREFIX."cat where id='".(int)$id."' AND is_active='1';";
		return $db->get($sql);
	}
	
	function parseCat(){
		
		define('INSTALLCOPY', true);
		
		require_once '../application/library/classes/base_controller.class.php';
		require_once '../application/models/products.model.php';
		require_once '../application/models/importers.model.php';
		require_once '../application/models/outprice.model.php';
		require_once '../application/models/details.model.php';
		require_once '../application/models/accounts.model.php';
		
		$db = Register::get('db');
		
		$sql = "
		SELECT 
			id,name,parent 
		FROM ".DB_PREFIX."cat
		WHERE 
			is_active='1' AND 
			is_body_module IN (0) 
		ORDER BY id ASC;";
		$catalog = $db->query($sql);
		if (isset($catalog) && count($catalog)>0){
			foreach ($catalog as $cat) {
				$this->tree = array();
				$this->getLevelsBack($cat['id']);
				if (in_array($cat['id'],$this->tree)){
					
					$cat['name'] = iconv("utf8", "windows-1251", $cat['name']);
					
					$this->yml->add_category(stripslashes($cat['name']), $cat['id'], $cat['parent']);
				}
			}
		}
		
		$sql = "SELECT 
					PROD.id,
					PROD.name,
					PROD.content,
				
					CAT.name cat_name,
					CAT.id cat_id
				FROM ".DB_PREFIX."products PROD
				INNER JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk
				WHERE 
					PROD.set_isset='1' AND 
					CAT.is_active=1 AND 
					PROD.is_body_module IN (0)
				ORDER BY 
					PROD.sort,PROD.name,PROD.img1 DESC
				;";
		$products = $db->query($sql);
		if (isset($products) && count($products)>0){
			foreach ($products as $product) {
				
				$this->tree = array();
				$this->getLevelsBack($product['cat_id']);
				if (in_array($product['cat_id'],$this->tree)){
				
					$prices = ProductsModel::getAllPrices($product['id']);
					if ($prices['min']>0){
						
						$descr = nl2br(strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", str_replace(array("	",""),"",$product['content']))));
						$descr = str_replace("<br />","",$descr);
						$descr = str_replace(chr(194).chr(160),' ',$descr);
						$descr = trim(str_replace(array("\r","\n"),"",$descr));
						
						$product['name'] = iconv("utf8", "windows-1251", $product['name']);
						$descr = iconv("utf8", "windows-1251", $descr);
						
						$data = array(
							'url'=>"http://".$_SERVER['HTTP_HOST']."/product/".AliasViewHelper::doTraslit($product['name']).'-'.$product['id'].'/',
							'price'=>($prices['min']),
							'currencyId'=>"BYR", 
							'categoryId'=>$product['cat_id'], 
							'name'=>stripslashes(($product['name'])), 
							'description'=>$descr
						);
						
						if ($product['img1']) {
							if ($product['is_image_noresize']){
								$data['picture'] = StaticimgViewHelper::chk('autotovar',$product['img1']);
							} else {
								$data['picture'] = StaticimgViewHelper::chk('products','normal-'.$product['img1']);
							}
						}
						$this->yml->add_offer($product['id'], $data);
					}
				}
			}
		}
		


		/* details start */
// 		require_once './application/models/outprice.model.php';
// 		$ImportersModel = new ImportersModel();
// 		$this->yml->add_category("Запчасти", 1, 0);
// 		$sql = "SELECT IMPORT_ID,BRAND_NAME,ARTICLE,DESCR,PRICE FROM ".DB_PREFIX."details WHERE PRICE > 0 AND BOX > 0;";
// 		$details = $db->query($sql);
// 		if (isset($details) && count($details)>0){
// 			foreach ($details as $detail){
		
// 				$importer = $ImportersModel->getById($detail['IMPORT_ID'],false);
// 				$OUTPRICE_DATA = OutpriceModel::generate($importer,(array()),$detail['PRICE'],$detail['BRAND_NAME']);
		
// 				$data = array(
// 						'url'=>"http://".$_SERVER['HTTP_HOST']."/pricelist/".rawurlencode($detail['BRAND_NAME']).'/'.rawurlencode($detail['ARTICLE']).'/',
// 						'price'=>($OUTPRICE_DATA['resultPRICE']),
// 						'currencyId'=>"RUR",
// 						'categoryId'=>1,
// 						'name'=>stripslashes(urlencode($product['name'])),
// 						'description'=>$descr
// 				);
// 				$this->yml->add_offer($product['id'], $data);
// 			}
// 		}
		/* details end */
		
	}
}
?>
