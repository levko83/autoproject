<?php

class FaqModel extends Orm {
	
	public function __construct()
	{
		parent::__construct(DB_PREFIX.'faq_blocks');
	}
	
	public static function getAll($part,$page=1,$per_page=10)
	{
		$page = ($page - 1)*$per_page;
		$db = Register::get('db');
		$sql = "select faq.* from `".DB_PREFIX."faq` faq join `".DB_PREFIX."faq2block` f2b on (f2b.fk_faq=faq.id and f2b.fk_block='".(int)$part."') where faq.isset='1' group by f2b.fk_faq order by faq.sort limit ".$page.",".$per_page.";";
		return $db->query($sql);
	}
	
	public static function getByPaging($part)
	{
		$db = Register::get('db');
		$sql = "select count(*) cc from `".DB_PREFIX."faq` faq join `".DB_PREFIX."faq2block` f2b on (f2b.fk_faq=faq.id and f2b.fk_block='".(int)$part."') where faq.isset='1' order by faq.sort;";
		$data = $db->get($sql);
		return (isset($data['cc']))?$data['cc']:0;
	}
	
	public static function getByLimit($limit=3) 
	{
		$model = new FaqModel();
		return $model->select()->where("isset=1")->order("`sort`")->limit(0,$limit)->fetchAll();
	}
}