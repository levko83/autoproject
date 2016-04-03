<?php
class Dic_citiesModel extends Orm {
	public function __construct() {
		parent::__construct(DB_PREFIX.'dic_cities');
	}
	public static function getById($id) {
		$model = new Dic_citiesModel();
		return $model->select()->where("`id`=?",(int)$id)->fetchOne();
	}
	public static function getAll() {
		$model = new Dic_citiesModel();
		return $model->select()->where("`is_active`=?",(int)1)->order("`sort`")->fetchAll();
	}
	public static function find($name){
		$model = new Dic_citiesModel();
		$res = $model->select()->fields('id')->where("`name` LIKE ?",mysql_real_escape_string($name))->fetchOne();
		return $res['id'];
	}
	public static function find_add($name){
		$model = new Dic_citiesModel();
		$res = $model->select()->fields('id')->where("`name` LIKE ?",mysql_real_escape_string($name))->fetchOne();
		if ($res) {
			return $res['id'];
		}
		else {
			$db = Register::get('db');
			$sql = "INSERT INTO ".DB_PREFIX."dic_cities (`name`,`is_active`) VALUES ('".mysql_real_escape_string($name)."','1');";
			$db->post($sql);
			return $db->lastInsertId();
		}
	}
}
?>