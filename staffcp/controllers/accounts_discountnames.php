<?php

class Accounts_discountnamesController  extends CmsGenerator {
	
	public function save() {
		
		$form = $this->request('form');
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
		$form = $this->trimA($form);
		
		if (isset($form['code'])&&empty($form['code'])) {
			$form['code'] = strtolower($this->doTraslit($form['name']));
			$form['code'] = substr($form['code'],0,100);
		}
		if (empty($id)){
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление группы клиентов',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			$this->setMargins($id);
			$this->setIAccess($id);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление группы клиентов id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	
	public function edit() {
		$this->prepareEditData();
		
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$id = mysql_real_escape_string($id);
		
		$this->view->importers = ImportersModel::getAll();
		$this->view->save_margins = $this->getMargins($id);
		$this->view->save_iaccess = $this->getIAccess($id);
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."margins ORDER BY name;";
		$this->view->listMargins = $db->query($sql);
		
		$this->render('accounts_discountnames/edit');
	}
	#0#
	
	#1#
	private function setMargins($account_id=0){
		$db = Register::get('db');
		$importers = $this->request("importers");
		$db->post("DELETE FROM ".DB_PREFIX."accounts_margin2discountnames WHERE `discountname_id`='".(int)$account_id."';");
		if (isset($importers) && count($importers)>0){
			foreach ($importers as $importer_id=>$margin_id){
				if ($importer_id && $margin_id)
				$db->post("INSERT INTO ".DB_PREFIX."accounts_margin2discountnames (`discountname_id`,`importer_id`,`margin_id`) VALUES ('".(int)$account_id."','".(int)$importer_id."','".(int)$margin_id."');");
			}
			
		}
	}
	private function setIAccess($account_id=0){
		$db = Register::get('db');
		$iaccess = $this->request("iaccess");
		$db->post("DELETE FROM ".DB_PREFIX."accounts_iaccess_discountnames WHERE `discountname_id`='".(int)$account_id."';");
		if (isset($iaccess) && count($iaccess)>0){
			foreach ($iaccess as $importer_id){
				if ($importer_id)
				$db->post("INSERT INTO ".DB_PREFIX."accounts_iaccess_discountnames (`discountname_id`,`importer_id`) VALUES ('".(int)$account_id."','".(int)$importer_id."');");
			}
		}
	}
	#1#
	
	#2#
	private function getMargins($account_id=0){
		$db = Register::get('db');
		$res = $db->query("SELECT * FROM ".DB_PREFIX."accounts_margin2discountnames WHERE `discountname_id`='".(int)$account_id."';");
		$margins = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$margins [$dd['importer_id']]= $dd['margin_id'];
			}
		}
		return $margins;
	}
	private function getIAccess($account_id=0){
		$db = Register::get('db');
		$res = $db->query("SELECT importer_id FROM ".DB_PREFIX."accounts_iaccess_discountnames WHERE `discountname_id`='".(int)$account_id."';");
		$importer_id = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$importer_id []= $dd['importer_id'];
			}
		}
		return $importer_id;
	}
	#2#
}
?>