<?php
class CallmeModel extends Orm {
	public function __construct(){
		parent::__construct(DB_PREFIX.'callme');
	}
	public static function get($code){
		$model = new CallmeModel();
		$data = $model->select()->where("code=?",mysql_real_escape_string($code))->fetchOne();
		return $data['value'];
	}
	public static function getParams($list){
		$db = Register::get('db');
		$sql = "SELECT `code`,`value` FROM ".DB_PREFIX."callme WHERE `code` IN ('".join("','",$list)."');";
		return $db->query($sql);
	}
}
?>