<?php
class InputType extends Type  {
	public function getFormValue($val='') {
		
		$code = (isset($this->fieldInfo['settings_code']) && $this->fieldInfo['settings_code'])?$this->fieldInfo['settings_code']:false;
		if (empty($this->value) && empty($val[$this->fieldName]) && $code) {
			$db = Register::get('db');
			$sql = "SELECT `value` FROM ".DB_PREFIX."settings WHERE `code`='".mysql_real_escape_string($code)."';";
			$get = $db->get($sql);
			$this->value = $get['value'];
		}
		
		$style = isset($this->fieldInfo['style'])?$this->fieldInfo['style']:'';
		$szResult = '<input style="width:99%;'.$style.'" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
			
		$szResult .= '>';
		
		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>';
			
		return $szResult;
	}
	public function getViewValue() {
		$class = isset($this->fieldInfo['class'])?$this->fieldInfo['class']:false;
		if ($class) {
			return '<span title="'.htmlspecialchars(strip_tags($this->value)).'" class="'.$class.'">'.htmlspecialchars(strip_tags($this->value)).'</span>';
		}
		return htmlspecialchars(strip_tags($this->value));
	}

	public function getSaveValue($value='') {
		$func = $this->fieldInfo['func'];
		
		if ($func == 'clear'){
			return FuncModel::stringfilter($value);
		}
		elseif ($func == 'tecdoc_url_car'){
			
			$value = str_replace("http://","",$value);
			$value = explode("/",$value);
			$value = array_pop($value);
			$value = str_replace("id_","",$value);
			
			return $value;
		}
		else {
			return $value;
		}
		exit();
	}
}
?>