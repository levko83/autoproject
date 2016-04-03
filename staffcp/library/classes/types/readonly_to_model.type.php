<?php

class Readonly_to_modelType extends Type  {

	static $bread_crumbs = array();
	var $nexts = array();
	var $tree = array();
	
	public function getFormValue($val='') {
		
		$db = Register::get('db');
		
		if (isset($_REQUEST['model_id'])){
			$this->setValue((int)$_REQUEST['model_id']);
		}
		
		$szResult = '<input style="width:100px" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		$szResult .= ' readonly>';
		
		$car = $this->getCar($this->getValue());
		
		$szResult .= '<span class="label">* '.$car.'</span>';
		
		return $szResult;
	}

	public function getViewValue() {
		return htmlspecialchars($this->value);
	}
	
	function getCar($id){
		$db = Register::get('db');
		$sql = "SELECT CAR.NAME CA,MODEL.NAME MO FROM `".DB_PREFIX."to_models` MODEL LEFT JOIN `".DB_PREFIX."to_cars` CAR ON CAR.id=MODEL.car_id WHERE MODEL.id='".(int)$id."';";
		$car = $db->get($sql);
		return $car['CA'].' '.$car['MO'];
	}
}