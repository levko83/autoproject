<?php

class Readonly_to_carType extends Type  {

	static $bread_crumbs = array();
	var $nexts = array();
	var $tree = array();
	
	public function getFormValue($val='') {
		
		$db = Register::get('db');
		
		if (isset($_REQUEST['car_id'])){
			$this->setValue((int)$_REQUEST['car_id']);
		}
		
		$szResult = '<input style="width:100px" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		$szResult .= ' readonly>';
		
		$car = $this->getCar($this->getValue());
		
		$szResult .= '<span class="label">* '.$car['name'].'</span>';
		
		return $szResult;
	}

	public function getViewValue() {
		return htmlspecialchars($this->value);
	}
	
	function getCar($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."to_cars` WHERE `id`='".(int)$id."';";
		return $db->get($sql);
	}
}