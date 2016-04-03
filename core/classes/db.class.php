<?php

class Db {

	public $queries_cc = 0;
	private $error = '';
	private $connection = '';
	private $query = '';

	public function __construct(){
		$this->connect();
	}
	
	public function __destruct(){
		$this->disconnect();
	}
	
	/**
	* Connect to DB
	*/
	public function connect()
	{
		
		if (($this->connection = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) === false)
		{
			throw new Exception('Couldn\'t connect to DB');
		}
		if (($this->selectDB(DB_NAME)) === false)
		{
			throw new Exception('Couldn\'t select DB');
		}
		if (defined('DB_INIT'))
			if (DB_INIT)
				mysql_query(DB_INIT, $this->connection);
	}

	/**
	 * Disconnect from DB
	 */
	public function disconnect()
	{
		if (!mysql_close($this->connection)) {
			throw new Exception('Couldn\'t close connection');
		}
	}

	/**
	 * Use specified DB
	 * @param string $db_name
	 * @return mixed
	 */
	public function selectDB($db_name = '')
	{
		return mysql_select_db($db_name,$this->connection);
	}

	/**
	 * Get error during last query
	 * @return string
	 */
	public function error()
	{
		return mysql_error($this->connection);
	}

	/**
	 * Get info about last query
	 * @return array
	 */
	public function queryInfo()
	{
		return mysql_info($this->connection);
	}

	/**
	 * Execute some query without fetching data
	 * @param string $query
	 * @return resource
	 */
	public function post($query, $params = null)
	{
		$this->queries_cc++;
		
		if (count(func_get_args()) > 2)
			$params = func_get_args();
		$query = $this->prepareQuery($query, $params);
						
		$this->query = mysql_query($query,$this->connection);
		
		if (defined('debug_MYSQL') && debug_MYSQL && isset($_REQUEST['debug'])) {
			echo("<pre><b>".$this->queries_cc."</b>:".$query."</pre>");
		}

		if ($this->query === false )
		{
			throw new Exception(mysql_error($this->connection)."\r\n".$query);
		}
		return $this->query;
	}

	/**
	 * Execute query and fetch result
	 * @param string $query
	 * @param string $params
	 * @return array
	 */
	public function query($query, $params = null)
	{
		if (count(func_get_args()) > 2)
			$params = func_get_args();
		$query = $this->post($query, $params);

//		$result = array();
		$result = new Collection();
		if (($this->query != null) && (!is_bool($this->query)))
		{
			while ($row = mysql_fetch_array($this->query,MYSQL_ASSOC))
			{
				$result[] = $row;
			}
		}
		return $result;
	}

	/**
	 * Execute query and fetch first row of result
	 * @param string $query
	 * @param string $params
	 * @return array
	 */
	public function get($query, $params = null) {
		if (count(func_get_args()) > 2)
			$params = func_get_args();
		$result = $this->query($query, $params);
		return isset($result[0]) ? $result[0] : null;
	}

	/**
	 * Execute query and fetch first column in fisrt row of result
	 * @param string $query
	 * @param string $params
	 * @return array
	 */
	public function single($query, $params = null) {
		if (count(func_get_args()) > 2)
			$params = func_get_args();
		$aResult = $this->query($query, $params);
		if (empty($aResult[0]))
			return null;
		$keys = array_keys($aResult[0]);
		return $aResult[0][$keys[0]];
	}

	/**
	 * Get next ID by autoicreament
	 * @param string $tableName
	 * @return int
	 */
	public function getAutoIncrement($tableName = '') {
		$rows = $this->query("SHOW TABLE STATUS LIKE '$tableName'");
		return @$rows[0]['Auto_increment'];
	}

	/**
	 * Get number of affected rows during last query
	 * @return int
	 */
	public function getAffectedRows() {
		return mysql_affected_rows($this->query);
	}

	/**
	 * Get number of recieved rows  during last query
	 * @return int
	 */
	public function getNumRows() {
		return mysql_num_rows($this->query);
	}

	/**
	 * Get last insert ID
	 * @return int
	 */
	public function lastInsertId() {
		return mysql_insert_id($this->connection);
	}

	/**
	 * Prepare query for executing
	 * Replace all '?' in query by escaped values at funciton params
	 * @param string $query
	 * @param array $params
	 * @return string
	 */
	public function prepareQuery($query, $params = null)
	{
		if (count(func_get_args()) > 2)
			$params = func_get_args();
		if (count($params) > 1)
		{
			unset($params[0]);
			$replace = array();
			foreach ($params as $key => $value)
			{
				if (is_array($value))
				{
					foreach ($value as $k=>$v)
						$value[$k] = mysql_real_escape_string($v);
					$value = "('".join("', '",$value)."')";
				} else {
					$value = "'".mysql_real_escape_string($value)."'";
				}
				$query = preg_replace('/\?/is',$value, $query, 1);
			}
		}
		return $query;
	}

}
