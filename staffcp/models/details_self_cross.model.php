<?php

class Details_self_crossModel extends Orm {
	
	public static function getByIdDetail($id) {
		$db = Register::get('db');
		$sql = "SELECT * from ".DB_PREFIX."details WHERE ID='".(int)$id."';";
		$res = $db->get($sql);
		return $res;
	}
}