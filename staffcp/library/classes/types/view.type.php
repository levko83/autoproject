<?php

class ViewType extends Type  {
	
	public function getFormValue($val='') {
		if (isset($this->value))
			return $this->value.'<input name="form['.$this->fieldName.']" type="hidden" value="'.$this->value.'">';
		elseif (!empty($val[$this->fieldName]))
			return $this->value.'<input name="form['.$this->fieldName.']" type="hidden" value="'.htmlspecialchars($val[$this->fieldName]).'">';
	}
	
}