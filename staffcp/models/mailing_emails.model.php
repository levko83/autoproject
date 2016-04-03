<?php

class Mailing_emailsModel extends Orm {
	
	public function __construct(){
		parent::__construct(DB_PREFIX.'mailing__emails');
	}
	
	public static function getAll(){
		$model = new Mailing_emailsModel();
		return $model->select()->where("is_active='1'")->fetchAll();
	}
	
	public static function countSended() {
		$db = Register::get('db');
		$sql = "select count(*) as cc from ".DB_PREFIX."mailing__emails where `is_active`='1'";
		$data = $db->query($sql);
		$cc = (isset($data[0]))?$data[0]['cc']:0;
		return $cc;
	}
}