<?php
class GooglecoordsType extends Type  {
	public function getFormValue($val='') {
		$szResult = '<input style="width:99%" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		$szResult .= '>';
		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>';
			
		$szResult .= 'Определение координат на сайте: <a target="_blank" href="http://3planeta.com/googlemaps/karty-google-maps.html">http://3planeta.com/googlemaps/karty-google-maps.html</a>';
		return $szResult;
	}
	public function getViewValue() {
		return htmlspecialchars($this->value);
	}
}
?>