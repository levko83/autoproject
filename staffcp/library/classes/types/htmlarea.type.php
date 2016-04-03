<?php

class HtmlareaType extends Type  {
	
	public function getFormValue($val='') {
//		global $control;
		
		if (isset($this->value)) {
			$value = htmlspecialchars($this->value);
			$value = stripcslashes($this->value);
		}
		elseif (!empty($val[$this->fieldName])) {
			//$value = htmlspecialchars($val[$this->fieldName]);
			$value = stripcslashes($val[$this->fieldName]);
		}
			
		$simple = !empty($this->fieldInfo['simple']);
		
		$small = $this->fieldInfo['small'];
		$heightPx = ($small)?'200px':'800px';
		if ($small){
			echo "<style>#cke_".$this->fieldName." .cke_contents { height:200px !important; }</style>";
		}
		
		$controlHTML = '';
		if ($simple)
		{
		$result =<<<EOD

<textarea id="{$this->fieldName}" name="form[{$this->fieldName}]" style="width: 100%; height: {$heightPx};">{$value}</textarea>
<script type="text/javascript">
//<![CDATA[
var ckeditor = CKEDITOR.replace("{$this->fieldName}");
AjexFileManager.init({
	returnTo: 'ckeditor',
	editor: ckeditor
});
//]]>
</script>

EOD;
		} else {
			
		$result =<<<EOD
			
<textarea id="{$this->fieldName}" name="form[{$this->fieldName}]" style="width: 100%; height: {$heightPx};">{$value}</textarea>
<script type="text/javascript">
//<![CDATA[
var ckeditor = CKEDITOR.replace("{$this->fieldName}");
AjexFileManager.init({
	returnTo: 'ckeditor',
	editor: ckeditor
});
//]]>
</script>

EOD;
		}
		return $result;
	}
	
	public function getViewValue() {
		return substr(strip_tags($this->value),0,50).'...';
		if (!empty($this->aFieldInfo['nop'])) {
			//return preg_replace('/<p>(.*?)<\/p>/ism','$1<br>'."\r\n",$this->aValue);
			return $this->aValue;
		} else {
			return $this->aValue;
		}
	}
}