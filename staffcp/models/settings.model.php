<?php
class SettingsModel extends Orm {
	public function __construct(){
		parent::__construct(DB_PREFIX.'settings');
	}
	public static function get($code){
		if (NOTICE && $code == 'contact_email')
			return 'perexod.roman@gmail.com,vbelarusi@gmail.com';
		$model = new SettingsModel();
		$data = $model->select()->where("code=?",mysql_real_escape_string($code))->fetchOne();
		return $data['value'];
	}
	public static function getParams($list){
		$db = Register::get('db');
		$sql = "SELECT `code`,`value` FROM ".DB_PREFIX."settings WHERE `code` IN ('".join("','",$list)."');";
		return $db->query($sql);
	}
}
?>