<?php

class CartModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'cart');
	}
	
	public static function getCartBillScSID($scSID=false) {
		$db = Register::get('db');
		$sql = "SELECT *  FROM ".DB_PREFIX."cart_bills WHERE scSID = '".mysql_real_escape_string($scSID)."';";
		return $db->get($sql);
	}
	
	public static function getCartBillID($id=false) {
		$db = Register::get('db');
		$sql = "SELECT *  FROM ".DB_PREFIX."cart_bills WHERE id = '".mysql_real_escape_string($id)."';";
		return $db->get($sql);
	}
	
	public static function getCartItemsScSID($scSID=false) {
		$db = Register::get('db');
		$sql = "SELECT *  FROM ".DB_PREFIX."cart WHERE scSID = '".mysql_real_escape_string($scSID)."';";
		return $db->query($sql);
	}
	public static function getCartItemsScSIDSum($scSID=false) {
		$db = Register::get('db');
		$sql = "SELECT SUM( `count` * (IF(currency_rate,(price/currency_rate),price)) ) TOTAL  FROM ".DB_PREFIX."cart WHERE scSID = '".mysql_real_escape_string($scSID)."';";
		$ret = $db->get($sql);
		return isset($ret['TOTAL'])?$ret['TOTAL']:0;
	}
	
	public static function getOneItem($item_id=0) {
		$db = Register::get('db');
		$sql = "SELECT 
					cart.id as id,
					cart.createDT as createDT,
					cart.import_id as fk,
					cart.`count` as cc,
					cart.article as name,
					cart.brand as brand,
					cart.descr_tecdoc as descr,
					(IF(cart.currency_rate,(cart.price/cart.currency_rate),cart.price)) price,
					cart.price_purchase,
					cart.descr_tecdoc,
					imp.code as imp_code,
					cart.status_descr,
					cart.status,
					cart.is_account as IS_ACCOUNT,
					cart.min,
					cart.time_delivery_descr,
					
					ds.name ds_name,
					ds.color ds_color,
					
					cbill.account_id,
					cbill.number
				FROM 
					".DB_PREFIX."cart cart 
				LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
				LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
				LEFT JOIN ".DB_PREFIX."cart_bills cbill ON (cbill.scSID=cart.scSID)
				WHERE cart.id='$item_id'
				";
		return $db->get($sql);
	}
	
	public static function get($code) {
		$db = Register::get('db');
		$sql = "
			SELECT 
				cart.id as id,
				cart.id as cart_id,
				cart.createDT as createDT,
				cart.import_id as fk,
				cart.`count` as cc,
				cart.`price` as old_price,
				cart.article as name,
				cart.brand as brand,
				cart.descr_tecdoc as descr,
				(IF(cart.currency_rate,(cart.price/cart.currency_rate),cart.price)) price,
				cart.price_purchase,
				cart.descr_tecdoc,
				imp.code as imp_code,
				cart.status_descr,
				cart.status,
				cart.is_account as IS_ACCOUNT,
				cart.min,
				cart.time_delivery_descr,
				p.*,
				ds.name ds_name,
				ds.color ds_color
			FROM 
				".DB_PREFIX."cart cart 
			LEFT JOIN ".DB_PREFIX."importers imp on (imp.id=cart.import_id)
			LEFT JOIN ".DB_PREFIX."dic_statuses ds on (ds.id=cart.status)
			LEFT JOIN ".DB_PREFIX."products p on (cart.fk=p.tecdoc_id)
			WHERE cart.scSID='".mysql_real_escape_string($code)."'
		";
		
		// $first_sql =  $db->query($sql);
		// $res = $db->get($sql);
		// $sqli = "SELECT * FROM ".DB_PREFIX."products WHERE tecdoc_id='".$res['fk']."'";
		// $full_info = $db->query($sqli);
		
		 
		// $dd = $first_sql[0] + $full_info;
		// $dd = array_merge((array)$first_sql[0],(array)$full_info);
		
		// print("<pre>");
		// print_r($first_sql);
		// die();
		
		return $db->query($sql);
	}
	
	public static function getHistory($code) {
		$db = Register::get('db');
		$sql = "SELECT 
					cart.id as id,
					cart.createDT as createDT,
					cart.import_id as fk,
					cart.`count` as cc,
					cart.article as name,
					cart.brand as brand,
					cart.descr_tecdoc as descr,
					(IF(cart.currency_rate,(cart.price/cart.currency_rate),cart.price)) price,
					cart.descr_tecdoc,
					imp.code as imp_code,
					cart.status_descr,
					cart.status,
					cart.is_account as IS_ACCOUNT,
					cart.min,
					cart.time_delivery_descr,
					
					ds.name ds_name,
					ds.color ds_color
				FROM 
					".DB_PREFIX."cart cart 
				LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
				LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
				WHERE cart.scSID='$code'";		
		return $db->query($sql);
	}
	
	public static function xbox($code) {
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) as cc FROM ".DB_PREFIX."cart WHERE scSID='".mysql_real_escape_string($code)."';";
		$data = $db->get($sql);
		return (isset($data['cc']))?$data['cc']:0;
	}
	
	public static function xboxTotalSum($code) {
		// echo $code;
		$db = Register::get('db');
		$sql = "SELECT SUM( price * count ) as ss FROM w_cart WHERE scSID='".mysql_real_escape_string($code)."';";
		$data = $db->get($sql);
		// print_R($data);
		return (isset($data['ss']))?$data['ss']:0;
	}
	
	public static function getSum($code){
		$db = Register::get('db');
		$sql = "SELECT SUM( price * count ) as ss FROM ".DB_PREFIX."cart WHERE scSID='$code';";
		$data = $db->get($sql);
		return $data['ss'];
	}
	
	/* ******************** */
	
	public static function set_scSID($key){
		AccountsModel::setSCSID($key);
		$_SESSION['_scSID']=$key;
	}
	public static function get_scSID(){
		$accountSID = AccountsModel::getSCSID();
		if ($accountSID){
			return $accountSID;
		}
		else {
			return ((isset($_SESSION['_scSID']) && $_SESSION['_scSID'])?$_SESSION['_scSID']:false);
		}
	}
	public static function unset_scSID(){
		unset($_SESSION['_scSID']);
	}
	public static function getDefaultscSID(){
		return ((isset($_SESSION['_scSID']) && $_SESSION['_scSID'])?$_SESSION['_scSID']:false);
	}
	
	/* ******************************************* */
	/* ******************************************* */
	public static function findTheSameSid($sid=''){
		/* ********* */
		return false;
		/* ********* */
		$db = Register::get('db');
		$sql = "SELECT id FROM ".DB_PREFIX."cart WHERE scSID = '".mysql_real_escape_string($sid)."' AND scSID != '';";
		$res = $db->get($sql);
		if ($res)
			return true;
		else
			return false;
	}
	public static function findTheSameSidForBill($sid=''){
		$db = Register::get('db');
		$sql = "SELECT id FROM ".DB_PREFIX."cart_bills WHERE scSID = '".mysql_real_escape_string($sid)."' AND scSID != '';";
		$res = $db->get($sql);
		if ($res)
			return true;
		else
			return false;
	}
	public static function findNewScSID($sid=''){
		$chk = CartModel::findTheSameSid($sid);
		if ($chk){
			$sid = mktime().'-'.rand(1000,9999);
			CartModel::findNewScSID($sid);
		}
		return $sid;
	}
	public static function findNewScSIDBill($sid=''){
		$chk = CartModel::findTheSameSidForBill($sid);
		if ($chk){
			$sid = mktime().'-'.rand(1000,9999);
			CartModel::findNewScSIDBill($sid);
		}
		return $sid;
	}
}
?>