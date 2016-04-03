<?php

class NoneType extends Type  {
	public $hasLayout = false;
	public function getFormValue($val='') {
		return '';
	}
}