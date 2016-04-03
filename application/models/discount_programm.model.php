<?php

class Discount_programmModel extends Orm {
	
	private $db;
	
	function __construct(){
		$this->db = Register::get('db');
	}

	public static function checkDiscountLevel($account_id=0){
		$dp = new Discount_programmModel();
		if (SettingsModel::get('discount_programm_active')){
			#0 - берем дату установки скидки/регистрации
			$account_data = AccountsModel::getById($account_id);
			$start_time = ($account_data['datetime_set_discount_programm'])?$account_data['datetime_set_discount_programm']:$account_data['dt'];
			$end_time = strtotime("+1 year",$start_time);
			# - если дата установки скидки больше чем 1 год, то скидываем в самое начало дискпрограммы
			if (time() > $end_time){
				$discount_level = $dp->getDefaultDiscount();
				if ($discount_level){
					$dp->setDiscount($account_id,$discount_level,$account_data,true);
				}
			}
			else {
				#1 - берем сумму выполненных заказов по клиенту
				$account_sum = AccountsModel::gettotalSumPeriodYear($account_id,$start_time,$end_time);
				#2 - берем уже установленную скидку клиента
				$account_discount = ($account_data['is_firm'])?$account_data['firm_discount']:$account_data['discount'];
				#3 - проверяем уровень скидки
				$discount_level = $dp->getLevel($account_sum);
				if ($discount_level && $account_sum){
					#4 - проверяем их равенство
					if (($account_discount*-1) != ($discount_level*-1)){
						if (($account_discount*-1) <= ($discount_level*-1)){
							#5 - устанавливаем новую скидку!
							$dp->setDiscount($account_id,$discount_level,$account_data);
						}
					}
				}
			}
		}
		# end
	}
	
	public static function setDiscount($account_id=0,$discount=0,$account_data,$dt_active=false){
		$dp = new Discount_programmModel();
		$sql = "
			UPDATE ".DB_PREFIX."accounts 
			SET 
				".(($account_data['is_firm'])?'firm_discount':'discount')." = '".mysql_real_escape_string($discount)."'
				".(($dt_active)?",datetime_set_discount_programm = '".time()."'":"")."
			WHERE
				id = '".(int)$account_id."'; 
		";
		$dp->db->post($sql);
	}
	
	public static function getDefaultDiscount(){
		$dp = new Discount_programmModel();
		$sql = "SELECT discount FROM ".DB_PREFIX."discount_programm ORDER BY total_from ASC;";
		$res = $dp->db->get($sql);
		if ($res){
			return $res['discount'];
		}
		else
			return false;
	}
	
	public static function getLevel($sum=0){
		$dp = new Discount_programmModel();
		$sql = "SELECT discount FROM ".DB_PREFIX."discount_programm WHERE '".mysql_real_escape_string($sum)."' BETWEEN total_from AND total_to;";
		$res = $dp->db->get($sql);
		if ($res){
			return $res['discount'];
		}
		else
			return false;
	}
	
}

?>