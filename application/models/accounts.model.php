<?php

class AccountsModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'accounts');
	}
	
	public static function getNewMessages($bill_number=0){
		$db = Register::get('db');
		$sql = "
		SELECT
			COUNT(*) CC
		FROM ".DB_PREFIX."cart_bills_messages
		WHERE
			bill_number='".mysql_real_escape_string($bill_number)."' AND is_client = 0 AND is_new = 1
		;";
		$res = $db->get($sql);
		return isset($res['CC'])?$res['CC']:0;
	}
	
	/* ******************************** */
	public static function gettotalSumPeriodYear($id=0,$starttime=0,$endtime=0){
		$db = Register::get('db');
		$sql = "
			SELECT
				SUM(`count` * `price`) AS total
			FROM ".DB_PREFIX."cart_bills CB
			JOIN ".DB_PREFIX."cart C ON C.scSID=CB.scSID
			JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=C.status
			WHERE
				CB.account_id = '".(int)$id."' AND
				DS.type = '1' AND
				CB.dt BETWEEN '".mysql_real_escape_string($starttime)."' AND '".mysql_real_escape_string($endtime)."'
		";
		$res = $db->get($sql);
		return (isset($res['total'])?$res['total']:0);
	}
	/* ******************************** */
	
	public static function vinsList($id) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."vin_details WHERE vin_id='".(int)$id."';";
		$res = $db->query($sql);
		return isset($res)?$res:false;
	}
	public static function getIAccessLimitsCodes($impids=array()){
		$db = Register::get('db');
		if (count($impids)>0){
			$sql = "SELECT code FROM ".DB_PREFIX."importers WHERE id IN (".join(",",$impids).");";
			$res = $db->query($sql);
		}
		$array = array();
		if (isset($res) && count($res)>0) {
			foreach ($res as $dd){
				if ($dd['code']) {
					$array []= $dd['code'];
				}
			}
		}
		return $array;
	}
	public static function getIAccessLimits($id=0){
		$db = Register::get('db');
		$sql = "SELECT importer_id FROM ".DB_PREFIX."accounts_iaccess WHERE account_id = '".(int)$id."';";
		$res = $db->query($sql);
		$array = array(0);
		if (isset($res) && count($res)>0) {
			foreach ($res as $dd){
				if ($dd['importer_id']) {
					$array []= $dd['importer_id'];
				}
			}
		}
		return $array;
	}
	public static function getIAccessLimitsDiscountsnames($id=0){
		$db = Register::get('db');
		$sql = "SELECT importer_id FROM ".DB_PREFIX."accounts_iaccess_discountnames WHERE discountname_id = '".(int)$id."';";
		$res = $db->query($sql);
		$array = array(0);
		if (isset($res) && count($res)>0) {
			foreach ($res as $dd){
				$array []= $dd['importer_id'];
			}
		}
		return $array;
	}
	public static function getIAccessLimitsOffices($id=0){
		$db = Register::get('db');
		$sql = "SELECT importer_id FROM ".DB_PREFIX."accounts_iaccess_offices WHERE office_id = '".(int)$id."';";
		$res = $db->query($sql);
		$array = array(0);
		if (isset($res) && count($res)>0) {
			foreach ($res as $dd){
				$array []= $dd['importer_id'];
			}
		}
		return $array;
	}
	public static function getById($id) {
		$model = new AccountsModel();
		$db = Register::get('db');
		// $sql = "
			// SELECT 
				
				// A.*,
				// U.name manager_name,
				// U.photo manager_photo,
				// U.contacts manager_contacts,
				// AD.name discountname,
				// AD.id discountname_id,
				// AD.is_limit_active dis_limit_active,
				// CURR.code currencyCode,
				// CURR.rate currencyRate
				
			// FROM ".DB_PREFIX."accounts A 
			// LEFT JOIN ".DB_PREFIX."_user U ON U.id=A.set_manager_id
			// LEFT JOIN ".DB_PREFIX."accounts_discountnames AD ON AD.id=A.discountname_id
			// LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=A.out_price_currency
			// WHERE A.id='".(int)$id."'
		// ;";
		
		$sql = "
			SELECT 
				
				A.*,
				U.name manager_name,
				U.photo manager_photo,
				U.contacts manager_contacts
				
			FROM ".DB_PREFIX."accounts A 
			LEFT JOIN ".DB_PREFIX."_user U ON U.id=A.set_manager_id
			WHERE A.id='".(int)$id."'
		;";
		
		return $db->get($sql);
	}
	/* *********************************************************************** */
	public static function getBills($id) {
		$db = Register::get('db');
		$sql = "
			SELECT 
				CB.id,
				(SELECT COUNT(*) FROM ".DB_PREFIX."cart C WHERE C.scSID=CB.scSID) CC2
			FROM ".DB_PREFIX."cart_bills CB
			WHERE 
				CB.account_id='".(int)$id."'
			HAVING CC2 > 0
			;";
		$res = $db->query($sql);
		return (int)isset($res)?count($res):0;
	}
	public static function fetchBills($id,$page=false,$per_page=false) {
		
		$iSqlLimit = '';
		if ($page && $per_page) {
			$page = ($page - 1)*$per_page;
			$iSqlLimit = ' LIMIT '.(int)$page.','.$per_page;
		}
		
		$db = Register::get('db');
		$accountRate = Register::get('accountRate');
		#(SUM( (IF(C.currency_rate,(C.price/C.currency_rate),C.price)) * C.count ) + (IF($accountRate,(CB.delivery_price/$accountRate),CB.delivery_price)) ) sumOrder
		$sql = "
		SELECT 
			CB.*,
			DS.name statusName,
			DS.color,
			COUNT(C.id) CC2,
			(SUM( ( C.price*C.count ) ))+ CB.delivery_price sumOrder
		FROM ".DB_PREFIX."cart_bills CB
		LEFT JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=CB.status
		JOIN ".DB_PREFIX."cart C ON C.scSID=CB.scSID
		WHERE CB.account_id='".(int)$id."'
		GROUP BY CB.id
		HAVING CC2 > 0
		ORDER BY CB.dt DESC
		$iSqlLimit
		;";
		
		return $db->query($sql);
	}

	public static function totalSum($id) {
		$db = Register::get('db');
		$sql = "
		SELECT
			(SUM(C.price*C.count) + CB.delivery_price) sumOrders
		FROM ".DB_PREFIX."cart_bills CB
		JOIN ".DB_PREFIX."cart C ON C.scSID=CB.scSID
		WHERE CB.account_id='".(int)$id."'
			GROUP BY CB.account_id
		;";
		return $db->get($sql);
	}
	public static function getHistoryAllElements($id,$search=array(),$page=false,$per_page=false){
		
		$LIMIT = $iSQL = "";
		$page = ($page - 1)*$per_page;
		
		if (
			(isset($search['from']) && $search['from']) 
			&& 
			(isset($search['to']) && $search['to'])
		) {
			$date_from = strtotime($search['from']);
			$date_to = strtotime($search['to']);
			$iSQL .= " AND (cb.dt BETWEEN '".mysql_real_escape_string($date_from)."' AND '".mysql_real_escape_string($date_to)."') ";
		}
		
		if (isset($search['status']) && $search['status'] == 'done'){
			$iSQL .= " AND (ds.type IN (1,3)) ";
			$LIMIT = "LIMIT ".(int)$page.",".(int)$per_page."";
		} else {
			if (!isset($search['number'])) // Ищем заказ целиком, не учитывая статусы
				$iSQL .= " AND (ds.type NOT IN (1,3) OR cart.status = 0) ";
			$LIMIT = "LIMIT ".(int)$page.",".(int)$per_page."";
		}
		
		if (isset($search['number']) && $search['number']){
			$iSQL .= " AND cb.number LIKE '".(int)$search['number']."' ";
		}
		
		if (isset($search['article']) && $search['article']){
			$iSQL .= " AND cart.article LIKE '%".mysql_real_escape_string($search['article'])."%' ";
		}
		
		if (isset($search['brand']) && $search['brand']){
			$iSQL .= " AND cart.brand LIKE '%".mysql_real_escape_string($search['brand'])."%' ";
		}
		
		if (isset($search['descr']) && $search['descr']){
			$iSQL .= " AND cart.descr_tecdoc LIKE '%".mysql_real_escape_string($search['descr'])."%' ";
		}
		
		$db = Register::get('db');
		$sql = "
			SELECT 
				cart.id as id,
				cart.createDT as createDT,
				cart.import_id as fk,
				cart.`count` as cc,
				cart.`price` as old_price,
				cart.article as name,
				cart.brand as brand,
				cart.descr_tecdoc as descr,
				cart.price price,
				cart.descr_tecdoc,
				cart.status_descr,
				cart.status,
				cart.is_account as IS_ACCOUNT,
				cart.balance_minus,
				cart.time_delivery_wait_dt,
				cart.time_delivery_descr,
				
				imp.code as imp_code,
				imp.name as impName,
				imp.name_price as impNameClient,
				
				cb.id bill_id,
				cb.scSID bill_scSID,
				cb.account_id bill_account_id,
				cb.status bill_status,
				cb.dt bill_dt,
				cb.number bill_number,
				cb.f1 bill_f1,
				cb.f2 bill_f2,
				cb.f3 bill_f3,
				cb.message bill_message,
				cb.delivery,
				cb.delivery_price,
				(SELECT (SUM( (IF(c1.currency_rate,(c1.price/c1.currency_rate),c1.price)) * c1.`count` )) FROM ".DB_PREFIX."cart c1 WHERE c1.scSID=cb.scSID AND c1.is_payback=0) ss,
				cb.md5_hash md5_hash,
				p.*,
				ds.name ds_name,
				ds.color ds_color,
				
				cart.is_payback
			FROM 
				".DB_PREFIX."cart cart 
				LEFT JOIN ".DB_PREFIX."cart_bills cb on cart.scSID=cb.scSID
				LEFT JOIN ".DB_PREFIX."importers imp on (imp.id=cart.import_id)
				LEFT JOIN ".DB_PREFIX."dic_statuses ds on (ds.id=cart.status)
				LEFT JOIN ".DB_PREFIX."products p on (cart.fk=p.tecdoc_id)
			WHERE 
				cb.account_id='".(int)$id."'
				$iSQL
			ORDER BY cb.dt DESC,cart.id DESC
			$LIMIT
			;";
		return $db->query($sql);
	}
	public static function getHistoryAllElementsCOUNTS($id,$search=array()){
	
		$iSQL = "";
		if ($search['from'] && $search['to']) {
			$date_from = strtotime($search['from']);
			$date_to = strtotime($search['to']);
			$iSQL .= " AND (cb.dt BETWEEN '".mysql_real_escape_string($date_from)."' AND '".mysql_real_escape_string($date_to)."') ";
		}
		if (isset($search['status']) && $search['status'] == 'done'){
			$iSQL .= " AND (ds.type IN (1,3)) ";
		} else {
			if (!isset($search['number']))
				$iSQL .= " AND (ds.type NOT IN (1,3) OR cart.status = 0) ";
		}
		if (isset($search['number']) && $search['number']){
			$iSQL .= " AND cb.number LIKE '".(int)$search['number']."' ";
		}
		if (isset($search['article']) && $search['article']){
			$iSQL .= " AND cart.article LIKE '%".mysql_real_escape_string($search['article'])."%' ";
		}
		if (isset($search['brand']) && $search['brand']){
			$iSQL .= " AND cart.brand LIKE '%".mysql_real_escape_string($search['brand'])."%' ";
		}
		if (isset($search['descr']) && $search['descr']){
			$iSQL .= " AND cart.descr_tecdoc LIKE '%".mysql_real_escape_string($search['descr'])."%' ";
		}
	
		$db = Register::get('db');
		$sql = "
		SELECT
			COUNT(*) ccc
		FROM
			".DB_PREFIX."cart cart
			LEFT JOIN ".DB_PREFIX."cart_bills cb on cart.scSID=cb.scSID
			LEFT JOIN ".DB_PREFIX."importers imp on (imp.id=cart.import_id)
			LEFT JOIN ".DB_PREFIX."dic_statuses ds on (ds.id=cart.status)
		WHERE
			cb.account_id='".(int)$id."'
				$iSQL
				ORDER BY cb.dt DESC,cart.id DESC
				;";
		return $db->get($sql);
	}
		
	public static function getCountActiveLost($id,$active=true,$group=false){
		$db = Register::get('db');
		
		$iSQL = "";
		if ($active) {
			$iSQL .= "  AND (ds.type NOT IN (1,3) OR cart.status = 0) ";
		} else {
			$iSQL .= "  AND (ds.type IN (1,3)) ";
		}
		if ($group) {
			$iSQL .= " GROUP BY cb.id ";
		}
		
		$sql = "
			SELECT 
				COUNT(*) cc
			FROM 
				".DB_PREFIX."cart cart 
				JOIN ".DB_PREFIX."cart_bills cb on cart.scSID=cb.scSID
				LEFT JOIN ".DB_PREFIX."dic_statuses ds on (ds.id=cart.status)
			WHERE 
				cb.account_id='".(int)$id."' 
			$iSQL
			;";
		$res = $db->get($sql);
		return (int)$res['cc'];
	}
	public static function getHistoryAllElementsBILLS($id){
		
		$db = Register::get('db');
		$sql = "
			select 
				cb.id bill_id,
				cb.dt bill_dt,
				cb.number bill_number
			from 
				".DB_PREFIX."cart_bills cb
			where cb.account_id='".(int)$id."' ORDER BY cb.dt DESC;";
		return $db->query($sql);
	}
	public static function getAccountBill($id=0,$bill=0){
		$db = Register::get('db');
		$sql = "
			SELECT 
				cb.*
			FROM 
				".DB_PREFIX."cart_bills cb
			WHERE 
				cb.account_id='".(int)$id."' AND cb.number LIKE '".mysql_real_escape_string($bill)."' 
			ORDER BY cb.dt DESC;";
		return $db->get($sql);
	}
	
	/* ********************************************************************** */
	
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
	public static function signin($email,$pass) {
		$model = new AccountsModel();
		return $model->select()->where("((`email`='".addslashes($email)."' AND `pass`='".addslashes($pass)."') OR (`phones`='".addslashes($email)."' AND `pass`='".addslashes($pass)."')) AND is_active='1'")->fetchOne();
	}
	public static function lastLogin($id) {
		$db = Register::get('db');
		$db->post("UPDATE ".DB_PREFIX."accounts SET `last_loginin`='".time()."' WHERE id='".(int)$id."';");
	}
	public static function setSCSID($key) {
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$db = Register::get('db');
		$db->post("UPDATE ".DB_PREFIX."accounts SET `CartScSID`='".mysql_real_escape_string($key)."' WHERE id='".(int)$accountFetchid."';");
		unset($_SESSION['__getTempScSID']);
	}
	public static function updateSCSID() {
		$tmpKey = getTempKeyscSID();
		if (isset($tmpKey)) {
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
			$accountFetchScSID = isset($_SESSION['account']['CartScSID'])?$_SESSION['account']['CartScSID']:$accountCookie['CartScSID'];
			$db = Register::get('db');
			$db->post("UPDATE ".DB_PREFIX."cart SET `scSID`='".mysql_real_escape_string($tmpKey)."', account_id='".(int)$accountFetchid."' WHERE scSID='".mysql_real_escape_string($accountFetchScSID)."';");
			unset($_SESSION['__getTempScSID']);
		}
	}
	public static function getSCSID() {
		
		$db = Register::get('db');
		$obj = new AccountsModel();
		$getTSID = $obj->getTempKeyscSID();
		
		/* CHECK THE SAME SID */
		if (CartModel::findTheSameSidForBill($getTSID)){
			$get_scSID = CartModel::findNewScSIDBill(time().'-'.rand(1000,9999));
			AccountsModel::setSCSID($get_scSID);
			
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
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
	public function unsetTempKeyscSID(){
		unset($_SESSION['__getTempScSID']);
	}
	
	/* ********************************************************************** */
	
	public function getMarginIsAccount(){
		return SettingsModel::get('extramargin_account');
	}
	public function getMarginIsFirm(){
		return SettingsModel::get('extramargin_account_firm');
	}
	public static function add($data) {
		
		$ipaddress = '';
		 if (isset($_SERVER['HTTP_CLIENT_IP']))
				 $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		 else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				 $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		 else if(isset($_SERVER['HTTP_X_FORWARDED']))
				 $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		 else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
				 $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		 else if(isset($_SERVER['HTTP_FORWARDED']))
				 $ipaddress = $_SERVER['HTTP_FORWARDED'];
		 else if(isset($_SERVER['REMOTE_ADDR']))
				 $ipaddress = $_SERVER['REMOTE_ADDR'];
		 else
				 $ipaddress = 'UNKNOWN';

	
		if (function_exists('geoip_country_name_by_name')) {
			$country = geoip_country_name_by_name($ipaddress);
		} else $country = "";

		$db = Register::get('db');
		$obj = new AccountsModel();
		
		//$data['city'] = Dic_citiesModel::find_add($data['city']);
		//$data['city'] = 'Ungheni';
		
		// $getOfficeID = $data['office_id'];
		// if (!$getOfficeID){
			// $getOfficeID = OfficesModel::getDefaultOfficeId();
			// $getOfficeID = $getOfficeID['id'];
		// }
		// $getManagerID = OfficesModel::getManagerOfOffice($getOfficeID);
		
		$confirm_sms_reg = SettingsModel::get('confirm_sms_reg');
		$registration_confirm = SettingsModel::get('registration_confirm');
		
		$sms_confirm = 0;
		if ($confirm_sms_reg){
			$sms_alert_active = SettingshiddenModel::get('sms_alert_active');
			if ($sms_alert_active){
				$sms_confirm = rand(100000,999999);
				$sms_params = array("code"=>$sms_confirm,"sitename"=>$_SERVER['SERVER_NAME']);
				SmsSystemHelper::sendSmsMessage(4,$sms_params,$data['phones']);
			} else {
				$confirm_sms_reg = 0;
			}
		}
		
		
		if (isset($data['is_firm']) && $data['is_firm']==1) {
			$sql = "
				INSERT INTO ".DB_PREFIX."accounts (
					`email`,
					`pass`,
					`name`,
					`phones`,
					`country`,
					`city`,
					`address`,
					`dt`,
					`is_active`,
					`is_firm`,
					`md5`,
					`firm_name`,
					`firm_inn`,
					`firm_kpp`,
					`firm_bank`,
					`firm_pc`,
					`firm_kc`,
					`firm_bnk`,
					`firm_ogrn`,
					`firm_okpo`,
					`firm_discount`,
					`is_scribe`,
					`office_id`,
					`set_manager_id`,
					`datetime_set_discount_programm`,
					`sms_confirm`,
					`zip`,
					`nachname`,
					`hausnummer`
				) VALUES (
					'".addslashes($data['email'])."',
					'".addslashes($data['pass1'])."',
					'".addslashes($data['name'])."',
					'".addslashes($data['phones'])."',
					'".addslashes($country)."',
					'".addslashes($data['city'])."',
					'".addslashes($data['address'])."',
					'".time()."',
					'1',
					'1',
					'".md5($data['email'])."',
					'".addslashes($data['firm_name'])."',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'".(int)$obj->getMarginIsFirm()."',
					'1',
					'',
					'',
					'".time()."',
					'".$sms_confirm."',
					'".addslashes($data['zip'])."',
					'".addslashes($data['nachname'])."',
					'".addslashes($data['hausnummer'])."'
				);";
		}
		else {
			$sql = "
				INSERT INTO ".DB_PREFIX."accounts (
					`social_identity`,
					`email`,
					`pass`,
					`name`,
					`phones`,
					`country`,
					`city`,
					`address`,
					`dt`,
					`is_active`,
					`md5`,
					`discount`,
					`is_scribe`,
					`office_id`,
					`set_manager_id`,
					`datetime_set_discount_programm`,
					`sms_confirm`,
					`zip`,
					`nachname`,
					`hausnummer`
				)  VALUES  (
					'".@addslashes($data['social_identity'])."',
					'".addslashes($data['email'])."',
					'".addslashes($data['pass1'])."',
					'".addslashes($data['name'])."',
					'".addslashes($data['phones'])."',
					'".addslashes($country)."',
					'".addslashes($data['city'])."',
					'".addslashes($data['address'])."',
					'".time()."',
					'1',
					'".md5($data['email'])."',
					'".(int)$obj->getMarginIsAccount()."',
					'1',
					'',
					'',
					'".time()."',
					'".$sms_confirm."',
					'".addslashes($data['zip'])."',
					'".addslashes($data['nachname'])."',
					'".addslashes($data['hausnummer'])."'
				);";
		}
		
		if ($db->query($sql)) {
			return $db->lastInsertId();
		}
		else {
			return false;
		}
	}
	public static function edit($id,$data) {
		
		$is_scribe = (isset($data['is_scribe'])&&$data['is_scribe'])?1:0;
		$db = Register::get('db');
		
		$sql = "
			UPDATE ".DB_PREFIX."accounts SET 
				`email`='".addslashes($data['email'])."',
				`name`='".addslashes($data['name'])."',
				`phones`='".addslashes($data['phones'])."',
				`country`='".addslashes($data['country'])."',
				`city`='".addslashes($data['city'])."',
				`nachname`='".addslashes($data['nachname'])."',
				`zip`='".addslashes($data['zip'])."',
				`hausnummer`='".addslashes($data['hausnummer'])."',
				`address`='".addslashes($data['address'])."',
				`is_scribe`='".(int)$is_scribe."'
			WHERE id='".(int)$id."'
		;";
		
		if ($db->query($sql)) {
			return true;
		}
		else {
			return false;
		}
	}
	public static function change($id,$data) {
		$db = Register::get('db');
		$sql = "UPDATE ".DB_PREFIX."accounts SET `pass`='".addslashes($data['pass1'])."' WHERE id='".(int)$id."';";
		if ($db->query($sql)) {
			return true;
		}
		else {
			return false;
		}
	}
	public static function find($email,$id=0) {
		$db = Register::get('db');
		if ($id)
		$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE email LIKE '".addslashes($email)."' AND id != '".$id."';";
		else
		$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE email LIKE '".addslashes($email)."';";
		return $db->query($sql);
	}
	public static function findPhone($phones='',$id=0) {
		$db = Register::get('db');
		if ($id)
		$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE phones LIKE '".addslashes($phones)."' AND id != '".$id."';";
		else
		$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE phones LIKE '".addslashes($phones)."';";
		return $db->query($sql);
	}
	public static function bill($id,$scSID,$number,$data) {
		$db = Register::get('db');
		
		$office_id = $set_manager_id = 0;
		$acc = AccountsModel::getById($id);
		if (empty($acc['email']) && $data['email']){
			$db->post("UPDATE ".DB_PREFIX."accounts SET email = '".addslashes($data['email'])."' WHERE id = '".(int)$id."';");
		}
		if ($acc){
			$office_id = $acc['office_id'];
			$set_manager_id = $acc['set_manager_id'];
		}
		else {
			$baseController = new BaseController();
			$office_id = $baseController->getOfficeId();
		}
		
		$sql = "INSERT INTO ".DB_PREFIX."cart_bills 
		(
			`scSID`,`account_id`,`manager_id`,
			`office_id`,`status`,`dt`,`number`,`message`,
			`f1`,`f2`,`f3`,`nachname`,
			`delivery`,`delivery_price`,`md5_hash`,
			`payment_name`,`delivery_addess`,`is_bill_byfrontend`
		) 
		VALUES (
			'".$scSID."','".(int)$id."','".(int)$set_manager_id."',
			'".(int)$office_id."','0','".time()."','".$number."','".addslashes($data['message'])."',
			'".addslashes($data['name'])."','".addslashes($data['phone'])."','".addslashes($data['email'])."','".addslashes($data['delivery']['price2'])."',
			'".addslashes($data['delivery']['name'])."','".addslashes($data['delivery']['price'])."','".md5("o.".$number)."',
			'".mysql_real_escape_string($data['paymentname'])."','".mysql_real_escape_string($data['address'])."','1'
		);";
		$db->query($sql);
	}
}

?>