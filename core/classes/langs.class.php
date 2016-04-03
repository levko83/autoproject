<?php

class Langs {
	
	public static function get($id,$ln='') {
		// die($id);
		if ($ln){
			$setLang = $ln;
		}
		else {
			$setLang = isset($_SESSION['setLang'])?$_SESSION['setLang']:LANG;
		}
		$db = Register::get('db');
		
		$sql = "SELECT `id`,`code`,`".$setLang."` FROM ".DB_PREFIX."langs WHERE `side`='".(int)$id."';";
		$res = $db->query($sql);
		$data = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$data [$dd['code']]= (!empty($dd[$setLang]))?$dd[$setLang]:"NOT_SET_{$dd['id']}";
			}
		}
		return $data;
	}
}