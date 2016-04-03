<?php

class FaqblocksModel extends Orm {
	
	public function __construct()
	{
		parent::__construct(DB_PREFIX.'faq_blocks');
	}
	
	public static function getFirst()
	{
		$model = new FaqblocksModel();
		return $model->select()->where("isset=1")->order("sort")->fetchOne();
	}
	
	public static function getByCode($id)
	{
		$model = new FaqblocksModel();
		return $model->select()->where("code=?",$id)->fetchOne();
	}
	
	public static function getAll()
	{
		$model = new FaqblocksModel();
		return $model->select()->where("isset=1")->order("sort")->fetchAll();		
	}
}