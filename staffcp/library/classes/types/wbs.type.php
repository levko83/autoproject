<?php

class WbsType extends Type {
	
	public function __getValue()  {
		return htmlspecialchars($this->value);
	}
	
	public function getFormValue($val='') {
		
		$valid = @$val[$this->fieldName];
		
		if (!empty($this->fieldInfo['size']))
				$szSize = $this->fieldInfo['size'];
		else
			$szSize = 1;
			
		if (!empty($this->fieldInfo['class'])) 
			$szClass = $this->fieldInfo['class'];
		else
			$szClass = '';
			
		$result = '<select size="'.$szSize.'" name="form['.$this->fieldName.']" class="'.$szClass.'">';
		$szName = '';
		foreach (glob('../wbs/*') as $value) {
			$szSelected ='';
			
			$value = basename($value);
			
			if ($value==$this->value) {
				$szSelected = 'selected';
				$szName = $value;
			}
			elseif ($value == $valid) {
				$selected = 'selected';
				$szName = $value;
			}
			
			if (!in_array($value,array("_config.php","_get.php")))
			$result .= '<option value="'.$value.'" '.$szSelected.'>'.$value.'</option>'."\n";
		}
		$result .= '</select>';
		return $result;
	}

	public function getViewValue() {
		return $this->__getValue();
	}
}