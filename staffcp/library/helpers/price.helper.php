<?php
/**
 * Price
 *
 */
class PriceHelper {
	
	var $num = 0;
	var $type1 = '.';
	var $type2 = '.';
	
	function __construct(){
		$this->num = 2;
			$this->type1 = '.';
	}
	
	public static function number($number){
		
		$ph = new PriceHelper();
		
		if (!$number)
			return (int)$number;
		return number_format($number, $ph->num, $ph->type1, $ph->type2);
	}
	
	public static function numberDoc($number){
		
		$ph = new PriceHelper();
		
		if (!$number)
			return (int)$number;
		return number_format($number, $ph->num, $ph->type1, $ph->type2);
	}
	
	public static function myceil($number){
		
		$ph = new PriceHelper();
		
		if (!$number)
			return (int)$number;
		return number_format($number, $ph->num, $ph->type1, $ph->type2);
	}
	
	public static function universal($number){
		
		$ph = new PriceHelper();
		
		if (!$number)
			return (int)$number;
		return number_format($number, $ph->num, $ph->type1, $ph->type2);
	}
}
?>