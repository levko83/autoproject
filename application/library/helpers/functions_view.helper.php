<?php
/**
 */
class FunctionsViewHelper {
	
	public static function mb_ucfirst($str, $enc = 'utf-8') { 
		return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc).mb_substr($str, 1, mb_strlen($str, $enc), $enc); 
	}
	
	public static function style_ucfirst($str,$i=null) {
		if ($i == 1)
			return '<span class="letter-name">'.substr($str, 0, 1).'</span>'.substr($str, 1, strlen($str));
		else
			return $str; 
	}
}
?>