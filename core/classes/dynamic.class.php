<?php
/**
 * Implode dynamic class extentions<br>
 *<br>
 * For example:<br>
 * We have class View and wand to add method escape($string) for this class
 * without changing source code. For this reason we should extend class View
 * from Dynamic class like:<br>
 *<br>
 * class View extended Dynamic {<br>
 *  ...<br>
 *  public $sufix = 'ViewHelper';<br>
 *  ...<br>
 * }<br>
 *<br>
 * Then we should create escape view helper like:<br>
 *<br>
 * class EscapeViewHelper {<br>
 *   public function escape($string) {<br>
 *      ...<br>
 *   }<br>
 * }<br>
 *<br>
 * Now, we can use method escape in our objects of View class like:<br>
 *<br>
 * $view = new View();<br>
 * echo $view->escape('bla lba lba');<br>
 *
 */
class Dynamic {
	
	public $sufix = 'Helper';

     /**
      * 
      * @param string $name
      * @param array $arguments
      * @return mixed
      */
	public function __call($name, $arguments)
	{
		if (is_callable(array(ucfirst($name.$this->sufix),$name))) {
			$className = ucfirst($name.$this->sufix);
			$classObj = new $className();
			return call_user_method_array($name, $classObj, $arguments);
		} else {
			throw new Exception('Undefined method '.$name);
		}
	}
}