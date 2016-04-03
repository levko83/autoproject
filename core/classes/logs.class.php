<?php

class Logs {
	
	public static function addLog($user=null,$descr=null,$url=null){
		if ($user && $descr){
			
			$user = UsersModel::getById($user);
			$user = $user['login'].' :: '.$user['name'];
			
			$db = Register::get('db');
			if (defined('NOTICE') && !NOTICE){
				$db->post("
					INSERT INTO ".DB_PREFIX."logs 
						(`dt`,`user`,`descr`,`url`) 
					VALUES 
						('".time()."','".mysql_real_escape_string($user)."','".mysql_real_escape_string($descr)."','".mysql_real_escape_string($url)."');
				");
			}
		}
	}
}
?>