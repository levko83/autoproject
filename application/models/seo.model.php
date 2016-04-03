<?php


class SeoModel extends Orm {
			
	public function __construct()
	{
		parent::__construct(DB_PREFIX.'seo');
	}

	public static  function getById($code) {
		$model = new SeoModel();
		$data = $model->select()->where("id = ? ", $code)->fetchOne();
		if (!empty($data))
			return $data;
		else {
			$data = $model->select()->where("id = ? ", 'index')->fetchOne();
			return $data;
		}
	}
}