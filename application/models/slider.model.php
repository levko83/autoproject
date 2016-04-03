<?php

class SliderModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'slider');
	}
	
	public static function getAll() {
		$model = new SliderModel();
		return $model->select()->where("is_active=1")->order("`sort`")->fetchAll();		
	}
}