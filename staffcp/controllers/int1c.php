<?php

class int1cController  extends CmsGenerator {
	
	public function index(){

		Logs::addLog(Acl::getAuthedUserId(),'Настройка 1С',URL_NOW);
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."settings_hidden WHERE `group`='1C' ORDER BY id";
		$this->view->data = $db->query($sql);
		
		$data = array();
		$files = glob("../1C/zakaz/*.csv");
		if ($files && count($files)) {
			foreach ($files as $filename) {
				$data []= array('name'=>$filename,"size"=>filesize($filename));
			}
		}
		$this->view->files = $data;
		
		if (isset($_POST['save']) && $_POST['save']){
			
			$code = $this->request("code");
			if (isset($code) && count($code)>0){
				foreach ($code as $id => $item){
					$db->post("UPDATE ".DB_PREFIX."settings_hidden SET `value`='".mysql_real_escape_string($item)."' WHERE id='".(int)$id."';");
				}
			}
			$this->redirectUrl('/staffcp/int1c/');
		}
	}
	/* *** */
	
	function order(){
		
		$number = $this->request("number",false);
		if ($number){
			
			$db = Register::get('db');
			
			$sql = "SELECT * FROM ".DB_PREFIX."cart_bills WHERE number LIKE '".mysql_real_escape_string($number)."';";
			$bill = $db->get($sql);
			
			$sql = "SELECT * FROM ".DB_PREFIX."cart WHERE scSID = '".(int)$bill['scSID']."';";
			$bill_items = $db->query($sql);
			
			$file = $bill['number'].";нет данных;".$bill['delivery'].";".$bill['account_id'].";".date("d.m.Y",$bill['dt'])."\n";
			if (isset($bill_items) && count($bill_items)>0){
				foreach ($bill_items as $item){
					$imp = ImportersModel::getById($item['fk']);
					$file .= str_replace(" ","",$item['article']).";".$item['brand'].";".strip_tags($item['descr_tecdoc']).";".$imp['name'].";".$item['price_purchase'].";".$item['price'].";".$item['count']."\n";
				}
			}
			#$file = iconv("utf-8","windows-1251//ignore",$file);
			file_put_contents("../1C/zakaz/zakaz-".$bill['number'].".csv",$file);
			
			Logs::addLog(Acl::getAuthedUserId(),'Выгрузка заказа в 1С #'.$bill['number'],URL_NOW);
			
			$this->redirectUrl('/staffcp/int1c');
		}
	}
	/* *** */
}
?>