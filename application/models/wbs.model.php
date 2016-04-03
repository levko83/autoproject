<?php
class WbsModel extends Orm {
	public function __construct() {
		parent::__construct(DB_PREFIX.'wbs');
	}
	public static function getConfig($file) {
		$model = new WbsModel();
		return $model->select()->where("file=?",mysql_real_escape_string($file))->fetchOne();
	}
	public static function activated() {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."wbs WHERE is_active='1';";
		return $db->query($sql);
	}
	public static function activatedForWbs($fks=array()) {
		$db = Register::get('db');
		if (count($fks)>0){
			$sql = "SELECT * FROM ".DB_PREFIX."wbs WHERE is_active='1' AND importer_id IN (".join(",",$fks).");";
			return $db->query($sql);
		}
		else 
			return array();
	}
}
?>