<?php
class Extends_input_clearType extends Type  {
	public function getFormValue($val='') {
		
		$szResult = '<input type="hidden" name="form['.$this->fieldName.']" ';
		
		if (isset($this->value)){
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
			$szResult .= '> '.$this->getValue();
		}
		elseif (!empty($val[$this->fieldName])){
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
			$szResult .= '> '.$val[$this->fieldName];
		}
		
		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>';
			
		return $szResult;
	}
	public function getViewValue() {
		return htmlspecialchars($this->value);
	}

	public function getSaveValue($value='') {
		$field = $this->fieldInfo['field'];
		$value = $_POST['form'][$field];
		return FuncModel::stringfilter($value);
		exit();
	}
}
?>