<?php

class MultipleType extends Type  {

	/* not valid */
	public function getFormValue() {
		
		$translates = Register::get('translates');
		
		$db = Register::get('db');
		$link = $this->fieldInfo['link'];
		$foreign = $this->fieldInfo['foreign'];
		$fieldName = $this->fieldName;
		
		$sql = 'SELECT * FROM '.$foreign['table'];
		if (!empty($foreign['cond']))
			$sql .= ' WHERE '.$foreign['cond'];
		if (!empty($foreign['order']))
			$sql .= ' ORDER BY '.$foreign['order'].'';
		
		$foreignData = $db->query($sql);

		$selectedValues = array();
		$indexValue = $this->indexValue;
		if (!empty($indexValue))
		{
			$sql = 'SELECT * FROM '.$link['table'].' WHERE `'.$link['source_id'].'`=\''.$indexValue.'\'';
			$linkData = $db->query($sql);
			foreach($linkData as $value)
				$selectedValues[] = $value[$link['foreign_id']];
		}
		
		$result = '<input type="hidden" name="form['.$fieldName.']" value="1">';
		$result .= '<table class="list">';
		$result .= '<thead><th width="20px">ID</th><th width="20px">'.$translates['select'].'</th><th>'.$translates['admin.main.value'].'</th></thead>';
		$result .= '<tbody>';
		for($i = 0; $i < count($foreignData); $i +=3 )
		{
			$value = $foreignData[$i];
			$selected = (in_array($value[$foreign['id']], $selectedValues))?'checked':'';
			
			$result .= '<tr>';
			$result .= '<td>'.$value[$foreign['id']].'</td><td><label><input type="checkbox" name="form['.$fieldName.'][]" '.$selected.' value="'.$value[$foreign['id']].'" /></label></td><td>'.$value[$foreign['name']].'</td>';
			$result .= '</tr>';
			
			$result .= '<tr>';
			if (!empty($foreignData[$i + 1]))
			{
				$value = $foreignData[$i + 1];
				$selected = (in_array($value[$foreign['id']], $selectedValues))?'checked':'';
				$result .= '<td>'.$value[$foreign['id']].'</td><td><label><input type="checkbox" name="form['.$fieldName.'][]" '.$selected.' value="'.$value[$foreign['id']].'" /></label></td><td>'.$value[$foreign['name']].'</td>';
			} else {
				$result .= '<td></td>';
			}
			$result .= '</tr>';
			
			$result .= '<tr>';
			if (!empty($foreignData[$i + 2]))
			{
				$value = $foreignData[$i + 2];
				$selected = (in_array($value[$foreign['id']], $selectedValues))?'checked':'';
				$result .= '<td>'.$value[$foreign['id']].'</td><td><label><input type="checkbox" name="form['.$fieldName.'][]" '.$selected.' value="'.$value[$foreign['id']].'" /></label></td><td>'.$value[$foreign['name']].'</td>';
			} else {
				$result .= '<td></td>';
			}
			$result .= '</tr>';
		}
		$result .= '</tbody>';
		$result .= '</table>';
		
		
		return $result;
	}

	function getSaveValue($values) {
		$db = Register::get('db');
		$link = $this->fieldInfo['link'];
		if (!empty($this->indexValue))
		{
			$sql = 'DELETE FROM '.$link['table'].' WHERE `'.$link['source_id'].'`=\''.$this->indexValue.'\'';
			$db->query($sql);
		} else {
			$this->indexValue = $db->getAutoIncrement($this->table);
		}
		if (is_array($values))
		{
			foreach($values as $val)
			{
				$sql = 'INSERT INTO '.$link['table'].' (`'.$link['source_id'].'`, `'.$link['foreign_id'].'`)
					VALUES (\''.$this->indexValue.'\', \''.mysql_real_escape_string($val).'\')';
				$db->query($sql);
			}
		}
		return Type::NOT_SET;
	}

	function getViewValue() {
		$db = Register::get('db');
		$link = $this->fieldInfo['link'];
		$foreign = $this->fieldInfo['foreign'];
		$fieldName = $this->fieldName;

		$indexValue = $this->indexValue;
		
		$sql = 'SELECT * FROM '.$foreign['table'].' as `f`';
		$sql .= 'LEFT JOIN '.$link['table'].' as `l` ON f.`'.$foreign['id'].'`=l.`'.$link['foreign_id'].'`';
		$sql .= ' WHERE l.`'.$link['source_id'].'`=\''.$this->indexValue.'\'';

		if (!empty($foreign['order']))
			$sql .= ' ORDER BY f.'.$foreign['order'].'';

		$foreignData = $db->query($sql);

		$printData = array();
		foreach($foreignData as $item)
			$printData[] = $item[$foreign['name']];

		$result = join(', ',$printData);
		return $result;
	}

}