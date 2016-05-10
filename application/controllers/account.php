<?php

class AccountController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$account = AccountsModel::getById($accountFetchid);
		
		$action = $this->request("action",false);
		// if ($action){
			// switch ($action){
				// case 'save_car': 
					// $this->save_car($accountFetchid);
					// break;
				// case 'delete_car':
					// $car_id = $this->request("car_id",false);
					// if ($car_id)
						// $this->deleteCar($car_id);
					// break;
			// }
		// }

		$this->view->data = $account;
		$this->view->viewCity = Dic_citiesModel::getById($account['city']);
		$this->view->all_cars = $this->getAllPersonalCars($accountFetchid);
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
	}
	
	public function vins(){
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
	
		$this->view->vins = $this->getQueriesVins($accountFetchid);
		$this->unsetAlerts($accountFetchid,'vin');
	
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs ['Мой запросы по Vin']= '#';
	}
	private function getQueriesVins($id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."vin WHERE account_id = '".(int)$id."' AND isset=1 ORDER BY dt DESC LIMIT 0,10;";
		return $db->query($sql);
	}
	
	public function autolist(){
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		
		$action = $this->request("action",false);
		if ($action){
			switch ($action){
				case 'save_car': 
					$this->save_car($accountFetchid);
					break;
				case 'delete_car':
					$car_id = $this->request("car_id",false);
					if ($car_id)
						$this->deleteCar($car_id);
					break;
				case 'delete_car_to':
					$car_id = $this->request("car_id",false);
					if ($car_id)
						$this->deleteCarTo($car_id);
					break;
			}
		}
		
		$this->view->all_cars = $this->getAllPersonalCars($accountFetchid);
		$this->view->all_cars_to = $this->getTOcars($accountFetchid);
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs ['Мой гараж']= '#';
	}	
	/* CREATED AUTO ************************************ */
	private function getTOcars($id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars_to WHERE account_id='".(int)$id."';";
		return $db->query($sql);
	}
	private function getToUrl($id=0){
		$db = Register::get('db');
		$sql = "SELECT id FROM ".DB_PREFIX."to_types WHERE tecdoc_id = '".(int)$id."';";
		$res = $db->get($sql);
		if ($res){
			$sql = "
			SELECT 
				TT.id type_id,
				TM.id model_id,
				TC.id car_id
			FROM ".DB_PREFIX."to_types TT 
			JOIN ".DB_PREFIX."to_models TM ON TM.id=TT.model_id 
			JOIN ".DB_PREFIX."to_cars TC ON TC.id=TM.car_id 
			WHERE 
				TT.tecdoc_id = '".(int)$id."';
			";
			$r = $db->get($sql);
			return '/to/index/car_id/'.$r['car_id'].'/model_id/'.$r['model_id'].'/type_id/'.$r['type_id'].'/';
		}
		else 
			return false;
	}
	function getAllPersonalCars($id=0){
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars WHERE account_id='".(int)$id."';";
		$res = $db->query($sql);
		
		$aData = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				if ($dd['car_type_id']){
					$urlTo = $this->getToUrl($dd['car_type_id']);
				}
				else {
					$urlTo = false;
				}
				$aData []= array_merge((array)$dd,(array)array('url_to'=>$urlTo));
				
			}
		}
		
		return $aData;
	}
	function getPersonalCarById($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars WHERE id='".(int)$id."';";
		return $db->get($sql);
	}
	private function deleteCar($id){
		$db = Register::get('db');
		$db->post("DELETE FROM ".DB_PREFIX."accounts_cars WHERE id='".(int)$id."';");
	}
	private function deleteCarTo($id){
		$db = Register::get('db');
		$db->post("DELETE FROM ".DB_PREFIX."accounts_cars_to WHERE id='".(int)$id."';");
	}
	private function save_car($id){
		
		$car_photo = '';
		if ($_FILES['add_photo']['name']) {
			
			$ext = strtolower(array_pop(explode(".",basename($_FILES['add_photo']['name']))));
			if (in_array($ext,array("png","jpg","gif","jpeg"))){
				$file = 'media/files/data/'.mktime().'_'.$_FILES['add_photo']['name'];
				$fileResize = 'media/files/data/normal-'.mktime().'_'.$_FILES['add_photo']['name'];
				if (move_uploaded_file($_FILES['add_photo']['tmp_name'],$file)) {
					$img = ImageresizeViewHelper::img_resize($file,$fileResize,130,100);
					$car_photo = basename($file);
				}
			}
		}
		
		$db = Register::get('db');
		$car = $this->request('car');
		$sql = "
		INSERT INTO ".DB_PREFIX."accounts_cars 
			(
				`account_id`,
				`car_id`,
				`car_model_id`,
				`car_type_id`,
				`car_name`,
				`car_year`,
				`car_kpp`,
				`car_rul`,
				`car_cond`,
				`car_abs`,
				`car_quattro`,
				`car_body`,
				`car_vin`,
				`car_info`,
				`car_photo`
			)
		VALUES
			(
				'".(int)$id."',
				'".mysql_real_escape_string($car['mark'])."',
				'".mysql_real_escape_string($car['model'])."',
				'".mysql_real_escape_string($car['type'])."',
				'".mysql_real_escape_string($car['name'])."',
				'".mysql_real_escape_string($car['car_year'])."',
				'".mysql_real_escape_string($car['car_kpp'])."',
				'".mysql_real_escape_string($car['car_rul'])."',
				'".mysql_real_escape_string($car['car_cond'])."',
				'".mysql_real_escape_string($car['car_abs'])."',
				'".mysql_real_escape_string($car['car_quattro'])."',
				'".mysql_real_escape_string($car['car_body'])."',
				'".mysql_real_escape_string($car['car_vin'])."',
				'".mysql_real_escape_string($car['car_info'])."',
				'".mysql_real_escape_string($car_photo)."'
			)
		;";
		$db->post($sql);
	}
	
	function ajax_models(){
		$this->layout = "ajax";
		$ID = $this->request("id");
		if (!$ID){ echo('operation error.'); exit(); }
		$options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
	    $client = new SoapClient(SOAP, $options);
	    $ACCESS_C = array('SERVER'=>$_SERVER,'KEY'=>KEY);
	    $LANG_ID = $this->setLang;
	    $results = $client->ModelsModel_query(
		array('request'=>array('ACCESS_C'=>$ACCESS_C,'MFA_ID'=>(int)$ID,'LANG_ID'=>(int)$LANG_ID)));
		$data = json_decode($results);
		if (is_string($data))
			echo $data;
		else if ($data)
			$this->view->items = $data;
	}
	
	function ajax_types(){
		$this->layout = "ajax";
		$ID = $this->request("id");
		if (!$ID){ echo('operation error.'); exit(); }
		$options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
	    $client = new SoapClient(SOAP, $options);
	    $ACCESS_C = array('SERVER'=>$_SERVER,'KEY'=>KEY);
	    $LANG_ID = $this->setLang;
	    $results = $client->TypesModel_query(
		array('request'=>array('ACCESS_C'=>$ACCESS_C,'MOD_ID'=>(int)$ID,'LANG_ID'=>$LANG_ID)));
		$data = json_decode($results);
		if (is_string($data))
			echo $data;
		else if ($data)
			$this->view->items = $data;
	}
	
	/* CREATED AUTO END ************************************ */
	
	public function edit() {
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		$this->view->errors = (isset($_SESSION['errors']))?$_SESSION['errors']:array();
		unset($_SESSION['errors']);
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$account = AccountsModel::getById($accountFetchid);
		$this->view->data = $account;
		$this->view->viewCity = Dic_citiesModel::getById($account['city']);
		
		$this->view->cities = Dic_citiesModel::getAll();
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs [$translates['f.redaktirovanie']]= '#';
	}
	
	public function save() {
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		$data = $this->request("form",array());
		
		$_SESSION['errors'] = array();
		if (count($data)>0) {
			
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
			
			$translates = Register::get('translates');
			/*
			if (md5($data['code']) != $_SESSION['captcha_keystring']) {
				$_SESSION['errors'] []= $translates['front.err.codeincorrect'];
			}*/
			$check = AccountsModel::find($data['email'],$accountFetchid);
			if (count($check)>0) {
				$_SESSION['errors'] []= $translates['front.err.email.exist'];
			}
			$check2 = AccountsModel::findPhone($data['phones'],$accountFetchid);
			if (count($check2)>0) {
				$_SESSION['errors'] []= $translates['front.err.exist.phone'];
			}
			
			if (count($_SESSION['errors'])>0) {
				$this->redirectUrl('/account/edit/');
			}
			elseif (AccountsModel::edit($accountFetchid,$data))
				$this->redirectUrl('/account/?accept');
		}
		$this->redirectUrl('/account/?deny');
	}
	
	/* HISTORY ************************************************************** */
	public function history() {
		
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		
		$bill_id = (int)$this->request("id",0);
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		
		$is_payback = $this->request("is_payback",false);
		if ($is_payback){
			$db = Register::get('db');
			$itemData = CartModel::getOneItem($is_payback);
			$accountInfo = AccountsModel::getById($itemData['account_id']);
			$translates = Register::get('translates');
			$msg = '<h1>'.$translates['front.okaz.ot.zakaz'].'</h1>';
			$msg .= '<h2>'.$translates['front.client.name'].'</h2>';
			$msg .= '<p>';
				$msg .= '<b>'.$translates['front.client.name'].':</b> '.$accountInfo['name'].'<br/>';
				$msg .= '<b>'.$translates['front.request.vin.info14'].':</b> '.$accountInfo['phones'].'<br/>';
				$msg .= '<b>E-mail:</b> '.$accountInfo['email'].'<br/>';
			$msg .= '</p>';
			
			$msg .= '<h2>'.$translates['front.order.name'].' #'.$itemData['number'].' от '.date("d.m.Y",$itemData['createDT']).'</h2>';
			$msg .= '<p>';
				$msg .= '<b>'.$translates['front.otkaz.ot'].':</b> '.$itemData['name'].' '.$itemData['brand'].' '.$itemData['descr'].' ('.$itemData['cc'].' x '.PriceHelper::number($itemData['price']).' = '.PriceHelper::number($itemData['cc']*$itemData['price']).')<br/>';
			$msg .= '</p>';
			$msg .= '<p><b><a href="'.HTTP_ROOT.'/staffcp/index/crm/?search[number]='.$itemData['number'].'">'.$translates['front.prosmotr.by.link'].': http://'.$_SERVER['SERVER_NAME'].'/staffcp/index/crm/?search[number]='.$itemData['number'].'</a></b></p>';
			$vars = array();
			$vars ['message']= $msg;
			$email = SettingsModel::get('contact_email');
			EmailsModel::get('payback',$vars,$email.','.$accountInfo['email'],$accountInfo['email'],'Отказ от товара',false);
			$db->post("UPDATE ".DB_PREFIX."cart SET is_payback = 1 WHERE id = '".(int)$is_payback."';");
			$this->redirectUrl('/account/history/');
		}
		
		$search = $this->request("search",array());
		$search ['status']= (isset($search['status']) && $search['status'])?$search['status']:'active';
		if ( $search['status'] == 'active' && (!isset($search['from']) && !isset($search['to'])) ) {
			$search ['from']= 0;
			$search ['to']= 0;
		}
		else {
			$search ['from']= (isset($search['from']) && $search['from'])?$search['from']:"";
			$search ['to']= (isset($search['to']) && $search['to'])?$search['to']:"";
		}
		$search ['from']= (isset($search['from']) && $search['from'])?$search['from']:"";
		$search ['to']= (isset($search['to']) && $search['to'])?$search['to']:"";
		$this->view->_search = $search;

		$this->view->ccOrders = AccountsModel::getBills($accountFetchid);
		$billsDone = AccountsModel::getCountActiveLost($accountFetchid,false);
		$this->view->billsDone = $billsDone;

		$per_page = 50;
		$page = (int)$this->request("page",1);
		$cartItemsCount = AccountsModel::getHistoryAllElementsCOUNTS($accountFetchid,$search);
		$ccAlls = (int)(isset($cartItemsCount['cc'])&&$cartItemsCount['cc'])?$cartItemsCount['cc']:0;
		$this->view->totalPages = (int)(($ccAlls - 1) / $per_page) + 1;
		$this->view->currentPage = $page;
		
		$data = AccountsModel::getHistoryAllElements($accountFetchid,$search,$page,$per_page);
		$this->view->fetchbills = $data;
		
		$this->view->sumMoneyInWork = $this->moneyInWork($accountFetchid);
		
		$this->unsetAlerts($accountFetchid,'status');
		
		if (isset($search['number']) && $search['number']){
			
			$act = ((isset($_REQUEST['act']) && $_REQUEST['act'])?$_REQUEST['act']:false);
			switch ($act){
				case 'add_message':
					$bill = ((isset($_REQUEST['bill']) && $_REQUEST['bill'])?$_REQUEST['bill']:false);
					$message = ((isset($_REQUEST['message']) && $_REQUEST['message'])?$_REQUEST['message']:false);
					$this->add_message($bill,$message);
					$this->redirectUrl('/account/history/?search[number]='.$bill.'#messages');
					break;
			}
			$this->view->bill_messages =  $this->list_bill_message($search['number']);
		}
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs [$translates['front.history']]= '#';
	}
	
	/* ************************************************************* */
	
	private function list_bill_message($number=0){
		$db = Register::get('db');
		
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills_messages WHERE bill_number = '".(int)$number."' ORDER BY dt ASC;";
		$res = $db->query($sql);
		
		if (isset($_REQUEST['unset_new'])){
			$db->post("UPDATE ".DB_PREFIX."cart_bills_messages SET is_new = 0 WHERE bill_number='".mysql_real_escape_string($number)."';");
		}
		
		return $res;
	}
	
	private function add_message($number=0,$message=''){
		$db = Register::get('db');
		$sql = "
			INSERT INTO ".DB_PREFIX."cart_bills_messages
				(`bill_number`,`is_client`,`dt`,`message`,`is_new`)
			VALUES
				(
					'".mysql_real_escape_string($number)."',
					'1',
					'".time()."',
					'".mysql_real_escape_string($message)."',
					'1'
				)
		;";
		if ($number && $message)
			$db->post($sql);
	}
	
	/* ************************************************************* */
	
	public function history_orders() {
	
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		
		$ccOrders = AccountsModel::getBills($accountFetchid);
		$this->view->ccOrders = $ccOrders;
		
		$per_page = 50;
		$page = (int)$this->request("page",1);
		$this->view->ordersList = AccountsModel::fetchBills($accountFetchid,$page,$per_page);
		$this->view->totalPages = (int)(($ccOrders - 1) / $per_page) + 1;
		$this->view->currentPage = $page;
		
		$this->view->totalSum = AccountsModel::totalSum($accountFetchid);
		
		$this->unsetAlerts($accountFetchid,'status');
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs [$translates['front.history']]= '/account/history/';
	}
	public function printbill(){
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		
		$this->view->feedbackCaller = false;
		$this->view->triggerToHide = true;
		$this->layout = "simple";
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		
		$number = mysql_real_escape_string($this->request("number",false));
		$search = array("number"=>$number);
		$bill = AccountsModel::getHistoryAllElements($accountFetchid,$search,1,999);
		if (!$bill)
			$this->error404();
		$this->view->fetchbills = $bill;
		
		$bill = AccountsModel::getAccountBill($accountFetchid,$number);
		$this->view->bill = $bill;
	}
	
	/* HISTORY END************************************************************** */
	
	private function getStatuses($account_id=0){
		$db = Register::get('db');
		$sql = "
			SELECT 
				DS.*,
				(SELECT COUNT(*) FROM ".DB_PREFIX."cart CART LEFT JOIN ".DB_PREFIX."cart_bills CB ON CB.scSID=CART.scSID WHERE CART.status=DS.id AND CB.account_id = '".(int)$account_id."') cc
			FROM ".DB_PREFIX."dic_statuses DS
			ORDER BY DS.id;";
		return $db->query($sql);
	}
	
	public function password() {
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		$this->view->errors = (isset($_SESSION['errors']))?$_SESSION['errors']:array();
		unset($_SESSION['errors']);
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs [$translates['front.changepassword']]= '#';
	}
	
	public function change() {
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		$data = $this->request("form",array());
		
		$_SESSION['errors'] = array();
		if (count($data)>0) {
			
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
			
			$translates = Register::get('translates');
			
			// if (md5($data['code']) != $_SESSION['captcha_keystring']) {
				// $_SESSION['errors'] []= $translates['front.err.codeincorrect'];
			// }
			$passOLD = AccountsModel::getById($accountFetchid);
			if ($passOLD['pass'] != $data['old_pass']) {
				$_SESSION['errors'] []= $translates['front.err.pass1.ne.pass2'];
			}
			if ($data['pass1']!=$data['pass2']) {
				$_SESSION['errors'] []= $translates['front.err.pass.ne.sovpadet'];
			}
			if (count($_SESSION['errors'])>0) {
				$this->redirectUrl('/account/password/');
			}
			elseif (AccountsModel::change($accountFetchid,$data))
				$this->redirectUrl('/account/?pass=accept');
		}
		$this->redirectUrl('/account/?pass=deny');
	}
	
	public function signin() {
		if ($this->verification()) {
			$this->redirectUrl('/account/');
		}
		
		$this->view->errors = (isset($_SESSION['errors']))?$_SESSION['errors']:array();
		if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']){
			$_SESSION['__rego']=$_SERVER['HTTP_REFERER'];
		}
		unset($_SESSION['errors']);
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs [$translates['login']]= '#';
	}
	
	public function signin_window() {
		$this->layout = "ajax";
		$this->render('account/signin_window');
	}
	
	public function signup() {
		if ($this->verification()) {
			$this->redirectUrl('/account/index/');
		}
		
		$this->view->errors_data = (isset($_SESSION['errors_data']))?$_SESSION['errors_data']:array();
		$this->view->errors = (isset($_SESSION['errors']))?$_SESSION['errors']:array();
		
		if (isset($_SESSION['errors_data']['is_firm']) && $_SESSION['errors_data']['is_firm'] == 1){
			$this->view->tab2 = true;
		}
		
		unset($_SESSION['errors']);
		unset($_SESSION['errors_data']);
		
		// $this->view->cities = Dic_citiesModel::getAll();
		// $this->view->offices = OfficesModel::getOffices();
		// $this->view->default_office_id = OfficesModel::getDefaultOfficeId();
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs ['Register']= '#';
	}
	
	public function signup_offices_by_city(){
		$this->layout = "ajax";
		
		$city = $this->request("city",false);
		if ($city){
			$city = Dic_citiesModel::find(urldecode($city));
			if ($city){
				$offices = OfficesModel::getOffices($city);
				if (count($offices) <= 0)
					$offices = OfficesModel::getOffices();
				$this->view->offices = $offices;
			}else{
				$this->view->offices = OfficesModel::getOffices();
			}
		}
		else
			$this->view->offices = OfficesModel::getOffices();
	}
	
	public function deny(){
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs ['Ошибка']= '#';
	}
	
	public function confirmation(){
		
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
	}
	
	
	
	public function add() {
		$data = $this->request("form",array());
		$_SESSION['errors_data']=$data;
		
		// $data['phones'] = "+".str_replace("+","",$data['code1'].''.$data['code2'].''.$data['phones']);
		// $data['name'] = FunctionsViewHelper::mb_ucfirst(((isset($data['name2'])&&$data['name2'])?$data['name2'].' ':'').($data['name']).((isset($data['name3'])&&$data['name3'])?' '.$data['name3']:''));
					
		$_SESSION['errors'] = array();
		if (count($data)>0) {
			$translates = Register::get('translates');
			
			/*if (mb_strlen($data['name'],'utf-8') <= 3){
				$_SESSION['errors'] []= 'Name must be more than 3 letters';
			}*/
			
			$check = AccountsModel::find($data['email'],false);
			if (count($check)>0) {
				$_SESSION['errors'] []= $translates['front.err.email.exist'];
			}
			
			$check2 = AccountsModel::findPhone($data['phones'],false);
			if (count($check2)>0) {
				$_SESSION['errors'] []= $translates['front.err.exist.phone'];
			}
			/*
			if (md5($data['code']) != $_SESSION['captcha_keystring']) {
				$_SESSION['errors'] []= $translates['front.err.codeincorrect'];
			}*/
			
			if ($data['pass1']!=$data['pass2']) {
				$_SESSION['errors'] []= $translates['front.err.pass.ne.sovpadet'];
			}
			
			if (count($_SESSION['errors'])>0) {
				$this->redirectUrl('/account/signup/');
			}
			else {
				unset($_SESSION['errors_data']);
				
				/* **************** */
				if (NOTICE){
					$this->redirectUrl('/account/deny/');
				} else {
					
					$site = $_SERVER['HTTP_HOST'];
					$last_id = AccountsModel::add($data);
					if (SettingsModel::get('registration_confirm')){
						$u_hash = $last_id."-".$data['email']."-".$data['pass1']."-".$data['name'];
						$u_hash = sha1(md5($u_hash));
						//$data['link'] = "http://".$site."/account/accept/?id=".$last_id."&hash=".$u_hash;
						$data['link'] = "http://".$site."/account/";
						EmailsModel::get('regconfirm',$data,$data['email'],('no-reply@'.$site),$translates['front.act.reg'].' '.$site,false);
						$this->redirectUrl('/account/confirmation/');
					} else {
						EmailsModel::get('reg',$data,$data['email'],('no-reply@'.$site),$translates['front.act.reg'].' '.$site,false);
						$_SESSION['errors'] = array();
						$account = AccountsModel::signin($data['email'],$data['pass1']);
						if (count($account)>0) {
							$_SESSION['account'] = $account;
							AccountsModel::lastLogin($_SESSION['account']['id']);
							$this->redirectUrl('/account/');
						}
						else {
							$this->redirectUrl('/account/accept/');	
						}
					}
				}
				/* **************** */
			}
		}
		else {
			$_SESSION['errors'] []= $translates['front.err.reg'];
			$this->redirectUrl('/account/signin/');
		}
	}
	
	public function accept() {
		
		$sms_code = $this->request("sms_code",false);
		$id = $this->request("id",false);
		$hash = $this->request("hash",false);
		
		if ($sms_code){
			if (SettingsModel::get('confirm_sms_reg')){
				
				$db = Register::get('db');
				$sql = "SELECT id,phones,pass FROM ".DB_PREFIX."accounts WHERE is_active = 0 AND sms_confirm = '".(int)$sms_code."';";
				$getConfirmSmsAccount = $db->get($sql);
				if ($getConfirmSmsAccount){
					$db->post("UPDATE ".DB_PREFIX."accounts SET is_active = 1 WHERE id = '".(int)$getConfirmSmsAccount['id']."';");
					
					$account = AccountsModel::signin($getConfirmSmsAccount['phones'],$getConfirmSmsAccount['pass']);
					if (count($account)>0) {
						$_SESSION['account'] = $account;
						AccountsModel::lastLogin($_SESSION['account']['id']);
						$this->redirectUrl('/account/');
					}
					else {
						$this->redirectUrl('/account/accept/?err=smscode');	
					}
				}
				$this->redirectUrl('/account/accept/?err=smscode');
			}
		} elseif (((int)$id) > 0 && !empty($hash)) {
			if (SettingsModel::get('registration_confirm')){
				$db = Register::get('db');
				// die("SELECT * FROM ".DB_PREFIX."accounts WHERE is_active = 0 AND id = '".(int)$id."' ;");
				$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE is_active = 0 AND id = '".(int)$id."' ;";
				$getConfirmEmailAccount = $db->get($sql);
				if ($getConfirmEmailAccount){
					$u_hash = $getConfirmEmailAccount['id']."-".$getConfirmEmailAccount['email']."-".$getConfirmEmailAccount['pass']."-".$getConfirmEmailAccount['name'];
					// $u_hash = $getConfirmEmailAccount['email']."-".$getConfirmEmailAccount['pass']."-".$getConfirmEmailAccount['name'];
					$u_hash = sha1(md5($u_hash));
					
					if ($u_hash==$hash) {
						$db->post("UPDATE ".DB_PREFIX."accounts SET is_active = 1 WHERE id = '".(int)$getConfirmEmailAccount['id']."';");
						
						$account = AccountsModel::signin($getConfirmEmailAccount['phones'],$getConfirmEmailAccount['pass']);
						if (count($account)>0) {
							$_SESSION['account'] = $account;
							AccountsModel::lastLogin($_SESSION['account']['id']);
							$this->redirectUrl('/account/');
						} else {
							$this->redirectUrl('/account/accept/?err=emailcode');	
						}
					} else {
						$this->redirectUrl('/account/accept/?err=emailcode');	
					}
					
				} else $this->redirectUrl('/account/accept/?err=emailcode');
			}
		}
		
		$this->view->sms_confirm_form = false;
		if (SettingsModel::get('confirm_sms_reg') && SettingshiddenModel::get('sms_alert_active')){
			$this->view->sms_confirm_form = true;
		}
		
		$this->view->email_confirm_form = false;
		if (SettingsModel::get('registration_confirm')){
			$this->view->email_confirm_form = true;
		}
		
		// $translates = Register::get('translates');
		// $this->breadcrumbs [$translates['myprofile']]= '/account/';
		// $this->breadcrumbs ['Регистрация']= '#';
	}
	
	public function login() {
		
		$ui = $this->request("ui",false);
		$translates = Register::get('translates');
		//$guest_cart = CartModel::get_scSID();
		//AccountsModel::setTempKeyscSID($guest_cart);
		echo $guest_cart;
		
		$data = $this->request("form",array());
		$_SESSION['errors'] = array();
		if (count($data)>0) {
			
			if (empty($data['email']) && empty($data['pass'])){
				$_SESSION['errors'] []= $translates['front.err.signin.somerrores'];
				$this->redirectUrl('/account/signin/');
			}
			
			$account = AccountsModel::signin($data['email'],$data['pass']);
			
			// print("<pre>");
			// print_r($account);
			// die();
			if (count($account)>0) {
				
				/* ACCOUNT ACL REMEMBER ME */
				$action = isset($_REQUEST['action'])?$_REQUEST['action']:false;
				$data = isset($_REQUEST['form'])?$_REQUEST['form']:array();
				if (isset($data['rememberme']) && $data['rememberme'] == 1 && $action=='signin') {
					$ip=getenv("HTTP_X_FORWARDED_FOR");
					if (empty($ip) || $ip=='unknown'){ 
						$ip=getenv("REMOTE_ADDR"); 
					}
					$login = $data["email"];
					$password = md5(md5($data["pass"]));
					setcookie("cook_email",$login,time()+(60*60*24*365),"/",$_SERVER['HTTP_HOST']);
					setcookie("cook_pass",$password,time()+(60*60*24*365),"/",$_SERVER['HTTP_HOST']);
					
					
				
				}
				/* ~END ACLSYS */
				
				// Discount_programmModel::checkDiscountLevel($account['id']);
				// die($account['id']); 
				//$_SESSION['account'] = $account;
				$_SESSION['account'] = $account;
				AccountsModel::lastLogin($_SESSION['account']['id']);
				
				
				$db = Register::get('db');
				$db->post("UPDATE ".DB_PREFIX."cart SET `scSID`='".mysql_real_escape_string($account['CartScSID'])."', account_id='".(int)$account['id']."' WHERE scSID='".mysql_real_escape_string($guest_cart)."';");
				// echo ("UPDATE ".DB_PREFIX."cart SET `scSID`='".mysql_real_escape_string($account['CartScSID'])."', account_id='".(int)$account['id']."' WHERE scSID='".mysql_real_escape_string($guest_cart)."';");
				// print("<br/><pre>");
				// print_r($db->queryInfo());
				// die(); 
				// AccountsModel::updateSCSID();
				/*
				
				
				$accSCSID = $account['CartScSID'];
					// echo $guest_cart."<br/>".$accSCSID."<br/>".$_SESSION['account']['id']; 
					
					
					$db = Register::get('db');
					// $db->post("UPDATE ".DB_PREFIX."cart SET scSID='".mysql_real_escape_string($accSCSID)."', account_id='".(int)$_SESSION['account']['id']."' WHERE scSID='".(int)$guest_cart."';");
					$db->query("UPDATE ".DB_PREFIX."cart SET `scSID`='".mysql_real_escape_string($accSCSID)."', `account_id`='".(int)$_SESSION['account']['id']."' WHERE `scSID`='".(int)$guest_cart."'");
					print("<pre>");
					print_r($db->queryInfo());
					die();*/
					
					// die();
				
				// AccountsModel::setSCSID($get_scSID);
				/*
				
				if (isset($_SESSION['redirect_from_TO'])) {
					$pathD = $_SESSION['redirect_from_TO'];
					unset($_SESSION['redirect_from_TO']);
					$this->redirectUrl($pathD);
				}
				
				if (isset($_SESSION['path_direct'])) {
					$pathD = $_SESSION['path_direct'];
					unset($_SESSION['path_direct']);
					// $this->redirectUrl(str_replace("&ajax=true", "", $pathD));
					$this->redirectUrl($pathD);
				}
				if (isset($_SESSION['__rego'])){
					// $this->redirectUrl(str_replace("&ajax=true", "", $_SESSION['__rego']));
					$this->redirectUrl($_SESSION['__rego']);
				}
				else*/
					$this->redirectUrl('/account/');
			}
			else {
				$_SESSION['errors'] []= $translates['front.err.signin.somerrores'];
				$this->redirectUrl('/account/signin/');	
			}
		}
		else {
			$_SESSION['errors'] []= $translates['front.err.signin.somerrores'];
			$this->redirectUrl('/account/signin/');
		}
	}
	
	public function verification() {
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$check = AccountsModel::getById($accountFetchid);
		if (count($check)>0) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function remide() {
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs [$translates['front.pass.restore.name']]= '#';
	}
	
	public function restore() {
		$data = $this->request("form",array());
		if (isset($data['email'])) {
			
			$db = Register::get('db');
			$sql = "select * from ".DB_PREFIX."accounts where email like '".addslashes($data['email'])."';";
			$res = $db->get($sql);
			
			if (count($res)>0){
				$form = array();
				$form ['email']= $res['email'];
				$form ['name']= $res['name'];
				$form ['pass']= $res['pass'];
				
				$translates = Register::get('translates');
				$site = $_SERVER['SERVER_NAME'];
				EmailsModel::get('remide',$form,$res['email'],('no-reply@'.$site),$translates['front.pass.restore.name'].' '.$site,false);
				$this->redirectUrl('/account/remide/?accept');
			}
			else $this->redirectUrl('/account/remide/?deny');
		}
		$this->redirectUrl('/account/remide/?deny');
	}
	
	public function logout() {
		//CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));

		$scSID_KEY = time().'-'.rand(1000, 9999);
		// $_SESSION['_scSID']=$scSID_KEY;
		$obj = new AccountsModel();
		$obj->unsetTempKeyscSID();
		
		setcookie("cook_email","",time(),"/",$_SERVER['HTTP_HOST']);
		setcookie("cook_pass","",time(),"/",$_SERVER['HTTP_HOST']);
		unset($_SESSION['account']);
		$this->redirectUrl('/account/index/');
	}
	
	/* *********************************************** WAREHOUSE **************/
	public function warehouse(){
		if (!$this->verification() && $this->activatedWarehouse()) {
			$this->redirectUrl('/account/');
		}
		#if isset act to save order by accept use the redirect for  update data
		$this->save_my_orders_wh();
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$this->view->mydetails = $this->getMyDetails($accountFetchid);
		$this->view->brands = $this->getBrandsALL();
		
		$edit = (int)$this->request("edit",0);
		if ($edit)
			$this->view->edit = $this->getByIdDetail($edit);
			
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs ['Мой склад']= '#';
	}
	function save_my_orders_wh(){
		$act = $this->request("act");
		if ($act == 'accept_my_orders') {
			$db = Register::get('db');
			
			$items = $this->request("item");
			if (isset($items)&&count($items)>0){
				
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
				
			$IS_ACCOUNT = $accountFetchid;
			$sql = "UPDATE `".DB_PREFIX."cart` SET `is_account_accept`='0' WHERE is_account='".mysql_real_escape_string($IS_ACCOUNT)."';";
			$db->post($sql);
					
				foreach ($items as $K=>$V){
					$sql = "UPDATE `".DB_PREFIX."cart` SET `is_account_accept`='1' WHERE is_account='".mysql_real_escape_string($IS_ACCOUNT)."' AND id='".(int)$K."';";
					$db->post($sql);
				}
			}
			$this->redirectUrl('/account/warehouse/');
		}
	}
	public function savedetail(){
		if (!$this->verification() || !$this->activatedWarehouse()) {
			$this->redirectUrl('/account/');
		}
		$db = Register::get('db');
		$form = $this->request("form");
		
		$EDIT = (int)$form['EDIT'];
		$BRAND_ID = (int)$form['BRAND_ID'];
		$BRA_FETCH = $this->getBrand($BRAND_ID);
		$BRAND_NAME = $BRA_FETCH['BRA_BRAND'];
		$ARTICLE = mysql_real_escape_string($form['ARTICLE']);
		$ARTICLE = strtoupper(FuncModel::stringfilter($ARTICLE));
		
		$PRICE = mysql_real_escape_string($form['PRICE']);
		$DESCR = mysql_real_escape_string($form['DESCR']);
		$BOX = (int)$form['BOX'];
		$DELIVERY = (int)$form['DELIVERY'];
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$IS_ACCOUNT = $accountFetchid;
		
		if ($BRAND_ID && $ARTICLE && $PRICE && $BOX) {
			
			if ($EDIT) {
				$sql = "UPDATE `".DB_PREFIX."details` SET BRAND_ID='".$BRAND_ID."',BRAND_NAME='".$BRAND_NAME."',ARTICLE='".$ARTICLE."',PRICE='".$PRICE."',DESCR='".$DESCR."',BOX='".$BOX."',DELIVERY='".$DELIVERY."',IS_ACCOUNT='".$IS_ACCOUNT."' WHERE ID='".(int)$EDIT."';";
				$db->post($sql);
			}
			else {
				$sql = "INSERT INTO `".DB_PREFIX."details` (`BRAND_ID`,`BRAND_NAME`,`ARTICLE`,`PRICE`,`DESCR`,`BOX`,`DELIVERY`,`IS_ACCOUNT`) VALUES ('".$BRAND_ID."','".$BRAND_NAME."','".$ARTICLE."','".$PRICE."','".$DESCR."','".$BOX."','".$DELIVERY."','".$IS_ACCOUNT."');";
				$db->post($sql);
			}
		}
		$this->redirectUrl('/account/warehouse/');
	}
	public function deletedetail(){
		if (!$this->verification() || !$this->activatedWarehouse()) {
			$this->redirectUrl('/account/');
		}
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$IS_ACCOUNT = $accountFetchid;
		
		$db = Register::get('db');
		$del_id = $this->request("delete");
		$db->post("DELETE FROM `".DB_PREFIX."details` WHERE ID = '".(int)$del_id."' AND IS_ACCOUNT='".$IS_ACCOUNT."';");
		
		$this->redirectUrl('/account/warehouse/');
	}
	private function getByIdDetail($id){
		if (!$this->verification() || !$this->activatedWarehouse()) {
			$this->redirectUrl('/account/');
		}
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."details` WHERE `ID`='".(int)$id."';";
		return $db->get($sql);
	}
	private function getBrand($id){
		if (!$this->verification() || !$this->activatedWarehouse()) {
			$this->redirectUrl('/account/');
		}
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."brands` WHERE `BRA_ID`='".(int)$id."';";
		return $db->get($sql);
	}
	private function getBrandsALL(){
		if (!$this->verification() || !$this->activatedWarehouse()) {
			$this->redirectUrl('/account/');
		}
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."brands` ORDER BY BRA_BRAND;";
		return $db->query($sql);
	}
	private function getMyDetails($id){
		$db = Register::get('db');
		$sql = "SELECT T1.*,T2.BRA_BRAND FROM `".DB_PREFIX."details` T1 LEFT JOIN `".DB_PREFIX."brands` T2 ON T1.BRAND_ID = T2.BRA_ID WHERE `IS_ACCOUNT`='".(int)$id."';";
		return $db->query($sql);
	}
	/* *********************************************** WAREHOUSE END **************/
	
	function operation(){
		if (!$this->verification()) {
			$this->redirectUrl('/account/');
		}
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_history WHERE account_id='".(int)$accountFetchid."' ORDER BY dt DESC LIMIT 0,20;";
		$res = $db->query($sql);
		$this->view->operations = $res;
		
		$this->view->sumMoneyInWork = $this->moneyInWork($accountFetchid);
			
		$translates = Register::get('translates');
		$this->breadcrumbs [$translates['myprofile']]= '/account/';
		$this->breadcrumbs ['История операций']= '#';
	}
	private function moneyInWork($account_id=0){
		$db = Register::get('db');
		$sql = "
			SELECT
				SUM(ITEMS.price*ITEMS.count) tsum,
				BILLS.delivery_price,
				BILLS.delivery_set_balance
			FROM ".DB_PREFIX."cart ITEMS
			JOIN ".DB_PREFIX."cart_bills BILLS ON BILLS.scSID=ITEMS.scSID
			LEFT JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=ITEMS.status 
			WHERE 
				BILLS.account_id = '".(int)$account_id."' AND
				ITEMS.balance_minus = 0 AND
				DS.type = 1
			GROUP BY
				BILLS.id
		;";
		$res = $db->query($sql);
		$total = 0;
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				if ($dd['delivery_set_balance'] == 0){
					$total += $dd['tsum'] + $dd['delivery_price'];
				}
				else {
					$total += $dd['tsum'];
				}
			}
		}
		return $total;
	}
	
	function beforeAction(){
		parent::beforeAction();
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		
		$account_office_id = $this->request("account-office-id",false);
		if ($account_office_id){
			$this->updateOffice($account_office_id);
			$this->redirectUrl($_SERVER['HTTP_REFERER']);
		}
	}

	function beforeRender() {
		parent::beforeRender();
	}
	
	/* ********************* */
	function updateOffice($id){
		if (isset($this->accountData) && isset($this->accountData['city']) && $this->accountData['city']) {
			$db = Register::get('db');
			$db->post("UPDATE ".DB_PREFIX."accounts SET office_id='".(int)$id."' WHERE id='".(int)$this->accountData['id']."';");
		}
	}
}

?>