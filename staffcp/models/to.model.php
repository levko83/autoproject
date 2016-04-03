<?php

class ToModel extends Orm {

	public static function getAllModelsByIdCount($id){
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) CC FROM ".DB_PREFIX."to_models WHERE car_id='".(int)$id."';";
		$res = $db->get($sql);
		return $res['CC'];
	}
	
	public static function getAllTypesByIdCount($id){
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) CC FROM ".DB_PREFIX."to_types WHERE model_id='".(int)$id."';";
		$res = $db->get($sql);
		return $res['CC'];
	}
	
	public static function getAllToByIdCount($id){
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) CC FROM ".DB_PREFIX."to WHERE type_id='".(int)$id."';";
		$res = $db->get($sql);
		return $res['CC'];
	}
	
	public static function getCatById($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."to WHERE id='".(int)$id."';";
		return $db->get($sql);
	}
	
	public static function getTypeById($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."to_types WHERE id='".(int)$id."';";
		return $db->get($sql);
	}
	
	public static function getModelById($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."to_models WHERE id='".(int)$id."';";
		return $db->get($sql);
	}
}

?>