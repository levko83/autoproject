<?php

class HarvesterClaasController  extends CmsGenerator {
	
	public $layout = 'global';
	
	function index() {
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела обработки прайсов',URL_NOW);
		
		$this->view->file_to_parse = isset($_SESSION['harvester']['file'])?$_SESSION['harvester']['file']:false;
		
		$this->view->list_cron = $this->getListFiles();
		$this->view->gai = $this->getAllImporters();
	}
	function getListFiles() {
		$data = array();
		$files = glob("./../".PRICE_PATH."/*.csv");
		if ($files && count($files)) {
			foreach ($files as $filename) {
				$data []= array('name'=>$filename,"size"=>filesize($filename));
			}
		}
		return $data;
	}
	/* ******************** */
	private function getAllImporters(){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."importers ORDER BY name;";
		return $db->query($sql);
	}
	private function getByIdImporter($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."importers WHERE `id`='".(int)$id."';";
		return $db->get($sql);
	}
}