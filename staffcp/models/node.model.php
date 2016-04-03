<?php

class NodeModel extends Orm {
	
	public function __construct()
	{
		parent::__construct('w_products_node');
	}
	
	public static function getAll($fk)
	{
		$db = Register::get('db');
		$sql = "select prod.* from w_products_node pn join w_products prod on (pn.fk_node=prod.id and pn.fk_product='".(int)$fk."');";
		return $db->query($sql);
	}
}