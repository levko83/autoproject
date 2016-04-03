<?php
/**
 * Custom helper for quick load static images
 */
class StaticimgViewHelper {
	public static function chk($folder,$file) {
		
		if (strpos($file,"placeholder")) {
			return "http://".$_SERVER['HTTP_HOST']."/media/files/".$folder."/".$file;
		} else {
			return "http://static.ryli.by/media/files/".$folder."/".$file;
		}
	}
}
?>