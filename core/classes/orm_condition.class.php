<?php

class OrmCondition {
	
	private $fields = null;
	private $where = null;
	private $order = null;
	private $group = null;
	private $having = null;
	private $limit = null;
	private $joins = null;
	private $leftjoins = null;
	
	private $from = null;
	private $orm = null;
	
	public function __construct($orm = null, $from = null) 
	{
		$this->where = array();
		$this->order = array();
		$this->group = array();
		$this->having = array();
		$this->fields = array();
		$this->joins = array();
		$this->leftjoins = array();
		if (!empty($from))
			$this->from = $from;
		if (!empty($orm))
			$this->orm = $orm;
			
// 		$this->fields($orm->getTable().'.*');
	}
	
	public function from($from)
	{
		$this->from = $from;
		return $this;
	}
	
	public function limit($from, $to)
	{
		$this->limit = $from.', '.$to;
		return $this;
	}
	
	public function where($where)
	{

		$functionArgs = func_get_args();
		$db = Register::get('db');
		$where = $db->prepareQuery($where, $functionArgs);
		$this->where[] = $where;
		
		return $this;
	}
	
	public function having($having)
	{
		if (is_string($having))
		{
			$this->having[] = $having;
		} else {
			$this->having = array_merge($this->having, $having);
		}
		return $this;
	}	

	public function group($group)
	{
		if (is_string($group))
		{
			$this->group[] = $group;
		} else {
			$this->group = array_merge($this->group, $group);
		}
		return $this;
	}

	public function order($order)
	{
		if (is_string($order))
		{
			$this->order[] = $order;
		} else {
			$this->order = array_merge($this->order, $order);
		}
		return $this;
	}
	
	public function fields($fields = "*")
	{
		if (is_string($fields))
		{
			$this->fields[] = $fields;
		} else {
			$this->fields = array_merge($this->fields, $fields);
		}
		return $this;
	}
	
	public function join($table, $condition = '1=1', $type = 'LEFT')
	{
		$this->joins[] = array('table' => $table, 'condition' => $condition, 'type' => $type);
		return $this;
	}
	
	public function leftjoin($table, $condition = '1=1', $type = 'LEFT')
	{
		$this->leftjoins[] = array('table' => $table, 'condition' => $condition, 'type' => $type);
		return $this;
	}
	
	public function getWhere($conditionOnly = false)
	{
		$cond = "";
		if (count($this->where) > 0)
		{
			$cond = '('.join(') AND (',$this->where).') ';
			if (!$conditionOnly)
				$cond = 'WHERE '.$cond;
		}
		return $cond;
	}
	
	public function getHaving()
	{
		$cond = "";
		if (count($this->having) > 0)
		{
			$cond = 'HAVING ('.join(') AND (',$this->having).') ';
		}
		return $cond;
	}
	
	public function getOrder()
	{
		$cond = "";
		if (count($this->order) > 0)
		{
			$cond = 'ORDER BY '.join(', ',$this->order).' ';
		}
		return $cond;
	}
	
	public function getGroup()
	{
		$cond = "";
		if (count($this->group) > 0)
		{
			$cond = 'GROUP BY '.join(', ',$this->group).' ';
		}
		return $cond;
	}
	
	public function getFields()
	{
		$cond = "";
		if (count($this->fields) > 0)
		{
			$cond = join(', ',$this->fields).' ';
		} else {
			if (!empty($this->joins))
				$cond = ' '.$this->from.'.* ';
			else
				$cond = ' '.$this->orm->getTable().'.* ';
		}
		return $cond;
	}
	
	public function getJoins()
	{
		$cond = "";
		if (!empty($this->joins))
		{
			foreach ($this->joins as $join)
			{
				$cond .= ' '.$join['type'].' JOIN '.$join['table'].' ON '.$join['condition'].' ';
			}
		}
		return $cond;
	}
	
	public function getLeftJoins()
	{
		$cond = "";
		if (!empty($this->leftjoins))
		{
			foreach ($this->leftjoins as $join)
			{
				$cond .= ' '.$join['type'].' LEFT JOIN '.$join['table'].' ON '.$join['condition'].' ';
			}
		}
		return $cond;
	}
	
	public function getLimit()
	{
		$cond = "";
		if (strlen($this->limit) > 0)
		{
			$cond = "LIMIT ".$this->limit.' ';
		}
		return $cond;
	}
	
	public function getFrom()
	{
		return "FROM ".$this->from." ";
	}
	
	public function __call($methodName, $arguments)
	{
		
		if (!empty($this->orm))
		{
			$method = array($this->orm, $methodName);
			
			if (($result = $this->orm->callExtension($methodName, $this)) !== false) {
				return $result;
			} elseif(is_callable($method)) {
				return call_user_func_array(array($this->orm, $methodName), $arguments);
			} else {
				throw new Exception("Unknow method ".$methodName);
			}
		} else {
			throw new Exception("Unknow method ".$methodName);
		}
	}
	
	public function selectSql()
	{
		$sql = "SELECT ".$this->getFields().
						$this->getFrom().
						$this->getJoins().
						$this->getLeftJoins().
						$this->getWhere().
						$this->getGroup().
						$this->getOrder().
						$this->getHaving().
						$this->getLimit();
// 		echo $sql.'<br>';
		return $sql;
	}
}