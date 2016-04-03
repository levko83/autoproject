<?php
/**
 * Pager
 *
 */
class AliasViewHelper {
	
	public static function decode_header($header, $out_charset){
		preg_match_all('/=\\?(.+?)\\?(.+?)\\?(\\S+)/', $header, $matches);
		for($i = 0; $i < count($matches[0]); $i++){
			switch( strtoupper($matches[2][$i])){
				case 'B': $matches[3][$i] = imap_base64($matches[3][$i]); break;
				case 'QP': $matches[3][$i] = imap_qprint($matches[3][$i]); break;
				default:
					trigger_error('Error'); // , E_USER_ERROR :))
				return false;
			}
			if( strtoupper($matches[1][$i]) != strtoupper($out_charset)){
				$matches[3][$i] = iconv($matches[1][$i], $out_charset . '//IGNORE', $matches[3][$i]);
			}
		}
		return str_replace($matches[0], $matches[3], $header);
	}

	public static function translitIt($str) {
	    $tr = array("А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y","ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", " "=> "-", "."=> "", "/"=> "-");
	    return strtr($str,$tr);
	}
	public static function doTraslit($urlstr){
		if (preg_match('/[^A-Za-z\-]/', $urlstr)) {
		    $urlstr = AliasViewHelper::translitIt($urlstr);
		    $urlstr = preg_replace('/[^A-Za-z\-]/', '', $urlstr);
		}
		$urlstr = str_replace(array("--","---"),"-",$urlstr);
		return strtolower($urlstr);
	}
	
	public static function translitItFile($str){
		$tr = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
			"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
			" "=> "", "."=> ".", "/"=> "-"
		);
		return strtr($str,$tr);
	}
	
	public static function doTraslitFile($urlstr){
		if (preg_match('/[^A-Za-z\-0-9.]/', $urlstr)) {
			$urlstr = AliasViewHelper::translitItFile($urlstr);
			$urlstr = preg_replace('/[^A-Za-z\-0-9.]/', '', $urlstr);
		}
		return strtolower($urlstr);
	}
}