<?php

class Merchant_assistController  extends CmsGenerator {
	
	public function index() {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."settings_merchants WHERE `group`='ASSIST' ORDER BY id";
		$this->view->data = $db->query($sql);
		
		if (isset($_POST['save']) && $_POST['save']){
			Logs::addLog(Acl::getAuthedUserId(),'Настройка экварийнга ASSIST',URL_NOW);
			$code = $this->request("code");
			if (isset($code) && count($code)>0){
				foreach ($code as $id => $item){
					$db->post("UPDATE ".DB_PREFIX."settings_merchants SET `value`='".mysql_real_escape_string($item)."' WHERE id='".(int)$id."';");
				}
			}
			$this->redirectUrl('/staffcp/merchant_assist/');
		}
	}
}

?>