<?php
/**
 * Custom helper for quick load static images
 */
class HidestartsViewHelper {
	public static function hide($str='') {
		if (strlen($str)>2){
			$str = FuncModel::stringfilter($str);
			$long = strlen($str);
			$view = $str{0};
			//$view .= $str{1};
			$view .= str_repeat("●",$long-1);
			//$view .= $str{$long-2};
			$view .= $str{$long-1};
			return $view;	
		}
		else {
			return '';
		}
	}
}
?>