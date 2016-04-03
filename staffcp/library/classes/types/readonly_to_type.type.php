<?php

class Readonly_to_typeType extends Type  {

	static $bread_crumbs = array();
	var $nexts = array();
	var $tree = array();
	
	public function getFormValue($val='') {
		
		$db = Register::get('db');
		
		if (isset($_REQUEST['type_id'])){
			$this->setValue((int)$_REQUEST['type_id']);
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
		$sql = "SELECT CAR.NAME CA,MODEL.NAME,TYPE.NAME TY FROM `".DB_PREFIX."to_types` TYPE LEFT JOIN `".DB_PREFIX."to_models` MODEL ON TYPE.model_id=MODEL.id LEFT JOIN `".DB_PREFIX."to_cars` CAR ON CAR.id=MODEL.car_id WHERE TYPE.id='".(int)$id."';";
		$car = $db->get($sql);
		return $car['CA'].' '.$car['MO'].' '.$car['TY'];
	}
}