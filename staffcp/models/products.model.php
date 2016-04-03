<?php

class ProductsModel extends Orm {
	
	public function __construct()
	{
		parent::__construct('w_products');
	}
	
	public static function getById($id)
	{
		$model = new ProductsModel();
		return $model->select()->where("id=?",(int)$id)->fetchOne();
	}
	
	public static function getAllPricesCount($id){
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) CC FROM ".DB_PREFIX."products2importers P2I WHERE P2I.p2i_product_id='".(int)$id."';";
		$res = $db->get($sql);
		return isset($res['CC'])?$res['CC']:0;
	}
	
	public static function getAllPrices($id){
		$model = new ProductsModel();
		$db = Register::get('db');
		$prices = array();		
		
		$sql = "
			SELECT 
				D.IMPORT_ID,D.PRICE 
			FROM w_products2importers P2I 
			JOIN w_details D ON 
				P2I.p2i_importer_id=D.IMPORT_ID AND 
				P2I.p2i_key=D.ARTICLE AND
				P2I.p2i_key_brand=D.BRAND_NAME
			WHERE 
				P2I.p2i_product_id='".(int)$id."'
			GROUP BY D.IMPORT_ID,D.PRICE
			;";
		$res = $db->query($sql);
		
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$prices []= array("price"=>$dd['PRICE']);
			}
		}
		
		return $prices;
	}
}