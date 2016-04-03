<?php

class DocumentsController extends CmsGenerator {
	
	function index(){
		
		$numberBill = $this->request("billId",$billId);
		$f = $this->request("f",false);
		
		Logs::addLog(Acl::getAuthedUserId(),'Формирование документа '.$f.' id:'.$numberBill,URL_NOW);
		
		$doc = new Doc();
		if (!function_exists($doc->$f($numberBill))){
			$this->error404($exp='');
		}
		exit();
	}
	
}

?>