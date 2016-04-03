<?php

class EmailsModel extends Orm {
	
	public function __construct()
	{
		parent::__construct(DB_PREFIX.'emails');
	}
	
	public static function get($code,$vars,$to,$from,$from_name,$captcha=false)
	{
		$model = new EmailsModel();
		$data = $model->select()->where("code = ? ", $code)->fetchOne();
		$letter = $data['value'];
		$subject = $data['name'];
		if (empty($letter))
			return false;
			
		foreach ($vars as $kk=>$vv) {
			$str = '{'.$kk.'}';
			$letter = str_replace($str,$vv,$letter);
		}
		
		$letter = str_replace(array("../../../"),"http://".$_SERVER['SERVER_NAME']."/",$letter);
		$letter = str_replace(array("/media"),"http://".$_SERVER['SERVER_NAME']."/media",$letter);
		// die($letter);
		$mail = new Phpmailer();
		$mail->From     = $from;
		$mail->FromName = $from_name;
		$mail->Subject  = $subject;
		$mail->MsgHTML($letter);
		
		$splitEmails = explode(",",$to);
		if (count($splitEmails)>1){
			foreach ($splitEmails as $sEmail){
				$mail->AddAddress($sEmail);
			}
		} else {
			$mail->AddAddress($to);
		}
		
		$admin = SettingsModel::get('contact_email');
		$admin = SettingsModel::get('contact_email');
		$exp = explode(",",$admin);
		if (count($exp)>1){
			foreach ($exp as $e){
				$mail->AddAddress($e);
			}
		}
		else {
			$mail->AddAddress($admin);
		}
		
		// if ($captcha){
			// if (md5($vars['code'])==$_SESSION['captcha_keystring']) {
				// if ($mail->Send()){
					// $mail->ClearAddresses();
					// $_SESSION['sendC'] = 1;
					// return true;
				// }
				// else 
					// return false;
			// }
			// else {
				// $_SESSION['error_data'] = $vars;
				// $_SESSION['sendC'] = 2;
				// return false;
			// }
		// }
		// else {
			if ($mail->Send()){
				$mail->ClearAddresses();
				$_SESSION['sendC'] = 1;
				return true;
			}
			else 
				return false;
		// }
	}
}
?>