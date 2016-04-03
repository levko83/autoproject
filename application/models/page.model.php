<?php

class PageModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'page');
	}
	
	public static function getByCode($code) {
		$model = new PageModel();
		return $model->select()->where("code=?",addslashes($code))->fetchOne();
	}
	
	public static function getAll() {
		$model = new PageModel();
		return $model->select()->where("is_active=1")->order("`sort`,`name` ASC")->fetchAll();		
	}
	
	public static function getAllList() {
		$db = Register::get('db');
		$sql = "SELECT id,code,name FROM `".DB_PREFIX."page` WHERE `is_active`=1 ORDER BY `sort`,`name` ASC";
		return $db->query($sql);
		#$model = new PageModel();
		#return $model->select()->where("is_active=1")->order("`sort`,`name` ASC")->fetchAll();		
	}
}