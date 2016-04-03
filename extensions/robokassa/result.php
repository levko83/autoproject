<?php

require('../../core/classes/register.class.php');
require('../../application/config/db.cfg.php');
require('../../application/library/helpers/alias_view.helper.php');
require('../../core/classes/orm_condition.class.php');
require('../../core/classes/collection.class.php');
require('../../core/classes/db.class.php');
$db = new Db();

$pass2 = $db->get("SELECT value FROM ".DB_PREFIX."settings_merchants WHERE code='robokassa_password2';");
$mrh_pass2 = $pass2['value'];

$tm=getdate(time()+9*3600);
$date="$tm[year]-$tm[mon]-$tm[mday] $tm[hours]:$tm[minutes]:$tm[seconds]";

$out_summ = isset($_REQUEST["OutSum"])?$_REQUEST["OutSum"]:false;
$inv_id = isset($_REQUEST["InvId"])?$_REQUEST["InvId"]:false;
$shp_item = isset($_REQUEST["Shp_item"])?$_REQUEST["Shp_item"]:false;
$crc = isset($_REQUEST["SignatureValue"])?$_REQUEST["SignatureValue"]:false;

$crc = strtoupper($crc);
$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2:Shp_item=$shp_item"));
if ($my_crc !=$crc){
	echo "bad sign\n";
	exit();
}

if ($inv_id) {
	
	$db->post("
		UPDATE 
			".DB_PREFIX."settings_merchants_result 
		SET 
			status='Признак успешно проведенной операции',
			check_dt='".mktime()."'
		WHERE 
			merchant='ROBOKASSA' AND 
			orderid_bill='".(int)$inv_id."';
	");
}

?>