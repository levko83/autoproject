<?php

require('../../core/classes/register.class.php');
require('../../application/config/db.cfg.php');
require('../../application/library/helpers/alias_view.helper.php');
require('../../core/classes/orm_condition.class.php');
require('../../core/classes/collection.class.php');
require('../../core/classes/db.class.php');
$db = new Db();


$inv_id = isset($_REQUEST["InvId"])?$_REQUEST["InvId"]:false;
if ($inv_id) {
	
	$db->post("
		UPDATE 
			".DB_PREFIX."settings_merchants_result 
		SET 
			status='Отказались от оплаты',
			check_dt='".mktime()."'
		WHERE 
			merchant='ROBOKASSA' AND 
			orderid_bill='".(int)$inv_id."';
	");
}

?>