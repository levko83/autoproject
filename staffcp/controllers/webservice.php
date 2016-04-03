<?php

class WebserviceController  extends CmsGenerator {
	
	public function index(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Настройка раздела - собственный веб-сервис',URL_NOW);
		
		$db = Register::get('db');
		$sql = "
		SELECT 
			A.*,
			(SELECT COUNT(*) FROM ".DB_PREFIX."a2i A2I WHERE A2I.fk_account=A.id) CC
		FROM ".DB_PREFIX."accounts A 
		WHERE 
			A.web_service = 1
		ORDER BY A.id DESC;";
		$res = $db->query($sql);
		
		$this->view->access_accounts = $res;
		
		$wbs = isset($_REQUEST['wbs'])?$_REQUEST['wbs']:false;
		if ($wbs){
			$aid = isset($wbs['client'])?$wbs['client']:0;
			if ($aid){
				
				$sql = "SELECT web_service_login,web_service_pass FROM ".DB_PREFIX."accounts WHERE id = '".(int)$aid."';";
				$client = $db->get($sql);
				
				if (isset($wbs['group']) && $wbs['group']){
					$this->view->result_articles = $this->articles($client['web_service_login'],$client['web_service_pass'],$wbs['group']);
				}
				elseif (isset($wbs['article']) && $wbs['article']){
					$this->view->result_groups = $this->groups($client['web_service_login'],$client['web_service_pass'],$wbs['article']);
				}
			}
		}
	}
	function groups($login,$pass,$article){
		$url = "http://".$_SERVER['HTTP_HOST']."/webservice/getGroups/?login=".$login."&pass=".$pass."&article=".$article;
		#var_dump($url);
		$content = file_get_contents($url);
		return (array)json_decode($content);
	}
	function articles($login,$pass,$group){
		$url = "http://".$_SERVER['HTTP_HOST']."/webservice/getArticles/?login=".$login."&pass=".$pass."&group=".$group;
		#var_dump($url);
		$content = file_get_contents($url);
		return (array)json_decode($content);
	}
}

?>