<?php

class BrandsModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'brands');
	}
	
	public static function getAll() {
		$model = new BrandsModel();
		return $model->select()->order("`BRA_BRAND`")->fetchAll();
	}
	
	public static function getById($id) {
		$model = new BrandsModel();
		return $model->select()->where("BRA_ID=?",(int)$id)->fetchOne();
	}
	
	public static function find($name) {
		$model = new BrandsModel();
		return $model->select()->where("BRA_MFC_CODE LIKE '".$name."' OR BRA_BRAND LIKE '".$name."'")->fetchOne();
	}
}