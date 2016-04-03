<?php

class EmailsModel extends Orm {
	
	public function __construct()
	{
		parent::__construct(DB_PREFIX.'emails');
	}
	
	public static function get($code,$vars,$to,$from,$from_name,$captcha=false,$add_file_path='')
	{
		if (!$code)
			return false;
			
		$model = new EmailsModel();
		$data = $model->select()->where("code = ? ", $code)->fetchOne();
		
		if (!$data)
			return false;
			
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
		
		$mail = new Phpmailer();
		$mail->FromName = $from_name;
		$mail->Subject  = $subject;
		$mail->MsgHTML($letter);
		if ($add_file_path)
			$mail->AddAttachment($add_file_path,basename($add_file_path));
		
		if (explode(",", $from)>1)
			$mail->From = array_pop(explode(",", $from));
		else 
			$mail->From = $from;
		
		$splitEmails = explode(",",$to);
		if (count($splitEmails)>1){
			foreach ($splitEmails as $sEmail){
				$mail->AddAddress($sEmail);
			}
		} else {
			$mail->AddAddress($to);
		}
		
		//$mail->Send();
		//$mail->ClearAddresses();
		//echo('<pre>');
		//var_dump($mail);
		//exit();
		
		if ($captcha) {
			
			if (md5($form['code'])==$_SESSION['captcha_keystring']) {
				if ($mail->Send()){
					$mail->ClearAddresses();
					$_SESSION['sendC'] = 1;
					return true;
				}
				else 
					return false;
			}
			else {
				$_SESSION['error_data'] = $form;
				$_SESSION['sendC'] = 2;
				return false;
			}
		}
		else {
			if ($mail->Send()){
				$mail->ClearAddresses();
				$_SESSION['sendC'] = 1;
				return true;
			}
			else 
				return false;
		}
	}
	
//	public static function result()
//	{
//		if (isset($_SESSION['sendC'])) {
//			if ($_SESSION['sendC']==1) {
//				$this->view->send = 1;
//			}
//			elseif ($_SESSION['sendC']==2) {
//				$this->view->send = 2;
//				$this->view->error_data = @$_SESSION['error_data'];
//				unset($_SESSION['error_data']);
//			}
//			unset($_SESSION['sendC']);
//		} else {
//		  $this->view->send = 0;
//		}
//	}
}