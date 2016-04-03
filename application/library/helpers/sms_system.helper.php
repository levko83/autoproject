<?php

class SmsSystemHelper {
	
	public function sendSmsMessage($id=0,$params=array(),$to=false){
		
		$sms = new SmsSystemHelper();
		
		$db = Register::get('db');
		$sql = "SELECT content FROM ".DB_PREFIX."dic_sms WHERE id='".(int)$id."';";
		$get = $db->get($sql);
		$content = strip_tags($get['content']);
		
		if (isset($params) && count($params)>0){
			foreach ($params as $kk=>$vv) {
				$str = '{'.$kk.'}';
				$content = str_replace($str,$vv,$content);
			}
		}
		
		$msg = $content;
		
		$sms_alert_code_gate = SettingshiddenModel::get('sms_alert_code_gate'); #frm
		$sms_alert_pass = SettingshiddenModel::get('sms_alert_pass'); #sms_pass
		$sms_alert_login = SettingshiddenModel::get('sms_alert_login'); #sms_login
		$sms_alert_key = SettingshiddenModel::get('sms_alert_key'); #key
		$sms_alert_id = SettingshiddenModel::get('sms_alert_id'); #id
		$sms_alert_service = SettingshiddenModel::get('sms_alert_service'); #prv
		
		if ($to){
			$sms_alert_phone = str_replace(array("+","(",")","-"," "),"",$to); #num
		}
		else {
			$sms_alert_phone = str_replace(array("+","(",")","-"," "),"",SettingshiddenModel::get('sms_alert_phone')); #num
		}
		
		$u = array();
		$u['sms.ru'] = "http://sms.ru/sms/send?api_id=".$sms->uc($sms_alert_key)."&to=".$sms->uc($sms_alert_phone)."&text=".$sms->uc($msg);
		$u['bytehand.com'] = "http://bytehand.com:3800/send?id=".$sms->uc($sms_alert_id)."&key=".$sms->uc($sms_alert_key)."&to=".$sms->uc($sms_alert_phone)."&partner=callme&from=".$sms->uc($sms_alert_code_gate)."&text=".$sms->uc($msg);
		$u['sms-sending.ru'] = "http://lcab.sms-sending.ru/lcabApi/sendSms.php?login=".$sms->uc($sms_alert_login)."&password=".$sms->uc($sms_alert_pass)."&txt=".$sms->uc($msg)."&to=".$sms->uc($sms_alert_phone);
		$u['infosmska.ru'] = "http://api.infosmska.ru/interfaces/SendMessages.ashx?login=".$sms->uc($sms_alert_login)."&pwd=".$sms->uc($sms_alert_pass)."&sender=SMS&phones=".$sms->uc($sms_alert_phone)."&message=".$sms->uc($msg);
		$u['smsaero.ru'] = "http://gate.smsaero.ru/send/?user=".$sms->uc($sms_alert_login)."&password=".md5($sms->uc($sms_alert_pass))."&to=".$sms->uc($sms_alert_phone)."&text=".$sms->uc($msg)."&from=".$sms->uc($sms_alert_code_gate);
		$u['sms-assistent.by'] = "https://userarea.sms-assistent.by/api/v1/send_sms/plain?user=".$sms->uc($sms_alert_login)."&password=".$sms->uc($sms_alert_pass)."&recipient=".$sms->uc($sms_alert_phone)."&message=".$sms->uc($msg)."&sender=".$sms->uc($sms_alert_code_gate);
		$u['rocketsms.by'] = "http://api.rocketsms.by/json/send?username=".$sms->uc($sms_alert_login)."&password=".$sms->uc(md5($sms_alert_pass))."&phone=".$sms->uc($sms_alert_phone)."&text=".$sms->uc($msg)."&sender=&priority=true";
		
		//var_dump($status,$u[$sms_alert_service]);
		//exit();

		if ($sms_alert_service){
			$status = @file_get_contents($u[$sms_alert_service]);
			return $status;
		}
		else {
			return false;
		}
	}
	
	/* SMS ********************************** */
	
	private function uc($s){
		$s = urlencode($s);
		return $s;
	}
	private function gf($s){ // no shit
		$s = substr((htmlspecialchars($_GET[$s])), 0 , 500);
		if (strlen($s)>1) return $s;
	}
}

?>