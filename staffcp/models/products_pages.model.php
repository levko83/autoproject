<?php

class Products_pagesModel extends Orm {
	
	public function __construct()
	{
		parent::__construct('w_products_pages');
	}
	
	public static function getByFk($fk)
	{
		$model = new Products_pagesModel();
		return $model->select()->where("fk=?",(int)$fk)->fetchAll();
	}
}