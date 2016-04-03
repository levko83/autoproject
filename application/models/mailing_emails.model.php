<?php

class Mailing_emailsModel extends Orm {
	
	public function __construct(){
		parent::__construct(DB_PREFIX.'mailing__emails');
	}
	
	public static function check($email){
		$model = new Mailing_emailsModel();
		return $model->select()->where("email like ?",addslashes($email))->fetchOne();
	}
	
	public static function add($data) {
		
		$db = Register::get('db');
		
		if (isset($data) && is_array($data) && isset($data['email']) && !empty($data['email'])) {
		
			$email = $data['email'];
			$check = Mailing_emailsModel::check($email);
			
			if (count($check)<=0) {
				$db->query("insert into ".DB_PREFIX."mailing__emails (`name`,`email`,`is_active`,`dt`) values ('','".addslashes($email)."','0','".mktime()."');");
				$_SESSION['mailing'] = 1;
				$data ['url']= 'http://'.$_SERVER['SERVER_NAME'].'/mailing/allow/'.base64_encode($email).'/';
				EmailsModel::get('scribe_agree',$data,$email,'no-reply@'.$_SERVER['SERVER_NAME'],'Mailing robot',false);
				return true;
			}
			else 
				return false;
		}
		else 
			return false;
	}
	
	public static function allow($key){
		
		if (!empty($key)) {
			$db = Register::get('db');
			$email = base64_decode($key);
			$db->query("update ".DB_PREFIX."mailing__emails set `is_active`='1' where `email`='".addslashes($email)."';");
			$_SESSION['mailing'] = 2;
			return true;
		}
		else 
			return false;
	}
	
	public static function deny($key){
		
		if (!empty($key)) {
			$db = Register::get('db');
			$email = base64_decode($key);
			//$db->query("delete from ".DB_PREFIX."mailing__emails where `email`='".addslashes($email)."';");
			$db->query("update ".DB_PREFIX."mailing__emails set `is_active`='0' where `email`='".addslashes($email)."';");
			$_SESSION['mailing'] = 3;
			return true;
		}
		else 
			return false;
	}
	
//	if (isset($_SESSION['mailing'])) {
//		$this->view->mailing = $_SESSION['mailing'];
//		unset($_SESSION['mailing']);
//	} else {
//	  	$this->view->mailing = 0;
//	}
}