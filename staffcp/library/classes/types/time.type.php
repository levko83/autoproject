<?php

class TimeType extends Type  {

	public function getFormValue($val='') {
		$translates = Register::get('translates');
		
		$szResult = '<input style="width:99%" type="text" MAXLENGTH="5" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		$szResult .= '> '.$translates['explame.time'].'';

		return $szResult;
	}

	public function getViewValue() {
		return htmlspecialchars($this->value);
	}

	public function getSaveValue($value) {
		if (strlen($value) == 4)
			return '0'.$value;
		return $value;
	}

}