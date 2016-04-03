<?php

class ColorType extends Type  {

	public function getFormValue($val='') {
		
		$szResult = '';
		$szResult .= <<<END
		
<script type="text/javascript">
$(document).ready(function() {
	$('#color-{$this->fieldName}').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	})
	.bind('keyup', function(){
		$(this).ColorPickerSetColor(this.value);
	});
});
</script>

END;
		
		$szResult .= '<input type="text" id="color-'.$this->fieldName.'" name="form['.$this->fieldName.']" ';
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
		return '<div style="width:19px;height:19px;background:#'.$this->value.';"></div>';
	}

}