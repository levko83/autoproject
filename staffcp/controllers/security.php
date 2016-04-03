<?php

class SecurityController  extends Controller {
	
	public $layout = 'simple';
	
	function index(){
		$this->redirectUrl('/staffcp/security/login/');
	}
	function login() {
		$translates = Register::get('translates');
		$this->view->translates = $translates;
		
		if ($this->request->isPost()) {
			$login = $this->request('login');
			$pass = $this->request('password');
			$remember = $this->request('remember',0);
			$acl = new Acl();
			$user = $acl->login(mysql_real_escape_string($login), mysql_real_escape_string($pass));
			if (!empty($user)){
				
				/* ACCOUNT ACL REMEMBER MY */
				if ($remember == 1) {
					$ip=getenv("HTTP_X_FORWARDED_FOR");
					if (empty($ip) || $ip=='unknown'){ 
						$ip=getenv("REMOTE_ADDR"); 
					}
					#$password = md5(md5($pass).$ip);
					$password = md5(md5($pass));
					setcookie("adm_cook_email",$login,time()+(60*60*24*365),"/",$_SERVER['HTTP_HOST']);
					setcookie("adm_cook_pass",$password,time()+(60*60*24*365),"/",$_SERVER['HTTP_HOST']);
				}
				/* ~END ACLSYS */
				
				Acl::saveAuthData($user);
				
				Logs::addLog(Acl::getAuthedUserId(),'Авторизация в зоне администрирования',URL_NOW);
				$this->redirect('index','index');
			}
			$this->view->msg = $translates['admin.login.status'];
			$this->view->login = $login;
			$this->view->pass = $pass;
		}
	}
	function logout(){
		
		$cmsgen = new CmsGenerator();
		$cmsgen->unset_simpleview_shopping_system();
		
		Acl::logout();
		Logs::addLog(Acl::getAuthedUserId(),'Выход из зоны администрирования',URL_NOW);
		
		$this->redirect('login','security');
	}
	public function denied(){ 
		$translates = Register::get('translates');
		$this->view->translates = $translates;
	}
	public function error404($ext=false){
	}
}