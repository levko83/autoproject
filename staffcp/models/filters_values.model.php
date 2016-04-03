<?php

class Filters_valuesModel extends Orm {
	
	public function __construct(){
		parent::__construct(DB_PREFIX.'filters_values');
	}
	
	public static function fetchingAll($filter_id){
		$model = new Filters_valuesModel();
		return $model->select()->where("filter_id=?",(int)$filter_id)->fetchAll();
	}

}