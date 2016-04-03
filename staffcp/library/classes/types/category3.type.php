<?php

class Category3Type extends Type  {
	
	public function getFormValue($val='') {
		$db = Register::get('db');
		
		$valid = @$val[$this->fieldName];
		
		$szOrder = $this->fieldInfo['ordered'];
		
		$szNameField = $this->fieldInfo['cross_name'];
		$szIndex = $this->fieldInfo['cross_index'];
		$szTable = $this->fieldInfo['cross_table'];
		$cross_join_index = $this->fieldInfo['cross_join_index'];
		
		$JoinField = $this->fieldInfo['join_name'];
		$JoinIndex = $this->fieldInfo['join_index'];
		$JoinTable = $this->fieldInfo['join_table'];
		
		
		if (!empty($this->fieldInfo['cross_cond'])) {
			$szCond = ' WHERE t1.'.$this->fieldInfo['cross_cond'].'';
		}
		elseif(!empty($this->fieldInfo['cross_group'])) {
			$szCond = ' WHERE t1.`'.$this->fieldInfo['cross_parent'].'`="'.$data->nID.'"';
		}
		else $szCond = '';
		
		//if (!empty($szOrder)) {
		//	$szCond .= ' ORDER BY '.$szTable.'.`'.$szOrder.'`';
		//}
		//else {
		//	$szCond .= ' ORDER BY '.$szTable.'.`'.$szIndex.'`';
		//}
		
		$aResult = array();
		$aData = $db->query('SELECT t1.`'.$szNameField.'`,t1.`'.$szIndex.'`,t2.`'.$JoinField.'` as name2,t2.`'.$szIndex.'` as id2 FROM `'.$szTable.'` t1 LEFT JOIN '.$JoinTable.' t2 on (t1.'.$cross_join_index.'=t2.'.$JoinIndex.') '.$szCond.' ORDER BY t2.`'.$JoinField.'`');
		
		//var_dump('SELECT t1.`'.$szNameField.'`,t1.`'.$szIndex.'`,t2.`'.$JoinField.'` as name2,t2.`'.$JoinIndex.'` as id2 FROM `'.$szTable.'` t1 LEFT JOIN '.$JoinTable.' t2 on (t1.'.$cross_join_index.'=t2.'.$JoinIndex.') '.$szCond.' ORDER BY t2.`'.$JoinField.'`');

		$aParse = array();
		$result = '<select name="form['.$this->getFieldName().']">';
		
		if (!empty($this->fieldInfo['first'])) {
			foreach($this->fieldInfo['first'] as $key=>$value)
			{
				if ($value == $this->value)
					$selected = 'selected';
				elseif ($value == $valid)
					$selected = 'selected';
				else
					$selected = '';
				$result .= '<option value="'.$key.'" '.$selected.'>'.htmlspecialchars($value).'</option>';
			}
		}
		foreach ($aData as $key=>$value) {

			if ($value['id2'] == $_REQUEST['parent'])
				$selected = 'selected';
			elseif ($value[$szIndex] == $_REQUEST['fk'])
				$selected = 'selected';
			elseif ($_REQUEST['parent']==$value[$szIndex])
				$selected = 'selected';
			elseif ($value[$szIndex] == $this->value) 
				$selected = 'selected';
			elseif ($value[$szIndex] == $valid)
				$selected = 'selected';
			else 
				$selected = '';
			$result .= '<option value="'.$value['id2'].'" '.$selected.'>'.$value['name2'].' &raquo; '.htmlspecialchars($value[$szNameField]).'</option>';
		}
		$result .= "</select>";
		
		return $result;
	}
	
	public function getViewValue() {
		$current = $this->getValue();
		if (empty($current))
			return null;
		$db = Register::get('db');
		$szOrder = $this->fieldInfo['ordered'];
		
		$szNameField = $this->fieldInfo['cross_name'];
		$szIndex = $this->fieldInfo['cross_index'];
		$szTable = $this->fieldInfo['cross_table'];
		$cross_join_index = $this->fieldInfo['cross_join_index'];
		
		$JoinField = $this->fieldInfo['join_name'];
		$JoinIndex = $this->fieldInfo['join_index'];
		$JoinTable = $this->fieldInfo['join_table'];
		
		$aResult = $db->query('SELECT t1.`'.$szNameField.'`,t1.`'.$szIndex.'`,t2.`'.$JoinField.'` as name2 FROM `'.$szTable.'` t1 LEFT JOIN '.$JoinTable.' t2 on (t1.'.$cross_join_index.'=t2.'.$JoinIndex.') where t1.`'.$szIndex.'`='.$this->value.' LIMIT 0,1;');
		$szDefault = isset($this->fieldInfo['default'])?$this->fieldInfo['default']:'&nbsp;';
		$szResult = (!empty($aResult[0][$szNameField])?$aResult[0][$szNameField].' &raquo; '.$aResult[0]['name2']:$szDefault);
		//var_dump($szResult);
		return $szResult;
	}

}