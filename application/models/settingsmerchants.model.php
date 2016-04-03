<?php
class SettingsmerchantsModel extends Orm {
	public function __construct(){
		parent::__construct(DB_PREFIX.'settings_merchants');
	}
	public static function get($code){
		$model = new SettingsmerchantsModel();
		$data = $model->select()->where("code = ? ", $code)->fetchOne();
		return $data['value'];
	}
}
?>