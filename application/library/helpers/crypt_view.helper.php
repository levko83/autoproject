<?php
/**
 * Custom helper for quick load static images
 */
class CryptViewHelper {
	
	public static function xdecode($str){
		return base64_decode(base64_decode($str));
	}
	
	public static function xencode($str){
		return base64_encode(base64_encode($str));
	}
}
?>