<?php

class NewsModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'news');
	}
	
	public static function getById($id) {
		$model = new NewsModel();
		return $model->select()->where("id=?",(int)$id)->fetchOne();
	}
	
	public static function getByCode($id) {
		$model = new NewsModel();
		return $model->select()->where("code=?",addslashes($id))->fetchOne();
	}
	
	public static function getByLimit($limit=3) {
		$model = new NewsModel();
		return $model->select()->where("is_active=1 AND is_index=1")->order("`dt` DESC")->limit(0,$limit)->fetchAll();
	}

	public static function getByLimitAll($limit=3) {
		$model = new NewsModel();
		return $model->select()->where("is_active=1")->order("`dt` DESC")->limit(0,$limit)->fetchAll();
	}
	
	public static function getAll($page,$per_page) {
		$page = ($page - 1)*$per_page;
		$model = new NewsModel();
		return $model->select()->where("is_active=1")->order("`dt` DESC")->limit($page,$per_page)->fetchAll();		
	}
	
	public static function getByPaging() {
		$db = Register::get('db');
		$sql = "select count(*) cc from ".DB_PREFIX."news where is_active='1';";
		$data = $db->get($sql);
		return isset($data['cc'])?$data['cc']:0;
	}
}