<?php

class TextType extends Type  {

	public function getViewValue() {
		if (isset($this->fieldInfo['view']) && $this->fieldInfo['view'] == 'full_text') {
			$class = isset($this->fieldInfo['class'])?$this->fieldInfo['class']:false;
			if ($class) {
				return '<span title="'.htmlspecialchars(strip_tags($this->value)).'" class="'.$class.'">'.htmlspecialchars(strip_tags($this->value)).'</span>';
			}
			return $this->value;
		}
		else {
			$class = isset($this->fieldInfo['class'])?$this->fieldInfo['class']:false;
			if ($class) {
				return '<span title="'.htmlspecialchars(strip_tags($this->value)).'" class="'.$class.'">'.htmlspecialchars(strip_tags($this->value)).'</span>';
			}
			return substr(htmlspecialchars($this->value),0,100)."...";
		}
	}

	public function getFormValue($val='') {
		
		$result = '<textarea ';
		if (isset($this->fieldInfo['style'])) {
			$result .= ' style="'.$this->fieldInfo['style'].'" ';
		} else {
			$result .= ' style="width:99%" rows="20" ';
		}
		$result .= 'name="form['.$this->fieldName.']" ';
		if (isset($this->fieldInfo['class']))
			$result .= 'class="'.$this->fieldInfo['class'].'" ';
		$result .= '>';
		if (isset($this->value)) 
			$result .= htmlspecialchars($this->value);
		elseif (!empty($val[$this->fieldName]))
			$result .= htmlspecialchars($val[$this->fieldName]);
		$result .= '</textarea>';

		return $result;
	}

}