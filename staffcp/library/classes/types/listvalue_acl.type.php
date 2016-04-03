<?php

class Listvalue_aclType extends Type {
	
	public function __getValue()  {
		if (array_key_exists($this->value,$this->fieldInfo['values'])) 
			if (is_array($this->fieldInfo['values'][$this->value]))
				if (!isset($this->fieldInfo['values'][$this->value]['value'])) 
					postError('Clistvalue::__getValue problem');
				else
					return $this->fieldInfo['values'][$this->value]['value'];
			else
				return $this->fieldInfo['values'][$this->value];
		else{ 
			
				return '&nbsp;';
		}
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
		
		if ($_SESSION['__acl']['user']['is_super']==1)
		{		
			$result = '<select size="'.$szSize.'" name="form['.$this->fieldName.']" class="'.$szClass.'">';
			$szName = '';
			foreach ($this->fieldInfo['values'] as $key=>$value) {
				$szSelected ='';
				
				if ($key==$this->value) {
					$szSelected = 'selected';
					$szName = $value;
				}
				elseif ($key == $valid) {
					$selected = 'selected';
					$szName = $value;
				}
				
				if (is_array($value)) {
					if (!empty($value['class'])) 
						$szClass = $value['class'];
					else
						$szClass = '';
					$result .= '<option value="'.$key.'" class="'.$szClass.'" '.$szSelected.'>'.$value['value'].'</option>'."\n";
				} else 
					$result .= '<option value="'.$key.'" '.$szSelected.'>'.$value.'</option>'."\n";
			}
			$result .= '</select>';
		}
		else {
			$result = '<input style="width:99%" type="hidden" name="form['.$this->fieldName.']" ';
			
			if (isset($this->value))
				$result.='value="'.htmlspecialchars($this->getValue()).'" ';
			elseif (!empty($val[$this->fieldName]))
				$result.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
			$result .= '> Информация недоступна';
		}
		return $result;
	}

	public function check($value,$fieldInfo) {
		foreach ($fieldInfo['values'] as $key=>$lvalue) {
			if ($value == $key) {
				return true;
			}
		} 
		return false;
	}

	public function getViewValue() {
		return $this->__getValue();
	}
}