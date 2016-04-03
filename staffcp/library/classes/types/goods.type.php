<?php

class GoodsType extends Type  {

	var $tmp = array();
	
	public function getFormValue($val='') {
		
		$db = Register::get('db');
		
		if (!$this->getValue())
			$this->setValue($_REQUEST['parent']);
		
		$product = $this->getById($this->getValue());
		
		$szResult = '<input readonly style="width:100px" type="hidden" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		$szResult .= '> ID:'.$product['id'].' - '.$product['name'];
		
		return $szResult;
	}

	public function getViewValue() {
		$product = $this->getById($this->value);
		return 'ID:'.$product['id'].' - '.$product['name'];
	}
	
	function getById($id){
		
		if (isset($this->tmp[$id]) && $this->tmp[$id])
			return $this->tmp[$id];
		
		$db = Register::get('db');
		$sql = "
			SELECT 
				P.id,CONCAT(C.name,' ',P.name) name
			FROM ".DB_PREFIX."products P
			LEFT JOIN ".DB_PREFIX."cat C ON P.fk=C.id
			WHERE P.id='".(int)$id."';";
		$res = $db->get($sql);
		
		$this->tmp[$id]= $res;
		
		return $res;
	}
}