<?php

class AccountsModel extends Orm {
	var $data = array();
	var $cars = array();
	var $carsinfo = array();
	public function __construct() {
		parent::__construct(DB_PREFIX.'accounts');
	}
	
	public static function globalsearch($search=null){
		$model = new AccountsModel();
		$db = Register::get('db');
		$sql = "SHOW COLUMNS FROM `".DB_PREFIX.'accounts'."`;";
		$colums = $db->query($sql);
		$dataColums = array();
		if (isset($colums) && count($colums)>0) {
			foreach ($colums as $dd){
				$dataColums []= '`'.$dd['Field'].'`';
			}
			$searchString = join(" LIKE '%".mysql_real_escape_string(trim($search))."%' OR ", $dataColums)." LIKE '%".mysql_real_escape_string(trim($search))."%'";
			return $model->select()->where($searchString)->fetchAll();
		}
		return array();
	}
	
	/* ******************************** */
	public static function gettotalSum($id=0){
		$db = Register::get('db');
		$sql = "
			SELECT 
				SUM(`count` * `price`) AS total
			FROM ".DB_PREFIX."cart_bills CB
			JOIN ".DB_PREFIX."cart C ON C.scSID=CB.scSID
			JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=C.status
			WHERE 
				CB.account_id = '".(int)$id."' AND
				DS.type = '1'
		";
		$res = $db->get($sql);
		return (isset($res['total'])?$res['total']:0);
	}
	/* ******************************** */
	
	public static function getFilterList(){
		$db = Register::get('db');
		$sql = "SELECT id,email,name,phones FROM ".DB_PREFIX."accounts ORDER BY name;";
		$res = $db->query($sql);
		return $res;
	}
	public static function getAll() {
		$model = new AccountsModel();
		return $model->select()->order("`name`,`id`")->fetchAll();
	}
	public static function getAllScribers() {
		$model = new AccountsModel();
		return $model->select()->where("is_scribe=1")->fetchAll();
	}
	public static function getById($id){
		
		$model = new AccountsModel();
		
		if (isset($model->data[$id]) && count($model->data[$id])>0)
			return $model->data[$id];
		
		$res = $model->select()->where("id='".mysql_real_escape_string($id)."'")->fetchOne();
		if ($res['discountname_id']){
			$dnm = AccountsModel::getDiscountName($res['discountname_id']);
			$res ['discountname']= $dnm['name'];
		}
		if ($res['set_manager_id']){
			$uN = UsersModel::getById($res['set_manager_id']);
			$res ['managerName']= $uN['name'];
		}
		if ($res['office_id']){
			$uN = UsersModel::getOfficeById($res['office_id']);
			$res ['Office']= $uN['name'];
		}
		$model->data[$id] = $res;
		
		return $res;
	}
	public function getByIdCar($id){
		
		$model = new AccountsModel();
		
		if (isset($model->cars[$id]) && count($model->cars[$id])>0)
			return $model->cars[$id];
			
		$db = Register::get('db');
		$sql = "SELECT account_id FROM ".DB_PREFIX."accounts_cars WHERE id='".(int)$id."';";
		$res = $db->get($sql);
		
		$model->cars[$id] = $res;
		
		return $res;
	}
	public function getByIdCarInfo($id){
		
		$model = new AccountsModel();
		
		if (isset($model->carsinfo[$id]) && count($model->carsinfo[$id])>0)
			return $model->carsinfo[$id];
			
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars WHERE id='".(int)$id."';";
		$res = $db->get($sql);
		
		$model->carsinfo[$id] = $res;
		
		return $res;
	}
	public static function getDiscountName($id){
		$db = Register::get('db');
		$sql = "SELECT name FROM ".DB_PREFIX."accounts_discountnames WHERE id='".(int)$id."';";
		$res = $db->get($sql);
		return $res;
	}
	
	/* *********************************************** */
	public static function getByCookie() {
		$db = Register::get('db');
		$ip=getenv("HTTP_X_FORWARDED_FOR");
		if (empty($ip) || $ip=='unknown'){
			$ip=getenv("REMOTE_ADDR");
		}
		$email = (isset($_COOKIE['cook_email'])?$_COOKIE['cook_email']:'');
		$pass = (isset($_COOKIE['cook_pass'])?$_COOKIE['cook_pass']:'');
	
		if ($email && $pass) {
			#$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE email='".mysql_real_escape_string($email)."' AND MD5(CONCAT(MD5(pass),'$ip'))='".mysql_real_escape_string($pass)."';";
			$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE email='".mysql_real_escape_string($email)."' AND MD5(MD5(pass))='".mysql_real_escape_string($pass)."';";
			$res = $db->get($sql);
			if (count($res)>0) {
				return (int)$res['id'];
			}
			else
				return 0;
		}
		else
			return 0;
	}
	public static function setSCSID($key) {
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$db = Register::get('db');
		$db->post("UPDATE ".DB_PREFIX."accounts SET `CartScSID`='".mysql_real_escape_string($key)."' WHERE id='".(int)$accountFetchid."';");
		unset($_SESSION['__getTempScSID']);
	}
	public static function getSCSID() {
	
		$db = Register::get('db');
		$obj = new AccountsModel();
		$getTSID = $obj->getTempKeyscSID();
	
		/* CHECK THE SAME SID */
		if (CartModel::findTheSameSidForBill($getTSID)){
			$get_scSID = CartModel::findNewScSIDBill(mktime().'-'.rand(1000,9999));
			AccountsModel::setSCSID($get_scSID);
			$res = $db->get("SELECT CartScSID FROM ".DB_PREFIX."accounts WHERE `id`='".(int)$accountFetchid."';");
			$obj->setTempKeyscSID($res['CartScSID']);
		}
		/* ****************** */
	
		if ($getTSID){
			return $getTSID;
		}
		else {
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
			if ($accountFetchid){
	
				$res = $db->get("SELECT CartScSID FROM ".DB_PREFIX."accounts WHERE `id`='".(int)$accountFetchid."';");
	
				if ($res['CartScSID'] == 0 || $res['CartScSID'] == ''){
					$get_scSID = CartModel::findNewScSIDBill(CartModel::getDefaultscSID());
					AccountsModel::setSCSID($get_scSID);
					$res = $db->get("SELECT CartScSID FROM ".DB_PREFIX."accounts WHERE `id`='".(int)$accountFetchid."';");
				}
	
				/* CHECK THE SAME SID */
				if (CartModel::findTheSameSidForBill($res['CartScSID'])){
					$get_scSID = CartModel::findNewScSIDBill(mktime().'-'.rand(1000,9999));
					AccountsModel::setSCSID($get_scSID);
					$res = $db->get("SELECT CartScSID FROM ".DB_PREFIX."accounts WHERE `id`='".(int)$accountFetchid."';");
				}
				/* ****************** */
	
				$obj->setTempKeyscSID($res['CartScSID']);
				return $res['CartScSID'];
			}
			else
				return false;
		}
		return false;
	}
	private function getTempKeyscSID(){
		return (isset($_SESSION['__getTempScSID'])&&$_SESSION['__getTempScSID'])?$_SESSION['__getTempScSID']:false;
	}
	private function setTempKeyscSID($key){
		$_SESSION['__getTempScSID']=$key;
	}
	public static function getByIdCarsAll($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars WHERE account_id='".(int)$id."';";
		$res = $db->query($sql);
		return $res;
	}
}