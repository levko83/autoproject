<?php

class FiltersModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'filters');
	}
	
	public static function getById($id) {
		$model = new FiltersModel();
		return $model->select()->where("`id`=?",(int)$id)->fetchOne();
	}
	
	public static function getByFilterView($id) {
		$model = new FiltersModel();
		return $model->select()->where("`view_id`=? AND `is_active`='1'",(int)$id)->order("sort")->fetchAll();
	}
	
	public static function getParamsView($product_id=0){
		$db = Register::get('db');
		$sql = "
			SELECT
				F.name,FV.name value
			FROM ".DB_PREFIX."filters_values2products FV2P
			LEFT JOIN ".DB_PREFIX."filters_values FV ON FV.id=FV2P.value_id
			LEFT JOIN ".DB_PREFIX."filters F ON F.id=FV.filter_id
			WHERE
				FV2P.product_id = '".(int)$product_id."' AND F.is_product_view = 1
			ORDER BY
				F.name;";
		return $db->query($sql);
	}
}