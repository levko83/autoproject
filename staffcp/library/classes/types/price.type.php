<?php

class PriceType extends Type  {

	public function getFormValue($val='') {
		
		$db = Register::get('db');
		
		$szResult = '<input style="width:99%" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		$szResult .= '>';
		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>';
		
		return $szResult;
	}
	public function getViewValue($item=array()) {
		$getItemProvider = ImportersModel::getById($item['IMPORT_ID']);
		$this->value = $this->extra($this->value,$getItemProvider['discount']).'<sup style="font-size:10px;">('.$getItemProvider['discount'].'%)</sup>';
		return $this->value;
	}
	function extra($price,$extra) {
		return $price+($price*$extra/100);
	}
	function percent($price,$extra){
		return $price*$extra/100;
	}
}