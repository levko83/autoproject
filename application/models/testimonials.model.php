<?php

class TestimonialsModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'testimonials');
	}
	
	public static function getByProductId($id) {
		$model = new TestimonialsModel();
		return $model->select()->where("product_id=? AND is_active = 1",(int)$id)->order("dt DESC")->fetchAll();
	}
	
	public static function getRating($id=0) {
		$db = Register::get('db');
		$sql = "
		SELECT (SUM(raiting)/COUNT(*)) total FROM ".DB_PREFIX."testimonials 
		WHERE 
			product_id = '".(int)$id."' AND 
			is_active = 1
		;";
		return $db->get($sql);
	}
}