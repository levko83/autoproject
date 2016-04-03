<?php

class Imp_offices_paramsType extends Type  {
	
	public function getFormValue($val='') {
		$db = Register::get('db');
		
		$fieldName = $this->fieldName;
		$indexValue = $this->indexValue;
		
		$sql = "
			SELECT 
				offices.id,
				offices.name,
				iop.delivery
			FROM ".DB_PREFIX."offices offices 
			LEFT JOIN ".DB_PREFIX."importers_offices_params iop ON iop.office_id =  offices.id AND iop.imp_id = '".(int)$indexValue."'
			ORDER BY name;";
		//var_dump($sql);
		$res = $db->query($sql);
		
		$result = '<input type="hidden" name="form['.$fieldName.']" value="1">';
		$result .= '<table cellpadding="3px" cellspacing="0px" style="border:0px;">';
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$result .= '<tr>';
					$result .= '<td style="vertical-align:middle;">Офис "<b>'.$dd['name'].'</b>"</td>';
					$result .= '<td style="vertical-align:middle;"><input type="text" name="form['.$fieldName.']['.$dd['id'].']" value="'.$dd['delivery'].'"> (дн.)</td>';
				$result .= '</tr>';
			}
		}
		$result .= '</table>';
		
		return $result;
	}

	function getSaveValue($values) {
		$db = Register::get('db');
		if (!empty($this->indexValue)){
			$db->post('DELETE FROM '.DB_PREFIX.'importers_offices_params WHERE `imp_id`=\''.(int)$this->indexValue.'\'');
		} else {
			$this->indexValue = $db->getAutoIncrement($this->table);
		}
		if (isset($values) && count($values)>0){
			foreach ($values as $office_id=>$delivery){
				if ($office_id && $delivery)
				$db->post("INSERT INTO ".DB_PREFIX."importers_offices_params (`imp_id`,`office_id`,`delivery`) VALUES ('".(int)$this->indexValue."','".(int)$office_id."','".mysql_real_escape_string($delivery)."');");
			}
		}
		return Type::NOT_SET;
	}
	
	public function getViewValue() {}
}

?>