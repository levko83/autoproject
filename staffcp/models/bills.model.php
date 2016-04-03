<?php

class BillsModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'cart_bills');
	}

	public static function getNewMessages($bill_number=0){
		$db = Register::get('db');
		$sql = "
		SELECT 
			COUNT(*) CC 
		FROM ".DB_PREFIX."cart_bills_messages 
		WHERE 
			bill_number='".mysql_real_escape_string($bill_number)."' AND is_client = 1 AND is_new = 1
		;";
		$res = $db->get($sql);
		return isset($res['CC'])?$res['CC']:0;
	}

	/* *********************************************************************** */
	
	public static function getHistory($params,$page=1,$per_page=999,$lineReturn=true) {
		
		$page = ($page - 1)*$per_page;
		
		$sql_more = $sql_more2 = '';
	
		#берем определенные записи таблицы w_cart
		if (isset($params['cart_ids']) && $params['cart_ids']){
			$sql_more .= " AND cart.id IN (".join(",", $params['cart_ids']).") ";
		}
		
		#1
		if (isset($params['bill_id']) && $params['bill_id']){
			$sql_more .= " AND cb.id='".(int)$params['bill_id']."' ";
		}
		#2
		if (isset($params['scSID']) && $params['scSID']){
			$sql_more .= " AND cart.scSID='".addslashes($params['scSID'])."' ";
		}
		#3
		if ((isset($params['status']) && count($params['status'])>0) && (isset($params['archive']) && $params['archive'])){
			$sql_more .= " AND (ds.id IN (".join(",",$params['status']).") OR ds.type='".(int)$params['archive']."') ";
			$sql_more2 .= " AND (cart2.status NOT IN (".join(",",$params['status']).") OR dicStatuses.type = '".(int)$params['archive']."') ";
		}
		elseif (isset($params['status']) && count($params['status'])>0) {
									
			$statuses_array = array();
			if ($params['status'] && count($params['status'])>0){
				foreach ($params['status'] as $pst){
					if ($pst == -1){
						$statuses_array []= $pst;
						$pst = 0;
					}
					$statuses_array []= $pst;
				}
			}
			$params['status'] = $statuses_array;
								
			$sql_more .= " AND (ds.id IN (".join(",",$params['status']).") ".(in_array(-1,$params['status'])?'OR cart.status = 0':'')." ) ";
			$sql_more2 .= " AND (cart2.status IN (".join(",",$params['status']).") ".(in_array(-1,$params['status'])?'OR cart.status = 0':'')." ) ";
		}
		elseif (isset($params['archive']) && $params['archive']) {
			$sql_more .= " AND (ds.type = '1' OR ds.type = '3')";
			$sql_more2 .= " AND (dicStatuses.type = '1' OR dicStatuses.type = '3')";
		}
		elseif(
			!isset($params['scSID']) && 
				!isset($params['checked_statuses']) && 
					!isset($params['status_new']) &&
						!isset($params['number']) &&
							!isset($_REQUEST['export'])
		){
			$sql_more .= " AND (((ds.type != '1' AND ds.type != '3') OR ds.type IS NULL) OR cart.status = 0) ";
			$sql_more2 .= " AND (((dicStatuses.type != '1' AND dicStatuses.type != '3') OR dicStatuses.type is NULL)) ";
		}
	
		if (isset($params['only_new']) && $params['only_new']){
			$sql_more .= " AND ds.type IS NULL ";
			$sql_more2 .= " AND dicStatuses.type IS NULL ";
		}
	
		#4
		if (isset($params['city']) && $params['city']){
			$sql_more .= " AND acc.city='".(int)$params['city']."' ";
		}
	
		#5
		if (isset($params['account_id']) && $params['account_id']){
			$sql_more .= " AND cb.account_id='".(int)$params['account_id']."' ";
		}
	
		#6
		if (isset($params['manager_id']) && $params['manager_id']){
			$sql_more .= " AND ( cb.office_id = '".(int)$params['office_id']."' OR cb.manager_id = '".(int)$params['manager_id']."' ) ";
		}
	
		#7
		if (isset($params['number']) && $params['number']){
			$sql_more .= " AND cb.number = '".(int)$params['number']."' ";
		}
	
		if (isset($params['only_new']) && $params['only_new']){
			$sql_more .= " AND ds.type IS NULL ";
		}
	
		#8
		if (isset($params['phonenumber']) && $params['phonenumber']){
			$sql_more .= " AND cb.f2 LIKE '%".mysql_real_escape_string($params['phonenumber'])."%' ";
		}
	
		#9
		if ((isset($params['dt_from']) && $params['dt_from']) && (isset($params['dt_to']) && $params['dt_to'])){
	
			$params['dt_from'] = strtotime($params['dt_from']);
			$params['dt_to'] = strtotime($params['dt_to']);
									
			$sql_more .= " AND cb.dt BETWEEN '".mysql_real_escape_string($params['dt_from'])."' AND '".mysql_real_escape_string($params['dt_to'])."' ";
		}
		elseif ((isset($params['dt_from']) && $params['dt_from'])){
			
			$params['dt_from'] = strtotime($params['dt_from']);
			$sql_more .= " AND cb.dt >= '".mysql_real_escape_string($params['dt_from'])."' ";
		}
		elseif ((isset($params['dt_to']) && $params['dt_to'])){
			
			$params['dt_to'] = strtotime($params['dt_to']);
			$sql_more .= " AND cb.dt <= '".mysql_real_escape_string($params['dt_to'])."' ";
		}

		#10
		if (isset($params['checked_statuses']) && count($params['checked_statuses'])>0){
			$sql_more .= " AND ((ds.id IN (".join(",",$params['checked_statuses']).") OR ds.type=1) ".(isset($params['status_new'])?'OR cart.status = 0':'')." ) ";
		}
		#11
		elseif (isset($params['status_new'])){
			$sql_more .= " AND cart.status = 0 ";
		}
		
		#12 *******************************************************************
		if (isset($params['article']) && $params['article']){
			
			$some_articles = explode(",",$params['article']);
			if (count($some_articles)>1){
				$sql_more .= " AND (";
				$z=0; foreach ($some_articles as $article_one){ $z++;
					$sql_more .= "( cart.article LIKE '".mysql_real_escape_string(FuncModel::stringfilter($article_one))."' OR cart.article LIKE '".mysql_real_escape_string($article_one)."' ) ".($z!=count($some_articles)?' OR ':'');
				}
				$sql_more .= ")";
			}
			else {
				$sql_more .= " AND ( cart.article LIKE '".mysql_real_escape_string(FuncModel::stringfilter($params['article']))."' OR cart.article LIKE '".mysql_real_escape_string($params['article'])."' ) ";
			}
			
		}
		#13
		if (isset($params['brand']) && $params['brand']){
			$sql_more .= " AND cart.brand LIKE '%".mysql_real_escape_string(str_replace(" ", "%", $params['brand']))."%' ";
		}
		#14
		if (isset($params['descr']) && $params['descr']){
			$sql_more .= " AND cart.descr_tecdoc LIKE '%".mysql_real_escape_string(str_replace(" ", "%", $params['descr']))."%' ";
		}
		#**********************************************************************
	
// 		var_dump($sql_more);
		
		#SORT
		if (isset($params['orderby']) && $params['orderby']){
			$sort = $params['orderby'];
		} else {
			$sort = " cb.dt DESC,cart.id DESC ";
		}
	
		#ITEM ID
		if (isset($params['item_id']) && $params['item_id']){
			$sql_more .= " AND cart.id='".(int)$params['item_id']."' ";
		}
	
		if (isset($params['is_payback']) && $params['is_payback']){
			$sql_more .= " AND cart.is_payback='0' ";
		}
		
		#DONE DATE FOR SORT
		if ((isset($params['done_dt_from']) && $params['done_dt_from']) && (isset($params['done_dt_to']) && $params['done_dt_to'])){
			$params['done_dt_from'] = strtotime($params['done_dt_from']);
			$params['done_dt_to'] = strtotime($params['done_dt_to']);
			$sql_more .= " AND (cb.bill_dt_closed BETWEEN '".mysql_real_escape_string($params['done_dt_from'])."' AND '".mysql_real_escape_string($params['done_dt_to'])."') ";
		}
		elseif ((isset($params['done_dt_from']) && $params['done_dt_from'])){
			$params['done_dt_from'] = strtotime($params['done_dt_from']);
			$sql_more .= " AND cb.bill_dt_closed >= '".mysql_real_escape_string($params['done_dt_from'])."' ";
		}
		elseif ((isset($params['done_dt_to']) && $params['done_dt_to'])){
			$params['done_dt_to'] = strtotime($params['done_dt_to']);
			$sql_more .= " AND cb.bill_dt_closed <= '".mysql_real_escape_string($params['done_dt_to'])."' ";
		}
		
		#15 New sort By Office,Manager,Importer
		if (isset($params['importer_id']) && $params['importer_id']){
			$sql_more .= " AND cart.import_id = '".(int)$params['importer_id']."' ";
		}
		if (isset($params['office_id']) && $params['office_id']){
			$sql_more .= " AND cb.office_id = '".(int)$params['office_id']."' ";
		}
		if (isset($params['manager_id']) && $params['manager_id']){
			$sql_more .= " AND cb.manager_id = '".(int)$params['manager_id']."' ";
		}
		
		$db = Register::get('db');
		$sql = "
		SELECT
			(
				SELECT 
					COUNT(*) 
				FROM ".DB_PREFIX."cart cart2 
				LEFT JOIN ".DB_PREFIX."dic_statuses dicStatuses ON cart2.status=dicStatuses.id 
				WHERE cart2.scSID=cb.scSID 
			) ccItems,
				
			cb.scSID bill_scSID,
			cb.number bill_number,

			cart.id as id,
			cart.createDT as createDT,
			cart.import_id as fk,
			cart.`count` as cc,
			cart.article as article,
			cart.brand as brand,
			cart.descr_tecdoc as descr,
			cart.price,
			cart.descr_tecdoc,
			cart.status_descr,
			cart.status,
			cart.is_account as IS_ACCOUNT,
			cart.is_account_accept as IS_ACCOUNT_ACCEPT,
			cart.balance_minus,
			cart.price_purchase,
			cart.sold,
			cart.time_delivery_wait_dt as time_delivery_wait_dt,
			cart.time_delivery_descr as time_delivery_descr,
			cart.is_payback,
	
			cb.id bill_id,
			cb.account_id bill_account_id,
			cb.car_id car_id,
			cb.manager_id manager_id,
			cb.office_id office_id,
			cb.status bill_status,
			cb.dt bill_dt,
			cb.f1 bill_f1,
			cb.f2 bill_f2,
			cb.f3 bill_f3,
			cb.message bill_message,
			cb.delivery bill_delivery,
			cb.delivery_price bill_delivery_price,
			cb.dt bill_dt,
			cb.delivery_set_balance,
			cb.prepayment,
			cb.is_paid,
			cb.md5_hash,
	
			cb.time_give_order,
			cb.time_from,
			cb.time_to,
			cb.payment_name,
			cb.delivery_addess,
	
	
			imp.id as imp_id,
			imp.code as imp_code,
			imp.name as imp_name,
			imp.delivery as delivery,
			imp.name_price as namePrice,
	
			ds.id ds_id,
			ds.name ds_name,
			ds.color ds_color,
			ds.type ds_type,
	
			acc.id acc_id,
			acc.balance acc_balance
		FROM
		".DB_PREFIX."cart cart
		JOIN ".DB_PREFIX."cart_bills cb ON cart.scSID=cb.scSID
		LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
		LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
		LEFT JOIN ".DB_PREFIX."accounts acc ON (acc.id=cb.account_id)
		WHERE 1=1 $sql_more
		ORDER BY $sort
		LIMIT ".(int)$page.",".(int)$per_page."
		;";
		
// 		echo('<pre>');
// 		var_dump($sql);
// 		echo('</pre>');
// 		die();
		
		$return = $db->query($sql);
		
		if ($lineReturn){
			return $return;
		}
		
		$ret = array();
		if (isset($return) && count($return)>0){
			foreach ($return as $dd){
				$ret [$dd['bill_number']][]= $dd;
			}
		}
		return $ret;
	}
	public static function getHistoryCount($params) {
		
		$sql_more = $sql_more2 = '';
	
		#1
		if (isset($params['bill_id']) && $params['bill_id']){
			$sql_more .= " AND cb.id='".(int)$params['bill_id']."' ";
		}
		#2
		if (isset($params['scSID']) && $params['scSID']){
			$sql_more .= " AND cart.scSID='".addslashes($params['scSID'])."' ";
		}
		#3
		if ((isset($params['status']) && count($params['status'])>0) && (isset($params['archive']) && $params['archive'])){
			$sql_more .= " AND (ds.id IN (".join(",",$params['status']).") OR ds.type='".(int)$params['archive']."') ";
			$sql_more2 .= " AND (cart2.status NOT IN (".join(",",$params['status']).") OR dicStatuses.type = '".(int)$params['archive']."') ";
		}
		elseif (isset($params['status']) && count($params['status'])>0) {
									
			$statuses_array = array();
			if ($params['status'] && count($params['status'])>0){
				foreach ($params['status'] as $pst){
					if ($pst == -1){
						$statuses_array []= $pst;
						$pst = 0;
					}
					$statuses_array []= $pst;
				}
			}
			$params['status'] = $statuses_array;
								
			$sql_more .= " AND (ds.id IN (".join(",",$params['status']).") ".(in_array(-1,$params['status'])?'OR cart.status = 0':'')." ) ";
			$sql_more2 .= " AND (cart2.status IN (".join(",",$params['status']).") ".(in_array(-1,$params['status'])?'OR cart.status = 0':'')." ) ";
		}
		elseif (isset($params['archive']) && $params['archive']) {
			$sql_more .= " AND (ds.type = '1' OR ds.type = '3')";
			$sql_more2 .= " AND (dicStatuses.type = '1' OR dicStatuses.type = '3')";
		}
		elseif(!isset($params['scSID']) && !isset($params['checked_statuses']) && !isset($params['status_new'])){
			$sql_more .= " AND (((ds.type != '1' AND ds.type != '3') OR ds.type IS NULL) OR cart.status = 0) ";
			$sql_more2 .= " AND (((dicStatuses.type != '1' AND dicStatuses.type != '3') OR dicStatuses.type is NULL)) ";
		}
	
		if (isset($params['only_new']) && $params['only_new']){
			$sql_more .= " AND ds.type IS NULL ";
			$sql_more2 .= " AND dicStatuses.type IS NULL ";
		}
	
		#4
		if (isset($params['city']) && $params['city']){
			$sql_more .= " AND acc.city='".(int)$params['city']."' ";
		}
	
		#5
		if (isset($params['account_id']) && $params['account_id']){
			$sql_more .= " AND cb.account_id='".(int)$params['account_id']."' ";
		}
	
		#6
		if (isset($params['manager_id']) && $params['manager_id']){
			$sql_more .= " AND ( cb.office_id = '".(int)$params['office_id']."' OR cb.manager_id = '".(int)$params['manager_id']."' ) ";
		}
	
		#7
		if (isset($params['number']) && $params['number']){
			$sql_more .= " AND cb.number = '".(int)$params['number']."' ";
		}
	
		if (isset($params['only_new']) && $params['only_new']){
			$sql_more .= " AND ds.type IS NULL ";
		}
	
		#8
		if (isset($params['phonenumber']) && $params['phonenumber']){
			$sql_more .= " AND cb.f2 LIKE '%".mysql_real_escape_string($params['phonenumber'])."%' ";
		}
	
		#9
		if ((isset($params['dt_from']) && $params['dt_from']) && (isset($params['dt_to']) && $params['dt_to'])){
	
			$params['dt_from'] = strtotime($params['dt_from']);
			$params['dt_to'] = strtotime($params['dt_to']);
									
			$sql_more .= " AND cb.dt BETWEEN '".mysql_real_escape_string($params['dt_from'])."' AND '".mysql_real_escape_string($params['dt_to'])."' ";
		}
		elseif ((isset($params['dt_from']) && $params['dt_from'])){
			
			$params['dt_from'] = strtotime($params['dt_from']);
			$sql_more .= " AND cb.dt >= '".mysql_real_escape_string($params['dt_from'])."' ";
		}
		elseif ((isset($params['dt_to']) && $params['dt_to'])){
			
			$params['dt_to'] = strtotime($params['dt_to']);
			$sql_more .= " AND cb.dt <= '".mysql_real_escape_string($params['dt_to'])."' ";
		}
	
		#10
		if (isset($params['checked_statuses']) && count($params['checked_statuses'])>0){
			$sql_more .= " AND ((ds.id IN (".join(",",$params['checked_statuses']).") OR ds.type=1) ".(isset($params['status_new'])?'OR cart.status = 0':'')." ) ";
		}
		#11
		elseif (isset($params['status_new'])){
			$sql_more .= " AND cart.status = 0 ";
		}
	
		#SORT
		if (isset($params['orderby']) && $params['orderby']){
			$sort = $params['orderby'];
		} else {
			$sort = " cb.dt DESC,cart.id DESC ";
		}

		#ITEM ID
		if (isset($params['item_id']) && $params['item_id']){
			$sql_more .= " AND cart.id='".(int)$params['item_id']."' ";
		}

		if (isset($params['is_payback']) && $params['is_payback']){
			$sql_more .= " AND cart.is_payback='0' ";
		}
		
		#12 *******************************************************************
		if (isset($params['article']) && $params['article']){
			
			$some_articles = explode(",",$params['article']);
			if (count($some_articles)>1){
				$sql_more .= " AND (";
				$z=0; foreach ($some_articles as $article_one){ $z++;
					$sql_more .= "( cart.article LIKE '".mysql_real_escape_string(FuncModel::stringfilter($article_one))."' OR cart.article LIKE '".mysql_real_escape_string($article_one)."' ) ".($z!=count($some_articles)?' OR ':'');
				}
				$sql_more .= ")";
			}
			else {
				$sql_more .= " AND ( cart.article LIKE '".mysql_real_escape_string(FuncModel::stringfilter($params['article']))."' OR cart.article LIKE '".mysql_real_escape_string($params['article'])."' ) ";
			}
			
		}
		#13
		if (isset($params['brand']) && $params['brand']){
			$sql_more .= " AND cart.brand LIKE '%".mysql_real_escape_string(str_replace(" ", "%", $params['brand']))."%' ";
		}
		#14
		if (isset($params['descr']) && $params['descr']){
			$sql_more .= " AND cart.descr_tecdoc LIKE '%".mysql_real_escape_string(str_replace(" ", "%", $params['descr']))."%' ";
		}
		#**********************************************************************
		


		#DONE DATE FOR SORT
		if ((isset($params['done_dt_from']) && $params['done_dt_from']) && (isset($params['done_dt_to']) && $params['done_dt_to'])){
			$params['done_dt_from'] = strtotime($params['done_dt_from']);
			$params['done_dt_to'] = strtotime($params['done_dt_to']);
			$sql_more .= " AND (cb.bill_dt_closed BETWEEN '".mysql_real_escape_string($params['done_dt_from'])."' AND '".mysql_real_escape_string($params['done_dt_to'])."') ";
		}
		elseif ((isset($params['done_dt_from']) && $params['done_dt_from'])){
			$params['done_dt_from'] = strtotime($params['done_dt_from']);
			$sql_more .= " AND cb.bill_dt_closed >= '".mysql_real_escape_string($params['done_dt_from'])."' ";
		}
		elseif ((isset($params['done_dt_to']) && $params['done_dt_to'])){
			$params['done_dt_to'] = strtotime($params['done_dt_to']);
			$sql_more .= " AND cb.bill_dt_closed <= '".mysql_real_escape_string($params['done_dt_to'])."' ";
		}
		
		#15 New sort By Office,Manager,Importer
		if (isset($params['importer_id']) && $params['importer_id']){
			$sql_more .= " AND cart.import_id = '".(int)$params['importer_id']."' ";
		}
		if (isset($params['office_id']) && $params['office_id']){
			$sql_more .= " AND cb.office_id = '".(int)$params['office_id']."' ";
		}
		if (isset($params['manager_id']) && $params['manager_id']){
			$sql_more .= " AND cb.manager_id = '".(int)$params['manager_id']."' ";
		}

		$db = Register::get('db');
		$sql = "
		SELECT
			COUNT(*) cc
		FROM ".DB_PREFIX."cart cart
		JOIN ".DB_PREFIX."cart_bills cb ON cart.scSID=cb.scSID
		LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
		LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
		LEFT JOIN ".DB_PREFIX."accounts acc ON (acc.id=cb.account_id)
		WHERE 1=1 $sql_more;";
		
// 		echo('<pre><b>COUNT:</b>');
// 		var_dump($sql);
// 		echo('</pre>');
		
		return $db->get($sql);
	}
	
	/* *********************************************************************** */
	public static function iRequestToImporters(){
		
		$db = Register::get('db');
		$sql = "
		SELECT
			imp.*
		FROM ".DB_PREFIX."cart_bills cartbills
		LEFT JOIN ".DB_PREFIX."cart cart ON cart.scSID=cartbills.scSID
		LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
		LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
		WHERE
			cart.type='detail' AND
			ds.type='2' AND
			cart.imps_sent='0'
		GROUP BY
			imp.id
		HAVING COUNT(cart.id) > 0
		ORDER BY
			cartbills.dt DESC
		;";
// 		echo('<pre>');
// 		var_dump($sql);
// 		exit();
		return $db->query($sql);
	}
	public static function imps($code) {
		$db = Register::get('db');
		$sql = "
		SELECT 
				
			cb.scSID bill_scSID,
			cb.number bill_number,

			cart.id as id,
			cart.createDT as createDT,
			cart.import_id as fk,
			cart.`count` as cc,
			cart.article as article,
			cart.brand as brand,
			cart.descr_tecdoc as descr,
			cart.price,
			cart.descr_tecdoc,
			cart.status_descr,
			cart.status,
			cart.is_account as IS_ACCOUNT,
			cart.is_account_accept as IS_ACCOUNT_ACCEPT,
			cart.balance_minus,
			cart.price_purchase,
			cart.sold,
			cart.time_delivery_wait_dt as time_delivery_wait_dt,
			cart.time_delivery_descr as time_delivery_descr,
			cart.is_payback,
	
			cb.id bill_id,
			cb.account_id bill_account_id,
			cb.car_id car_id,
			cb.manager_id manager_id,
			cb.office_id office_id,
			cb.status bill_status,
			cb.dt bill_dt,
			cb.f1 bill_f1,
			cb.f2 bill_f2,
			cb.f3 bill_f3,
			cb.message bill_message,
			cb.delivery bill_delivery,
			cb.delivery_price bill_delivery_price,
			cb.dt bill_dt,
			cb.delivery_set_balance,
			cb.prepayment,
			cb.is_paid,
	
			cb.time_give_order,
			cb.time_from,
			cb.time_to,
			cb.payment_name,
			cb.delivery_addess,
	
	
			imp.id as imp_id,
			imp.code as imp_code,
			imp.name as imp_name,
			imp.delivery as delivery,
			imp.name_price as namePrice,
	
			ds.id ds_id,
			ds.name ds_name,
			ds.color ds_color,
			ds.type ds_type,
	
			acc.id acc_id,
			acc.balance acc_balance
		FROM ".DB_PREFIX."cart cart
		JOIN ".DB_PREFIX."cart_bills cb ON cart.scSID=cb.scSID
		LEFT JOIN ".DB_PREFIX."importers imp ON (imp.id=cart.import_id)
		LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
		LEFT JOIN ".DB_PREFIX."accounts acc ON (acc.id=cb.account_id)
		WHERE 
			cart.type='detail' AND 
			ds.type='2' AND 
			cart.imps_sent='0' AND 
			imp.code='".mysql_real_escape_string($code)."'
		ORDER BY 
				imp_code,
				cb.dt DESC
		;";
		return $db->query($sql);
	}
	public static function getBills($id) {
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills WHERE account_id='".(int)$id."';";
		$res = $db->get($sql);
		return (int)isset($res['cc'])?$res['cc']:0;
	}
	public static function fetchBills($id) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills WHERE account_id='".(int)$id."';";
		return $db->query($sql);
	}
	public static function fetchAllBills() {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills where status!=3 order by dt;";
		return $db->query($sql);
	}
	public static function fetchByIdBill($id) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills where id='".(int)$id."';";
		return $db->get($sql);
	}
	public static function fetchByIdBillNumber($id) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills where number='".(int)$id."';";
		return $db->get($sql);
	}
	public static function fetchByStatusCount($status=0) {
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills where status='".(int)$status."' order by dt;";
		$res = $db->get($sql);
		return ($res['cc'])?$res['cc']:0;
	}
	public static function fetchAllCount($status=3) {
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills where status!='".(int)$status."' order by dt;";
		$res = $db->get($sql);
		return ($res['cc'])?$res['cc']:0;
	}
	public static function fetchAllBillsDone() {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills where status=3 order by dt;";
		return $db->query($sql);
	}
	public static function getAccount($id) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts where id='".(int)$id."';";
		return $db->get($sql);
	}
	public static function getItem($id) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart where id='".(int)$id."';";
		return $db->get($sql);
	}
	public static function getOperation($id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_history WHERE account_id='".(int)$id."' ORDER BY dt DESC LIMIT 0,20;";
		return $db->query($sql);
	}
	public static function changeStatus($id,$status,$account) {
		$db = Register::get('db');
		$translates = Register::get('translates');
		$sql = "update ".DB_PREFIX."cart_bills set status='".(int)$status."' where id='".(int)$id."';";
		$db->query($sql);
		$account = self::getAccount($account);
		return true;
	}
	public static function billByIdFullInfo($smsOrderId=0){
		$db = Register::get('db');
		$sql = "
				SELECT
					BILLS.*,
					(SUM(ITEMS.price * ITEMS.count)) total_sum,
					COUNT(ITEMS.id) total_items,
					(SUM(ITEMS_PAYED.price * ITEMS_PAYED.count)) payed_total_sum,
					ACC.name account_name,
					ACC.phones account_phones,
					ACC.address account_address,
					DCITY.name account_cityname,
					ACC.info account_descr,
					ACCARS.car_name carname,
					ACCARS.car_year caryear,
					MANAGER.name manager,
					OFFICE.name office,
					DSTATUS.color,
					DSTATUS.name as statusName
				FROM ".DB_PREFIX."cart_bills BILLS
				LEFT JOIN ".DB_PREFIX."cart ITEMS ON ITEMS.scSID=BILLS.scSID
				LEFT JOIN ".DB_PREFIX."cart ITEMS_PAYED ON (ITEMS_PAYED.scSID=BILLS.scSID AND ITEMS_PAYED.balance_minus)
				LEFT JOIN ".DB_PREFIX."accounts ACC ON ACC.id=BILLS.account_id
				LEFT JOIN ".DB_PREFIX."dic_cities DCITY ON DCITY.id=ACC.city
				LEFT JOIN ".DB_PREFIX."accounts_cars ACCARS ON ACCARS.id=BILLS.car_id
				LEFT JOIN ".DB_PREFIX."_user MANAGER ON MANAGER.id=BILLS.manager_id
				LEFT JOIN ".DB_PREFIX."offices OFFICE ON OFFICE.id=BILLS.office_id
				LEFT JOIN ".DB_PREFIX."dic_statuses DSTATUS ON DSTATUS.id=BILLS.status
				WHERE
					BILLS.id = '".(int)$smsOrderId."'
				GROUP BY
					BILLS.id
			";
		return $db->get($sql);
	}
	
	public static function getStatisticTotalSum($search=array()){
		
		$Where = ""; $params = array();
		if (isset($search) && count($search)>0){
			if (isset($search['is_done'])){
				$params []= " DSTATUS.type = 1 ";
			}
			if (isset($search['this_month']) && $search['this_month']){
				$params []= " BILLS.dt BETWEEN '".mktime(0,0,0,$search['this_month'],1,date("Y"))."' AND '".mktime(0,0,0,$search['this_month'],date("t"),date("Y"))."' ";
			}
			if (isset($search['this_year'])){
				$params []= " BILLS.dt BETWEEN '".mktime(0,0,0,1,1,date("Y"))."' AND '".mktime(0,0,0,12,date("t"),date("Y"))."' ";
			}
			if (count($params)>0){
				$Where = " WHERE ";
				$Where .= join(" AND ", $params);
			}
		}
		
		$db = Register::get('db');
		$sql = "
			SELECT
				(SUM(ITEMS.price * ITEMS.count) + BILLS.delivery_price) total_price,
				(SUM(ITEMS.price_purchase * ITEMS.count)) total_price_purchase
			FROM ".DB_PREFIX."cart_bills BILLS
			LEFT JOIN ".DB_PREFIX."cart ITEMS ON ITEMS.scSID=BILLS.scSID
			LEFT JOIN ".DB_PREFIX."dic_statuses DSTATUS ON DSTATUS.id=ITEMS.status
			$Where
			GROUP BY BILLS.id
		";
		$res = $db->query($sql);
		$total_price = $total_price_purchase = 0;
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$total_price += $dd['total_price'];
				$total_price_purchase += $dd['total_price_purchase'];
			}
		}
		return array('total_price' => $total_price,'total_price_purchase' => $total_price_purchase);
	}
	
	
	public static function getBillByscSID($scsid=false) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills where scSID = '".mysql_real_escape_string($scsid)."';";
		return $db->get($sql);
	}
}

?>