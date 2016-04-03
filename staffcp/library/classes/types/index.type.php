<?php
class IndexType extends Type  {
	public $hasLayout = false;
	public function getFormValue($val='') {
	if (isset($this->value))
		return $this->value.'<input name="form['.$this->fieldName.']" type="hidden" value="'.$this->value.'">';
	elseif (!empty($val[$this->fieldName]))
		return $this->value.'<input name="form['.$this->fieldName.']" type="hidden" value="'.htmlspecialchars($val[$this->fieldName]).'">';
	}
	public function check($value,$fieldInfo = array()) {
		return !(($value < 0) || (($value != '') && (intval($value) != $value)));
	}
}
?>