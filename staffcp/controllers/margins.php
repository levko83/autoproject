<?php

class MarginsController  extends CmsGenerator {
	
	public $layout = 'global';
	
	public function index(){
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - группы наценок',URL_NOW);
		
		$this->prepareIndexData();
		$this->render('margins/list');
	}
	public function edit(){
		$id = (int)$this->request("id");
		$this->view->margs = $this->getById($id);
		$this->view->brands = $this->getBrands();
		$this->margins_fromto($id);
		$this->margins_brands($id);
		/* ********************************************************* */
		$this->prepareEditData();
		$this->render('margins/edit');
	}
	public function delete(){
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		if (!empty($id)){
			$this->model->delete(array($indexField => $id));
			$this->deleter($id);
			Logs::addLog(Acl::getAuthedUserId(),'Удаление группы наценок id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	public function delete_list(){
		$indexField = $this->dataModel->getIndexField();
		$ids = $this->request("delete_list",0);
		if (!empty($ids)) {
			foreach ($ids as $id) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
					$this->deleter($id);
					Logs::addLog(Acl::getAuthedUserId(),'Удаление группы наценок id:'.$id,URL_NOW);
				}
			}	
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	private function deleter($id){
		$db = Register::get('db');
		$db->query("DELETE FROM ".DB_PREFIX."margins_brands WHERE margin_id='".(int)$id."';");	
		$db->query("DELETE FROM ".DB_PREFIX."margins_fromto WHERE margin_id='".(int)$id."';");
	}
	/* ********************************************* */
	public function margins_fromto($id=0){
		
		$db = Register::get('db');
		
		$act = $this->request("act");
		$id = (int)$this->request("margin_id",$id);
		$margins_from = $this->request("margins_from",null);
		$margins_to = $this->request("margins_to",null);
		$margins_extra = $this->request("margins_extra",null);
		
		if (isset($act) && $act == 'add') {
			$this->layout = "ajax";
			
			if ($margins_from>=0 && $margins_to>=0) {
				$sql = "INSERT INTO ".DB_PREFIX."margins_fromto (`margin_id`,`from`,`to`,`margin`) VALUES ('".$id."','".mysql_real_escape_string($margins_from)."','".mysql_real_escape_string($margins_to)."','".mysql_real_escape_string($margins_extra)."');";
				$db->post($sql);
			}
			
			$this->getMarginsFromTo($id);
			
			$this->view->margs = $this->getById($id);
			
			Logs::addLog(Acl::getAuthedUserId(),'Добавление диапазона в группу id:'.$id,URL_NOW);
			$this->render('margins/margins-fromto');
		}
		
		$this->getMarginsFromTo($id);
	}
	private function getMarginsFromTo($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."margins_fromto WHERE margin_id='".$id."' ORDER BY `from` ASC;";
		$this->view->margins_fromto = $db->query($sql);
	}
	public function margins_fromto_delete(){
		$id = (int)$this->request("id");
		$db = Register::get('db');
		$sql = "DELETE FROM ".DB_PREFIX."margins_fromto WHERE id='".$id."';";
		$db->post($sql);
		
		Logs::addLog(Acl::getAuthedUserId(),'Удаление диапазона группы id:'.$id,URL_NOW);
		exit();
	}
	/* ********************************************* */
	public function margins_brands($id=0){
		
		$db = Register::get('db');
		
		$act = $this->request("act");
		$id = (int)$this->request("margin_id",$id);
		$brand_id = $this->request("brand_id",null);
		$margins_discount = $this->request("margin_brand",null);
		$margins_extra = $this->request("margin_brand_extra",null);
		$margin_dynamic = (int)$this->request("margin_brand_dynamic",0);
		
		if (isset($act) && $act == 'add') {
			#echo("?");
			#exit();
			
			$this->layout = "ajax";
			
			if ($brand_id) {
				$sql = "
				INSERT INTO ".DB_PREFIX."margins_brands 
					(`margin_id`,`brand`,`margin`,`extra`,`dynamic`) 
				VALUES 
					(
						'".$id."',
						'".mysql_real_escape_string($brand_id)."',
						'".mysql_real_escape_string($margins_discount)."',
						'".mysql_real_escape_string($margins_extra)."',
						'".mysql_real_escape_string($margin_dynamic)."'
					);
				";
				$db->post($sql);
				
				Logs::addLog(Acl::getAuthedUserId(),'Добавление скидки наценки бренда',URL_NOW);
			}
			
			$this->getMarginsBrands($id);
			
			$this->view->brands = $this->getBrands();
			$this->view->margs = $this->getById($id);
			
			$this->render('margins/margins-brands');
		}
		
		$this->getMarginsBrands($id);
	}
	private function getMarginsBrands($id){
		$db = Register::get('db');
		$sql = "SELECT IMB.*,IMB.brand AS brand_name FROM ".DB_PREFIX."margins_brands IMB WHERE IMB.margin_id='".$id."' ORDER BY `brand_name` ASC;";
		$this->view->margins_brands = $db->query($sql);
	}
	public function margins_brands_delete(){
		$id = (int)$this->request("id");
		$db = Register::get('db');
		$sql = "DELETE FROM ".DB_PREFIX."margins_brands WHERE id='".$id."';";
		$db->post($sql);
		Logs::addLog(Acl::getAuthedUserId(),'Удаление скидки наценки бренда id:'.$id,URL_NOW);
		exit();
	}
	/* ********************************************* */
	private function getById($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."margins WHERE `id`='".(int)$id."';";
		return $db->get($sql);
	}
	private function getBrands(){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."brands ORDER BY BRA_BRAND;";
		return $db->query($sql);
	}
	/* ******************************************** */
}
?>