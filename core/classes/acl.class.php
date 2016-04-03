<?php

class Acl {
	
	private $userId = null;
	private $db = null;
	private $userTable;
	private $groupTable;
	private $permissionTable;
	private $user2groupTable;
	private $user2permissionTable;
	private $permission2groupTable;
	private $userRights;
	public $isSuper = false;
	public $isManager = false;
	public $isLevel = false;
	public $purchase_margin = 0;
	
	public function __construct($userId = null){
		$this->userId = $userId;
		
		$this->db = Register::get('db');
		
		$this->userTable = DB_PREFIX.'_user';	
		$this->groupTable = DB_PREFIX.'_user_group';	
		$this->permissionTable = DB_PREFIX.'_user_permission';	
		$this->user2groupTable = DB_PREFIX.'_user2group';	
		$this->user2permissionTable = DB_PREFIX.'_user2permission';	
		$this->permission2groupTable = DB_PREFIX.'_user_permission2group';
		
		if (!empty($this->userId))	{
			
			$this->userRights = $this->getRights($this->userId);
			$sql = "SELECT * FROM ".$this->userTable." WHERE id='".$this->userId."'";
			$user = $this->db->get($sql);
			
			$this->purchase_margin = $user['purchase_margin'];
			
			if ($user['is_super'] == 1){
				$this->isSuper = true;
				$this->isManager = false;
				$this->isLevel = 1;
			}
			if ($user['is_super'] == 2){
				$this->isSuper = true;
				$this->isManager = true;
				$this->isLevel = 2;
			}
			if ($user['is_super'] == 3){
				$this->isSuper = true;
				$this->isManager = true;
				$this->isLevel = 3;
			}
		}
	}
	
	public function hasUserPermission($permissionName, $userId = null){
		return true;
	}
	
	public function getListIds($controller){
		if ($this->isSuperAdmin())
			return 'all';
		
		foreach ($this->userRights as $row){
			
			if ($row['type'] == $controller){
				if (!$row['active'])
					return 'none';
					
				if ($row['list'] == 'all')
					return 'all';
				
				if ($row['list'] == 'none')
					return 'none';
				
				if ($row['list'] == 'edit'){
					if ($row['edit'] == 'all')
						return 'all';
					if ($row['edit'] == 'none')
						return 'none';
					if ($row['edit'] == 'category')
						return 'category';
					return $row['edit'];
				}		
			}
		}
		return 'none';
	}
		
	
	public function hasRights($controller, $action, $itemId){
		
// 		if ($controller == 'settings'){
// 			if ($this->isSuperAdmin()){
// 				return true;
// 			} else {
// 				return false;
// 			}
// 		}
		
		if ($action == 'delete')
			$action = 'edit';
			
		if ($action == 'save' && !empty($_REQUEST['form']['id'])) {
			$action = 'edit';
			$itemId = $_REQUEST['form']['id'];
		}
		if ($action == 'save' && empty($_REQUEST['form']['id'])) {
			$action = 'add';
		}
		
		//var_dump($controller);echo('<br>');
		
		foreach ($this->userRights as $row){
			
			if ($row['type'] == $controller){
				
				if (!$row['active'])
					return false;
				
				if ($action == 'index'){
					
					if ($row['list'] == 'none')
						return false;
					
					elseif ($row['list'] == 'edit' && $row['edit'] == 'none')
						return false;
					
					else 
						return true;
				}
				
				if ($action == 'add'){
					if (!$row['add'])
						return false;
					else 
						return true;
				}
				
				if ($action == 'edit'){
					
					if ($row['edit'] == 'all')
						return true;
					
					if ($row['edit'] == 'none')
						return false;
					
					if (empty($itemId))
						return false;
					
					if ($row['edit'] == 'category'){
						
						if ($row['type'] == 'news'){
							$categoryTable = DB_PREFIX.'news_category';
							$categoryListIds = $this->getListIds('news_category');	
						}
						
						if ($categoryListIds == 'all')
							return true;
						
						if ($categoryListIds == 'none')
							return false;
						
						$sql = "SELECT * FROM ".$row['table'].' 
						left join '.$categoryTable.' on '.
						$categoryTable.'.id = '.$row['table'].'.category_id WHERE category_id in ('.$categoryListIds.') AND '
						.$row['table'].'.id = '.$itemId;
						$result = $this->db->query($sql);
						if (count($result))
							return true;
						return false;
					}
					
					if (in_array(intval($itemId),explode(',',$row['edit'])))
						return true;
					
					return false;
				}
			}
		}
		return false;
	}
	
	public function canViewMenuItem($url, $userId = 0){
		
		if ($url == 'http://api.iauto.by/')
			return true;
		
		if ($this->isSuper && $this->isManager){
			if (!$userId){
				$userId = $this->userId;
			}
		}
		
		if (!empty($userId))	
			$this->userRights = $this->getRights($userId);
		
		if ($this->isSuperAdmin($userId))
			return true;
		
		$url = str_replace("/staffcp/", "", $url);
		if ($url == "" || is_integer(strpos($url, "index/"))){
			return true;
		}
		
		if (preg_match('#^([\w\_\-]*?)/([\w\_\-]*?)(/?)(\??)(id=)?([\d]*?)(\??)(parent=)?([\d]*?)(\??)(parent=)?([\d]*?)|(id=)?([\d]*?)?$#', $url, $match)){
			
			if (!empty($match[1])){
				$controller = $match[1];
			}
			
			if (!empty($match[2])){
				$action = $match[2];
			} else {
				$action = 'index';
			}
			
			if ($action == 'delete')
				$action = 'edit';
			
			if (!empty($match[6])){
				$itemId = $match[6];
			} else {
				$itemId = null;
			}
			
			if ($controller == 'index')
				return true;
			
			if ($controller == 'margins')
				$controller = 'importers';
			
			return $this->hasRights($controller, $action, $itemId);
		}
		
		return false;
	}
	
	
	public function getRights($userId = 0){
		$sql = "
				SELECT 
					".DB_PREFIX."_user_rights.*,
					".DB_PREFIX."_user2rights.user_id, 
					".DB_PREFIX."_user2rights.list,
					".DB_PREFIX."_user2rights.add,
					".DB_PREFIX."_user2rights.edit,
					".DB_PREFIX."_user2rights.active
				FROM ".DB_PREFIX."_user_rights 
				LEFT JOIN ".DB_PREFIX."_user2rights on ".DB_PREFIX."_user_rights.id = ".DB_PREFIX."_user2rights.right_id AND (".DB_PREFIX."_user2rights.user_id='{$userId}' OR ".DB_PREFIX."_user2rights.user_id IS NULL)
				ORDER BY ".DB_PREFIX."_user_rights.id ASC";
		return $this->db->query($sql);
	}
	
	public function login($login, $password){
		$sql = "SELECT * FROM ".$this->userTable." WHERE login='".$login."' AND password='".$password."'";
		$user = $this->db->get($sql);
		return $user;
	}
	
	public function isSuperAdmin($userId = null){
		
		if (!empty($userId)){
			
			$sql = "SELECT * FROM ".$this->userTable." WHERE id='".$userId."'";
			$user = $this->db->get($sql);
			if (empty($user))
				return false;
			
			if ($user['is_super'] == 1)
				return true;
			
			return false;
			
		} else {
			return $this->isSuper;
		}
	}
	
	public static function isAuthed(){
		if (!empty($_SESSION['__acl']['user']))
			return true;
		
		$acl = new Acl();
		if($acl->isSuperAdminCookie())
			return true;
			
		return false;
	}
	
	public static function getAuthedUserId(){
		$acl = new Acl();
		$isCookie = $acl->isSuperAdminCookie();
		$id = (!empty($_SESSION['__acl']['user']['id'])?$_SESSION['__acl']['user']['id']:($isCookie?$isCookie:null));
		return $id;
	}
	
	public static function saveAuthData($user){
		$_SESSION['__acl']['user'] = $user;
	}
	public static function logout(){
		setcookie("adm_cook_email","",time(),"/",$_SERVER['HTTP_HOST']);
		setcookie("adm_cook_pass","",time(),"/",$_SERVER['HTTP_HOST']);
		unset($_SESSION['__acl']['user']);
	}
	
	public function isSuperAdminCookie(){
		$db = Register::get('db');
		$ip=getenv("HTTP_X_FORWARDED_FOR");
		if (empty($ip) || $ip=='unknown'){ 
			$ip=getenv("REMOTE_ADDR"); 
		}
		$email = (isset($_COOKIE['adm_cook_email'])?$_COOKIE['adm_cook_email']:'');
		$pass = (isset($_COOKIE['adm_cook_pass'])?$_COOKIE['adm_cook_pass']:'');
		if ($email && $pass) {
			#$sql = "SELECT * FROM ".$this->userTable." WHERE login='".mysql_real_escape_string($email)."' AND MD5(CONCAT(MD5(password),'$ip'))='".mysql_real_escape_string($pass)."';";
			$sql = "SELECT * FROM ".$this->userTable." WHERE login='".mysql_real_escape_string($email)."' AND MD5(CONCAT(MD5(password),''))='".mysql_real_escape_string($pass)."';";
			$res = $db->get($sql);
			if (count($res)>0) {
				return (int)$res['id'];
			}
			else 
				return 0;
		}
		else 
			return 0;
	}
}