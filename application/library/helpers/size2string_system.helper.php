<?php
/**
 * Return string presentation of file size
 * 
 *
 */
class Size2StringSystemHelper {
	
	/**
	 * Return string presentation of file size
	 *
	 * @param int $size file size in bytes
	 * @return string
	 */
	public function size2String($size)
	{
		$aVal = array('b','Kb','Mb','Gb','Tb');
		$pow = intval(log($size,1024));
		return round($size/pow(1024,$pow),0).' '.$aVal[$pow];
	}
}