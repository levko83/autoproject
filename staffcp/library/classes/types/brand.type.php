<?php
class BrandType extends Type  {
	
	public function getFormValue($val='') {
		
		$szResult = '<input style="width:99%" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';

		$szResult .= '>';

		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>123';

		return $szResult;
	}

	public function getViewValue() {
		return $this->__getValue();
	}
	
	function __getValue() {
		
		$db = Register::get('db');
		$sql = "
			SELECT 
				B2.BRA_BRAND
			FROM ".DB_PREFIX."brands B1
			LEFT JOIN ".DB_PREFIX."brands B2 ON B1.BRA_ID_GET = B2.BRA_ID
			WHERE 
				B1.BRA_BRAND LIKE '".mysql_real_escape_string($this->value)."';";
		$res = $db->get($sql);
		
		if ($res && ($this->value != $res['BRA_BRAND']))
			return $this->value.' <sup>('.$res['BRA_BRAND'].')</sup>';
		else 
			return $this->value;
	}

	public function getSaveValue($value='') {
		return $value;
	}
}
?>