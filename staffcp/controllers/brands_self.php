<?php
class Brands_selfController  extends CmsGenerator {
	public $layout = 'global';
	public function index(){
		
		$db = Register::get('db');
		
		$sql = "SELECT BRA_ID_GET,BRA_BRAND FROM ".DB_PREFIX."brands ORDER BY BRA_BRAND;";
		$this->view->brandsList = $db->query($sql);
	}
	public function upgrade(){
		
		$incorrectbrand = $this->request("incorrectbrand",false);
		$tecdoc = $this->request("upgrade",false);
		
		if ($incorrectbrand && $tecdoc){
			$ID_BRAND = $this->findBrand($tecdoc);
			if ($ID_BRAND){
				$this->addNode($ID_BRAND,$incorrectbrand);
				$this->updatePrices($ID_BRAND,$incorrectbrand);
			}
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Обучение брендов',URL_NOW);
		$this->redirectUrl("/staffcp/details/?search[brand]=".urlencode($incorrectbrand));
	}
	function findBrand($name){
		if ($name) {
			$db = Register::get('db');
			$sql = "SELECT BRA_ID_GET FROM ".DB_PREFIX."brands WHERE BRA_BRAND LIKE '".mysql_real_escape_string($name)."';";
			$res = $db->get($sql);
			return $res['BRA_ID_GET'];
		}
		else {
			return 0;
		}
	}/*
	function addNode($id=0,$brand=''){
		if ($id && $brand){
			$db = Register::get('db');
			$sql = "INSERT INTO ".DB_PREFIX."brands (BRA_ID_GET,BRA_BRAND) VALUES ('".(int)$id."','".mysql_real_escape_string($brand)."');";
			
			#var_dump($sql);
			$db->post($sql);
		}
	}*/
	function updatePrices($NewId=0,$Brand=''){
		if ($NewId && $Brand){
			$db = Register::get('db');
			$sql = "UPDATE ".DB_PREFIX."details SET BRAND_ID='".(int)$NewId."' WHERE BRAND_NAME LIKE '".mysql_real_escape_string($Brand)."' AND BRAND_ID=0";
			#var_dump($sql);
			$db->post($sql);
		}
	}
}
?>