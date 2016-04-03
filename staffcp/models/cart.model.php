<?php

class CartModel extends Orm {
	
	public static function get($code) {
		$db = Register::get('db');
		$sql = "
		SELECT 
			cart.id as id,cart.createDT as createDT,cart.import_id as fk,cart.`count` as cc,cart.article as name,
			cart.brand as brand,cart.descr_tecdoc as descr,cart.price,cart.descr_tecdoc,imp.code as imp_code,
			cart.status_descr,cart.status,cart.is_account as IS_ACCOUNT,cart.min,ds.name ds_name,ds.color ds_color
		FROM ".DB_PREFIX."cart cart 
		LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
		LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
		WHERE cart.scSID='$code'
		;";
		return $db->query($sql);
	}
	
	public static function getNotConfirmed($page=1,$per_page=20) {
		$page = ($page - 1)*$per_page;
		
		$db = Register::get('db');
		$sql = "
		SELECT
			cart.scSID,
			cart.id as id,
			cart.createDT as createDT,
			cart.article as article,
			cart.brand as brand,
			cart.descr_tecdoc as descr,
			cart.`count` as cc,
			cart.price,
			cart.import_id as import_id,
			imp.code as imp_code,
			imp.name as imp_name,
			accounts.name account_name,
			accounts.id account_id
		FROM ".DB_PREFIX."cart cart
		LEFT JOIN ".DB_PREFIX."cart_bills cart_bills ON (cart_bills.scSID=cart.scSID)
		LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
		LEFT JOIN ".DB_PREFIX."accounts accounts ON (accounts.id=cart.account_id)
		WHERE cart_bills.scSID IS NULL
		ORDER BY cart.createDT DESC
		LIMIT ".(int)$page.",".(int)$per_page.";";
		return $db->query($sql);
	}
	public static function getNotConfirmedCount() {
		$db = Register::get('db');
		$sql = "
		SELECT
			COUNT(*) CC
		FROM ".DB_PREFIX."cart cart
		LEFT JOIN ".DB_PREFIX."cart_bills cart_bills ON (cart_bills.scSID=cart.scSID)
		WHERE cart_bills.scSID IS NULL
		;";
		$res = $db->get($sql);
		return (int)$res['CC'];
	}
	
	public static function unconfirmed_id_delete($id=0) {
		$db = Register::get('db');
		$db->post("DELETE FROM ".DB_PREFIX."cart WHERE id = '".(int)$id."';");
	}
	
	public static function unconfirmed_all_delete() {
		$db = Register::get('db');
		$sql = "SELECT cart.id 
				FROM ".DB_PREFIX."cart cart 
				LEFT JOIN ".DB_PREFIX."cart_bills cart_bills ON (cart_bills.scSID=cart.scSID) 
				WHERE cart_bills.scSID IS NULL";
		$resIds = $db->query($sql);
		$ids = array();
		if (count($resIds)>0){
			foreach ($resIds as $item){
				$ids []= $item['id'];
			}
			$sql = "DELETE FROM ".DB_PREFIX."cart WHERE id IN (".join(",", $ids).");";
			$db->post($sql);
		}
	}
	
	/* ******************************************* */
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