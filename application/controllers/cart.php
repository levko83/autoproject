<?php

class CartController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		
		$get_scSID = CartModel::get_scSID();
		$this->view->data = CartModel::get($get_scSID);
		$this->view->cur_lang = $_SESSION["setLang"];
		
		$this->breadcrumbs ['Cart']= '/cart/';
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
	
	public function printcart(){
		
		$this->layout = "simple";
		$this->view->cur_lang = $_SESSION["setLang"];
		$get_scSID = CartModel::get_scSID();
		$this->view->data = CartModel::get($get_scSID);
	}
	
	public function cart_window(){
		
		$this->layout = "ajax";
		$this->view->cur_lang = $_SESSION["setLang"];
		$get_scSID = CartModel::get_scSID();
		$this->view->data = CartModel::get($get_scSID);
	}
	
	
	
	public function bill() {
		
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		
		// $delete = $this->request("delete",false);
		// if ($delete && count($delete)>0){
			// $db = Register::get('db');
			// $db->post("DELETE FROM ".DB_PREFIX."cart WHERE id IN (".join(",", $delete).")");
		// }
		
		// $ui = $this->request("ui",false);
		
		$get_scSID = CartModel::get_scSID();
		$this->view->data = CartModel::get($get_scSID);
		$this->view->cur_lang = $_SESSION["setLang"];
		
		$deliveries = DeliveriesModel::getAll(CartModel::getSum($get_scSID),$this->getOfficeId());
		$this->view->deliveries = $deliveries;
		
		$delivery = (int)$this->request("delivery",((isset($_SESSION['delivery'])&&$_SESSION['delivery'])?$_SESSION['delivery']:0));
		if ($delivery){
			$this->view->set_delivery = DeliveriesModel::getById($delivery,$deliveries,CartModel::getSum($get_scSID));
		} else {
			$this->view->set_delivery = DeliveriesModel::getDefault(CartModel::getSum($get_scSID),$this->getOfficeId());
		}
		
		// $delivery = $this->request("current_delivery",$this->request("temporaryDeliveryId"));
		// if ($delivery)
			// $_SESSION['delivery'] = $delivery;
		// $this->view->set_delivery = DeliveriesModel::getById($_SESSION['delivery']);
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$account = AccountsModel::getById($accountFetchid);
		
		$this->view->viewCity = Dic_citiesModel::getById($account['city']);
		$this->view->cities = Dic_citiesModel::getAll();
		
		$redirectSwitch = $this->request("redirectSwitch",true);
		if ($redirectSwitch == 'true')
			$this->refreshBoxCart(true,'/cart/bill/');
		else 
			$this->refreshBoxCart(true,'/cart/');
		
		$this->view->merchants_list = Merchants_listModel::getFull();
		
		$get_scSID = CartModel::get_scSID();
		$this->view->data = CartModel::get($get_scSID);
		
		$this->breadcrumbs ['Корзина']= '/cart/';
		$this->breadcrumbs ['Оформление заказа']= '#';
	}
	
	private function refreshBoxCart($direct=false,$url='/'){
		
		$arr_cc = $this->request("count");
		if (!empty($arr_cc)) {
			
			$get_scSID = CartModel::get_scSID();
			$db = Register::get('db');
			
			foreach ($arr_cc as $kk=>$cc) {
				$db->query("UPDATE ".DB_PREFIX."cart SET `count`='".(int)$cc."' WHERE id = '".(int)$kk."' AND scSID = '".mysql_real_escape_string($get_scSID)."';");
			}
			if ($direct)
				$this->redirectUrl($url);
		}
	}
	
	public function refresh_id(){
		
		$this->layout = "ajax";
		
		$id = $this->request("id",false);
		$count = $this->request("count",false);
		if ($id && $count) {
			$get_scSID = CartModel::get_scSID();
			$db = Register::get('db');
			$db->post("UPDATE ".DB_PREFIX."cart SET `count`='".(int)$count."' WHERE id = '".(int)$id."' AND scSID = '".mysql_real_escape_string($get_scSID)."';");
		}
		
		exit();
	}
	
	public function totalsum(){
		$this->layout = "ajax";
		$currenciesNames = Register::get('currenciesNames');
		
		$get_scSID = (isset($_SESSION['simple_scsid'])&&$_SESSION['simple_scsid'])?$_SESSION['simple_scsid']:CartModel::get_scSID();
		$res = CartModel::xboxTotalSum($get_scSID);
		
		echo (PriceHelper::number($res).' '.$currenciesNames[$this->getCurMoney()]);
		exit();
	}
	
	public function total_prod(){
		$this->layout = "ajax";
		
		$get_scSID = (isset($_SESSION['simple_scsid'])&&$_SESSION['simple_scsid'])?$_SESSION['simple_scsid']:CartModel::get_scSID();
		$count = CartModel::xbox($get_scSID);
		
		echo $count;
		exit();
	}
	
	public function add() {
		
		$this->layout = "clear";
		$db = Register::get('db');
		
		$fk = (int)$this->request("id");
		$count = (int)$_REQUEST['ccount'];
		$type = addslashes($this->request("type"));
		$price = addslashes($this->request("price",0));
		$min = addslashes($this->request("min",0));
		$price_purchase = addslashes(CryptViewHelper::xdecode($this->request("purchase",0)));
		//$descr = addslashes(CryptViewHelper::xdecode($this->request("descr")));
		$articleJS = addslashes(CryptViewHelper::xdecode($this->request("article")));
		$brandJS = addslashes(CryptViewHelper::xdecode($this->request("brand")));
		
		$timedelivery = addslashes($this->request("timedelivery",0));
		$timedeliveryDt = mktime(0,0,0,date("m"),(date("d")+(int)$timedelivery),date("Y"));
		
		/* ******************* */
		$groupId = $this->getGroupId();

		//$article = $articleJS;
		$article = addslashes($this->request("article"));
		$descr = addcslashes($this->request("descr"));
		$brand = $brandJS;
		$import_id = $fk;

		
		if (isset($_SESSION['simple_scsid'])&&$_SESSION['simple_scsid']){
			$get_scSID = $_SESSION['simple_scsid'];
		}
		else {
			$get_scSID = CartModel::get_scSID();
		}
		// Проверяем наличии сессии
		if ($get_scSID){
		
			$checkForExistItem = $this->checkForExistItem($get_scSID,$fk,$import_id,$price,$price_purchase,$article,$brand,$descr);
			if (isset($checkForExistItem) && $checkForExistItem){
				$db->post("UPDATE ".DB_PREFIX."cart SET `count` = `count` + '".(int)$count."' WHERE id = '".(int)$checkForExistItem."';");
			}
			else {
				$sql = "
				INSERT INTO ".DB_PREFIX."cart (
					`scSID`,
					`scSID_group`,
					`createDT`,
					`fk`,
					`wbs_id`,
					`count`,
					`type`,
					`price`,
					`price_purchase`,
					`article`,
					`brand`,
					`descr_tecdoc`,
					`import_id`,
					`is_account`,
					`min`,
					`time_delivery_wait_dt`,
					`time_delivery_descr`,
					`account_id`,
					`currency_rate`
				) VALUES (
					'{$get_scSID}',
					'".(int)$groupId."',
					'".time()."',
					'".(int)$fk."',
					'".(int)$import_id."',
					'".(int)$count."',
					'',
					'".mysql_real_escape_string($price)."',
					'".mysql_real_escape_string($price_purchase)."',
					'".mysql_real_escape_string($article)."',
					'".mysql_real_escape_string($brand)."',
					'".mysql_real_escape_string($descr)."',
					'".(int)$import_id."',
					'0',
					'".(int)$min."',
					'".mysql_real_escape_string($timedeliveryDt)."',
					'".mysql_real_escape_string($timedelivery)."',
					'".(int)((isset($this->accountData['id']) && $this->accountData['id'])?$this->accountData['id']:0)."',
					'".mysql_real_escape_string($this->accountRate)."'
				);";
				$db->post($sql);
			}
			
			$count = CartModel::xbox($get_scSID);
			echo ($count);
		}
		else {
			
		}
	}
	
	private function checkForExistItem($get_scSID,$fk,$import_id,$price,$price_purchase,$article,$brand,$descr){
		$db = Register::get('db');
		
		$sql = "
			SELECT 
				id 
			FROM ".DB_PREFIX."cart 
			WHERE 
				scSID='{$get_scSID}' AND
				fk='".(int)$fk."' AND
				wbs_id='".(int)$import_id."' AND
				price='".mysql_real_escape_string($price)."' AND
				price_purchase='".mysql_real_escape_string($price_purchase)."' AND
				article LIKE '".mysql_real_escape_string($article)."' AND
				brand LIKE '".mysql_real_escape_string($brand)."' AND
				descr_tecdoc LIKE '".mysql_real_escape_string($descr)."';";
		
		$res = $db->get($sql);
		return $res['id'];
	}
	private function getGroupId(){
		return (isset($_SESSION['shopping']['group_id']) && $_SESSION['shopping']['group_id'])?$_SESSION['shopping']['group_id']:0;
	}
	
	public function delete() {
		// $id = (int)$this->request("id");
		// die($this->request("id")); 
		$db = Register::get('db');
		$db->query("delete from ".DB_PREFIX."cart where id='".(int)$this->request("id")."'");
		$this->redirect("index","cart");
	}
	
	public function delete_prod() {
		$this->layout = "simple";
		// $id = (int)$this->request("id");
		// die($this->request("id")); 
		$db = Register::get('db');
		$db->query("delete from ".DB_PREFIX."cart where id='".(int)$this->request("id")."'");
		exit();
		// $this->redirect("index","cart");
	}
	
	
	
	function md5(){
		$md5 = $this->request("key");
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills WHERE md5_hash LIKE '".mysql_real_escape_string($md5)."';";
		$res = $db->get($sql);
		
		/* check status of paid */
		$sql = "SELECT paid FROM ".DB_PREFIX."settings_merchants_result WHERE orderid_bill = '".mysql_real_escape_string($res['number'])."';";
		$paid = $db->get($sql);
		$res ['is_paid']= (isset($paid['paid']) && $paid['paid'])?$paid['paid']:$res['is_paid'];
		/* ******************** */
		
		if (count($res) <= 0){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			$controller = new Dispatcher();
			$controller->process('/error404');
			exit();
		}
		
		$this->view->merchant_data = Merchants_listModel::getByName($res['payment_name']);
		$this->view->delivery_data = DeliveriesModel::getByName($res['delivery']);
		$this->view->orderData = $res;
		$this->view->data = CartModel::get($res['scSID']);
		$this->view->cur_lang = $_SESSION["setLang"];
		$this->view->page = SettingsModel::get('cart_page');
		$this->view->merchants_list = Merchants_listModel::getFull();
		
		$this->breadcrumbs ['Корзина']= '/cart/';
		$this->breadcrumbs ['Заказ']= '/cart/';
	}
	
	/* ********************************************************************************************* */
	
	public function paybridge(){
	
		$p = $this->request("p",false);
		$bill = $this->request("bill",false);
	
		if ($p && $bill){

			$payinfo = Merchants_listModel::getBymcode($p);
			
			$billinfo = CartModel::getCartBillScSID($bill);
			$itemsinfo = CartModel::getCartItemsScSID($bill);
			$sum = CartModel::getCartItemsScSIDSum($bill);
			
			$db = Register::get('db');
			$sql = "UPDATE ".DB_PREFIX."cart_bills SET payment_name = '".mysql_real_escape_string($payinfo['name'])."' WHERE scSID = '".mysql_real_escape_string($bill)."';";
			$db->post($sql);
			
			if (isset($payinfo['mcode']) && $payinfo['mcode']){
				
				$form = array();
				$form['name']=$billinfo['f1'];
				$form['phone']=$billinfo['f2'];
				$form['email']=$billinfo['f3'];
				$form['address']=$billinfo['delivery_addess'];
				$cart_counter = $billinfo['number'];
				$elements = $itemsinfo;
				
				switch ($payinfo['mcode']){
					case 'HG':
						$hg_active = SettingsmerchantsModel::get('hg_active');
						if ($hg_active){
							$HG = array();
							$HG ['order'] = $billinfo['number'];
							$HG ['date'] = date("d.m.Y H:i:s",$billinfo['dt']);
							$HG ['date_mktime'] = mktime();
							$HG ['name'] = $billinfo['f1'];
							$HG ['phone'] = $billinfo['f2'];
							$HG ['address'] = $billinfo['delivery_addess'];
							$Merchant_auth = array('user'=>SettingsmerchantsModel::get('hg_user'),'pwd'=>SettingsmerchantsModel::get('hg_pwd'),'eripId'=>SettingsmerchantsModel::get('hg_eripId'));
							$MERCHANT_params = array('incomeData'=>$HG,'sum'=>($sum+$billinfo['delivery_price']),'cart'=>$itemsinfo,'auth'=>$Merchant_auth,'delivery_sum'=>$billinfo['delivery_price']);
							MerchantsModel::HG($MERCHANT_params);
						}
						break;
					case 'IPAY':
						$ipay_active = SettingsmerchantsModel::get('ipay_active');
						if ($ipay_active){
							$this->IPAY($form['name'].' '.$form['phone'].' '.$form['email'],$cart_counter,$sum);
						}
						break;
					case 'WEBPAY':
						$webpay_active = SettingsmerchantsModel::get('webpay_active');
						if ($webpay_active){
							$this->WEBPAY($form,$cart_counter,$sum,$elements);
							exit();
						}
						break;
					case 'QIWI':
						$qiwi_active = SettingsmerchantsModel::get('qiwi_active');
						if ($qiwi_active){
							$Qlogin = SettingsmerchantsModel::get('qiwi_login');
							$Qpass = SettingsmerchantsModel::get('qiwi_password');
							$qiwiResult = $this->QIWI($Qlogin,$Qpass,$form['phone'],$sum,$cart_counter,'Оплата заказа №'.$cart_counter.' в интернет-магазине '.$_SERVER['SERVER_NAME']);
							exit();
						}
						break;
					case 'ASSIST':
						$assist_active = SettingsmerchantsModel::get('assist_active');
						if ($assist_active){
							$this->ASSIST($cart_counter,$sum,$form['name'].' '.$form['phone'],$form['email']);
							exit();
						}
						break;
					case 'DENGIONLINE':
						$dengionline_active = SettingsmerchantsModel::get('dengionline_active');
						if ($dengionline_active){
							$this->DENGIONLINE($form,$cart_counter,$sum,$elements);
							exit();
						}
						break;
					case 'paypal':
						$paypal_active = SettingsmerchantsModel::get('paypal_active');
						if ($paypal_active){
							$name = $form['name'].' '.$form['phone'].' '.$form['email'];
							$comment = 'Оплата заказа №'.$cart_counter.' в интернет-магазине '.$_SERVER['SERVER_NAME'];
							$db->post("
								INSERT INTO ".DB_PREFIX."settings_merchants_result
									(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
								VALUES
									('PAYPAL','".mysql_real_escape_string($name)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($cart_counter)."','".mysql_real_escape_string($comment)."','','".mktime()."','0');
							");
							$this->PAYPAL($form,$cart_counter,round($sum,2),$elements);
							exit();
						}
						break;
					case 'sofort':
						$sofort_active = SettingsmerchantsModel::get('sofort_active');
						if ($sofort_active){
							$name = $form['name'].' '.$form['phone'].' '.$form['email'];
							$comment = 'Оплата заказа №'.$cart_counter.' в интернет-магазине '.$_SERVER['SERVER_NAME'];
							$db->post("
								INSERT INTO ".DB_PREFIX."settings_merchants_result
									(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
								VALUES
									('SOFORT','".mysql_real_escape_string($name)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($cart_counter)."','".mysql_real_escape_string($comment)."','','".mktime()."','0');
							");
							$this->SOFORT($form,$cart_counter,$sum,$elements);
							exit();
						}
						break;
					
					case 'ROBOKASSA':
						$robokassa_active = SettingsmerchantsModel::get('robokassa_active');
						if ($robokassa_active){
							$name = $form['name'].' '.$form['phone'].' '.$form['email'];
							$comment = 'Оплата заказа №'.$cart_counter.' в интернет-магазине '.$_SERVER['SERVER_NAME'];
							$db->post("
								INSERT INTO ".DB_PREFIX."settings_merchants_result
									(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
								VALUES
									('ROBOKASSA','".mysql_real_escape_string($name)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($cart_counter)."','".mysql_real_escape_string($comment)."','','".mktime()."','0');
							");
							$this->redirectUrl('/cart/robokassa/?bill='.$cart_counter);
							exit();
						}
						break;
					case 'MONEXY':
						$monexy_active = SettingsmerchantsModel::get('monexy_active');
						if ($monexy_active){
							$this->MONEXY($form,$cart_counter,$sum,$elements);
							exit();
						}
						break;
					case 'alfa':
						$alfa_active = SettingsmerchantsModel::get('alfa_active');
						if($alfa_active){
							$this->alfa($sum, $cart_counter, $form);
							exit();
						}
						break;
					case 'YANDEXPC':
						$yaactive = SettingsmerchantsModel::get('yandexpc_active');
						if ($yaactive){
							$this->yandexPC($form,$cart_counter,$sum,$elements);
							exit();
						}
						break;
					case 'YANDEXAC':
						$yaactive = SettingsmerchantsModel::get('yandexac_active');
						if ($yaactive){
							$this->yandexAC($form,$cart_counter,$sum,$elements);
							exit();
						}
						break;
				}
			}
				
			$this->redirectUrl('/cart/md5/key/'.md5('o.'.$billinfo['number']).'/');
		}
	
		$this->redirectUrl('/');
	}
	/* ********************************************************************************************* */
	
	public function send() {
		
		if (NOTICE && !$this->accountData){
			$this->redirectUrl('/account/deny/');
		}
		
		$db = Register::get('db');
		$form = $this->request("form");
		$ui = $this->request("ui",false);
		$get_scSID = CartModel::get_scSID();
		$elements = CartModel::get($get_scSID);
		
		if (count($elements) < 1) {
			$this->redirectUrl('/cart/');
		}	
		
		// $delivery = DeliveriesModel::getById($_SESSION['delivery']);
		$delivery = (int)((isset($form['current_delivery']))?$form['current_delivery']:0);
		$delivery = DeliveriesModel::getById($delivery);
		$merchant_type = $this->request("merchant_type",false);
		$price1 =0;
if($merchant_type=='OFFICE_COUR') {
	$price1 = 5;
}
		$paymentName = Merchants_listModel::getBymcode($merchant_type);
		$price1 =0;			
if(!empty($paymentName['price'])) { 
$price1 = $paymentName['price'];
}
		$paymentName = $paymentName['name'];

		$_SESSION['cart_send'] = 1;
		
		$site = $_SERVER['SERVER_NAME'];
		
		$cart_counter = SettingsModel::get('cartcounter');
		// $email = SettingsModel::get('contact_email');
		$email = "bestellung@autoresurs.de";
		
		/* ****************************************************** */
		
		$translates = Register::get('translates');
		//$list = '<table cellpadding="10" cellspacing="0" border="0" width="100%">';
		//$list .= '<tr>';
		//$list .= '<td><b></b></td>';
		//$list .= '<td><b>'.$translates['front.price'].'</b></td>';
		//$list .= '<td><b>'.$translates['front.box'].'</b></td>';
		//$list .= '<td><b>'.$translates['front.cost'].'</b></td>';
		//$list .= '</tr>';
		$sum=0;
		$i=0;
		
			// print("<pre>");
			// print_r($elements);
			// die();  
		$lang = $_SESSION["setLang"];
		foreach ($elements as $dd){
		$i++;
			$colors = ($i%2)?"#f1f1f1":"";
			//$list .= '<tr bgcolor="'.$colors.'">';
			//$list .= '<td>'.$dd['name_'.$lang].' '.$dd['supplier_name'].'</td>';
			//$list .= '<td>'.PriceHelper::number($dd['price']).' Euro</td>';
			//$list .= '<td>'.$dd['cc'].'</td>';
			//$list .= '<td>'.PriceHelper::number($dd['cc']*$dd['price']).'</td>';
			//$list .= '</tr>';
			$sum += round($dd['cc']*$dd['price'],2);
		}
		$sum +=$price1;
		if ($delivery){ 
		if($sum>$delivery['free_from']&&$delivery['free_from']!=0) {
			$delivery['price']= 0;
		}
			//$list .= '<tr><td colspan="4" align="right"><b>'.$translates['front.delivery.block'].'</b>: '.PriceHelper::number($delivery['price']).'</td></tr>';
			//$list .= '<tr><td colspan="4" align="right"><b>'.$translates['front.paymentplus'].'</b>: '.PriceHelper::number($price1).'</td></tr>';
			//$list .= '<tr><td colspan="4" align="right"><b>'.$translates['front.summ'].'</b>: '.PriceHelper::number(($sum+$delivery['price'])).'</td></tr>';
		}
		else {
			//$list .= '<tr><td colspan="4" align="right"><b>'.$translates['front.summ'].'</b>: '.PriceHelper::number($sum).'</td></tr>';	
		}
		//$list .= '</table>';
		
		//$list .= '<span style="font-family:verdana,geneva,sans-serif;"><span style="font-size:18px;"><span style="background-color: rgb(211, 211, 211);">'.$translates['front.delivery.payment.name'].'</span></span></span>';
		//$list .= '<p><strong>'.$translates['front.delivery.block'].':</strong> '.$delivery['name'].'. '.$translates['front.cost'].': '.PriceHelper::number($delivery['price']).'</p>';
		//$list .= '<p><strong>'.$translates['front.name.payment'].':</strong> '.$paymentName.'. '.$translates['front.cost'].': '.PriceHelper::number($price1).'</p>';
		
		$form ['date'] = date("d.m.Y H:i:s");
		$form ['order_numer'] = $cart_counter;
		$form ['order']	= $list;
		
		
		$form ['message']= $form['message'].(isset($form['agree_check_items'])?(' <b style="color:red;">'.$translates['front.need.to.check'].'</b>'):(''));
		$form ['url']= '<a href="'.HTTP_ROOT.'/cart/md5/key/'.md5("o.".$cart_counter).'" target="_blank">http://'.$site.'/cart/md5/key/'.md5("o.".$cart_counter).'</a>';
		$form ['sitename'] = $site;
		$form ['paymentname'] = $paymentName;
		// print_r("<pre>");
		// print_r($form);
		// die();
		if (EmailsModel::get('cart',$form,((isset($form['email'])&&$form['email'])?$form['email']:"bestellung@autoresurs.de"),"bestellung@autoresurs.de",$translates['front.order.number'].''.$cart_counter.' ('.$site.')',false)) {
			$form ['delivery']= $delivery;
			/**** SMS ****/
			$sms_alert_active = SettingshiddenModel::get('sms_alert_active');
			if ($sms_alert_active){
				SmsSystemHelper::sendSmsMessage(1,array("number"=>$cart_counter,"sum"=>(($delivery)?PriceHelper::number($sum+$delivery['price']):PriceHelper::number($sum))));
				SmsSystemHelper::sendSmsMessage(1,array("number"=>$cart_counter,"sum"=>(($delivery)?PriceHelper::number($sum+$delivery['price']):PriceHelper::number($sum))),$form['phone']);
			}
			/* ********* */
 
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;			
			if (isset($accountFetchid) && $accountFetchid) { 
				AccountsModel::bill($accountFetchid,$get_scSID,$cart_counter,$form);
			}
			else {
				AccountsModel::bill(0,$get_scSID,$cart_counter,$form);
			}
			
			// $this->createCSVbill($cart_counter,$delivery,$elements,$accountFetchid,date("d.m.Y"),$form,$merchant_type);

			/* MERCHANTS */
			$billHG = CartModel::get_scSID();
			
			if ($merchant_type == 'HG'){
				$hg_active = SettingsmerchantsModel::get('hg_active');
				if ($hg_active){
					$HG = array();
					$HG ['order']			= $cart_counter;
					$HG ['date']			= $form['date'];
					$HG ['date_mktime']		= time();
					$HG ['name']			= $form['name'];
					$HG ['phone']			= $form['phone'];
					$HG ['address']			= $form['message'];
					$Merchant_auth 			= array('user'=>SettingsmerchantsModel::get('hg_user'),'pwd'=>SettingsmerchantsModel::get('hg_pwd'),'eripId'=>SettingsmerchantsModel::get('hg_eripId'));
					$MERCHANT_params		= array('incomeData'=>$HG,'sum'=>($sum+$delivery['price']),'cart'=>$elements,'auth'=>$Merchant_auth,'delivery_sum'=>$delivery['price']);
					$billHG 				= MerchantsModel::HG($MERCHANT_params);
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$billHG."' where scSID='".$get_scSID."';");
				}
			}
			// die($merchant_type);
			if ($merchant_type == 'IPAY'){
				$ipay_active = SettingsmerchantsModel::get('ipay_active');
				if ($ipay_active){
					$this->IPAY($form['name'].' '.$form['phone'].' '.$form['email'],$cart_counter,$sum);
				}
			}
			
			if ($merchant_type == 'WEBPAY'){
				$webpay_active = SettingsmerchantsModel::get('webpay_active');
				if ($webpay_active){
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
					
					$this->WEBPAY($form,$cart_counter,$sum,$elements);
					exit();
				}
			}
			
			
			if ($merchant_type == 'QIWI'){
				$qiwi_active = SettingsmerchantsModel::get('qiwi_active');
				if ($qiwi_active){
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
					
					$Qlogin = SettingsmerchantsModel::get('qiwi_login');
					$Qpass = SettingsmerchantsModel::get('qiwi_password');
					$qiwiResult = $this->QIWI($Qlogin,$Qpass,$form['phone'],$sum,$cart_counter,'Оплата заказа №'.$cart_counter.' в интернет-магазине '.$_SERVER['SERVER_NAME']);
					exit();
				}
			}
			
			if ($merchant_type == 'ASSIST'){
				$assist_active = SettingsmerchantsModel::get('assist_active');
				if ($assist_active){
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
					
					$this->ASSIST($cart_counter,$sum,$form['name'].' '.$form['phone'],$form['email']);
					exit();
				}
			}
			
			if ($merchant_type == 'DENGIONLINE'){
				$dengionline_active = SettingsmerchantsModel::get('dengionline_active');
				if ($dengionline_active){
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
					
					$this->DENGIONLINE($form,$cart_counter,$sum,$elements);
					exit();
				}
			}
			if ($merchant_type == 'paypal'){
				$paypal_active = SettingsmerchantsModel::get('paypal_active');
				if ($paypal_active){
				/*	print_r($form);
				echo "Cart<br><br><br>";	print_r($cart_counter);
				echo "Sum<br><br><br>";		print_r($sum);
					 print_r($elements);die(); */
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
					
					$this->PAYPAL($form,$cart_counter,$sum,$elements);
					exit();
				}
			}
			if ($merchant_type == 'sofort'){
				$sofort_active = SettingsmerchantsModel::get('sofort_active');
				if ($sofort_active){
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
					
					$this->SOFORT($form,$cart_counter,$sum,$elements);
					exit();
				}
			}
			
			if ($merchant_type == 'ROBOKASSA'){
				$robokassa_active = SettingsmerchantsModel::get('robokassa_active');
				if ($robokassa_active){
					
					$name = $form['name'].' '.$form['phone'].' '.$form['email'];
					$comment = 'Оплата заказа №'.$cart_counter.' в интернет-магазине '.$_SERVER['SERVER_NAME'];
					$db->post("
						INSERT INTO ".DB_PREFIX."settings_merchants_result 
							(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
						VALUES
							('ROBOKASSA','".mysql_real_escape_string($name)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($cart_counter)."','".mysql_real_escape_string($comment)."','','".mktime()."','0');
					");
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
					
					$this->redirectUrl('/cart/robokassa/?bill='.$cart_counter);
					exit();
				}
			}
			
			if ($merchant_type == 'MONEXY'){
				$monexy_active = SettingsmerchantsModel::get('monexy_active');
				if ($monexy_active){
					
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
						
					$this->MONEXY($form,$cart_counter,$sum,$elements);
					exit();
				}
			}
			
			if($merchant_type == 'alfa'){
				
				$get_scSID = CartModel::get_scSID();
				$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
				$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
				CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
				
				$alfa_active = SettingsmerchantsModel::get('alfa_active');
				if($alfa_active){
					$this->alfa($sum, $cart_counter, $form);
					exit();
				}
			}
			
			if ($merchant_type == 'YANDEXPC'){
				$yaactive = SettingsmerchantsModel::get('yandexpc_active');
				if ($yaactive){
						
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
			
					$this->yandexPC($form,$cart_counter,$sum,$elements);
					exit();
				}
			}
			
			if ($merchant_type == 'YANDEXAC'){
				$yaactive = SettingsmerchantsModel::get('yandexac_active');
				if ($yaactive){
			
					$get_scSID = CartModel::get_scSID();
					$db->post("update ".DB_PREFIX."cart_bills set MERCHANT='".$merchant_type."' where scSID='".$get_scSID."';");
					$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
					CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
						
					$this->yandexAC($form,$cart_counter,$sum,$elements);
					exit();
				}
			}
			
			$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
			CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
			
			$this->redirectUrl('/cart/md5/key/'.md5("o.".$cart_counter).($ui?'?ui=mobile':''));
		}
		else 
			$this->redirectUrl('/cart/bill/'.($ui?'?ui=mobile':''));
	}
	
	private function createCSVbill($cart_counter=0,$delivery=array(),$elements=array(),$accountFetchid=0,$date,$orderData,$payment_id=''){
		
		$int1C_active = SettingshiddenModel::get('1C_active');
		if ($int1C_active){
			$payment_id = $this->getPaymentType($payment_id);
			$file = $cart_counter.";".$payment_id.";".$delivery['name'].";".$accountFetchid.";".$date."\n";
			if (isset($elements) && count($elements)>0){
				foreach ($elements as $item){
					$imp = ImportersModel::getById($item['fk']);
					$file .= str_replace(" ","",$item['name']).";".$item['brand'].";".strip_tags($item['descr_tecdoc']).";".$imp['name'].";".$item['price_purchase'].";".$item['price'].";".$item['cc']."\n";
				}
			}
			#$file = iconv("utf-8","windows-1251//ignore",$file);
			file_put_contents("./application/payments/order-".$cart_counter.".csv",$file);
		}
	}
	private function getPaymentType($id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."settings_merchants_list WHERE mcode='".mysql_real_escape_string($id)."';";
		$res = $db->get($sql);
		return $res['name'];
	}
	
	private function getMoneyType($id){
		switch ($id) {
			case 0: $type = '$'; break;
			case 1: $type = '&euro;'; break;
			case 2: $type = 'руб.'; break;
			default:0;
		}
		return $type;
	}
	
	private function extra($price,$extra) {
		return $price+($price*$extra/100);
	}
	
	public function accept() {
		// $this->breadcrumbs ['Корзина']= '/cart/';
		// $this->breadcrumbs ['Выполнен']= '/cart/';
	}
	public function deny() {
		// $this->breadcrumbs ['Корзина']= '/cart/';
		// $this->breadcrumbs ['Ошибка']= '/cart/';
	}

	function beforeAction() {
		parent::beforeAction();
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;			
		$this->view->account = AccountsModel::getById($accountFetchid);
		
		if (isset($_SESSION['sendC'])) {
			if ($_SESSION['sendC']==1) {
				$this->view->send = 1;
			}
			elseif ($_SESSION['sendC']==2) {
				$this->view->send = 2;
				$this->view->error_data = $_SESSION['error_data'];
				unset($_SESSION['error_data']);
			}
			unset($_SESSION['sendC']);
		} else {
		  $this->view->send = 0;
		}
		
		
		if (Register::get('mode_view_articles')){
			$this->redirectUrl('/account/signin/');
		}
	}

	
	/* ********************************************************************************************* */
	/* 3D */
	public function repeat3dhosting(){
		$cart_counter = (isset($_REQUEST['bill'])?$_REQUEST['bill']:false);
		$sum = (isset($_REQUEST['sum'])?$_REQUEST['sum']:false);
		if ($cart_counter && $sum){
			$this->D3HOSTING($cart_counter,$sum);
			exit();
		}
		$this->redirectUrl("/cart/deny/");
	}
	private function D3HOSTING($_order_id=0,$_sum=0,$family='',$email=''){
	
		//$_url = SettingsmerchantsModel::get('');
		$_url = "https://testsanalpos.est.com.tr/fim/est3dgate";
	
		$clientId = "160000002"; // Merchant ID
		$amount = $_sum; // Total amount ( shopping total, checkout total )
		$oid = $_order_id; // Order Number, may be produced by some sort of code and set here, if it doesn't exist gateway produces it and returns
		$okUrl = "http://".$_SERVER['HTTP_HOST']."/extensions/3DHosting/3DHostingOdeme.php"; // return page ( hosted at merchant's server ) when process finished successfully, process means 3D authentication and payment after 3D auth
		$failUrl = "http://".$_SERVER['HTTP_HOST']."/extensions/3DHosting/3DHostingOdeme.php"; // return page ( hosted at merchant's server ) when process finished UNsuccessfully, process means 3D authentication and payment after 3D auth
		$rnd = microtime(); // Used to generate some random value
		$islemtipi="Auth"; // Transacation Type
		$storekey = "123456"; //  Merchant's store key, it must be produced using merchant reporting interface and set here.
		$taksit = ""; //  Installment (  how many installments will be for this sale )
		$hashstr = $clientId . $oid . $amount . $okUrl . $failUrl . $islemtipi . $taksit . $rnd . $storekey; // hash string
		$hash = base64_encode(pack('H*',sha1($hashstr))); // hash value
	
		$html =<<<OEM
	
<html>
<head><title></title></head>
<body onload="forms.paymentform.submit()">
Пожалуйста, подождите, идет перенаправление в платежный терминал...
<form action="{$_url}" method="POST" name="paymentform" id="paymentform">
<input type="hidden" name="clientid" value="{$clientId}">
<input type="hidden" name="amount" value="{$amount}">
<input type="hidden" name="oid" value="{$oid}">
<input type="hidden" name="okUrl" value="{$okUrl}" >
<input type="hidden" name="failUrl" value="{$failUrl}" >
<input type="hidden" name="islemtipi" value="{$islemtipi}" >
<input type="hidden" name="taksit" value="{$taksit}">
<input type="hidden" name="rnd" value="{$rnd}" >
<input type="hidden" name="hash" value="{$hash}" >
<input type="hidden" name="storetype" value="3d_pay_hosting" >
<input type="hidden" name="refreshtime" value="0" >
<input type="hidden" name="lang" value="ru">
<input type="hidden" name="currency" value="643" />
<input type="hidden" name="encoding" value="utf-8" />
	
<input type="hidden" name="tel" value="012345678">
<input type="hidden" name="Email" value="test@test.com">
<input type="hidden" name="firmaadi" value="Billing Company"> <!-- Название компании-получателя счета -->
<input type="hidden" name="Faturafirma" value="John Smith"> <!-- Имя/фамилия получателя счета -->
<input type="hidden" name="Fadres" value="Address line 1"> <!-- Адрес -->
<input type="hidden" name="Fadres2" value="Address line 2"> <!-- Адрес -->
<input type="hidden" name="Filce" value="Warsaw"> <!-- Город получателя счета -->
<input type="hidden" name="Fil" value="mystate"> <!-- Штат/область получателя счета -->
<input type="hidden" name="Fpostakodu" value="12345"> <!-- Почтовый индекс получателя счета -->
<input type="hidden" name="Fulkekodu" value="400"> <!-- Код страны получателя счета -->
<input type="submit" value="Перейти прямо сейчас...">
</form>
<script type="text/javascript">
document.paymentform.submit();
</script>
</body>
</html>
	
OEM;
	
		echo $html;
	}
	
	/* ********************************************************************************************* */
	/* QIWI */
	function QIWI($login,$pass,$phone,$sum,$orderId,$comment){
		
		$qiwi_connect = SettingsmerchantsModel::get('qiwi_connect');
		if ($qiwi_connect == 'HTTP'){
			
			$db = Register::get('db');
			$db->post("
				INSERT INTO ".DB_PREFIX."settings_merchants_result 
					(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
				VALUES
					('QIWI','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($orderId)."','".mysql_real_escape_string($comment)."','','".mktime()."','0');
			");
			$comment = str_replace("-"," ",AliasViewHelper::doTraslit($comment));
			$phone = str_replace("+","",$phone);
			$phone = "+".str_replace(array(" ","-"),"",$phone);
			header("location: https://w.qiwi.com/order/external/create.action?txn_id=".$orderId."&from=".$login."&to=".$phone."&summ=".$sum."&com=".urlencode($comment)."&lifetime=48&check_agt=true&currency=RUB&successUrl=http://".$_SERVER['SERVER_NAME']."/cart/md5/key/".md5("o.".$orderId));
			exit();
		}
	}
	
	/* ********************************************************************************************* */
	/* ASSIST */
	private function ASSIST($_order_id=0,$_sum=0,$family='',$email=''){
		
	#https://test.paysecure.ru/pay/order.cfm
	#https://test.paysec.by/pay/order.cfm
	
	$assist_url = SettingsmerchantsModel::get('assist_url');
	$Merchant_ID = SettingsmerchantsModel::get('assist_Merchant_ID');
	$MONEY = SettingsmerchantsModel::get('assist_MONEY');
		
$html =<<<OEM

Пожалуйста, подождите, идет перенаправление в платежный терминал...
<form action="{$assist_url}" method="POST" id="assist_from">
<input type="hidden" name="Merchant_ID" value="{$Merchant_ID}">
<input type="hidden" name="OrderNumber" value="{$_order_id}">
<input type="hidden" name="OrderAmount" value="{$_sum}">
<input type="hidden" name="OrderCurrency" value="{$MONEY}">
<input type="hidden" name="FirstName" value="{$family}">
<input type="hidden" name="LastName" value="">
<input type="hidden" name="Email" value="{$email}">
<input type="hidden" name="OrderComment" value="Оплата товара в магазине {$_SERVER['SERVER_NAME']}">

<input type="hidden" name="TestMode" value="0">

<input type="hidden" name="YMPayment" value="1">
<input type="hidden" name="WMPayment" value="1">
<input type="hidden" name="QIWIPayment" value="1">
<input type="hidden" name="QIWIMtsPayment" value="1">
<input type="hidden" name="QIWIMegafonPayment" value="1">
<input type="hidden" name="QIWIBeelinePayment" value="1">

<input type="hidden" name="URL_RETURN" value="http://{$_SERVER['SERVER_NAME']}/">
<input type="hidden" name="URL_RETURN_OK" value="http://{$_SERVER['SERVER_NAME']}/cart/accept/">
<input type="hidden" name="URL_RETURN_NO" value="http://{$_SERVER['SERVER_NAME']}/cart/deny/">
<input type="submit" value="Перейти прямо сейчас...">
</form>
<script type="text/javascript">
document.assist_from.submit();
</script>

OEM;

	echo $html;
	}
	
	/* ********************************************************************************************* */
	/* IPAY */
	
	function testipay(){
		header("location: https://besmart.serveftp.net:4443/pls/ipay/!iSOU.Login?srv_no=815&pers_acc=98&amount=32420&amount_editable=N&provider_url=http://avtofakt.by/cart/accept/");
		exit();
	}
	
	private function IPAY($_name='',$_order_id=0,$_sum=0){
		$srv_no = SettingsmerchantsModel::get('ipay_srv_no');
		$provider_url = SettingsmerchantsModel::get('ipay_provider_url');
		$db = Register::get('db');
		$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result 
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('IPAY','".mysql_escape_string($_name)."','".mysql_escape_string($_sum)."','".mysql_escape_string($_order_id)."','','','".mktime()."','0');
		");
		return $_order_id;
		
		#var_dump("location: $provider_url?srv_no=".$srv_no."&pers_acc=".$_order_id."&amount=".$_sum."&amount_editable=N&provider_url=http://".$_SERVER['SERVER_NAME'].'/cart/accept/');
		#exit();
		#header("location: $provider_url?srv_no=".$srv_no."&pers_acc=".$_order_id."&amount=".$_sum."&amount_editable=N&provider_url=http://".$_SERVER['SERVER_NAME'].'/cart/accept/');
		//exit();
	}
	function ServiceInfo(){
		
		#ob_start();	
		
		$db = Register::get('db');
		
		$xml = ($_POST['XML']);
		$xml = preg_replace('/^.*\<\?xml/sim', '<?xml', $xml);
 		$xml = preg_replace('/\<\/ServiceProvider_Request\>.*/sim', '</ServiceProvider_Request>', $xml);
 		$xml = stripslashes($xml);
 		
		$arr = $this->xml2array($xml);
		$order = $arr['ServiceProvider_Request']['PersonalAccount'];

		$sql = "SELECT * FROM ".DB_PREFIX."settings_merchants_result WHERE merchant LIKE 'IPAY' AND orderid_bill = '".(int)$order."';";
		$res = $db->get($sql);
		$cartBill = $db->get("SELECT scSID FROM ".DB_PREFIX."cart_bills WHERE number = '".(int)$order."';");
		$cartItems = $db->query("SELECT * FROM ".DB_PREFIX."cart WHERE scSID = '".(int)$cartBill['scSID']."';");
		$infoLine = '';
		if (isset($cartItems) && count($cartItems)>0){
			foreach ($cartItems as $CI){
				$infoLine .= $CI['article'].' '.$CI['brand'].' 1x'.$CI['price'].'руб. / ';
			}
		}
		$infoLine = mb_substr($infoLine,0,1900);

		$_const = 'kTav5xc17sdrf<';
		$shop_sign = 'SALT+MD5: ';
		/** Далее идут проверки, после каждой проверки отправляем свою подпись и ответ xml*/
		$signature = '';
		if (preg_match('/SALT\+MD5\:\s(.*)/', $_SERVER['HTTP_SERVICEPROVIDER_SIGNATURE'], $matches)){
			$signature = $matches[1];
		}
		if (strcasecmp(md5($_const.$xml), $signature)){}

		//проверяем существует ли такой заказ
		if(isset($res['osum']) && $res['osum']){
			
			//оплачен ли заказ?
			if(isset($res['paid']) && $res['paid'] == '1'){

				$response = '<?xml version="1.0" encoding="windows-1251"?>
				<ServiceProvider_Response>
				<Error>
				<ErrorLine>'.iconv("utf-8","windows-1251",'Заказ # '.$order.' уже оплачен').'</ErrorLine>
				</Error>
				</ServiceProvider_Response>';

				$shop_sign .= md5($_const.$response);
				header("ServiceProvider-Signature: $shop_sign");
				header('Content-type: text/xml; charset=windows-1251');

				echo $response;
				exit;
				
			}else{
			
				//проверяем заказ на блокировку
				if(isset($res['blocked']) && $res['blocked'] == '1'){
					
					$response = '<?xml version="1.0" encoding="windows-1251" ?>
					<ServiceProvider_Response>
					<Error>
					<ErrorLine>'.iconv("utf-8","windows-1251",'Заказ # '.$order.' находится в процессе оплаты').'</ErrorLine>
					</Error>
					</ServiceProvider_Response>';

					$shop_sign .= md5($_const.$response);
					header("ServiceProvider-Signature: $shop_sign");
					header('Content-type: text/xml; charset=windows-1251');

					echo $response;
					exit;

				}else{

					$nameIO = explode(" ",$res['name']);
					$res['name'] = isset($nameIO[0])?$nameIO[0]:'no name';
					
					$response = '<?xml version="1.0" encoding="windows-1251"?>
					<ServiceProvider_Response>
					<ServiceInfo>
					<Amount>
					<Debt>'.$res['osum'].'</Debt>
					</Amount>
					<Name>
					<Surname>'.iconv("utf-8","windows-1251",$res['name']).'</Surname>
					</Name>
					<Info xml:space="preserve">
					<InfoLine>'.iconv("utf-8","windows-1251",'Оплата заказа # '.$order.'. '.$infoLine).'</InfoLine>
					</Info>
					</ServiceInfo>
					</ServiceProvider_Response>';

					$shop_sign .= md5($_const.$response);
					header("ServiceProvider-Signature: $shop_sign");
					header('Content-type: text/xml; charset=windows-1251');
					
					echo $response;
					exit;
				}
			} // !END! оплачен ли заказ?

		 // !END! проверяем существует ли такой заказ
		}else{
			
			$response = '<ServiceProvider_Response>
			<Error>
			<ErrorLine>'.iconv("utf-8","windows-1251",'Заказ # '.$order.' не существует, начните оплату заново на сайте '.$_SERVER['HTTP_HOST']).'</ErrorLine>
			</Error>
			</ServiceProvider_Response>';
			
			$shop_sign .= md5($_const.$response);
			header("ServiceProvider-Signature: $shop_sign");
			header('Content-type: text/xml; charset=windows-1251');
			
			echo $response;
			exit;   
		}
		
		exit();
	}
	function TransactionStart(){

		#ob_start();	
		
		$db = Register::get('db');
		
		$xml = ($_POST['XML']);
		$xml = preg_replace('/^.*\<\?xml/sim', '<?xml', $xml);
 		$xml = preg_replace('/\<\/ServiceProvider_Request\>.*/sim', '</ServiceProvider_Request>', $xml);
 		$xml = stripslashes($xml);
 		
		$arr = $this->xml2array($xml);
		$tran_id = $arr['ServiceProvider_Request']['TransactionStart']['TransactionId'];
		$amount_ipay = $arr['ServiceProvider_Request']['TransactionStart']['Amount'];
		$tran_serv = $arr['ServiceProvider_Request']['PersonalAccount'];
		$order = $tran_serv;
		
		$sql = "SELECT * FROM ".DB_PREFIX."settings_merchants_result WHERE merchant LIKE 'IPAY' AND orderid_bill = '".(int)$order."';";
		$res = $db->get($sql);

		$_const = 'kTav5xc17sdrf<';
		$shop_sign = 'SALT+MD5: ';
		/** Далее идут проверки, после каждой проверки отправляем свою подпись и ответ xml*/
		$signature = '';
		if (preg_match('/SALT\+MD5\:\s(.*)/', $_SERVER['HTTP_SERVICEPROVIDER_SIGNATURE'], $matches)){
			$signature = $matches[1];
		}
		if (strcasecmp(md5($_const.$xml), $signature)){}
		
		#var_dump($xml);
		#var_dump($res);
		#$out1 = ob_get_contents();
		#ob_end_clean();
		#file_put_contents("./test/test-".date("d-m-Y-H:i:s").".csv",$out1);

		if(isset($res['osum']) && $res['osum']){
			
			if(isset($res['paid']) && $res['paid'] == '1'){
				
				$response = '<?xml version="1.0" encoding="windows-1251" ?>
				<ServiceProvider_Response>
				<Error>
				<ErrorLine>'.iconv("utf-8","windows-1251",'Заказ # '.$order.' уже оплачен').'</ErrorLine>
				</Error>
				</ServiceProvider_Response>';
				
				$shop_sign .= md5($_const.$response);
				header("ServiceProvider-Signature: $shop_sign");
				header('Content-type: text/xml; charset=windows-1251');
				
				echo $response;
				//exit;
				
			}else{
				
				if(isset($res['blocked']) && $res['blocked'] == '1'){
					
					$response = '<?xml version="1.0" encoding="windows-1251" ?>
					<ServiceProvider_Response>
					<Error>
					<ErrorLine>'.iconv("utf-8","windows-1251",'Заказ # '.$order.' находится в процессе оплаты').'</ErrorLine>
					</Error>
					</ServiceProvider_Response>';
					
					$shop_sign .= md5($_const.$response);
					header("ServiceProvider-Signature: $shop_sign");
					header('Content-type: text/xml; charset=windows-1251');
					
					echo $response;
					//exit;
					
				// если предыдущие проверки прошли, обновляем базу и блокируем заказ, ставим `blocked` = 1
				}else{ 

					$str = "UPDATE ".DB_PREFIX."settings_merchants_result SET `blocked`='1', `status`='".mysql_escape_string($tran_id)."' WHERE `merchant` LIKE 'IPAY' AND `orderid_bill` = '".(int)$order."' ";
					$db->query($str);

					$response = '<?xml version="1.0" encoding="windows-1251" ?>
					<ServiceProvider_Response>
					<TransactionStart>
					<ServiceProvider_TrxId>'.$tran_serv.'</ServiceProvider_TrxId>
					<Info xml:space="preserve">
						<InfoLine></InfoLine>
					</Info>
					</TransactionStart>
					</ServiceProvider_Response>';
					
					$shop_sign .= md5($_const.$response);
					header("ServiceProvider-Signature: $shop_sign");
					header('Content-type: text/xml; charset=windows-1251');
					
					echo $response;
					//exit;
				}
			}
			
		}else{
			$response = '<ServiceProvider_Response>
			<Error>
			<ErrorLine>'.iconv("utf-8","windows-1251",'Заказ # '.$order.' не существует, начните оплату заново на сайте '.$_SERVER['HTTP_HOST']).'</ErrorLine>
			</Error>
			</ServiceProvider_Response>';
			
			$shop_sign .= md5($_const.$response);
			header("ServiceProvider-Signature: $shop_sign");
			header('Content-type: text/xml; charset=windows-1251');
			
			echo $response;
			//exit;
		}
		
		exit();
	}
	function TransactionResult(){
		
		#ob_start();
		
		$db = Register::get('db');
		
		$xml = ($_POST['XML']);
		$xml = preg_replace('/^.*\<\?xml/sim', '<?xml', $xml);
 		$xml = preg_replace('/\<\/ServiceProvider_Request\>.*/sim', '</ServiceProvider_Request>', $xml);
 		$xml = stripslashes($xml);

		$arr = $this->xml2array($xml);
		$tran_id = $arr['ServiceProvider_Request']['TransactionResult']['TransactionId'];
		$tran_serv = $arr['ServiceProvider_Request']['TransactionResult']['ServiceProvider_TrxId'];
		$error = $arr['ServiceProvider_Request']['TransactionResult']['ErrorText'];
		$pers_acc = $arr['ServiceProvider_Request']['PersonalAccount'];
		
		$_const = 'kTav5xc17sdrf<';
		$shop_sign = 'SALT+MD5: ';
		/** Далее идут проверки, после каждой проверки отправляем свою подпись и ответ xml*/
		$signature = '';
		if (preg_match('/SALT\+MD5\:\s(.*)/', $_SERVER['HTTP_SERVICEPROVIDER_SIGNATURE'], $matches)){
			$signature = $matches[1];
		}
		if (strcasecmp(md5($_const.$xml), $signature)){}
		
		#var_dump($xml);
		#$out1 = ob_get_contents();
		#ob_end_clean();
		#file_put_contents("./test/test-".date("d-m-Y-H:i:s").".csv",$out1);

		// проверяем была ли ошибка
		if(empty($error['value'])){

			$order = $pers_acc;
			
			//если ошибки не было ставим `blocked` = 0, а `paid` = 1
			$sql = "UPDATE ".DB_PREFIX."settings_merchants_result SET `blocked`='0',`paid`='1',`check_dt`='".mktime()."' WHERE `merchant` LIKE 'IPAY' AND `orderid_bill` = '".(int)$order."' ";
			$db->post($sql);

			$response = '<?xml version="1.0" encoding="windows-1251" ?>
			<ServiceProvider_Response>
			<TransactionResult>
			<Info xml:space="preserve">
			<InfoLine>'.iconv("utf-8","windows-1251",'Спасибо за покупку!').'</InfoLine>
			</Info>
			</TransactionResult>                
			</ServiceProvider_Response>';
			
			$shop_sign .= md5($_const.$response);
			header("ServiceProvider-Signature: $shop_sign");
			header('Content-type: text/xml; charset=windows-1251');
			
			echo $response;
			//exit;   

		}else{
			
			// если была ошибка оплаты, снимаем блокировку с заказа
			$order = $pers_acc;
			$sql = "UPDATE ".DB_PREFIX."settings_merchants_result SET `blocked`='0',`paid`='0' WHERE `merchant` LIKE 'IPAY' AND `orderid_bill` = '".(int)$order."' ";
			$db->post($sql);

			$response = '<?xml version="1.0" encoding="windows-1251" ?>
			<ServiceProvider_Response>
			<TransactionResult>
			<Info xml:space="preserve">
			<InfoLine>'.iconv("utf-8","windows-1251",'Операция отменена').'</InfoLine>
			</Info>              
			</TransactionResult>
			</ServiceProvider_Response>';
			
			$shop_sign .= md5($_const.$response);
			header("ServiceProvider-Signature: $shop_sign");
			header('Content-type: text/xml; charset=windows-1251');
			
			echo $response;
			//exit;   
		}
		
		exit();
	}
		
	function xml2array($contents, $get_attributes=1, $priority = 'tag') {
	    if(!$contents) return array();
	
	    if(!function_exists('xml_parser_create')) {
	        //print "'xml_parser_create()' function not found!";
	        return array();
	    }
	
	    //Get the XML parser of PHP - PHP must have this module for the parser to work
	    $parser = xml_parser_create('');
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	    xml_parse_into_struct($parser, trim($contents), $xml_values);
	    xml_parser_free($parser);
	
	    if(!$xml_values) return;//Hmm...
	
	    //Initializations
	    $xml_array = array();
	    $parents = array();
	    $opened_tags = array();
	    $arr = array();
	
	    $current = &$xml_array; //Refference
	
	    //Go through the tags.
	    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
	    foreach($xml_values as $data) {
	        unset($attributes,$value);//Remove existing values, or there will be trouble
	
	        //This command will extract these variables into the foreach scope
	        // tag(string), type(string), level(int), attributes(array).
	        extract($data);//We could use the array by itself, but this cooler.
	
	        $result = array();
	        $attributes_data = array();
	        
	        if(isset($value)) {
	            if($priority == 'tag') $result = $value;
	            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
	        }
	
	        //Set the attributes too.
	        if(isset($attributes) and $get_attributes) {
	            foreach($attributes as $attr => $val) {
	                if($priority == 'tag') $attributes_data[$attr] = $val;
	                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
	            }
	        }
	
	        //See tag status and do the needed.
	        if($type == "open") {//The starting of the tag '<tag>'
	            $parent[$level-1] = &$current;
	            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
	                $current[$tag] = $result;
	                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
	                $repeated_tag_index[$tag.'_'.$level] = 1;
	
	                $current = &$current[$tag];
	
	            } else { //There was another element with the same tag name
	
	                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
	                    $repeated_tag_index[$tag.'_'.$level]++;
	                } else {//This section will make the value an array if multiple tags with the same name appear together
	                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
	                    $repeated_tag_index[$tag.'_'.$level] = 2;
	                    
	                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
	                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
	                        unset($current[$tag.'_attr']);
	                    }
	
	                }
	                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
	                $current = &$current[$tag][$last_item_index];
	            }
	
	        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
	            //See if the key is already taken.
	            if(!isset($current[$tag])) { //New Key
	                $current[$tag] = $result;
	                $repeated_tag_index[$tag.'_'.$level] = 1;
	                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;
	
	            } else { //If taken, put all things inside a list(array)
	                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...
	
	                    // ...push the new element into that array.
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
	                    
	                    if($priority == 'tag' and $get_attributes and $attributes_data) {
	                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
	                    }
	                    $repeated_tag_index[$tag.'_'.$level]++;
	
	                } else { //If it is not an array...
	                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
	                    $repeated_tag_index[$tag.'_'.$level] = 1;
	                    if($priority == 'tag' and $get_attributes) {
	                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
	                            
	                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
	                            unset($current[$tag.'_attr']);
	                        }
	                        
	                        if($attributes_data) {
	                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
	                        }
	                    }
	                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
	                }
	            }
	
	        } elseif($type == 'close') { //End of tag '</tag>'
	            $current = &$parent[$level-1];
	        }
	    }
	    
	    return($xml_array);
	}
	
	/* IPAY END ******************************************************************************* */
	
	private function WEBPAY($data=array(),$counter=0,$sum=0,$cart=array()){
		
		$url = SettingsmerchantsModel::get('webpay_url');
		$Merchant_ID = SettingsmerchantsModel::get('webpay_merchant_id');
		
		$dt = mktime();
		$name = addslashes($data['name']);
		$phone = addslashes($data['phone']);
		$city = addslashes($data['city']);
		$email = addslashes($data['email']);
		$comment = addslashes($data['message']);
		
		$wsb_seed = $dt;
		$wsb_storeid = $Merchant_ID;
		$wsb_order_num = $counter;
		$wsb_test = 1;
		$wsb_currency_id = "BYR";
		$wsb_total = $sum;
		$SecretKey = "181429826";
		$wsb_signature = sha1($wsb_seed.$wsb_storeid.$wsb_order_num.$wsb_test.$wsb_currency_id.$wsb_total.$SecretKey);
		
		$db = Register::get('db');
		$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result 
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('WEBPAY','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($counter)."','".mysql_real_escape_string($comment)."','','".$dt."','0');
		");
		
		$html = '';
		$html .= 'Пожалуйста, подождите, идет перенаправление в платежный терминал...';
		$html .= '<form action="'.$url.'" method="POST" id="sandbox_from">';
		$html .= '<input type="hidden" name="*scart">';
		$html .= '<input type="hidden" name="wsb_version" value="2">';
		$html .= '<input type="hidden" name="wsb_language_id" value="russian">';
		$html .= '<input type="hidden" name="wsb_storeid" value="'.$wsb_storeid.'">';
		$html .= '<input type="hidden" name="wsb_store" value="'.$_SERVER['SERVER_NAME'].'">';
		$html .= '<input type="hidden" name="wsb_order_num" value="'.$counter.'">';
		$html .= '<input type="hidden" name="wsb_test" value="'.$wsb_test.'">';
		$html .= '<input type="hidden" name="wsb_currency_id" value="'.$wsb_currency_id.'">';
		$html .= '<input type="hidden" name="wsb_seed" value="'.$dt.'">';
		$html .= '<input type="hidden" name="wsb_return_url" value="http://'.$_SERVER['SERVER_NAME'].'/cart/accept/">';
		$html .= '<input type="hidden" name="wsb_cancel_return_url" value="http://'.$_SERVER['SERVER_NAME'].'/cart/deny/">';
		$html .= '<input type="hidden" name="wsb_notify_url" value="http://'.$_SERVER['SERVER_NAME'].'/">';
		$html .= '<input type="hidden" name="wsb_email" value="'.$email.'" id="wsb_email">';
		$html .= '<input type="hidden" name="wsb_phone" value="'.$phone.'" id="wsb_phone">';
		
		if (isset($cart) && count($cart)>0){
			foreach ($cart as $dd){
			$html .= '<input type="hidden" name="wsb_invoice_item_name[]" value="'.$dd['name'].' '.$dd['brand'].' '.$dd['descr'].'">';
			$html .= '<input type="hidden" name="wsb_invoice_item_quantity[]" value="'.$dd['cc'].'">';
			$html .= '<input type="hidden" name="wsb_invoice_item_price[]" value="'.($dd['price']).'">';
			}
		}
		
		$html .= '<input type="hidden" name="wsb_total" value="'.$sum.'">';
		$html .= '<input type="hidden" name="wsb_signature" value="'.$wsb_signature.'">';
		$html .= '<input type="submit" value="Перейти прямо сейчас...">';
		$html .= '</form>';
		$html .= '<script type="text/javascript">';
		$html .= 'document.sandbox_from.submit();';
		$html .= '</script>';

		echo $html;
	}
	
	/* WEBPAY END ******************************************************************************* */
	
	private function DENGIONLINE($data=array(),$counter=0,$sum=0,$cart=array()){
		
		$Merchant_ID = SettingsmerchantsModel::get('dengionline_merchant_id');
		
		$dt = mktime();
		$name = addslashes($data['name']);
		$phone = addslashes($data['phone']);
		$city = addslashes($data['city']);
		$email = addslashes($data['email']);
		$comment = addslashes($data['message']);
		
		$db = Register::get('db');
		$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result 
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('DENGIONLINE','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($counter)."','".mysql_real_escape_string($comment)."','','".$dt."','0');
		");
		
		header("location: https://paymentgateway.ru/?project=".$Merchant_ID."&source=".$Merchant_ID."&order_id=".$counter."&amount=".$sum."&nickname=".urldecode($name)."&nick_extra=".urldecode($phone.' '.$city.' '.$email.' '.$comment));
		exit();
	}
	
	private function PAYPAL($data=array(),$counter=0,$sum=0,$cart=array())
	{	
		// print("<pre>");
		// var_dump($data);
		// var_dump($cart);
		// $cart_counter = SettingsModel::get('cartcounter');
		// var_dump($cart_counter);die();
		$this->layout = "ajax";
		$sandbox = SettingsmerchantsModel::get('paypal_mode');
		$pp_email = SettingsmerchantsModel::get('paypal_merchant_id');
		$prod = array();
			
		if ($sandbox=='1') {
			$path = "sandbox.paypal";
		} else {
			$path = "paypal";
		}
		
		
		$dt = time();
		$name = addslashes($data['name']);
		$this->view->name = $name;
		$nachname = addslashes($data['nachname']);
		$this->view->nachname = $nachname;
		$phone = addslashes($data['phone']);
		$this->view->phone = $phone;
		// $this->view->invoice = $cart_counter;
		$this->view->invoice = $counter . ' - ' . html_entity_decode($name, ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($nachname, ENT_QUOTES, 'UTF-8');
		$this->view->custom = $counter;
		// $this->view->invoice = $cart_counter;
		$hausnummer = addslashes($data['hausnummer']);
		$address = addslashes($data['address']);
		$this->view->address = $address." ".$hausnummer;
		$zip = addslashes($data['zip']);
		$this->view->zip = $zip;
		$email = addslashes($data['email']);
		$this->view->email = $email;
		$comment = addslashes($data['message']);
		$this->view->comment = $comment;
		$city = addslashes($data['city']);
		$city = Dic_citiesModel::getById($city);
		$city = $city['name'];
		$this->view->city = $city;	
		
		$db = Register::get('db');
		$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result 
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('PAYPAL','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($counter)."','".mysql_real_escape_string($comment)."','','".$dt."','0');
		");
		$cur_lang = $_SESSION["setLang"];
		if (isset($cart) && count($cart)>0){
			foreach ($cart as $dd){
				$prod[] = array(
					'name'     => htmlspecialchars($dd['supplier_name'].' '.$dd['name_'.$cur_lang]),
					'model'    => htmlspecialchars($dd['art_nr']),
					'price'    => $dd['price']." EUR",
					'quantity' => $dd['cc']
				);
			} 
		}
		
		
		
		$this->view->laccount = $pp_email;
		$this->view->path = $path;
		
		$delivery = DeliveriesModel::getById($data['current_delivery']);
		$delivery_price = $delivery['price'];
		$sum = $sum + $delivery_price;
		
		if ($delivery_price!='0')
		{
			$prod[] = array(
					'name'     => htmlspecialchars($delivery['name']),
					'model'    => "",
					'price'    => $delivery_price." EUR",
					'quantity' => ""
				);
		}
		
		$this->view->total = $sum; 
		$this->view->shipping = $delivery_price;
		$this->view->currency = "EUR";
		$this->view->language = "de_DE";
		$this->view->prod = $prod;
		$this->view->cancel_return = "http://www.autoresurs.de/cart/deny/";
		$this->view->notify_url = "http://www.autoresurs.de/notify/paypal/";
		$this->view->returnl = "http://www.autoresurs.de/cart/accept/";
		
		$this->render("cart/merchants/paypal");
		die();
	}
	
	private function SOFORT($data=array(),$counter=0,$sum=0,$cart=array())
	{
		$this->layout = "ajax";
		$user_id = SettingsmerchantsModel::get('sofort_user_id');
		$this->view->user_id = $user_id;
		$project_id = SettingsmerchantsModel::get('sofort_project_id');
		$this->view->project_id = $project_id;
		$project_passwd = SettingsmerchantsModel::get('sofort_project_passwd');
		
		
		$dt = time();
		$name = addslashes($data['name']);
		$this->view->name = $name;
		$nachname = addslashes($data['nachname']);
		$this->view->nachname = $nachname;
		$phone = addslashes($data['phone']);
		$this->view->phone = $phone;
		$this->view->invoice = $counter;
		$hausnummer = addslashes($data['hausnummer']);
		$address = addslashes($data['address']);
		$this->view->address = $address." ".$hausnummer;
		$zip = addslashes($data['zip']);
		$this->view->zip = $zip;
		$email = addslashes($data['email']);
		$this->view->email = $email;
		$comment = addslashes($data['message']);
		$this->view->comment = $comment;
		$city = addslashes($data['city']);
		$city = Dic_citiesModel::getById($city);
		$city = $city['name'];
		$this->view->city = $city;	
		
		$db = Register::get('db');
		$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result 
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('SOFORT','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($counter)."','".mysql_real_escape_string($comment)."','','".$dt."','0');
		");
		
		$hash = $user_id."|".$project_id."|||||".$sum."|EUR|".$counter."||".$counter."|AutoResurs|||||".$project_passwd;
		echo $user_id."|".$project_id."|||||".$sum."|EUR|".$counter."||".$counter."|AutoResurs|||||".$project_passwd;
		// $hash = $data['cnr']."|".$data['projectid']."|||||".$order_info['total']."|".$data['currency_code']."|".$data['msg_1']."|".$data['msg_2']."|".$order_info['order_id']."|OpenCart|||||".$data['configkey'];
			$hash = sha1($hash);
		
		$this->view->hasha = $hash;
		$this->view->total = $sum;
		$this->view->currency = "EUR";
		$this->view->language = "de_DE";
		$this->view->cancel_return = "http://www.autoresurs.de/cart/deny/";
		$this->view->notify_url = "http://autoresurs.de/notify/sofort/";
		$this->view->returnl = "http://www.autoresurs.de/cart/accept/";
		
		$this->render("cart/merchants/sofort");
		die();
	}
	
	/* DENGIONLINE END ******************************************************************************* */
	
	/* ROBOKASSA *//* ************************************************************************************* */
	function robokassa(){
		$bill = $this->request("bill",false);
		
		if (!$bill){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			$controller = new Dispatcher();
			$controller->process('/error404');
			exit();
		}
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."settings_merchants_result WHERE orderid_bill = '".(int)$bill."' AND merchant = 'ROBOKASSA';";
		$orderData = $db->get($sql);
		$this->view->bill = $orderData;
		
		$inv_id = $orderData['orderid_bill'];
		$mrh_login = SettingsmerchantsModel::get('robokassa_login');
		$mrh_pass1 = SettingsmerchantsModel::get('robokassa_password1');
		$comment = $orderData['comment'];
		$inv_desc = urlencode($comment);
		$out_summ = $orderData['osum'];
		$shp_item = 1;
		$culture = "ru";
		$encoding = "utf-8";
		$crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1:shpItem=$shp_item");
		
		$this->view->mrh_login = $mrh_login;
		$this->view->out_summ = $out_summ;
		$this->view->inv_id = $inv_id;
		$this->view->inv_desc = $inv_desc;
		$this->view->crc = $crc;
		$this->view->shp_item = $shp_item;
		$this->view->culture = $culture;
		$this->view->encoding = $encoding;
		
		
		$this->breadcrumbs ['Корзина']= '/cart/';
		$this->breadcrumbs ['Оплата']= '/cart/';
		
		$this->render("cart/merchants/robokassa");
	}
	
	/* ************************************************************************************* */
	private function MONEXY($data=array(),$counter=0,$sum=0,$cart=array()){
		
		$dt = mktime();
		$name = addslashes($data['name']);
		$phone = addslashes($data['phone']);
		$city = addslashes($data['city']);
		$email = addslashes($data['email']);
		$comment = "Заказ №".$counter.". Оплата товаров в интернет магазине ".$_SERVER['SERVER_NAME'].".";
		
		$db = Register::get('db');
		$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result 
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('MONEXY','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($counter)."','".mysql_real_escape_string($comment)."','','".$dt."','0');
		");
		
		
		$MonexyPassword = SettingsmerchantsModel::get('MonexyPassword');
		$MonexyMerchantID = SettingsmerchantsModel::get('MonexyMerchantID');
		
		$MPASSWORD = $MonexyPassword;
		$params = array();
		$params["myMonexyMerchantCurrency"] = "UAH";
		$params["myMonexyMerchantExpTime"] = 140;
		$params["myMonexyMerchantID"] = $MonexyMerchantID;
		$params["myMonexyMerchantShopName"] = $_SERVER['SERVER_NAME'];
		$params["myMonexyMerchantOrderDesc"] = $comment;
		$params["myMonexyMerchantSum"] = $sum;
		ksort($params);
		$req_str = '';
		foreach($params AS $pkey => $pval) $req_str.=($pkey.'='.$pval);
		$params['myMonexyMerchantHash'] = md5($req_str.$MPASSWORD);
		
		$html = '';
		$html .= 'Пожалуйста, подождите, идет перенаправление в платежный терминал...';
		$html .= '<form method="POST" action="https://www.monexy.ua/merchant/merchant.php" name="submit_sandbox">';
		$html .= '<input type="hidden" name="myMonexyMerchantCurrency" value="'.$params["myMonexyMerchantCurrency"].'">';
		$html .= '<input type="hidden" name="myMonexyMerchantExpTime" value="'.$params["myMonexyMerchantExpTime"].'">';
		$html .= '<input type="hidden" name="myMonexyMerchantID" value="'.$params["myMonexyMerchantID"].'">';
		$html .= '<input type="hidden" name="myMonexyMerchantOrderDesc" value="'.$params["myMonexyMerchantOrderDesc"].'">';
		$html .= '<input type="hidden" name="myMonexyMerchantShopName" value="'.$params["myMonexyMerchantShopName"].'">';
		$html .= '<input type="hidden" name="myMonexyMerchantSum" value="'.$params["myMonexyMerchantSum"].'">';
		$html .= '<input type="hidden" name="myMonexyMerchantHash" value="'.$params['myMonexyMerchantHash'].'">';
		$html .= '<input type="hidden" name="myMonexyPaymentSimple" value="0">';
		$html .= '<input type="submit" value="Перейти прямо сейчас...">';
		$html .= '</form>';
		echo $html;
		echo('<script type="text/javascript">document.submit_sandbox.submit();</script>');
	}
	
	/* ************************************************************************************* */
	private function alfa($amount, $orderNumber, $data){
		$dt = mktime();
		$phone = addslashes($data['phone']);
		$rParams = array(
            'amount' => $amount*100,
            'returnUrl' => 'http://'.$_SERVER['HTTP_HOST'].'/cart/md5/key/'.md5("o.".$orderNumber),
            'orderNumber' => $orderNumber,
			'description' => '',
        );
        $result = $this->getAlfa('register.do', $rParams);
        
        //if($result->formUrl){
        	$db = Register::get('db');
			$db->post("
				INSERT INTO ".DB_PREFIX."settings_merchants_result 
					(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
				VALUES
					('alfa','".mysql_escape_string($phone)."','".mysql_escape_string($amount)."','".mysql_escape_string($result->orderId)."','".mysql_escape_string($result->orderId)."','','".$dt."','0');
			");
			$this->redirectUrl($result->formUrl);
        //}
	}

	private function getAlfa($method= null, $paramsArray = null){
        if($paramsArray && $method){
			$login = SettingsmerchantsModel::get('alfa_login');
			$password = SettingsmerchantsModel::get('alfa_password');
			$url = SettingsmerchantsModel::get('alfa_url');
            $params =null;
            foreach($paramsArray as $key => $value){
                $params .= '&'.$key.'='.$value;
            }
            $url = $url.$method.'?userName='.$login.'&password='.$password.$params;
            $result = file_get_contents($url);
            $result = json_decode($result);
        } else {
            $result = null;
        }
        return $result;
    }
    
    /* ************************************************************************************* */
    public function yamoneytransactinfo(){
    	$shopId = '16021';
    	$invoiceId = $_REQUEST['invoiceId'];
    	$datetime = new DateTime();
    	$performedDatetime = $datetime->format('c');
    	echo '
		<?xml version="1.0" encoding="UTF-8"?>
		<checkOrderResponse performedDatetime="'.$performedDatetime.'" code="0" invoiceId="'.$invoiceId.'" shopId="'.$shopId.'"/>
		';
    	exit;
    }
    
    public function yamoney(){
    	$shopId = '16021';
    	$invoiceId = $_REQUEST['invoiceId'];
    	$performedDatetime = $_REQUEST['performedDatetime'] ;
    	echo '
		<?xml version="1.0" encoding="UTF-8"?>
		<paymentAvisoResponse performedDatetime ="'.$performedDatetime.'" code="0" invoiceId="'.$invoiceId.'" shopId="'.$shopId.'"/>
		';
    	exit;
    }
    
    public function yandexPC($data=array(),$counter=0,$sum=0,$cart=array()){
    	
    	$dt = mktime();
    	$name = addslashes($data['name']);
    	$phone = addslashes($data['phone']);
    	$city = addslashes($data['city']);
    	$email = addslashes($data['email']);
    	$comment = "Заказ №".$counter.". Оплата в интернет магазине ".$_SERVER['SERVER_NAME'].".";
    	
    	$db = Register::get('db');
    	$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('YANDEXPC','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($counter)."','".mysql_real_escape_string($comment)."','','".$dt."','0');
		");
    	
    	$p0 = SettingsmerchantsModel::get('yandexpc_urldirect');
    	$p1 = SettingsmerchantsModel::get('yandexpc_scsid');
    	$p2 = SettingsmerchantsModel::get('yandexpc_shopid');
    	
    	echo 'Пожалуйста, подождите, идет перенаправление в платежный терминал...';
    	echo '<form class="ya_form" method="POST" action="'.$p0.'" id="submit_sandbox">';
		echo '<input type="hidden" name="scid" value="'.$p1.'">';
		echo '<input type="hidden" name="ShopID" value="'.$p2.'">';
		echo '<input type="hidden" name="Sum" value="'.$sum.'">';
		echo '<input type="hidden" name="CustomerNumber" value="'.$_SERVER['SERVER_NAME'].' / Заказ №'.$counter.'">';
		echo '<input type="hidden" name="orderNumber" value="'.$counter.'">';
		echo '<input type="hidden" name="paymentType" value="PC">';
		echo '<input type="hidden" name="shopSuccessURL" value="http://'.$_SERVER['SERVER_NAME'].'/cart/md5/key/'.md5("o.".$counter).'">';
		echo '<input type="hidden" name="shopFailURL" value="http://'.$_SERVER['SERVER_NAME'].'/cart/deny/">';
		echo '<input type="submit" value="Перейти прямо сейчас...">';
		echo '</form>';
		echo('<script type="text/javascript">document.submit_sandbox.submit();</script>');
    }
    
    public function yandexAC($data=array(),$counter=0,$sum=0,$cart=array()){
    	 
    	$dt = mktime();
    	$name = addslashes($data['name']);
    	$phone = addslashes($data['phone']);
    	$city = addslashes($data['city']);
    	$email = addslashes($data['email']);
    	$comment = "Заказ №".$counter.". Оплата в интернет магазине ".$_SERVER['SERVER_NAME'].".";
    	 
    	$db = Register::get('db');
    	$db->post("
			INSERT INTO ".DB_PREFIX."settings_merchants_result
				(`merchant`,`name`,`osum`,`orderid_bill`,`comment`,`status`,`create_dt`,`check_dt`)
			VALUES
				('YANDEXAC','".mysql_real_escape_string($phone)."','".mysql_real_escape_string($sum)."','".mysql_real_escape_string($counter)."','".mysql_real_escape_string($comment)."','','".$dt."','0');
		");
    	 
    	$p0 = SettingsmerchantsModel::get('yandexac_urldirect');
    	$p1 = SettingsmerchantsModel::get('yandexac_scsid');
    	$p2 = SettingsmerchantsModel::get('yandexac_shopid');
    	 
    	echo 'Пожалуйста, подождите, идет перенаправление в платежный терминал...';
    	echo '<form class="ya_form" method="POST" action="'.$p0.'" id="submit_sandbox">';
    	echo '<input type="hidden" name="scid" value="'.$p1.'">';
    	echo '<input type="hidden" name="ShopID" value="'.$p2.'">';
    	echo '<input type="hidden" name="Sum" value="'.$sum.'">';
    	echo '<input type="hidden" name="CustomerNumber" value="'.$_SERVER['SERVER_NAME'].' / Заказ №'.$counter.'">';
    	echo '<input type="hidden" name="orderNumber" value="'.$counter.'">';
    	echo '<input type="hidden" name="paymentType" value="AC">';
    	echo '<input type="hidden" name="shopSuccessURL" value="http://'.$_SERVER['SERVER_NAME'].'/cart/md5/key/'.md5("o.".$counter).'">';
    	echo '<input type="hidden" name="shopFailURL" value="http://'.$_SERVER['SERVER_NAME'].'/cart/deny/">';
    	echo '<input type="submit" value="Перейти прямо сейчас...">';
    	echo '</form>';
    	echo('<script type="text/javascript">document.submit_sandbox.submit();</script>');
    }
}
?>