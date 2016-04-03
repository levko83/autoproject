<?php

class CodeType extends Type  {

	public function getFormValue($val='') {
		
		$szResult = '<input style="width:99%" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
		{
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		}
		elseif (!empty($val[$this->fieldName]))
		{
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		}
		else {
			$szResult.='value="" ';
		}
		$szResult .= '/>';
		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>';
		
		return $szResult;
	}

	public function getViewValue() {
		return htmlspecialchars($this->value);
	}

}