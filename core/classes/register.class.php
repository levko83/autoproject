<?php
/**
 * Storage for global objects.
 * Such as object for DB or Utils.
 * Example of usage:
 * //init
 * Register::add('db',new DB());
 * // using at any place in application
 * $db = Register::get('db');
 * $db->query(...);
 *
 * // or
 * // init
 * Register::add('someData',array('key'=>'val'));
 * // using at any place in application
 * $myData = Register::get('someData');
 * // changing exists data
 * $myData['newKey'] = 'newValue';
 * Register::set('someData',$myData);
 * // remove data from storage
 * Register::remove('someData');
 * 
 */
class Register {
	
	private static $values;

	/**
	 * Add value to global storage
	 * @param string $name
	 * @param mixed $value
	 */
	public static function add($name, $value) {
		self::$values[$name] = $value;
	}

	/**
	 * Get object from storage
	 * @param string $name
	 * @return mixed
	 */
	public static function get($name)
	{
		if (array_key_exists($name, self::$values))
		{
			return self::$values[$name];
		} else {
			return null;
			//throw new Exception("Unset key");
		}
	}

	/**
	 * Change object in storage
	 * @param string $name
	 * @param mixed $value new value
	 */
	public static function set($name, $value)
	{
		self::$values[$name] = $value;
	}

	/**
	 * Remove object from storage
	 * @param string $name
	 */
	public static function remove($name)
	{
		unset(self::$values[$name]);
	}
}