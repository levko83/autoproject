<?php

class HtmlareatinymceType extends Type  {
	
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
		$controlHTML = '';
		if ($simple)
		{
		$result =<<<EOD
			<textarea id="{$this->fieldName}" name="form[{$this->fieldName}]" style="width: 100%; height: 400px;">{$value}</textarea>
			<label style="color: #535353;font-family:'MS Sans Serif',sans-serif,Verdana,Arial;font-size:9pt; display: block;"><input type="checkbox" value="" checked name="tinemce" onclick="if (this.checked) tinyMCE.get('{$this->fieldName}').show(); else tinyMCE.get('{$this->fieldName}').hide();"> Включить визуальный редактор</label>
<script type="text/javascript">
	//$('#{$this->fieldName}').wysiwyg();

	// O2k7 skin (silver)
	tinyMCE.init({
		// General options
		mode : "exact",
		language: 'ru',
		elements : "{$this->fieldName}",
		theme : "advanced",
		skin : "o2k7",
		skin_variant : "black",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,outdent,indent",
		theme_advanced_buttons2 : "bullist,numlist,|,undo,redo,|,search,replace,cut,copy,paste,pastetext,pasteword,|,link,unlink,charmap,code,cleanup",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true

	});

</script>

EOD;
		} else {
			
		$result =<<<EOD
			<textarea id="{$this->fieldName}" name="form[{$this->fieldName}]" style="width: 100%; height: 400px;">{$value}</textarea>
			<label style="color: #535353;font-family:'MS Sans Serif',sans-serif,Verdana,Arial;font-size:9pt; display: block;"><input type="checkbox" value="" checked name="tinemce" onclick="if (this.checked) tinyMCE.get('{$this->fieldName}').show(); else tinyMCE.get('{$this->fieldName}').hide();"> Включить визуальный редактор</label>
<script type="text/javascript">
	//$('#{$this->fieldName}').wysiwyg();

	// O2k7 skin (silver)
	tinyMCE.init({
		// General options
		mode : "exact",
		language: 'ru',
		elements : "{$this->fieldName}",
		theme : "advanced",
		skin : "o2k7",
		skin_variant : "black",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,outdent,indent,|,bullist,numlist,|,undo,redo,|,search,replace,cut,copy,paste,pastetext,pasteword",
		theme_advanced_buttons2 : "link,unlink,image,media,charmap,code,styleselect,|,formatselect,fontselect,fontsizeselect,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,cleanup",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		// content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
//		external_link_list_url : "lists/link_list.js",
//		external_image_list_url : "lists/image_list.js",
//		media_external_list_url : "lists/media_list.js",

		file_browser_callback : 'foFileManager.open'

	});

	foFileManager.init(tinyMCE);
</script>

EOD;
		}
		return $result;
	}
	
	public function getViewValue() {
		return strip_tags(substr($this->value,0,50)).'...';
		if (!empty($this->aFieldInfo['nop'])) {
			//return preg_replace('/<p>(.*?)<\/p>/ism','$1<br>'."\r\n",$this->aValue);
			return $this->aValue;
		} else {
			return $this->aValue;
		}
	}
}