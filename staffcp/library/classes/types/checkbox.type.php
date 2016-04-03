<?php

class CheckboxType extends Type  {
	
	var $nId;
	var $szDataName;
	
	public function getFormValue($val='') {
		
		/* valid */
		if (!empty($val[$this->fieldName]))
			$this->setValue($val[$this->fieldName]);
			
		$value = $this->getValue();
		if (empty($value)){
			$value = 0;
			$checked = '';
		} else {
			$checked = 'checked';
		}

		$result=<<<EOD
<input type="hidden" id="{$this->fieldName}" name="form[{$this->fieldName}]" value="{$value}">
<input type="checkbox" id="{$this->fieldName}_checkbox" onclick="document.getElementById('$this->fieldName').value=(this.checked?1:0);" {$checked}> 
&nbsp;<label for="{$this->fieldName}_checkbox"> {$this->fieldInfo['label']} </label>
EOD;
		return $result;
	}
	
	public function getViewValue() {
		
		$value = $this->getValue();
		if (!$value){
			$value = 0;
		}
		
		$indexField = (isset($this->fieldInfo['index']) && $this->fieldInfo['index'])?$this->fieldInfo['index']:'id';
		
		$result='
		<input 
		type="checkbox" 
		name="" 
		value="" 
		id="'.$this->fieldName.'_checkbox_'.$this->indexValue.'"
		onchange="setvalueajax(\''.$this->table.'\',\''.$this->fieldName.'\',\''.$indexField.'\',\''.$this->indexValue.'\');" '.(($value)?'checked':'').'>
		<span id="'.$this->fieldName.'_checkbox_'.$this->indexValue.'_result"></span>
		';
		//$result='<img src="/staffcp/media/images/checkbox'.$value.'.gif" >';
		
		return $result;
	}
}