<?php
/**
 * @todo Reafactor this code
 */
class CmsGeneratorConfig {
	
	public $fields;
	public static $VALIDATE_FIELD_TEXT = 'text';
	public static $VALIDATE_FIELD_EMAIL = 'email';
	public static $VALIDATE_FIELD_EXIST = 'exist';
	public static $VALIDATE_FIELD_FILE = 'file';
	public static $VALIDATE_FIELD_SUBMIT = 'submit';
	public static $VALIDATE_FIELD_NUM = 'num';
	
	//	private $tabs;
	private $config;
	private $modelName;
	
	public function __construct($modelName)
	{
		$this->modelName = $modelName;
		if (!empty($GLOBALS['cmsGenerator'][$modelName]))
		{
			$this->config = $GLOBALS['cmsGenerator'][$modelName];
			$this->initFields();
		}
	}
	
	public function getModelName()
	{
		return $this->modelName;
	}
	
	private function initFields()
	{
		
		$this->fields = array();
		if (isset($this->config['fields']) && count($this->config['fields'])>0){
			foreach ($this->config['fields'] as $name=>$field)
			{
				if (!is_array($field))
					$field = array('type'=>$field);
				$fieldType = $field['type'];
				$fieldClass = ucfirst($fieldType).'Type';
				$this->fields[$name] = new $fieldClass($name, $field, null);
				$this->fields[$name]->table = $this->config['table'];
			}
		}
	}
	
	public function setValues($values)
	{
		$indexValue = null;
		foreach ($values as $fieldName => $fieldValue)
		{
			if (!empty($this->fields[$fieldName])) {
				$this->fields[$fieldName]->setValue($fieldValue);
				if ($this->fields[$fieldName]->fieldInfo['type'] == 'index')
					$indexValue = $fieldValue;
			}
		}

		foreach ($this->fields as $fieldName => $fieldValue)
		{
			if (!empty($this->fields[$fieldName]))
				$this->fields[$fieldName]->indexValue = $indexValue;
		}
	}
	
	public function getFieldLabel($fieldName)
	{
		if (!empty($this->config['generator']['fields'][$fieldName]))
			return $this->config['generator']['fields'][$fieldName];
		
		$label = str_replace('_',' ',$fieldName);
		$label = ucfirst($label);
		return $label;	
	}
	
	public function getEditTabs()
	{
		if (empty($this->config['generator']['edit']['fields']))
			return null;
		$aTabs = array();
		foreach ($this->config['generator']['edit']['fields'] as $name => $fields)
			$aTabs[] = $name;
		return $aTabs;
	}
	
	public function getEditTabFields($tabName)
	{
		if (empty($this->config['generator']['edit']['fields'][$tabName]))
			return null;
		$aFields = array();
		foreach ($this->config['generator']['edit']['fields'][$tabName] as $fieldName)
			if (!empty($this->fields[$fieldName]))
				$aFields[$fieldName] = $this->fields[$fieldName];
		return $aFields;
	}
	
	public function getRequiredFields()
	{
		if (empty($this->config['generator']['required']))
			return null;
		
		$aFields = array();
		foreach ($this->config['generator']['required'] as $fieldType=>$fieldCode)
		{
			$aFields[] = array(
				'label' => $this->config['generator']['fields'][$fieldType],
				'name'	=> $fieldType,
				'type'	=> $fieldCode
			);
			
		}
		return $aFields;
	}
	
	public function getDisabledFields()
	{
		if (empty($this->config['generator']['disabled']))
			return null;
		
		$aFields = array();
		foreach ($this->config['generator']['disabled'] as $fieldCode=>$fieldType){
			$aFields[] = $fieldType;
		}
		
		return $aFields;
	}
	
	public function getAddTabs()
	{
		if (empty($this->config['generator']['add']['fields']))
			return null;
		$aTabs = array();
		foreach ($this->config['generator']['add']['fields'] as $name => $fields)
			$aTabs[] = $name;
		return $aTabs;
	}
	
	public function getAddTabFields($tabName)
	{
		if (empty($this->config['generator']['add']['fields'][$tabName]))
			return null;
		$aFields = array();
		foreach ($this->config['generator']['add']['fields'][$tabName] as $fieldName)
			if (!empty($this->fields[$fieldName]))
				$aFields[$fieldName] = $this->fields[$fieldName];
		return $aFields;
	}
	
	public function getListFields()
	{
		if (empty($this->config['generator']['list']['fields']))
			return null;
		$aFields = array();
		foreach ($this->config['generator']['list']['fields'] as $fieldName)
			if (!empty($this->fields[$fieldName]))
				$aFields[$fieldName] = $this->fields[$fieldName];
		return $aFields;
	}
	
	public function getLayout(){
		return isset($this->config['layout'])?$this->config['layout']:false;
	}
	
	public function getListTitle()
	{
		if (empty($this->config['generator']['list']['title']))
			return $this->modelName;
		return $this->config['generator']['list']['title'];
	}
	
	public function getAddTitle()
	{
		if (empty($this->config['generator']['add']['title']))
			return $this->modelName;
		return $this->config['generator']['add']['title'];
	}
	
	public function getEditTitle()
	{
		if (empty($this->config['generator']['edit']['title']))
			return $this->modelName;
		return $this->config['generator']['edit']['title'];
	}
	
	public function getAddSubmit()
	{
		if (empty($this->config['generator']['add']['submit']))
			return $this->modelName;
		return $this->config['generator']['add']['submit'];
	}
	
	public function getEditSubmit()
	{
		if (empty($this->config['generator']['edit']['submit']))
			return $this->modelName;
		return $this->config['generator']['edit']['submit'];
	}
	
	public function getTable()
	{
		if (empty($this->config['table']))
			return DB_PREFIX.$this->modelName;
		return $this->config['table'];
	}
	
	public function getTitle()
	{
		if (empty($this->config['title']))
			return '';
		
		return $this->config['title'];
	}
	
	public function getDescr()
	{
		return isset($this->config['descr'])?$this->config['descr']:'';
	}

	public function getParentTitle()
	{
		if (empty($this->config['parent']['title']))
			return null;
		return $this->config['parent']['title'];
	}

	public function getParentUrl()
	{
		if (empty($this->config['parent']['url']))
			return null;
		return $this->config['parent']['url'];
	}
	
	public function getIndexField()
	{
		foreach ($this->config['fields'] as $name=>$field)
		{
			$type = (!is_array($field))?$field:$field['type'];
			if ($type == 'index')
				return $name;
		}
		return null;
	}
	
	public function getSaveData($data)
	{
		$formData = array();
		$indexValue = null;
		foreach ($data as $key=>$value)
		{
			if (!empty($this->fields[$key]))
			{
				if ($this->fields[$key]->fieldInfo['type'] == 'index')
					$indexValue = $this->fields[$key]->getSaveValue($value);
			}
		}
		foreach ($data as $key=>$value)
		{
			if (!empty($this->fields[$key]))
			{
				$this->fields[$key]->indexValue = $indexValue;
				$fieldSaveValue = $this->fields[$key]->getSaveValue($value);
				if ($fieldSaveValue != Type::NOT_SET)
					$formData[$key] = $fieldSaveValue;
			}
		}
		return $formData;
	}
}