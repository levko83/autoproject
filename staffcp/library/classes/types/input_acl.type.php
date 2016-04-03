<?php

class Input_aclType extends Type  {

	public function getFormValue($val='') {
		
		$translates = Register::get('translates');
		
		if ($_SESSION['__acl']['user']['is_super']==1)
		{
			$szResult = '<input style="width:99%" type="text" name="form['.$this->fieldName.']" ';
			
			if (isset($this->value))
				$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
			elseif (!empty($val[$this->fieldName]))
				$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
			$szResult .= '>';
		}
		else {
			$szResult = '<input style="width:99%" type="hidden" name="form['.$this->fieldName.']" ';
			
			if (isset($this->value))
				$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
			elseif (!empty($val[$this->fieldName]))
				$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
			$szResult .= '> '.$translates['denyinfo'].'';
		}
		
		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>';
		
		return $szResult;
	}

	public function getViewValue() {
		return htmlspecialchars($this->value);
	}

}