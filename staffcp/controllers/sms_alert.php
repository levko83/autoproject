<?php

class Sms_alertController  extends CmsGenerator {
	
	public function index(){

		Logs::addLog(Acl::getAuthedUserId(),'Настройка раздела SMS уведомлений',URL_NOW);
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."settings_hidden WHERE `group`='SMS_ALERT' ORDER BY id";
		$this->view->data = $db->query($sql);
		
		if (isset($_POST['save']) && $_POST['save']){
			
			$code = $this->request("code");
			if (isset($code) && count($code)>0){
				foreach ($code as $id => $item){
					$db->post("UPDATE ".DB_PREFIX."settings_hidden SET `value`='".mysql_real_escape_string($item)."' WHERE id='".(int)$id."';");
				}
			}
			$this->redirectUrl('/staffcp/sms_alert/');
		}
	}
}
?>