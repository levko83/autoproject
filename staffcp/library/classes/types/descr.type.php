<?php

class DescrType extends Type  {

	public function getViewValue() {
		$class = isset($this->fieldInfo['class'])?$this->fieldInfo['class']:false;
		if ($class) {
			return '<span title="'.htmlspecialchars(strip_tags($this->value)).'" class="'.$class.'">'.htmlspecialchars(strip_tags($this->value)).'</span>';
		}
		return $this->value;
	}

	public function getFormValue($val='') {
		
		$class = isset($this->fieldInfo['class'])?$this->fieldInfo['class']:false;
		if ($class) {
			return '<span title="'.htmlspecialchars(strip_tags($this->value)).'" class="'.$class.'">'.htmlspecialchars(strip_tags($this->value)).'</span>';
		}
		return $this->value;
	}

}
?>