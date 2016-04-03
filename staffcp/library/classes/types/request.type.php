<?php

class RequestType extends Type  {
	
	public function getFormValue($val='') {
		return '<input name="form[fk]" type="hidden" value="'.(int)$_REQUEST['fk'].'"/>';
	}
}