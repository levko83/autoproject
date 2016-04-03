<?php

class Filters_valuesModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'filters_values');
	}
	
	public static function getById($id) {
		$model = new Filters_valuesModel();
		return $model->select()->where("`id`=?",(int)$id)->fetchOne();
	}
	
	/* ****** */
	public static function getByFilter($id,$isset_values=array(),$sort='default') {
		
		switch ($sort){
			case 'default': $sortby = ' ORDER BY FV.sort,FV.name'; break;
			case 'cast': $sortby = ' ORDER BY FV.sort,cast(FV.name as unsigned)'; break;
			default: $sortby = ' ORDER BY FV.sort,FV.name'; break;
		}
		
		$db = Register::get('db');
		$model = new Filters_valuesModel();
		$sql = "
		SELECT 
			FV.id,FV.name,COUNT(product_id) C
		FROM ".DB_PREFIX."filters_values FV 
		JOIN ".DB_PREFIX."filters_values2products FV2P ON FV2P.value_id = FV.id
		JOIN ".DB_PREFIX."products P ON (P.id=FV2P.product_id AND P.set_isset = 1)
		JOIN ".DB_PREFIX."cat CAT ON (CAT.id=P.fk AND CAT.is_active=1)
		WHERE 
			FV.filter_id=".(int)$id." AND 
			FV.is_active='1' ".((isset($isset_values)&&count($isset_values)>0)?("AND FV2P.product_id IN (".join(",",$isset_values).")"):"")."
		GROUP BY FV.id
		".$sortby."
		;";
		return $db->query($sql);
	}
	
	private function getCountProductsByValueId($id,$pidsall=array()){
		$db = Register::get('db');

		if (isset($pidsall) && count($pidsall)>0){
			$sql = "
					SELECT DISTINCT
						COUNT(product_id) C
					FROM ".DB_PREFIX."filters_values2products FV2P
					JOIN ".DB_PREFIX."products P ON P.id=FV2P.product_id AND P.set_isset = 1
					JOIN ".DB_PREFIX."cat CAT ON (CAT.id=P.fk AND CAT.is_active=1)
					WHERE
						value_id = '".(int)$id."' AND
						product_id IN (".join(",",$pidsall).")
					;";
			$res = $db->get($sql);
			return isset($res['C'])?$res['C']:0;
		}
		
		$sql = "
			SELECT DISTINCT 
				COUNT(FV2P.product_id) C 
			FROM ".DB_PREFIX."filters_values2products FV2P 
			JOIN ".DB_PREFIX."products P ON P.id=FV2P.product_id AND P.set_isset = 1
			JOIN ".DB_PREFIX."cat CAT ON (CAT.id=PROD.fk AND CAT.is_active=1)
			WHERE 
				FV2P.value_id = '".(int)$id."'
		;";
		$res = $db->get($sql);
		return isset($res['C'])?$res['C']:0;
	}

}

?>