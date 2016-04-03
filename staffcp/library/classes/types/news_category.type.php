<?php

class News_categoryType extends Type  {
	
	public function getFormValue($val='') {
		$db = Register::get('db');
		$translates = Register::get('translates');
		
		$valid = @$val[$this->fieldName];
		
		$onlyRoot = !empty($this->aFieldInfo['only_root'])?$this->aFieldInfo['only_root']:false;
		
		$aData = $db->query('SELECT id, name FROM `'.DB_PREFIX.'news_category` WHERE parent_id = 0 ORDER BY `order` ASC');
		if (!$onlyRoot)
		{
			foreach ($aData as $key=>$value)
			{
				$aData[$key]['children'] = $db->query('SELECT id, name FROM `'.DB_PREFIX.'news_category` WHERE parent_id = '.$value['id'].' ORDER BY `order` ASC');
			}
		}
		$acl = new Acl(Acl::getAuthedUserId());
		$listIds = $acl->getListIds('news_category');
			
		$result = '<select name="form['.$this->getFieldName().']">';
		if ($onlyRoot)
			$result .= '<option value="0">'.$translates['admin.main.no'].'</option>';
		foreach ($aData as $key=>$value) {
			
			if ($value['id'] == $this->value)
				$selected = 'selected';
			elseif ($value['id'] == $valid)
				$selected = 'selected';
			else 
				$selected = '';
				
			if ($listIds == 'all' || in_array($value['id'], explode(',',$listIds)))
			{
				$result .= '<option value="'.$value['id'].'" '.$selected.'>'
					.htmlspecialchars($value['name']).'</option>';
				if (!empty($value['children']))
				{
					foreach ($value['children'] as $child)
					{
						if ($child['id'] == $this->value)
							$selected = 'selected';
						else 
							$selected = '';
						$result .= '<option value="'.$child['id'].'" '.$selected.'> - '
						.htmlspecialchars($child['name']).'</option>';
					}
				}
			}
			
		
		}
		$result .= "</select>";
		
		return $result;
	}
	
	public function getViewValue() {
		global $_BASE_DATA;
		$current = $this->getValue();
		if (empty($current))
			return 'нет';
		$db = Register::get('db');
		
		$aResult = $db->query('SELECT * FROM `'.DB_PREFIX.'news_category` WHERE `id`='.$this->value.' LIMIT 0,1');
		$szResult = (!empty($aResult[0]['name'])?$aResult[0]['name']:'нет');
		return $szResult;
	}

}