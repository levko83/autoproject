<?php

class AliasType extends Type  {

	public function getFormValue($val='') {
		
		$alias = $this->getDbValue();
		$alias = strtolower($this->doTraslit($alias));
		if (empty($this->value)) {
			$this->setValue($alias);
		}
		
		$szResult = '<input style="width:99%" type="text" name="form['.$this->fieldName.']" ';
		if (isset($this->value)){
			$szResult.='value="'.htmlspecialchars($this->getValue()).'" ';
		}
		elseif (!empty($val[$this->fieldName])){
			$szResult.='value="'.htmlspecialchars($val[$this->fieldName]).'" ';
		}
		else {
			$szResult.='value="" ';
		}
		$szResult .= '/>';
		
		if (!empty($this->fieldInfo['label']))
			$szResult .= '<span class="label">* '.$this->fieldInfo['label'].'</span>';
		
		return $szResult;
	}

	public function getViewValue() {
		return htmlspecialchars($this->value);
	}
	
	private function getDbValue(){
		$db = Register::get('db');
		$sql = "SELECT `".(is_array($this->fieldInfo['field'])?join("`,`", $this->fieldInfo['field']):$this->fieldInfo['field'])."` FROM `".$this->table."` WHERE `".$this->fieldInfo['index']."` = '".mysql_real_escape_string($this->indexValue)."';";
		$res = $db->get($sql);
		if (is_array($this->fieldInfo['field'])) {
			return join(" ", $res);
		}
		else {
			return isset($res[$this->fieldInfo['field']])?$res[$this->fieldInfo['field']]:"";
		}
	}
	
	private function translitIt($str){
		$tr = array("А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y","ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", " "=> "_", "."=> "", "/"=> "_");
		return strtr($str,$tr);
	}
	
	private function doTraslit($urlstr){
		if (preg_match('/[^A-Za-z0-9_\-]/', $urlstr)) {
			$urlstr = $this->translitIt($urlstr);
			$urlstr = preg_replace('/[^A-Za-z0-9_\-]/', '', $urlstr);
			$urlstr = str_replace("__", "_", $urlstr);
		}
		return $urlstr;
	}

}