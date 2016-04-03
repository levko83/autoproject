<?php
class UsersModel extends Orm {
	var $data = array();
	public static function getById($id){
		
		$obj = new UsersModel();
		
		if (isset($obj->data[$id]) && count($obj->data[$id])) {
			return $obj->data[$id];
		}
		else {
			$db = Register::get('db');
			$sql = "SELECT * FROM ".DB_PREFIX."_user WHERE id='".(int)$id."';";
			$data = $db->get($sql); 
			$obj->data[$id]=$data;
			return $data;
		}
	}
	public static function getManagers($type='2'){
		$db = Register::get('db');
		
		if (is_array($type)){

			$sql = "SELECT
					U.id,U.login,U.name,O.id office_id,O.name office,C.name city
				FROM ".DB_PREFIX."_user U
				LEFT JOIN ".DB_PREFIX."offices O ON O.id=U.office_id
				LEFT JOIN ".DB_PREFIX."dic_cities C ON C.id=O.city_id
				WHERE
					U.is_super IN (".join(",",$type).") ORDER BY city,office;";
		}
		else {

			$sql = "SELECT
					U.id,U.login,U.name,O.id office_id,O.name office,C.name city
				FROM ".DB_PREFIX."_user U
				LEFT JOIN ".DB_PREFIX."offices O ON O.id=U.office_id
				LEFT JOIN ".DB_PREFIX."dic_cities C ON C.id=O.city_id
				WHERE
					U.is_super='".(int)$type."' ORDER BY city,office;";
		}
		return $db->query($sql);
	}
	
	public static function getManagersByOffice($office_id=0,$type='2'){
		$db = Register::get('db');
		$sql = "SELECT 
					U.id,U.login,U.name,O.id office_id,O.name office,C.name city
				FROM ".DB_PREFIX."_user U 
				LEFT JOIN ".DB_PREFIX."offices O ON O.id=U.office_id
				LEFT JOIN ".DB_PREFIX."dic_cities C ON C.id=O.city_id
				WHERE 
					U.is_super='".(int)$type."' AND U.office_id='".(int)$office_id."' ORDER BY city,office;";
		return $db->query($sql);
	}
	
	public static function getOfficeById($office_id=0){
		$db = Register::get('db');
		$sql = "SELECT 
					*
				FROM ".DB_PREFIX."offices
				WHERE 
					id='".(int)$office_id."';";
		return $db->get($sql);
	}
}
?>