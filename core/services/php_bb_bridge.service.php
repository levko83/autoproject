<?php

class PhpBbBridge {
	
//	private $db;
	
	public function __construct()
	{
		//$this->db = Register::get('db');
		
	}
	
	
	public function signup($login, $password, $email, $loginto = false)
	{
		// include files
		define('IN_PHPBB', true);
	    /* set scope for variables required later */
	    global $phpbb_root_path,$phpEx,$db,$config,$user,$auth,$cache,$template;
	
	    # your php extension
	    $phpEx = substr(strrchr(__FILE__, '.'), 1);
	    $phpbb_root_path = 'forum/';
	
	    /* includes all the libraries etc. required */
	    require($phpbb_root_path .'common.php');
	    
	    $user->session_begin();
	    //var_dump($user);
	    
	    $auth->acl($user->data);
	    
	
	    /* the file with the actual goodies */
	    require($phpbb_root_path .'includes/functions_user.php');
	    require($phpbb_root_path .'includes/utf/utf_normalizer.php');
	
	    /* All the user data (I think you can set other database fields aswell, these seem to be required )*/
	    $user_row = array(
		    'username' => $login,
		    'user_password' => md5($password),
		    'user_email' => $email,
		    'group_id' => 2,
		    'user_timezone' => 0,
		    'user_dst' => 0,
		    'user_lang' => 'ru',
		    'user_type' => '0',
		    'user_actkey' => '',
		    'user_dateformat' => 'D M d, Y g:i a',
		    'user_style' => 1,
		    'user_regdate' => time(),
		    'user_timezone' => '2.00', 
		    'user_dst' => date("I")
	    );
		
	    /* Now Register user */
	    $phpbb_user_id = @user_add($user_row);
	    
	    if ($loginto)
	    {
	    	$auth->login($login, $password);
	    	 setcookie('phpbb3_cto51_sid',$user->session_id, 0, '/');
	    }
	}
	
	public function login($login, $password)
	{
		// include files
		if (!defined('IN_PHPBB'))
			define('IN_PHPBB', true);
	    /* set scope for variables required later */
	    global $phpbb_root_path,$phpEx,$db,$config,$user,$auth,$cache,$template;
	
	    # your php extension
	    $phpEx = substr(strrchr(__FILE__, '.'), 1);
	    $phpbb_root_path = 'forum/';
	
	    /* includes all the libraries etc. required */
	    require($phpbb_root_path .'common.php');
	    require($phpbb_root_path .'includes/utf/utf_normalizer.php');
	    
	    $user->session_begin();
	    
	    //var_dump($user);
	    
	    $auth->acl($user->data);
	    $auth->login($login, $password);
	    setcookie('phpbb3_cto51_sid',$user->session_id, 0, '/');
	}
	
	public function logout()
	{
		
		// include files
		if (!defined('IN_PHPBB'))
			define('IN_PHPBB', true);
	    /* set scope for variables required later */
	    global $phpbb_root_path,$phpEx,$db,$config,$user,$auth,$cache,$template;
	
	    # your php extension
	    $phpEx = substr(strrchr(__FILE__, '.'), 1);
	    $phpbb_root_path = 'forum/';
	
	    /* includes all the libraries etc. required */
	    require($phpbb_root_path .'common.php');
	    require($phpbb_root_path .'includes/utf/utf_normalizer.php');
	    
	    $user->session_begin();
	    $user->session_kill(false);
	    
		setcookie('phpbb3_cto51_sid','',  mktime(0,0,0,1,1,2009), '/','ypeer.by.lunga.neolocation.net');
		setcookie('phpbb3_cto51_u','',    mktime(0,0,0,1,1,2009), '/','ypeer.by.lunga.neolocation.net');
		setcookie('phpbb3_cto51_k','',    mktime(0,0,0,1,1,2009), '/','ypeer.by.lunga.neolocation.net');
		
		unset($_COOKIE['phpbb3_cto51_sid']);
		unset($_COOKIE['phpbb3_cto51_u']);
		unset($_COOKIE['phpbb3_cto51_k']);
		
	}
	
	
}