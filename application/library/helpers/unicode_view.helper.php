<?php
/**
 * Pager
 *
 */
class UnicodeViewHelper {
	
	public static function mb_ucfirst($string, $enc = 'UTF-8'){
		return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc).mb_substr($string, 1, mb_strlen($string, $enc), $enc);
	}

	/* ******************************************* */
	
	function utf8_to_unicode_code($utf8_string)
	{
	  $expanded = iconv("UTF-8", "UTF-32", $utf8_string);
	  return unpack("L*", $expanded);
	}
	
	public static function unicode_code_to_utf8($unicode_list)
	{ 
	  $result = "";
	  foreach($unicode_list as $key => $value) {
	      $one_character = pack("L", $value);
	      $result .= iconv("UTF-32", "UTF-8", $one_character);
	  }
	  return $result;
	}
	
	public static function enconvert_symbols($str) {
		$r = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');
		$s = $this->utf8_to_unicode_code($r);
		return $s;
	}
}