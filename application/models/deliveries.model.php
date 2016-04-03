<?php

class DeliveriesModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'deliveries');
	}
		public static function getByName($id) { 
		$model = new DeliveriesModel();
		return $model->select()->where("name=?",addslashes($id))->fetchOne();
	}
	
	public static function getById($id,$list=array(),$getSum=0) {
		$model = new DeliveriesModel();
		$res = $model->select()->where("id=?",(int)$id)->fetchOne();
		
		$in_array = array();
		if (isset($list) && count($list)>0){
			foreach ($list as $dd){
				$in_array []= $dd['id'];
			}
			
			if (in_array($res['id'],$in_array)){
				return $res;
			}
			else {
				return DeliveriesModel::getDefault($getSum);
			}
		}
		else {
			return $res;
		}
	}
	
	public static function getDefault($cartSum=0,$office_id=false) {
		
		$db = Register::get('db');
		
		$sqlInject = "";
		if ($office_id){
				
			$officesIds = array();
			$sql = "SELECT fk_delivery FROM ".DB_PREFIX."delivery2office WHERE fk_office = '".mysql_real_escape_string($office_id)."';";
			$get = $db->get($sql);
			if (isset($get) && count($get)>0){
				foreach ($get as $oneOffice){
					$officesIds []= $oneOffice['fk_delivery'];
				}
			}
			if (count($officesIds)>0)
				$sqlInject = " AND id IN (".join(",", $officesIds).")";
		}
		
		$sql = "
			SELECT 
				* 
			FROM ".DB_PREFIX."deliveries 
			WHERE is_default=1 and 
				is_active = 1 AND
				(
					('".mysql_real_escape_string($cartSum)."' BETWEEN `view_if_price_from` AND `view_if_price_to`) OR 
					(`view_if_price_from` <= '".mysql_real_escape_string($cartSum)."' AND `view_if_price_to`=0)
				)
				$sqlInject
			ORDER BY sort
			;";
		$res = $db->get($sql);
		if ($res){
			return $res;
		}
		else {
			$model = new DeliveriesModel();
			return $model->select()->where("is_default=1")->fetchOne();
		}
	}
	
	public static function getAll($cartSum=0,$office_id=false) {
		
		$db = Register::get('db');
		
		$sqlInject = "";
		if ($office_id){
			
			$officesIds = array();
			$sql = "SELECT fk_delivery FROM ".DB_PREFIX."delivery2office WHERE fk_office = '".mysql_real_escape_string($office_id)."';";
			$get = $db->get($sql);
			if (isset($get) && count($get)>0){
				foreach ($get as $oneOffice){
					$officesIds []= $oneOffice['fk_delivery'];
				}
			}
			if (count($officesIds)>0)
				$sqlInject = " AND id IN (".join(",", $officesIds).")";
		}
		
		$sql = "
			SELECT 
				* 
			FROM ".DB_PREFIX."deliveries 
			WHERE 
				is_active = 1 AND
				(
					('".mysql_real_escape_string($cartSum)."' BETWEEN `view_if_price_from` AND `view_if_price_to`) OR 
					(`view_if_price_from`=0 AND `view_if_price_to`=0) OR
					(`view_if_price_from` <= '".mysql_real_escape_string($cartSum)."' AND `view_if_price_to`=0)
				)
				$sqlInject
			ORDER BY sort
			;";
		
		return $db->query($sql);
	}
}
?>