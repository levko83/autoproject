<?

class PreviewHelper {

	public static function makePreview($value, $length = 100){
		if (strlen($value) < $length + 4) {
			return $value;
		}
		else {
			return substr($value, 0, strpos($value, " ", $length)) ." ...";
		}
	}
	
	public static function numberPreview($d=0){
		return number_format($d, 0, ".", " ");
	}
}

?>