<?php

class Merchants_listModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'settings_merchants_list');
	}
	
	public static function getBymcode($id) {
		$model = new Merchants_listModel();
		return $model->select()->where("mcode=?",mysql_real_escape_string($id))->fetchOne();
	}
	
	public static function getById($id) {
		$model = new Merchants_listModel();
		return $model->select()->where("id=?",(int)$id)->fetchOne();
	}
	
	public static function getByCode($id) {
		$model = new Merchants_listModel();
		return $model->select()->where("code=?",addslashes($id))->fetchOne();
	}
	public static function getByName($id) { 
		$model = new Merchants_listModel();
		return $model->select()->where("name=?",addslashes($id))->fetchOne();
	}
	public static function getByLimit($limit=3) {
		$model = new Merchants_listModel();
		return $model->select()->where("is_active=1")->order("`sort`")->limit(0,$limit)->fetchAll();
	}
	
	public static function getAll($page,$per_page) {
		$page = ($page - 1)*$per_page;
		$model = new Merchants_listModel();
		return $model->select()->where("is_active=1")->order("`sort`")->limit($page,$per_page)->fetchAll();		
	}
	
	public static function getByPaging() {
		$db = Register::get('db');
		$sql = "select count(*) cc from ".DB_PREFIX."settings_merchants_list where is_active='1';";
		$data = $db->get($sql);
		return isset($data['cc'])?$data['cc']:0;
	}
	
	public static function getFull() {
		$model = new Merchants_listModel();
		return $model->select()->where("is_active=1")->order("`sort`")->fetchAll();		
	}
}