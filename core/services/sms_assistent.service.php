<?php 

class Sms_assistent {
	
public static function send_message($str = 'Hello world') {

		$login = 'SnabLand';
		$pass = 'h8YiHEUY';
		$recipient = '375291234567';
		$sender = 'Avtostandar';
		
		$url = 'https://userarea.sms-assistent.by/api/v1/xml';
		$postdata = '
		<?xml version="1.0" encoding="utf-8" ?>
        <package login="'.$login.'" password="'.$pass.'">
        <message>
        <msg recipient="+'.$recipient.'" sender="'.$sender.'" validity_period="86400">'.$str.'</msg>
        </message>
        </package>
        ';
		
		$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
	
		$header = array();
		$header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Content-Type: text/xml";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: ";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
		$content = curl_exec($ch);
		$err     = curl_errno($ch);
		$errmsg  = curl_error($ch);
		$header  = curl_getinfo($ch);
		curl_close($ch);
	
		$header['errno']   = $err;
		$header['errmsg']  = $errmsg;
		$header['content'] = $content;
		
		return $header;
	}
}

?>