<?php

class Accounts_carsController  extends CmsGenerator {
	public function index(){
		$this->prepareIndexData();
		$this->render('accounts_cars/list');
	}
}
?>