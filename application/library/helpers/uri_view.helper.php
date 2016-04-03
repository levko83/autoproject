<?php

class UriViewHelper {
	
	public static function uri($uri,$params=array('grid=[A-z]','page=[0-9]')){
		
		if (isset($params) && count($params)>0){
			foreach ($params as $str){
				$uri = preg_replace("|&?".$str."+&?|", "", $uri);
			}
		}
		$has_params = (strpos($uri, "?") !== false);
		if ($has_params) {
			$uri .= "&";
		}
		else {
			$uri .= "?";
		}
		return $uri;
	}
}

?>