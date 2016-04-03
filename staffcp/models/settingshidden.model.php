<?php
class SettingshiddenModel extends Orm {
	public function __construct(){
		parent::__construct(DB_PREFIX.'settings_hidden');
	}
	public static function get($code){
		$model = new SettingshiddenModel();
		$data = $model->select()->where("code = ? ", $code)->fetchOne();
		return $data['value'];
	}
}
?>