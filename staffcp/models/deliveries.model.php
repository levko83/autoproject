<?php

class DeliveriesModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'deliveries');
	}
	
	public static function getById($id) {
		$model = new DeliveriesModel();
		return $model->select()->where("id=?",(int)$id)->fetchOne();
	}
		public static function getByName($id) { 
		$model = new DeliveriesModel();
		return $model->select()->where("name=?",addslashes($id))->fetchOne();
	}
	
	public static function getAll() {
		$db = Register::get('db');
		$sql = "
			SELECT 
				* 
			FROM ".DB_PREFIX."deliveries 
			WHERE 
				is_active = 1
			ORDER BY sort
			;";
		return $db->query($sql);
	}
}
?>