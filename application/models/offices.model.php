<?php
class OfficesModel extends Orm {
	public function __construct() {
		parent::__construct(DB_PREFIX.'offices');
	}
	public static function getById($id) {
		$model = new OfficesModel();
		return $model->select()->where("`id`=?",(int)$id)->fetchOne();
	}
	public static function getAllByCityId($id) {
		$model = new OfficesModel();
		return $model->select()->where("`city_id`=?",(int)$id)->order("`name`")->fetchAll();
	}
	public static function getAllByCityIdOne($id) {
		$db = Register::get('db');
		$sql = "
			SELECT 
				id
			FROM ".DB_PREFIX."offices O
			WHERE
				O.city_id = '".(int)$id."' AND 
				(SELECT COUNT(*) FROM ".DB_PREFIX."_user U WHERE U.office_id=O.id) > 0
			ORDER BY RAND()
		";
		return $db->get($sql);
	}
	public static function getDefaultOfficeId(){
		$db = Register::get('db');
		$sql = "
			SELECT 
				O.id,O.city_id
			FROM ".DB_PREFIX."dic_cities DC
			LEFT JOIN ".DB_PREFIX."offices O ON O.city_id = DC.id
			WHERE DC.is_default = 1;
		";
		return $db->get($sql);
	}
	public static function getOffices($city_id=0){
		$isql = null;
		if ($city_id){
			$isql = " WHERE O.city_id = '".(int)$city_id."' ";
		}
		$db = Register::get('db');
		$sql = "
			SELECT 
				O.id,O.name office,DC.name city
			FROM ".DB_PREFIX."offices O
			JOIN ".DB_PREFIX."dic_cities DC ON O.city_id = DC.id
			$isql
			ORDER BY O.name;
		";
		return $db->query($sql);
	}
	public static function getManagerOfOffice($id_office=0){
		$db = Register::get('db');
		$sql = "
			SELECT 
				DISTINCT U.id,
				(SELECT COUNT(*) FROM ".DB_PREFIX."accounts A WHERE A.set_manager_id=U.id) CC 
			FROM `".DB_PREFIX."_user` U 
			WHERE 
				U.office_id = 1 AND 
				U.is_super != 1 
		";
		$res = $db->query($sql);
		$CC = $manager = 0;
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				if ($dd['CC'] <= $CC){
					$manager = $dd['id'];
				}
				$CC = $dd['CC'];
			}
		}
		return (int)$manager;
	}
}
?>