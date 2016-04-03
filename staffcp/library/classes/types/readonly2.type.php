<?php

class Readonly2Type extends Type  {

	static $bread_crumbs = array();
	var $nexts = array();
	var $tree = array();
	
	public function getFormValue($val='') {
		
		$db = Register::get('db');
		
		if (!$this->getValue())
			$this->setValue($_REQUEST['parent']);
		
		$szResult = '<input style="width:100px" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value))
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		elseif (!empty($val[$this->fieldName]))
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		$szResult .= ' >';
		
		$this->getLevelsBack($this->getValue());
		$bread_crumbs = @array_reverse($this->bread_crumbs);
		
		$ibb='';
		if (count($bread_crumbs)>0){
			foreach ($bread_crumbs as $bb){
				$ibb .= $bb['name'].' / ';
			}
		}
		
		$szResult .= '<span class="label">* '.$ibb.'</span>';
		
		return $szResult;
	}

	public function getViewValue() {
		return htmlspecialchars($this->value);
	}

	
	function getLevelsBack($id) {
		$ids = $this->getCatName($id);
		if (!empty($ids['name'])){
			$this->tree []= (int)$ids['id'];
			$this->bread_crumbs []= array("id"=>(int)$ids['id'],"name"=>$ids['name']);
			if (!empty($ids['parent'])) {
				$this->getLevelsBack($ids['parent']);
			}
		}
	}
	
	function getCatName($id){
		$db = Register::get('db');
		$sql = "select id,name,parent from ".DB_PREFIX."cat where id='".(int)$id."';";
		return $db->get($sql);
	}
}