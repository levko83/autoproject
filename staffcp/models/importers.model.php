<?php

class ImportersModel extends Orm {
	
	var $data = array();
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'importers');
	}
	
	public static function getAll() {
		$model = new ImportersModel();
		return $model->select()->order("`name`,`id`")->fetchAll();
	}
	
	public static function getByCode($code) {
		$model = new ImportersModel();
		return $model->select()->where("code='".mysql_real_escape_string($code)."'")->fetchOne();
	}
	
	public function getById($id){
		if ($this->data[$id])
			return $this->data[$id];
		$model = new ImportersModel();
		$res = $model->select()->where("id='".mysql_real_escape_string($id)."'")->fetchOne();
		$this->data[$id] = $res;
		return $res;
	}
	
	public static function getByIdProducts($id) {
		$importers = new ImportersModel();
		if ($id){
			if(isset($importers->accounts[$id]))
				return $importers->accounts[$id];
			
			$model = new ImportersModel();
			$res = $model->select()->where("id=? or code=?",(int)$id,mysql_real_escape_string($id))->fetchOne();
			
			return $res;
		}
	}
}