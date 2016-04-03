<?php

class Orm {
	
	protected $condition = null;
	protected $table = null;
	protected $extensions = null;
	protected $primaryKey = null;
	
	
	public function __construct($table = null, $primaryKey = null)
	{
		$this->table = $table;
		$this->primaryKey = $primaryKey;
	}
	
	public function select()
	{
		$this->condition = new OrmCondition($this, $this->table);
		return $this->condition;
	}
	
	public function fetchOne()
	{
		$this->condition->limit(0,1);
		$sql = $this->condition->selectSql();
		$db = Register::get('db');
		$rows = $db->query($sql);
		if (count($rows) > 0)
			return $rows[0];
		return null;
	}
	
	public function fetchLast()
	{
		$sql = $this->condition->selectSql();
		$db = Register::get('db');
		$rows = $db->query($sql);
		if (count($rows) > 0)
			return $rows[count($rows)-1];
		return null;		
	}
	
	public function fetchSingle()
	{
		$sql = $this->condition->selectSql();
		$db = Register::get('db');
		$rows = $db->query($sql);
		if (count($rows) > 0) {
			$keys = array_keys($rows[0]);
			return $rows[0][$keys[0]];
		}
		return null;		
	}
	
	public function fetchAll()
	{
		$sql = $this->condition->selectSql();
		$db = Register::get('db');
		$rows = $db->query($sql);
		$rows->setModel($this);
		return $rows;
	}
	
	public function update($data, $cond = '1=1')
	{
		$cond = $this->prepareCondition($cond);
		$items = array();
		foreach ($data as $key=>$value) {
			$items[] = "`{$key}` = '".mysql_real_escape_string($value)."'";
		}
		$sql = "UPDATE `{$this->table}` SET ".join(', ',$items)." WHERE ".$cond;
		$db = Register::get('db');
		$db->post($sql);
	}
	
	public function insert($data)
	{
		$items = array();
		foreach ($data as $key=>$value)
			$data[$key] = mysql_real_escape_string($value);
		$sql = "INSERT INTO `{$this->table}` (`".join('`, `', array_keys($data))."`) VALUES ('".join("', '",$data)."')";
		$db = Register::get('db');
		
		$db->post($sql);
	}

	public function delete($cond = '1=1')
	{
		$cond = $this->prepareCondition($cond);
		$sql = "DELETE FROM `{$this->table}` WHERE ".$cond;
		$db = Register::get('db');
		$db->post($sql);
	}
	
	public function attach($extensionName)
	{
		$className = ucfirst($extensionName).'Extension';
		$object = new $className($this);
		$this->extensions[] = $object;
	}
	
	public function callExtension($method, $param)
	{
		if (!empty($this->extensions))
		{
			foreach ($this->extensions as $extension)
			{
				if (is_callable(array($extension, $method)))
				{
					if (!is_array($param))
						$param = array($param);
					return call_user_method_array($method, $extension,  $param);
				}
			}
		}
		return false;
	}
	
	public function getCode()
	{
		$modelName = get_class($this);
		$modelCode = str_replace('Model','',$modelName);
		$modelCode = strtolower($modelCode);
		return $modelCode;
	}
	
	public function getTable()
	{
		return $this->table;
	}
	
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}
	
	public function __call($methodName, $arguments)
	{
		if (($result = $this->callExtension($methodName, $arguments)) !== false) {
			return $result;
		} else {
			throw new Exception("Unknow method ".$methodName);
		}
		
	}

	private function prepareCondition($condition)
	{
		if (is_string($condition))
			return $condition;

		if (is_array($condition))
		{
			$conditionTerms = array();
			foreach ($condition as $key=>$value)
				$conditionTerms[] = "`{$key}` = '".mysql_real_escape_string($value)."'";
			$conditionSql = join(' AND ', $conditionTerms);
			return $conditionSql;
		}

		if (is_a($condition, 'OrmCondition'))
		{
			$conditionSql = $condition->getWhere(true);
			return $conditionSql;
		}
		
		return null;
	}
}