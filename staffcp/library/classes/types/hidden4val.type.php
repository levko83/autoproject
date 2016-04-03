<?php

class Hidden4valType extends Type  {
	
	#public $hasLayout = false;
	
	public function getFormValue() {
		$val = $this->fieldInfo['value'];
		return '<input name="form['.$this->fieldName.']" type="hidden" value="'.$val.'">';
	}
	
}