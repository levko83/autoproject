<?php

class Galleries_imagesModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'galleries_images');
	}
	
	public static function getAllImagesByGalleryID($id=0) {
		$model = new Galleries_imagesModel();
		return $model->select()->where("fk_gallery=?",(int)$id)->order("`id` ASC")->fetchAll();		
	}
}

?>