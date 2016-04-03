<?php
class IndexController  extends CmsGenerator {
	
	public $layout = 'global';
	
	function index() {
		
		$this->indexOrders();
		$this->getDataAboutOffice();
		$this->getTempOrders();
		$this->params_templates();
		$this->getTestimonialsForCheck();
		$this->getStatisticsData();
		$this->getLogs();
		$this->uncached();
		
		if ($this->isManager){
			$this->render("index/manager");
		}
	}
	
	function uncached(){
		$files = glob("../cache/pages/*.cache");
		if (isset($files) && count($files)>0){
			foreach ($files as $file){
				@unlink($file);
			}
		}
	}
	
	function ajaxGA(){
		$this->layout = "ajax";
		$this->view->statGA = $this->statGA();
		$this->render("index/ga_ajax");
	}
	function statGA(){
		$u = SettingsModel::get('ga_u');
		$p = SettingsModel::get('ga_p');
		$id = SettingsModel::get('ga_id');
		if ($u && $p && $id){
			return StatgaExtension::stat($u,$p,$id);
		}
	}
	
	function parseBrands(){}
	function parseMarks(){}
	
	function details_loader() {
		$uploadPath = "../".PRICE_PATH."/";
		move_uploaded_file($_FILES['csv_file']['tmp_name'], $uploadPath . iconv('utf-8','windows-1251',$_FILES['csv_file']['name']));
		$this->redirectUrl('/staffcp/harvesterclaas/#tab-2');
	}
	
	function getTempOrders(){
		$db = Register::get('db');
		$sql = "SELECT id,save_dt,notice,name,phone FROM ".DB_PREFIX."cart_bills_temp ORDER BY save_dt DESC";
		$this->view->tempOrders = $db->query($sql);
	}
	
	/* *************************************************************** */
	
	function getLogs(){
		$db = Register::get('db');
		if (isset($_REQUEST['deletelogs'])){
			$this->deleteLogs();
		}
		
		$log = 5;
		$this->view->limitLog = $log;
		$sql = "SELECT * FROM ".DB_PREFIX."logs ORDER BY dt DESC LIMIT 0,".$log.";";
		$this->view->logsList = $db->query($sql);
	}
	function deleteLogs(){
		$db = Register::get('db');
		$db->post("TRUNCATE TABLE ".DB_PREFIX."logs;");
		$this->redirectUrl("/staffcp/");
	}
	
	function getTestimonialsForCheck(){
		
		$db = Register::get('db');
		
		$sql = "SELECT id,name,dt,message,raiting FROM ".DB_PREFIX."testimonials WHERE is_active = 0 LIMIT 0,5;";
		$this->view->check_testimonials = $db->query($sql);
		
		$sql = "SELECT id,dt,message,vin FROM ".DB_PREFIX."vin WHERE isset = 0 LIMIT 0,5;";
		$this->view->check_vins = $db->query($sql);

		$sql = "SELECT id,dt,question FROM ".DB_PREFIX."faq WHERE isset = 0 LIMIT 0,5;";
		$this->view->check_faqs = $db->query($sql);
	}
	
	function getStatisticsData(){

		$this->view->cc_ORDERS = $this->getQueryCcFronTable('cart_bills',"WHERE dt BETWEEN (".mktime(0,0,0,date("m"),date("d"),date("Y")).") AND (".mktime(24,0,0,date("m"),date("d"),date("Y")).")");
		$this->view->no_zero_product = $this->getQueryCcFronTable('products',"WHERE price > 0");
		$this->view->zero_product = $this->getQueryCcFronTable('products',"WHERE price = 0");
		
		$this->view->cc_importers = $this->getQueryCcFronTable('importers');
		// $this->view->cc_db_prices = $this->getQueryCcFronTable('details');
		// $this->view->cc_wbs = $this->getQueryCcFronTable('wbs',"WHERE is_active=1");
		
		$this->view->cc_products = $this->getQueryCcFronTable('products',"");
		// $this->view->cc_products = $this->getQueryCcFronTable('products',"WHERE is_body_module IN (0,".INSTALL_BODY_MODULE.")");
		
		// $this->view->cc_news = $this->getQueryCcFronTable('news');
		// $this->view->cc_articles = $this->getQueryCcFronTable('articles');
		
		$this->view->cc_accounts = $this->getQueryCcFronTable('accounts');
		$this->view->cc_accounts_this_month = $this->getQueryCcFronTable('accounts',"WHERE dt BETWEEN (".mktime(0,0,0,date("m"),1,date("Y")).") AND (".mktime(0,0,0,date("m")+1,0,date("Y")).")");
	}
	
	private function getQueryCcFronTable($table=false,$where='',$debug=false){
		if ($table) {
			$db = Register::get('db');
			$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX.$table." ".$where.";";
			$res = $db->get($sql);
			if($debug){
				echo('<pre>');
				var_dump($sql);
				echo('</pre>');
			}
			return $res['cc'];
		}
		return 0;
	}
	
	/* *************************************************************** */
	function disable(){
		$db = Register::get('db');
		if (isset($_REQUEST['cats'])) {
			$sql = "UPDATE `".DB_PREFIX."cat` SET `is_active`='0';";
			$db->post($sql);
		}
		if (isset($_REQUEST['products'])) {
			$sql = "UPDATE `".DB_PREFIX."products` SET `set_index`='0',`set_isset`='0';";
			$db->post($sql);
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Отключение данных раздела магазин',URL_NOW);
		$this->redirectUrl('/staffcp/#tab-fast-settings');
	}
	
	function enable(){
		$db = Register::get('db');
		if (isset($_REQUEST['cats'])) {
			$sql = "UPDATE `".DB_PREFIX."cat` SET `is_active`='1';";
			$db->post($sql);
		}
		if (isset($_REQUEST['products'])) {
			$sql = "UPDATE `".DB_PREFIX."products` SET `set_isset`='1';";
			$db->post($sql);
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Включение данных раздела магазин',URL_NOW);
		$this->redirectUrl('/staffcp/#tab-fast-settings');
	}
	/* *************************************************************** */
	
	function set_view_prices(){
		$prices = $this->request("prices");
		$db = Register::get('db');
		$db->post("UPDATE ".DB_PREFIX."settings SET `value`='".(int)$prices."' WHERE `code`='view-prices-type';");
		
		Logs::addLog(Acl::getAuthedUserId(),'Настройка типа отображения цен - мин, макс, все',URL_NOW);
		$this->redirectUrl('/staffcp/#tab-fast-settings');
	}
	public function set_currency(){
		
		$currency = $this->request("currency");
		$currency_eur = $this->request("currency_eur");
		$currency_usd_eur = $this->request("currency_usd_eur");
		$currency_rur = $this->request("currency_rur");
		
		$db = Register::get('db');
		
		//USD
		$db->post("UPDATE ".DB_PREFIX."settings SET `value`='".mysql_real_escape_string($currency)."' WHERE `code`='currency';");
		$db->post("UPDATE ".DB_PREFIX."currencies SET `rate`='".mysql_real_escape_string($currency)."' WHERE `code`='USD';");
		
		//EUR
		$db->post("UPDATE ".DB_PREFIX."settings SET `value`='".mysql_real_escape_string($currency_eur)."' WHERE `code`='currency_eur';");
		$db->post("UPDATE ".DB_PREFIX."currencies SET `rate`='".mysql_real_escape_string($currency_eur)."' WHERE `code`='EUR';");
		
		//USD/EUR
		$db->post("UPDATE ".DB_PREFIX."settings SET `value`='".mysql_real_escape_string($currency_usd_eur)."' WHERE `code`='currency_usd_eur';");

		//RUR
		$db->post("UPDATE ".DB_PREFIX."settings SET `value`='".mysql_real_escape_string($currency_rur)."' WHERE `code`='currency_rur';");
		$db->post("UPDATE ".DB_PREFIX."currencies SET `rate`='".mysql_real_escape_string($currency_rur)."' WHERE `code`='RUR';");
		
		Logs::addLog(Acl::getAuthedUserId(),'Установка курсов',URL_NOW);
		$this->redirectUrl('/staffcp/');
	}
	function clear_prices(){
		$db = Register::get('db');
		$db->post("TRUNCATE TABLE ".DB_PREFIX."details;");
		
		Logs::addLog(Acl::getAuthedUserId(),'Очистка базы цен',URL_NOW);
		$this->redirectUrl('/staffcp/details/');
	}
	/* ************************** */
	function switch_onoff(){
		$db = Register::get('db');
		/** *************** **/
		$switch_on_off_shop = $this->request("switch_on_off_shop",false);
		if ($switch_on_off_shop || $switch_on_off_shop == 0){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($switch_on_off_shop)."' WHERE code='switch_on_off_shop'");
		}
		$switch_on_off_shop_msg = $this->request("switch_on_off_shop_msg",false);
		if ($switch_on_off_shop_msg){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($switch_on_off_shop_msg)."' WHERE code='switch_on_off_shop_msg'");
		}		
		/** *************** **/
		Logs::addLog(Acl::getAuthedUserId(),'Отключение сайта',URL_NOW);
		$this->redirectUrl('/staffcp/#tab-fast-settings');
	}
	function set_params_templates(){
		$db = Register::get('db');

		if (isset($_REQUEST['set_width_percent'])){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($_REQUEST['set_width_percent'])."' WHERE code='set_width_percent'");
		}
		$color_header_ahover_other = $this->request("color_header_ahover_other",false);
		if ($color_header_ahover_other){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_header_ahover_other)."' WHERE code='color_header_ahover_other'");
		}
		$color_cart_border = $this->request("color_cart_border",false);
		if ($color_cart_border){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_cart_border)."' WHERE code='color_cart_border'");
		}
		$color_content_a = $this->request("color_content_a",false);
		if ($color_content_a){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_content_a)."' WHERE code='color_content_a'");
		}
		
		$color_item_border = $this->request("color_item_border",false);
		if ($color_item_border){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_item_border)."' WHERE code='color_item_border'");
		}
		$color_item_bg = $this->request("color_item_bg",false);
		if ($color_item_bg){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_item_bg)."' WHERE code='color_item_bg'");
		}
		$color_item_bg_search_buttons = $this->request("color_item_bg_search_buttons","");
		$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_item_bg_search_buttons)."' WHERE code='color_item_bg_search_buttons'");
		
		$color_bhead = $this->request("color_bhead",false);
		if ($color_bhead){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_bhead)."' WHERE code='color_bhead'");
		}
		$color_blnk = $this->request("color_blnk",false);
		if ($color_blnk){
			$db->post("UPDATE ".DB_PREFIX."settings SET value='".mysql_real_escape_string($color_blnk)."' WHERE code='color_blnk'");
		}
		
		#2569de
		Logs::addLog(Acl::getAuthedUserId(),'Настройка параметров шаблона',URL_NOW);
		$this->redirectUrl('/staffcp/#tab-fast-settings');
	}
	
	var $pview_data = array(
		'pview_calc','pview_news','pview_articles','pview_faq','pview_pricelist','pview_brands','pview_maps',
		'pview_vins','pview_currency','pview_head','pview_footer','pview_office',
		'pview_polmostrow','pview_indexproducts',
	);
	
	function set_viewparts(){
		$pview = $this->request('pview',false);
		if ($pview){
			$db = Register::get('db');
		
			$sql = "UPDATE ".DB_PREFIX."settings SET value = '0' WHERE code IN ('".join("','",$this->pview_data)."')";
			$db->post($sql);
			if (isset($pview) && count($pview)>0) {
				foreach ($pview as $code=>$value){
					$db->post("UPDATE ".DB_PREFIX."settings SET `value` = '".mysql_real_escape_string($value)."' WHERE `code` = '".mysql_real_escape_string($code)."';");
				}
			}
		}
		Logs::addLog(Acl::getAuthedUserId(),'Отключение разделов сайта',URL_NOW);
		$this->redirectUrl('/staffcp/#tab-fast-settings');
	}
	function params_templates(){
		$this->view->set_width_percent = SettingsModel::get('set_width_percent');
		$this->view->color_header_ahover_other = SettingsModel::get('color_header_ahover_other');
		$this->view->color_cart_border = SettingsModel::get('color_cart_border');
		$this->view->color_content_a = SettingsModel::get('color_content_a');
		
		$this->view->switch_on_off_shop = SettingsModel::get('switch_on_off_shop');
		$this->view->switch_on_off_shop_msg = SettingsModel::get('switch_on_off_shop_msg');
		
		$this->view->color_item_border = SettingsModel::get('color_item_border');
		$this->view->color_item_bg = SettingsModel::get('color_item_bg');
		$this->view->color_item_bg_search_buttons = SettingsModel::get('color_item_bg_search_buttons');
		
		$this->view->color_bhead = SettingsModel::get('color_bhead');
		$this->view->color_blnk = SettingsModel::get('color_blnk');
		
		$settings = SettingsModel::getParams($this->pview_data);
		if (isset($settings)&&count($settings)>0){
			foreach ($settings as $set){
				$this->view->$set['code'] = $set['value'];
			}
		}
	}

	public function beforeAction(){
		parent::beforeAction();
		$this->view->shopping_account = $this->getAccountIdShopping();
		$this->view->view_prices_type = SettingsModel::get('view-prices-type');
		$this->unset_simpleview_shopping_system();
	}
	
	public function beforeRender(){
		parent::beforeRender();
	}
	
	/* ********************** OFFICES ************************************ */
	
	function getAllOffices(){
		$db = Register::get('db');
		$sql = "SELECT 
					O.id office_id,
					O.name office_name,
					O.info office_info,
					C.name city_name
				FROM ".DB_PREFIX."offices O
				LEFT JOIN ".DB_PREFIX."dic_cities C ON O.city_id=C.id
				ORDER BY city_name,office_name;";
		return $db->query($sql);
	}
	
	/* ********************** NOTICE SYSTEM ****************************** */
	function sendNotice($kid,$cartIds=array()){
		$db = Register::get('db');
		
		$orderData = $db->get("SELECT * FROM ".DB_PREFIX."cart_bills WHERE id = '".mysql_real_escape_string($kid)."';");
		$account = $db->get("SELECT * FROM ".DB_PREFIX."accounts WHERE id='".(int)$orderData['account_id']."';");
		
		$model = new EmailsModel();
		$data = $model->select()->where("code = ? ", "client_notice")->fetchOne();
		
		$letter = $data['value'];
		$subject = $data['name'];
		
		if (!$letter)
			return false;
		
		$params = array('bill_id'=>$kid);
		if (isset($cartIds) && count($cartIds)>0){
			$params = array(
				'bill_id'=>$kid,
				'cart_ids'=>$cartIds
			);
		}
		$billPositions = BillsModel::getHistory($params);
		
		$msg = '<style>.infotable td,.infotable th{ border:solid 1px #000; font-family:arial; font-size:12px; font-weight:bold; }</style>';
		if (isset($billPositions) && count($billPositions)>0){
			$msg .= "<table class=\"infotable\" cellpadding=\"10px\" cellspacing=\"1px\">";
			$msg .= "<tr>";
				$msg .= "<th style=\"border:solid 1px #000;\">Дата заказа</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Артикул</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Бренд</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Наименование</th>";
				
				$msg .= "<th style=\"border:solid 1px #000;\">Цена</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Количество</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Сумма</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Долг</th>";
				
				$msg .= "<th style=\"border:solid 1px #000;\">Статус</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Дата ожидания</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Направление</th>";
				$msg .= "<th style=\"border:solid 1px #000;\">Комментарий</th>";
			$msg .= "</tr>";
			foreach ($billPositions as $BP){
			$msg .= "<tr bgcolor=\"".(($BP['ds_color'])?'#'.$BP['ds_color']:'')."\">";
				$msg .= "<td style=\"border:solid 1px #000;\">".date("d.m.Y",$BP['createDT'])."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".$BP['article']."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".$BP['brand']."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".$BP['descr']."</td>";
				
				$msg .= "<td style=\"border:solid 1px #000;\">".PriceHelper::number($BP['price'])."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".$BP['cc']."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".PriceHelper::number($BP['cc'] * $BP['price'])."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".(($dd['balance_minus'])?PriceHelper::number($BP['cc'] * $BP['price']):0)."</td>";
				
				$msg .= "<td style=\"border:solid 1px #000;font-weight:bold;\">".$BP['ds_name']."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".date("d.m.Y",$BP['time_delivery_wait_dt'])."</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".$BP['namePrice']."&nbsp;</td>";
				$msg .= "<td style=\"border:solid 1px #000;\">".$BP['status_descr']."&nbsp;</td>";
			$msg .= "</tr>";
			}
			$msg .= "<table>";
		}
			
		//echo($msg);
		//exit();
		
		$vars = array();
		$vars ['account_name']= (isset($account['name'])&&($account['name']))?$account['name']:$orderData['f1'];
		$vars ['order_number']= $orderData['number'].' от ('.date("d.m.Y H:i",$orderData['dt']).')';
		$vars ['order']= $msg;
		$vars ['domain']= "".$_SERVER['SERVER_NAME']."";
		
		if (isset($vars) && count($vars)>0){
			foreach ($vars as $kk=>$vv){
				$str = '{'.$kk.'}';
				$letter = str_replace($str,$vv,$letter);
			}
		}
		
		$email = SettingsModel::get('contact_email');	
		$site = "".$_SERVER['SERVER_NAME']."";
		
		if (isset($account['email']) && $account['email']) {
			$mail = new Phpmailer();
			$mail->From     = $email;
			$mail->FromName = $site;
			$mail->Subject  = $subject;
			$mail->MsgHTML($letter);
			$mail->AddAddress($account['email']);
			$mail->Send();
			$mail->ClearAddresses();
			
			return true;
		}
		elseif (isset($orderData['f3']) && $orderData['f3']){
			$mail = new Phpmailer();
			$mail->From     = $email;
			$mail->FromName = $site;
			$mail->Subject  = $subject;
			$mail->MsgHTML($letter);
			$mail->AddAddress($orderData['f3']);
			$mail->Send();
			$mail->ClearAddresses();
			
			return true;
		}
		else 
			return false;
	}
	private function getPosition($id){
		$db = Register::get('db');
		$sql = "
			SELECT 
				C.status,C.price,DC.name status_name,CB.account_id aid
			FROM ".DB_PREFIX."cart C
			LEFT JOIN ".DB_PREFIX."dic_statuses DC ON DC.id=C.status
			LEFT JOIN ".DB_PREFIX."cart_bills CB ON CB.scSID=C.scSID
			WHERE 
				C.id='".(int)$id."';";
		$res = $db->get($sql);
		return $res;
	}
	/* ********************** END NOTICE SYSTEM ************************** */
	
	/* ********************** ORDERS SYSTEM ****************************** */
	function indexOrders(){
		
		if ($this->isManager){
			
			$manager_id = Acl::getAuthedUserId();
			$office = UsersModel::getById($this->getManagerId());
			$this->view->ccBills = BillsModel::getHistoryCount(array('manager_id'=>$manager_id,'office_id'=>$office['office_id']));
			$this->view->newBills = BillsModel::getHistoryCount(array('only_new'=>true,'manager_id'=>$manager_id,'office_id'=>$office['office_id']));
			$this->view->doneBills = BillsModel::getHistoryCount(array('archive'=>true,'manager_id'=>$manager_id,'office_id'=>$office['office_id']));
			
		}
		else {
			
			$this->view->ccBills = BillsModel::getHistoryCount(array());	
			$this->view->newBills = BillsModel::getHistoryCount(array('only_new'=>true));
			$this->view->doneBills = BillsModel::getHistoryCount(array('archive'=>true));
			
			//Общий доход за весь период
			$billsStats = BillsModel::getStatisticTotalSum(array('is_done'=>true));
			$this->view->total_sum = $billsStats['total_price'];
			$this->view->total_sum_purchase = $billsStats['total_price_purchase'];
			
			//Доход за месяц
			$month = $this->request("stat_month",date("m"));
			$this->view->stat_month = $month;
			$billsStatsThisReq = BillsModel::getStatisticTotalSum(array('this_month'=>$month,'is_done'=>true));
			$this->view->total_this_month = ($billsStatsThisReq['total_price']-$billsStatsThisReq['total_price_purchase']);
			
			//Доход за год
			$billsStatsThisReq = BillsModel::getStatisticTotalSum(array('this_year'=>true,'is_done'=>true));
			$this->view->total_this_year = ($billsStatsThisReq['total_price']-$billsStatsThisReq['total_price_purchase']);
			
			/* TEMP CART ITEMS ******************** */
			$unconfirmed_id_delete = $this->request("unconfirmed_id_delete",false);
			if ($unconfirmed_id_delete)
				CartModel::unconfirmed_id_delete($unconfirmed_id_delete);
			
			$unconfirmed_all_delete = $this->request("unconfirmed_all_delete",false);
			if ($unconfirmed_all_delete){
				CartModel::unconfirmed_all_delete();
				$this->redirectUrl('/staffcp/');
			}
			
			$per_page = 20;
			$page = $this->request("page",1);
			$this->view->unconfirmed_cart_items = CartModel::getNotConfirmed($page,$per_page);
			$count_cart_items = CartModel::getNotConfirmedCount();
			$this->view->unconfItemsPages_num = (int)(($count_cart_items - 1) / $per_page) + 1;
			$this->view->unconfItemsPage = $page;
			/* TEMP CART ITEMS ******************** */
		}
		
	}
	function crm(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр системы заказов',URL_NOW);
		$this->layout = "screen";
		$translates = Register::get('translates');
		$db = Register::get('db');
		
		#CHOOSED OPERATION
		$set_operation = $this->request("set_operation",false);
		if ($set_operation){
			
			$set_operation_item_ids = $this->request("set_operation_item_ids",false);
			$set_operation_item_keys_ids = $this->request("set_operation_item_ids",false);
			
			if ($set_operation_item_ids && count($set_operation_item_ids)>0){
				
				$soii = array();
				foreach ($set_operation_item_ids as $bkey=>$cids){
					$soii = array_merge((array)$soii,(array)$cids);
				}
				$set_operation_item_ids = $soii;
				
				switch ($set_operation){
					
					case 'changeStatus':
						
						Logs::addLog(Acl::getAuthedUserId(),'Смена статуса позиций id:'.join(",", $set_operation_item_ids).' заказа',URL_NOW);
						
						$done_status = $db->get("SELECT id FROM ".DB_PREFIX."dic_statuses WHERE type = 1;");
						$done_status_id = $done_status['id'];
						
						$status = (int)$this->request("set_operation_status",0);
						
						$sql = "UPDATE ".DB_PREFIX."cart 
								SET ".DB_PREFIX."cart.status = ".(int)$status."
								WHERE ".DB_PREFIX."cart.id IN (".(join(",", $set_operation_item_ids)).");";
						$db->post($sql);
						
						$manager_id = Acl::getAuthedUserId();
						foreach ($set_operation_item_ids as $item){
							
							/* перевод заказы в выполнение в автомат. режиме ************************ */
							$sql = "
								SELECT COUNT(t1.id) c
								FROM ".DB_PREFIX."cart t1
								LEFT JOIN ".DB_PREFIX."dic_statuses t2 ON t1.status=t2.id
								WHERE 
									t1.scSID = (SELECT scSID FROM ".DB_PREFIX."cart WHERE id = '".(int)$item."') AND 
									(t2.type != 1 OR t2.type IS NULL)";
							$res_cc = $db->get($sql);
							$sql = "
								UPDATE
									".DB_PREFIX."cart,
									".DB_PREFIX."cart_bills
								SET
									".DB_PREFIX."cart_bills.status = IF((".((int)$res_cc['c']?0:1)."),".(int)$done_status_id.",".(($status == $done_status_id)?DB_PREFIX."cart_bills.status":$status)."),
									".DB_PREFIX."cart_bills.bill_dt_closed = IF((".((int)$res_cc['c']?0:1)."),".time().",".(0).")
								WHERE
									".DB_PREFIX."cart.id IN (".((int)$item).") AND
									".DB_PREFIX."cart.scSID = ".DB_PREFIX."cart_bills.scSID
							;";
							$db->post($sql);
							/* перевод заказы в выполнение в автомат. режиме - конец   *********** */
							
							/* логирование статусов ************************ */
							$sql = "
							SELECT 
								ITEMS.price,
								BILLS.account_id,
								DS.name dstatus
							FROM ".DB_PREFIX."cart ITEMS
							JOIN ".DB_PREFIX."cart_bills BILLS ON BILLS.scSID=ITEMS.scSID
							LEFT JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=ITEMS.status
							WHERE ITEMS.id = '".(int)$item."'
							;";
							$cartData = $db->get($sql);
							$db->post("
							INSERT INTO ".DB_PREFIX."cart_history
								(`fk_item`,`fk_user`,`status_name`,`dt`,`price`)
							VALUES
								('".(int)$item."','".(int)$manager_id."','".mysql_real_escape_string($cartData['dstatus'])."','".time()."','".$cartData['price']."')
							;");
						}
						
						if ($cartData['account_id'])
							$this->setAccountAlert($cartData['account_id'],'status');
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
						$this->redirectUrl('/staffcp/index/crm/');
						exit();
						
					break;
					case 'setPayments':
						
						foreach ($set_operation_item_ids as $item){
							
							$sql = "
							SELECT
								ITEMS.price,
								ITEMS.count,
								BILLS.account_id,
								BILLS.number,
								DS.name dstatus,
								CONCAT(ITEMS.article,' ',ITEMS.brand,' ',ITEMS.descr_tecdoc) name_position
							FROM ".DB_PREFIX."cart ITEMS
							JOIN ".DB_PREFIX."cart_bills BILLS ON BILLS.scSID=ITEMS.scSID
							LEFT JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=ITEMS.status
							WHERE 
								ITEMS.id = '".(int)$item."' AND
								ITEMS.balance_minus = 0
							;";
							$cartData = $db->get($sql);
							
							if (count($cartData)>0){
								$acc = $cartData['account_id'];
								if ($acc){
									$balance = $cartData['price']*$cartData['count'];
									$db->post("UPDATE ".DB_PREFIX."accounts SET balance=balance - ".$balance." WHERE id='".(int)$acc."';");
									$db->post("
									INSERT INTO ".DB_PREFIX."accounts_history 
										(`account_id`,`sum`,`operation`,`dt`,`comment`) 
									VALUES 
										(
										'".(int)$acc."',
										'".mysql_real_escape_string($balance)."',
										'minus',
										'".time()."',
										'Оплата товарной позиции за ".$cartData['name_position'].". Заказ №".mysql_real_escape_string($cartData['number'])."'
										)
									;");
								}
							}
							$sql = "UPDATE ".DB_PREFIX."cart SET balance_minus = '1' WHERE id = '".(int)$item."';";
							$db->post($sql);
						}
						
						Logs::addLog(Acl::getAuthedUserId(),'Проведение оплат заказа '.$cartData['number'],URL_NOW);
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
							$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
					case 'unsetPayments':
						
						foreach ($set_operation_item_ids as $item){
									
							$sql = "
							SELECT
								ITEMS.price,
								ITEMS.count,
								BILLS.account_id,
								BILLS.number,
								DS.name dstatus,
								CONCAT(ITEMS.article,' ',ITEMS.brand,' ',ITEMS.descr_tecdoc) name_position
							FROM ".DB_PREFIX."cart ITEMS
							JOIN ".DB_PREFIX."cart_bills BILLS ON BILLS.scSID=ITEMS.scSID
							LEFT JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=ITEMS.status
							WHERE 
								ITEMS.id = '".(int)$item."' AND
								ITEMS.balance_minus = 1
							;";
							$cartData = $db->get($sql);
							
							if (count($cartData)>0){
								$acc = $cartData['account_id'];
								if ($acc){
									$balance = $cartData['price']*$cartData['count'];
									$db->post("UPDATE ".DB_PREFIX."accounts SET balance=balance + ".$balance." WHERE id='".(int)$acc."';");
									$db->post("
									INSERT INTO ".DB_PREFIX."accounts_history
										(`account_id`,`sum`,`operation`,`dt`,`comment`)
									VALUES
										(
										'".(int)$acc."',
										'".mysql_real_escape_string($balance)."',
										'plus',
										'".time()."',
										'Возврат средств за ".$cartData['name_position'].". Заказ №".mysql_real_escape_string($cartData['number'])."'
										)
									;");
								}
							}
							$sql = "UPDATE ".DB_PREFIX."cart SET balance_minus = '0' WHERE id = '".(int)$item."';";
							$db->post($sql);
						}
						
						Logs::addLog(Acl::getAuthedUserId(),'Отмена оплат заказа '.$cartData['number'],URL_NOW);
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
							$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
					case 'isPayback':
						
						foreach ($set_operation_item_ids as $item){
							$sql = "UPDATE ".DB_PREFIX."cart SET is_payback = '1' WHERE id = '".(int)$item."';";
							$db->post($sql);
						}
						
						Logs::addLog(Acl::getAuthedUserId(),'Возврат в заказе',URL_NOW);
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
						$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
					case 'deleteItems':
						
						#DELETE LIST
						foreach ($set_operation_item_ids as $key_id){
							$this->delete_item($key_id);
						}
						
						Logs::addLog(Acl::getAuthedUserId(),'Удаление позиций id:'.join(",", $set_operation_item_ids).' заказа',URL_NOW);
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
						$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
					case 'sendEmailNotices':

						$_SESSION['__notice']='email';
						foreach ($set_operation_item_keys_ids as $bill_id=>$cartIds){
							$this->sendNotice($bill_id,$cartIds);
							Logs::addLog(Acl::getAuthedUserId(),'Отправка email уведомлений по позициям id:'.join(",", $cartIds),URL_NOW);
						}
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
							$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
					case 'sendSMSNotices':
						
						$sms_alert_active = SettingshiddenModel::get('sms_alert_active');
						if ($sms_alert_active){
							$_SESSION['__notice']='sms';
							foreach ($set_operation_item_keys_ids as $bill_id=>$cartIds){
								$params = array(
									'bill_id'=>$bill_id,
									'cart_ids'=>$cartIds
								);
								$billPositions = BillsModel::getHistory($params);
								$msg = '';
								if (isset($billPositions) && count($billPositions)>0){
									foreach ($billPositions as $BP){
										$msg .= $BP['article'].' : '.$BP['brand'].' : '.($BP['ds_name']?$BP['ds_name']:'Новый') . ' # ';
									}
								}
								$billDataFull = BillsModel::billByIdFullInfo($bill_id);
								$to_phone = (isset($billDataFull['f2']) && $billDataFull['f2'])?$billDataFull['f2']:$billDataFull['account_phones'];
								$params = array(
									'data' => $msg,
									'sitename' => $_SERVER['HTTP_HOST'],
								);
								SmsSystemHelper::sendSmsMessage(3,$params,$to_phone);
								Logs::addLog(Acl::getAuthedUserId(),'Отправка sms уведомлений по позициям id:'.join(",", $cartIds).' заказа',URL_NOW);
							}
						}
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
							$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
					case 'changeTimedelivery':
						
						$dtchangeTimedelivery = $this->request("dtchangeTimedelivery",false);
						foreach ($set_operation_item_ids as $item){
							$sql = "UPDATE ".DB_PREFIX."cart 
									SET 
										time_delivery_wait_dt = '".strtotime($dtchangeTimedelivery)."',
										time_delivery_descr = '' 
									WHERE id = '".(int)$item."';";
							$db->post($sql);
						}
						Logs::addLog(Acl::getAuthedUserId(),'Смена ожидаемого срока по позициям id:'.join(",", $set_operation_item_ids).' заказа',URL_NOW);
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
							$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
					case 'changeGivetoclient':
						
						$dtchangeTimedelivery = $this->request("dtchangeTimedelivery",false);
						foreach ($set_operation_item_keys_ids as $bill_id=>$cartIds){
							$sql = "UPDATE ".DB_PREFIX."cart_bills
									SET
										time_give_order = '".strtotime($dtchangeTimedelivery)."'
									WHERE id = '".(int)$bill_id."';";
							$db->post($sql);
							Logs::addLog(Acl::getAuthedUserId(),'Смена даты выдачи по позициям id:'.$bill_id.' заказа',URL_NOW);
						}
						
						if ($_SERVER['HTTP_REFERER'])
							header("location: ".$_SERVER['HTTP_REFERER']);
						else
							$this->redirectUrl("/staffcp/index/crm/");
						exit();
						
					break;
				}
			}
		}
		
		#ITEMS SAVE STATUSES
		$items_save_statuses = $this->request("items_save_statuses",false);
		if ($items_save_statuses && count($items_save_statuses)>0){
			
			/* взять статус выполнения */
			$done_status = $db->get("SELECT id FROM ".DB_PREFIX."dic_statuses WHERE type = 1;");
			$done_status_id = $done_status['id'];
			/* взять статус выполнения - конец */
			
			$manager_id = Acl::getAuthedUserId();
			foreach ($items_save_statuses as $key_id=>$item){
				$statusNow = $itemData = $this->getPosition($key_id);
				$statusNow = $statusNow['status'];
				if ($item != $statusNow) {
					
					$sql = "UPDATE ".DB_PREFIX."cart 
							SET ".DB_PREFIX."cart.status = ".(int)$item."
							WHERE ".DB_PREFIX."cart.id IN (".((int)$key_id).");";
					$db->post($sql);
					
					/* перевод заказы в выполнение в автомат. режиме ************************ */
					$sql = "
						SELECT COUNT(t1.id) c
						FROM ".DB_PREFIX."cart t1
						LEFT JOIN ".DB_PREFIX."dic_statuses t2 ON t1.status=t2.id
						WHERE
							t1.scSID = (SELECT scSID FROM ".DB_PREFIX."cart WHERE id = '".(int)$key_id."') AND
							(t2.type != 1 OR t2.type IS NULL)";
					$res_cc = $db->get($sql);
					$sql = "
						UPDATE
							".DB_PREFIX."cart,
							".DB_PREFIX."cart_bills
						SET
							".DB_PREFIX."cart_bills.status = IF((".((int)$res_cc['c']?0:1)."),".(int)$done_status_id.",".(($item == $done_status_id)?DB_PREFIX."cart_bills.status":$item)."),
							".DB_PREFIX."cart_bills.bill_dt_closed = IF((".((int)$res_cc['c']?0:1)."),".time().",".(0).")
						WHERE
							".DB_PREFIX."cart.id IN (".((int)$key_id).") AND
							".DB_PREFIX."cart.scSID = ".DB_PREFIX."cart_bills.scSID;";
					$db->post($sql);
					/* перевод заказы в выполнение в автомат. режиме - конец   *********** */
					
					$status = $db->get("SELECT * FROM ".DB_PREFIX."dic_statuses WHERE id='".(int)$item."';");
					$db->post("
						INSERT INTO ".DB_PREFIX."cart_history 
							(`fk_item`,`fk_user`,`status_name`,`dt`,`price`) 
						VALUES 
							('".(int)$key_id."','".(int)$manager_id."','".mysql_real_escape_string($status['name'])."','".time()."','".mysql_real_escape_string($itemData['price'])."');");
					$this->setAccountAlert($itemData['aid'],'status');
				}
			}
			
			Logs::addLog(Acl::getAuthedUserId(),'Смена статуса позиций',URL_NOW);
		}
		
		/* ************************* */
		
		#DELETE 
		$del = $this->request("del",false);
		if ($del) {
			$res = $this->delete_item($del);
			Logs::addLog(Acl::getAuthedUserId(),'Удаление позиции заказа id:'.$del,URL_NOW);
			
			$billSCSID = BillsModel::getBillByscSID($res['scSID']);
			$this->redirectUrl("/staffcp/index/crm/?search[number]=".$billSCSID['number']);
		}
		
		#DELETE REQUEST TO IMPS
		$del_imp_message = $this->request("del_imp_message",false);
		if ($del_imp_message) {
			$db->post("DELETE FROM ".DB_PREFIX."importers_sents WHERE id='".(int)$del_imp_message."';");
			Logs::addLog(Acl::getAuthedUserId(),'Удаление заявки заказа поставщику id:'.$del_imp_message,URL_NOW);
			$this->redirectUrl("/staffcp/index/crm/#tab-3");
		}
	
		$this->view->__notice = (isset($_SESSION['__notice']))?$_SESSION['__notice']:false;
		if (isset($_SESSION['__notice']))
			unset($_SESSION['__notice']);
		
		#ALL BILLS
		$search = $this->request("search",array());
		$this->view->search = $search;
		
		$per_page = 50;
		$page = (int)$this->request("page",1);
		
		if (isset($search['account_id']) && $search['account_id']){
			$this->view->accountSearchById = AccountsModel::getById($search['account_id']);
		}
		
		if ($this->isManager) {
			
			$office = UsersModel::getById($this->getManagerId());
			$search ['manager_id']= $this->getManagerId();
			$search ['office_id']= $office['office_id'];
			
			$cc = BillsModel::getHistoryCount($search);
			$this->view->totalPages = (int)(($cc['cc'] - 1) / $per_page) + 1;
			$this->view->currentPage = $page;
			
			$this->view->bills = BillsModel::getHistory($search,$page,$per_page,false);
			
			$this->view->cc_bills_archive = BillsModel::getHistoryCount(array('archive'=>1,'manager_id'=>$search['manager_id']));						$this->view->managers = UsersModel::getManagersByOffice($office['office_id']);
		}
		else {
			
			$bills = BillsModel::getHistory($search,$page,$per_page,false);
			$this->view->bills = $bills;
			
			$cc = BillsModel::getHistoryCount($search);
			$this->view->totalPages = (int)(($cc['cc'] - 1) / $per_page) + 1;
			$this->view->currentPage = $page;
			
			$this->view->cc_bills_archive = BillsModel::getHistoryCount(array('archive'=>1));
		}
		
		#MANAGERS
		$this->view->managers = UsersModel::getManagers(array(2,3));
		#OFFICES
		$this->view->offices = $this->getAllOffices();
		#ACCOUNTS
		$this->view->accountsList = AccountsModel::getFilterList();
		#ALL IMPS
		$this->view->imps = BillsModel::iRequestToImporters();
		#STATUSES
		$this->getStatusesAll();
		$this->view->imps_list = ImportersModel::getAll();
		
		#ALL SENT REQUEST
		$this->imps_sents();
				
		$this->addBreadCrumb('Система заказов','/staffcp/index/crm/');
		$this->render('index/crm/index');
	}
	function crm_list_orders(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела списка заказов',URL_NOW);
		$db = Register::get('db');
		
		$ajax = $this->request("ajax",false);
		if ($ajax){
			$this->layout = "ajax";
			$this->view->ajax = true;
		} else {
			$this->layout = "screen";
		}
		$this->view->not_view_filters = $this->request("not_view_filters",false);
		
		$translates = Register::get('translates');
		$page = (int)$this->request("page",1);
		$per_page = 50;
		
		#MANAGERS SET
		$managers = $this->request("managers");
		if (isset($managers) && count($managers)>0){
			foreach ($managers as $idBill => $man){
				if ($this->isManager) {
					$db->post("UPDATE ".DB_PREFIX."cart_bills SET manager_id='".(int)$man."' WHERE id='".(int)$idBill."';");
				}
				else {
					$aData = UsersModel::getById($man);
					$db->post("UPDATE ".DB_PREFIX."cart_bills SET manager_id='".(int)$man."',office_id='".(int)$aData['office_id']."' WHERE id='".(int)$idBill."';");
				}
			}
			Logs::addLog(Acl::getAuthedUserId(),'Установка менеджера на заказ',URL_NOW);
		}
		
		#OFFICES SET
		$offices = $this->request("office");
		if (isset($offices) && count($offices)>0){
			foreach ($offices as $idBill => $man){
				$db->post("UPDATE ".DB_PREFIX."cart_bills SET office_id='".(int)$man."' WHERE id='".(int)$idBill."';");
			}
			Logs::addLog(Acl::getAuthedUserId(),'Установка офиса на заказ',URL_NOW);
		}
		
		#EMAIL
		$emailOrderId = $this->request("email_order_id",false);
		if ($emailOrderId){
			$this->sendNotice($emailOrderId);
			Logs::addLog(Acl::getAuthedUserId(),'Отправка email уведомления по заказу id:'.$emailOrderId,URL_NOW);
			if (isset($_REQUEST['is_crm_redirect'])){
				$this->redirectUrl("/staffcp/index/crm/?email=".$emailOrderId."&search[number]=".$_REQUEST['is_crm_redirect']);
			}else{
				$this->redirectUrl("/staffcp/index/crm_list_orders/?email=".$emailOrderId);
			}
		}
		
		#SMS
		$smsOrderId = $this->request("sms_order_id",false);
		if ($smsOrderId){
			$sms_alert_active = SettingshiddenModel::get('sms_alert_active');
			if ($sms_alert_active){
				$billDataFull = BillsModel::billByIdFullInfo($smsOrderId);
				$params = array(
					"number"=>$billDataFull['number'],
					"status"=>($billDataFull['statusName'])?$billDataFull['statusName']:'Новый',
					"sitename"=>($_SERVER['SERVER_NAME']),
				);
				$to_phone = (isset($billDataFull['f2']) && $billDataFull['f2'])?$billDataFull['f2']:$billDataFull['account_phones'];
				SmsSystemHelper::sendSmsMessage(2,$params,$to_phone);
				Logs::addLog(Acl::getAuthedUserId(),'Отправка sms уведомления по заказу #'.$billDataFull['number'],URL_NOW);
			}
			if (isset($_REQUEST['is_crm_redirect'])){
				$this->redirectUrl("/staffcp/index/crm/?sms=".$smsOrderId."&search[number]=".$_REQUEST['is_crm_redirect']);
			}else{
				$this->redirectUrl("/staffcp/index/crm_list_orders/?sms=".$smsOrderId);
			}
		}
		
		#DELETE
		$del = $this->request("del",false);
		if ($del) {
			$this->delete_full_order($del);
			Logs::addLog(Acl::getAuthedUserId(),'Удаление заказа id:'.$del,URL_NOW);
			$this->redirectUrl("/staffcp/index/crm_list_orders/");
		}

		#STATUSES
		$this->getStatusesAll();
		#SET STATUS
		$set_items_status_order = $this->request("set_items_status_order",array());
		$items_save_statuses = $this->request("items_save_statuses",false);
		if ($items_save_statuses && count($items_save_statuses)>0){

			/* взять статус выполнения */
			$done_status = $db->get("SELECT id FROM ".DB_PREFIX."dic_statuses WHERE type = 1;");
			$done_status_id = $done_status['id'];
			/* взять статус выполнения - конец */
			
			foreach ($items_save_statuses as $order_id=>$status){
				
				if (in_array($order_id, $set_items_status_order)){
					$sql = "
					UPDATE 
						".DB_PREFIX."cart_bills,".DB_PREFIX."cart 
					SET 
						".DB_PREFIX."cart_bills.status = '".(int)$status."',
						".DB_PREFIX."cart_bills.bill_dt_closed = '".(($done_status_id == $status)?time():0)."',
						".DB_PREFIX."cart.status = '".(int)$status."' 
					WHERE 
						".DB_PREFIX."cart_bills.id = '".(int)$order_id."' AND
						".DB_PREFIX."cart_bills.scSID=".DB_PREFIX."cart.scSID
					;";
					$db->post($sql);
				}
				
				$sql = "
				UPDATE 
					".DB_PREFIX."cart_bills 
				SET 
					".DB_PREFIX."cart_bills.status = '".(int)$status."',
					".DB_PREFIX."cart_bills.bill_dt_closed = '".(($done_status_id == $status)?time():0)."'
				WHERE 
					".DB_PREFIX."cart_bills.id = '".(int)$order_id."'
				;";
				$db->post($sql);
			}
			Logs::addLog(Acl::getAuthedUserId(),'Уставнока статуса заказу id:'.$order_id,URL_NOW);
			if (isset($_REQUEST['is_crm_redirect'])){
				$this->redirectUrl("/staffcp/index/crm/?search[number]=".$_REQUEST['is_crm_redirect']);
			}else{
				$this->redirectUrl("/staffcp/index/crm_list_orders/");
			}
		}
		
		$slimSQL = $slimSQLCount = "";
		if ($this->isManager) {
			$office = UsersModel::getById($this->getManagerId());
			$slimSQL .= " AND ( BILLS.office_id = '".(int)$office['office_id']."' OR BILLS.office_id = '0' ) ";
			$slimSQLCount .= " AND ( office_id = '".(int)$office['office_id']."' OR office_id = '0' ) ";
		}
		
		/* НАЧАЛО: ПОИСК */
		$search = $this->request("search",false);
		$this->view->search = $search;
		
		$newRulesPaging = false;
		if (isset($search['number']) && $search['number']){
			$slimSQL .= " AND BILLS.number LIKE '".mysql_real_escape_string($search['number'])."' ";
			$newRulesPaging = true;
		}
		if (isset($search['status']) && $search['status']){
			$slimSQL .= " AND BILLS.status = '".mysql_real_escape_string($search['status'])."' ";
			$newRulesPaging = true;
		}
		if (isset($search['office_id']) && $search['office_id']){
			$slimSQL .= " AND BILLS.office_id = '".mysql_real_escape_string($search['office_id'])."' ";
			$newRulesPaging = true;
		}
		if (isset($search['manager_id']) && $search['manager_id']){
			$slimSQL .= " AND BILLS.manager_id = '".mysql_real_escape_string($search['manager_id'])."' ";
			$newRulesPaging = true;
		}
		if (isset($search['account_id']) && $search['account_id']){
			$slimSQL .= " AND BILLS.account_id = '".mysql_real_escape_string($search['account_id'])."' ";
			$newRulesPaging = true;
		}
		if ((isset($search['dt_from']) && $search['dt_from']) && (isset($search['dt_to']) && $search['dt_to'])){
			$search['dt_from'] = strtotime($search['dt_from']);
			$search['dt_to'] = strtotime($search['dt_to']);
			$slimSQL .= " AND (BILLS.dt BETWEEN '".mysql_real_escape_string($search['dt_from'])."' AND '".mysql_real_escape_string($search['dt_to'])."') ";
			$newRulesPaging = true;
		}
		elseif ((isset($search['dt_from']) && $search['dt_from'])){
			$search['dt_from'] = strtotime($search['dt_from']);
			$slimSQL .= " AND BILLS.dt >= '".mysql_real_escape_string($search['dt_from'])."' ";
			$newRulesPaging = true;
		}
		elseif ((isset($search['dt_to']) && $search['dt_to'])){
			$search['dt_to'] = strtotime($search['dt_to']);
			$slimSQL .= " AND BILLS.dt <= '".mysql_real_escape_string($search['dt_to'])."' ";
			$newRulesPaging = true;
		}
		if ($newRulesPaging){
			$per_page = 999;
			$slimSQLCount .= " AND id = 0 ";
		}
		
		/* КОНЕЦ: ПОИСК */
		
		#LISTS
		$ipage = ($page - 1)*$per_page;
		$sql = "
			SELECT 
				BILLS.*,
				ACC.name account_name,
				ACC.phones account_phones,
				ACC.address account_address,
				DCITY.name account_cityname,
				ACC.info account_descr,
				ACCARS.car_name carname,
				ACCARS.car_year caryear,
				MANAGER.name manager,
				OFFICE.name office,
				DSTATUS.color,
				DSTATUS.name statusName
			FROM ".DB_PREFIX."cart_bills BILLS
			LEFT JOIN ".DB_PREFIX."accounts ACC ON ACC.id=BILLS.account_id
			LEFT JOIN ".DB_PREFIX."dic_cities DCITY ON DCITY.id=ACC.city		
			LEFT JOIN ".DB_PREFIX."accounts_cars ACCARS ON ACCARS.id=BILLS.car_id
			LEFT JOIN ".DB_PREFIX."_user MANAGER ON MANAGER.id=BILLS.manager_id
			LEFT JOIN ".DB_PREFIX."offices OFFICE ON OFFICE.id=BILLS.office_id
			LEFT JOIN ".DB_PREFIX."dic_statuses DSTATUS ON DSTATUS.id=BILLS.status
			WHERE 1=1 $slimSQL
			ORDER BY
				BILLS.dt DESC
			LIMIT ".$ipage.",".$per_page.";";
		
// 		echo('<pre>');
// 		var_dump($sql);
// 		echo('</pre>');
		
		$resBills = $db->query($sql);
		$formattedData = array();
		if (isset($resBills) && count($resBills)>0){
			foreach ($resBills as $RB){
				$sql = "
					SELECT
						(SUM(ITEMS.price * ITEMS.count)) total_sum,
						COUNT(ITEMS.id) total_items,
						SUM(IF(ITEMS.balance_minus = 1,(ITEMS.price * ITEMS.count),0)) payed_total_sum,
						SUM(IF(ITEMS.is_payback = 1,1,0)) ispayback
					FROM 
						".DB_PREFIX."cart ITEMS
					WHERE
						ITEMS.scSID LIKE '".mysql_real_escape_string($RB['scSID'])."';";
				$resItemsData = $db->get($sql);
				$formattedData []= array_merge((array)$RB,(array)$resItemsData);
			}
		}
		$this->view->bills = $formattedData;
		/* formatted! end! */
		
		$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills WHERE 1=1 $slimSQLCount;";
		$cc = $db->get($sql);
		$this->view->totalPages = (int)(($cc['cc'] - 1) / $per_page) + 1;
		$this->view->currentPage = $page;
		
		#ARCHIVE
		if ($this->isManager) {
			$office = UsersModel::getById($this->getManagerId());
			$search ['manager_id']= $this->getManagerId();
			$this->view->cc_bills_archive = BillsModel::getHistoryCount(array('archive'=>1,'manager_id'=>$search['manager_id']));						$this->view->managers = UsersModel::getManagersByOffice($office['office_id']);
		}
		else {
			$this->view->cc_bills_archive = BillsModel::getHistoryCount(array('archive'=>1));
		}
		#MANAGERS
		$this->view->managers = UsersModel::getManagers(array(2,3));
		#OFFICES
		$this->view->offices = $this->getAllOffices();
		#ACCOUNTS
		$this->view->accountsList = AccountsModel::getFilterList();
		#ALL IMPS
		$this->view->imps = $this->view->imps_list = ImportersModel::getAll();
		
		$this->addBreadCrumb('Система заказов','/staffcp/index/crm/');
		$this->render('index/crm/orders_list');
	}
	function delete_full_order($id=0){
		$db = Register::get('db');
		$sql = "
		DELETE ".DB_PREFIX."cart_bills,".DB_PREFIX."cart FROM ".DB_PREFIX."cart_bills,".DB_PREFIX."cart
		WHERE ".DB_PREFIX."cart_bills.id = '".(int)$id."' AND ".DB_PREFIX."cart.scSID=".DB_PREFIX."cart_bills.scSID		
		;";
		$db->post($sql);

		$sql = "DELETE FROM ".DB_PREFIX."cart_bills WHERE ".DB_PREFIX."cart_bills.id = '".(int)$id."';";
		$db->post($sql);
	}
	function crm_history(){
		
		$this->layout = "ajax";
		$db = Register::get('db');
		
		$id = $this->request("id",false);
		if ($id) {
			
			$sql = "
				SELECT 
					CH.* ,U.name as manager
				FROM ".DB_PREFIX."cart_history CH 
				LEFT JOIN ".DB_PREFIX."_user U ON U.id=CH.fk_user
				WHERE 
					CH.fk_item = '".(int)$id."' 
				ORDER BY CH.dt DESC;";
			
			$this->view->history = $db->query($sql);
			
			$item = BillsModel::getHistory(array("item_id"=>$id));
			$this->view->bill_item = isset($item[0])?$item[0]:false;
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр истории смены статуса заказа #'.$this->view->bill_item['number'],URL_NOW);
		$this->render('index/crm/history');
	}
	private function delete_item($del=0){
		$db = Register::get('db');
		
		$res = $db->get("SELECT * FROM ".DB_PREFIX."cart WHERE id='".(int)$del."';");
		
		$db->query("DELETE FROM ".DB_PREFIX."cart WHERE id='".(int)$del."';");
		$db->query("DELETE FROM ".DB_PREFIX."cart_history WHERE fk_item='".(int)$del."';");
		
		$cc = $db->get("SELECT COUNT(*) cc FROM ".DB_PREFIX."cart WHERE scSID='".addslashes($res['scSID'])."';");
		if ($cc['cc']<=0) {
			$db->post("DELETE FROM ".DB_PREFIX."cart_bills WHERE scSID='".addslashes($res['scSID'])."';");
			$this->delete_all_messages($res['number']);
		}
		
		return $res;
	}
	private function getStatusesAll(){
		
		$db = Register::get('db');
		
		$userId = Acl::getAuthedUserId();
		$sql = "SELECT COUNT(*) CC FROM ".DB_PREFIX."_user_permission2status WHERE user_id = '".(int)$userId."';";
		$up2s = $db->get($sql);
		$up2s = $up2s['CC'];
		
		if ($up2s){
			$sql = "
				SELECT * 
				FROM ".DB_PREFIX."dic_statuses DS 
				JOIN ".DB_PREFIX."_user_permission2status UP2S ON UP2S.status_id=DS.id
				WHERE UP2S.user_id = '".(int)$userId."'
				ORDER BY sort,name;";
		}
		else {
			$sql = "SELECT * FROM ".DB_PREFIX."dic_statuses ORDER BY sort,name;";
		}
		
		$this->view->statuses = $db->query($sql);
	}
	function change_status() {
		$id = (int)$this->request("id");
		$status = (int)$this->request("status");
		$account = (int)$this->request("account");
		if (BillsModel::changeStatus($id,$status,$account)) {
			$this->redirectUrl("/staffcp/index/crm/");
		}
	}
	
	function order() {
		
		$this->layout = "screen";
		$translates = Register::get('translates');
		$db = Register::get('db');
		
		$item = $this->request("item",0);
		$this->view->sel_item = $item;
		
		$edit_order = $this->request("edit_order",0);
		$bill = BillsModel::fetchByIdBill($edit_order);
		$this->view->bill = $bill;
		$this->view->auto = AccountsModel::getByIdCarInfo($bill['car_id']);
		if ($bill['scSID'])
		$this->view->scSID = BillsModel::getHistory(array("scSID"=>$bill['scSID']));
		
		$this->view->ImportersModel = ImportersModel::getAll();
		$this->view->accountsList = AccountsModel::getFilterList();
		$this->getStatusesAll();
		
		if (isset($_REQUEST['act'])){
			switch ($_REQUEST['act']){
				case 'add_message':
					$message = (isset($_REQUEST['message']) && $_REQUEST['message'])?$_REQUEST['message']:false;
					$number = (isset($_REQUEST['number']) && $_REQUEST['number'])?$_REQUEST['number']:false;
					$bill_id = (isset($_REQUEST['bill_id']) && $_REQUEST['bill_id'])?$_REQUEST['bill_id']:false;
					if ($message){
						$this->add_message($number,$message);
						$this->redirectUrl('/staffcp/index/order/?edit_order='.$bill_id.'#messages');
					}
				break;
				case 'delete_message':
					$bill_id = (isset($_REQUEST['bill_id']) && $_REQUEST['bill_id'])?$_REQUEST['bill_id']:false;
					$id_message = (isset($_REQUEST['id_message']) && $_REQUEST['id_message'])?$_REQUEST['id_message']:false;
					if ($id_message){
						$this->delete_message($id_message);
						$this->redirectUrl('/staffcp/index/order/?edit_order='.$bill_id.'#messages');
					}
					break;
			}
		}
		$this->view->bill_messages = $this->list_messages_by_number($bill['number']);
		
		Logs::addLog(Acl::getAuthedUserId(),'Редактирование заказа #'.$bill['number'],URL_NOW);
		
		$this->addBreadCrumb($translates['admin.index.sysorder'],'/staffcp/index/crm/');
		$this->render('index/crm/order');
	}
	
	/* ********************* */
	
	function list_messages_by_number($bill_number=0){
		
		$db = Register::get('db');
		
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills_messages WHERE bill_number='".mysql_real_escape_string($bill_number)."' ORDER BY dt ASC;";
		$res = $db->query($sql);
		
		if (isset($_REQUEST['unset_new'])){
			$db->post("UPDATE ".DB_PREFIX."cart_bills_messages SET is_new = 0 WHERE bill_number='".mysql_real_escape_string($bill_number)."';");
		}
		 
		return $res; 
	}
	
	function add_message($bill_number=0,$message=''){
		$db = Register::get('db');
		$sql = "
		INSERT INTO ".DB_PREFIX."cart_bills_messages
			(`bill_number`,`is_client`,`dt`,`message`,`is_new`)
		VALUES
			(
				'".mysql_real_escape_string($bill_number)."',
				'0',
				'".time()."',
				'".mysql_real_escape_string($message)."',
				'1'
			);	
		";
		$db->post($sql);
	}
	
	function delete_message($id=0){
		$db = Register::get('db');
		$sql = "DELETE FROM ".DB_PREFIX."cart_bills_messages WHERE id = '".(int)$id."';";
		$db->post($sql);
	}
	
	function delete_all_messages($number=0){
		$db = Register::get('db');
		$sql = "DELETE FROM ".DB_PREFIX."cart_bills_messages WHERE bill_number = '".mysql_real_escape_string($number)."';";
		$db->post($sql);
	}
	
	/* ********************* */
	
	function order_save() {
		$db = Register::get('db');
		
		$manager_id = Acl::getAuthedUserId();
		$order_id = $this->request("order_id");
		$item = $this->request("item");
		$price = $this->request("price");
		$descr = $this->request("descr");
		$price_purchase = $this->request("price_purchase");
		$sold = $this->request("sold");
		$count = $this->request("count");
		$minus = $this->request("minus");
		$importer = $this->request("importer");
		$ioarticle = $this->request("ioarticle");
		$iobrand = $this->request("iobrand");
		$iodescr = $this->request("iodescr");
		$io_time_delivery_wait_dt = $this->request("time_delivery_wait_dt");
		$cartinfo = $this->request("cartinfo");
		
		$iSQL = "";
		if (isset($cartinfo['delivery_set_balance']) && $cartinfo['delivery_set_balance']){
			$iSQL = " ,delivery_set_balance = '1' ";
			$db->post("UPDATE ".DB_PREFIX."accounts SET balance=balance-'".mysql_real_escape_string($cartinfo['delivery_price'])."' WHERE id='".((int)$cartinfo['account_id'])."';");
			$db->post("
				INSERT INTO ".DB_PREFIX."accounts_history 
				(`account_id`,`sum`,`operation`,`dt`,`comment`) 
				VALUES 
				(
					'".((int)$cartinfo['account_id'])."',
					'".mysql_real_escape_string($cartinfo['delivery_price'])."',
					'minus',
					'".mktime()."',
					'Списание средств за доставку заказа №".mysql_real_escape_string($cartinfo['number'])."'
				);
			");
			
		}
		$sql = "
			UPDATE ".DB_PREFIX."cart_bills SET 
				account_id='".(int)((isset($cartinfo['account_id']) && $cartinfo['account_id'])?$cartinfo['account_id']:0)."',
				f1='".mysql_real_escape_string($cartinfo['f1'])."',
				f2='".mysql_real_escape_string($cartinfo['f2'])."',
				f3='".mysql_real_escape_string($cartinfo['f3'])."',
				message='".mysql_real_escape_string($cartinfo['message'])."',
				delivery='".mysql_real_escape_string($cartinfo['delivery'])."',
				delivery_price='".mysql_real_escape_string($cartinfo['delivery_price'])."',
				
				time_give_order='".mysql_real_escape_string(strtotime($cartinfo['time_give_order']))."',
				time_from='".mysql_real_escape_string($cartinfo['time_from'])."',
				time_to='".mysql_real_escape_string($cartinfo['time_to'])."',
				payment_name='".mysql_real_escape_string($cartinfo['payment_name'])."',
				delivery_addess='".mysql_real_escape_string($cartinfo['delivery_addess'])."',
				prepayment='".mysql_real_escape_string($cartinfo['prepayment'])."',
				is_paid='".mysql_real_escape_string((isset($cartinfo['is_paid'])?1:0))."'
				
				$iSQL
			WHERE id='".(int)$order_id."';
		";
		$db->post($sql);
		
		$add_new = $this->request("add_new",false);
		$scSID = $this->request("scSID",false);
		if (isset($add_new['status']) && count($add_new['status'])>0 && $scSID){
			foreach ($add_new['status'] as $N_key=>$N_status){
				$N_article = $add_new['article'][$N_key];
				$N_brand = $add_new['brand'][$N_key];
				$N_descr = $add_new['descr'][$N_key];
				$N_importer = $add_new['importer'][$N_key];
				$N_price = $add_new['price'][$N_key];
				$N_count = $add_new['count'][$N_key];
				$N_price_purchase = $add_new['price_purchase'][$N_key];
				$N_status_descr = $add_new['status_descr'][$N_key];
				$N_sold = $add_new['sold'][$N_key];
				$sql = "
					INSERT INTO ".DB_PREFIX."cart 
					(`scSID`,`createDT`,`fk`,`wbs_id`,`count`,`type`,`price`,`price_purchase`,`status`,`article`,`brand`,`descr_tecdoc`,`status_descr`,`import_id`,`sold`)
					VALUES 
					('".mysql_real_escape_string($scSID)."','".mktime()."','".(int)$N_importer."','".(int)$N_importer."','".(int)$N_count."','detail','".mysql_real_escape_string($N_price)."','".mysql_real_escape_string($N_price_purchase)."','".(int)$N_status."','".mysql_real_escape_string($N_article)."','".mysql_real_escape_string($N_brand)."','".mysql_real_escape_string($N_descr)."','".mysql_real_escape_string($N_status_descr)."','".(int)$N_importer."','".mysql_real_escape_string($N_sold)."');
				";
				$db->post($sql);
				$this->setAccountAlert((int)((isset($cartinfo['account_id']) && $cartinfo['account_id'])?$cartinfo['account_id']:0),'status');
			}
		}
				
		if (count($item)>0) {
			foreach ($item as $kk=>$dd) {
				
				$sql_price = $price[$kk];
				$sql_descr = $descr[$kk];
				$sql_item = $item[$kk];
				$sql_price_purchase = $price_purchase[$kk];
				$sql_minus = isset($minus[$kk])?" ,balance_minus='1' ":'';
				$sql_sold = $sold[$kk];
				$sql_count = $count[$kk];
				$sql_imp = $importer[$kk];
				$sql_ioarticle = $ioarticle[$kk];
				$sql_iobrand = $iobrand[$kk];
				$sql_iodescr = $iodescr[$kk];
				$sql_io_time_delivery_wait_dt = strtotime($io_time_delivery_wait_dt[$kk]);
				
				$sql = "
					UPDATE ".DB_PREFIX."cart SET 
						`fk` = '".mysql_real_escape_string($sql_imp)."',
						`wbs_id` = '".mysql_real_escape_string($sql_imp)."',
						`import_id` = '".mysql_real_escape_string($sql_imp)."',
						`article` = '".mysql_real_escape_string($sql_ioarticle)."',
						`brand` = '".mysql_real_escape_string($sql_iobrand)."',
						`descr_tecdoc` = '".mysql_real_escape_string($sql_iodescr)."',
						`price` = '".mysql_real_escape_string($sql_price)."',
						`price_purchase` = '".mysql_real_escape_string($sql_price_purchase)."',
						`status` = '".mysql_real_escape_string($sql_item)."',
						`status_descr` = '".mysql_real_escape_string($sql_descr)."',
						`sold` = '".mysql_real_escape_string($sql_sold)."',
						`count`='".mysql_real_escape_string($sql_count)."',
						`time_delivery_wait_dt`='".mysql_real_escape_string($sql_io_time_delivery_wait_dt)."' 
						$sql_minus
					WHERE id='".(int)$kk."';";
				$db->post($sql);
				
				$this->setAccountAlert((int)((isset($cartinfo['account_id']) && $cartinfo['account_id'])?$cartinfo['account_id']:0),'status');
				
				$status = $db->get("SELECT * FROM ".DB_PREFIX."dic_statuses WHERE id='".(int)$sql_item."';");
				$db->post("
				INSERT INTO ".DB_PREFIX."cart_history 
					(`fk_item`,`fk_user`,`status_name`,`dt`,`price`) 
				VALUES 
					('".(int)$kk."','".(int)$manager_id."','".mysql_real_escape_string($status['name'])."','".mktime()."','".mysql_real_escape_string($sql_price)."')
				;");
				
				if ($sql_minus) {
					$bill = BillsModel::fetchByIdBill($order_id);
					$acc = $bill['account_id'];
					$balance_item = BillsModel::getItem($kk);
					$balance = $balance_item['price']*$balance_item['count'];
					$db->post("UPDATE ".DB_PREFIX."accounts SET balance=balance-".$balance." WHERE id='".(int)$acc."';");
					
					$db->post("
						INSERT INTO ".DB_PREFIX."accounts_history 
						(`account_id`,`sum`,`operation`,`dt`,`comment`) 
						VALUES 
						(
							'".(int)$acc."',
							'".mysql_real_escape_string($balance)."',
							'minus',
							'".mktime()."',
							'Оплата товарной позиции. Заказ №".mysql_real_escape_string($cartinfo['number'])."'
						)
					;");
				}
			}
		}
		
		$send_notice = $this->request("send_notice",false);
		if ($send_notice)
			$this->sendNotice($send_notice);
		
		Logs::addLog(Acl::getAuthedUserId(),'Сохранение при редактировании заказа id:'.$order_id,URL_NOW);
		
		$this->redirectUrl("/staffcp/index/crm/?search[number]=".$cartinfo['number']);
	}
	
	function imps_sents() {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."importers_sents ORDER BY dt DESC;";
		$this->view->imps_sents = $db->query($sql);
	}
	
	function imps_send() {
		
		$db = Register::get('db');
		
		$code = $this->request("code");
		$imps = BillsModel::imps($code);
		$getImp = ImportersModel::getByCode($code);
		
		/* XLSX START ***************************** */
		require_once '../xreaders/readers/PHPExcel.php';
		$phpexcel = new PHPExcel();
		$page = $phpexcel->setActiveSheetIndex(0);
		$arr_name = array(
				'Артикул',
				'Бренд',
				'Количество',
				'Наименование',
		
				'Срок',
				'Цена закупки',
				'Цена продажи',
				'Ожидаем (дата)',
				'Поставщик (склад)',
		);
		$page->fromArray($arr_name, NULL, 'A1');
		/* XLSX END ***************************** */
		
		$translates = Register::get('translates');
		$forUpdateIds = array();
		$message = '<table width="100%" border="1">';
		$message .= '<tr>';
		$message .= '<td><b>'.$translates['admin.index.name'].'</b></td>';
		$message .= '<td><b>'.$translates['admin.index.brand'].'</b></td>';
		$message .= '<td><b>'.$translates['admin.details.box'].'</b></td>';
		$message .= '<td><b>'.$translates['admin.details.descr'].'</b></td>';
		$message .= '<td><b>Срок</b></td>';
		$message .= '<td><b>Цена закупки</b></td>';
		$message .= '<td><b>Цена продажи</b></td>';
		$message .= '<td><b>Ожидаем (дата)</b></td>';
		$message .= '<td><b>Поставщик</b></td>';
		$message .= '</tr>';
		$i=0; foreach ($imps as $vv){ $i++;
			
			$message .= '<tr>';
			$message .= '<td>'.$vv['article'].'</td>';
			$message .= '<td>'.$vv['brand'].'</td>';
			$message .= '<td>'.$vv['cc'].'</td>';
			$message .= '<td>'.$vv['descr'].' '.$vv['descr_tecdoc'].'</td>';
			$message .= '<td>'.$vv['time_delivery_descr'].' дн.</td>';
			$message .= '<td>'.$vv['price_purchase'].'</td>';
			$message .= '<td>'.$vv['price'].'</td>';
			$message .= '<td>'.date("d.m.Y",$vv['time_delivery_wait_dt']).'</td>';
			$message .= '<td>'.$vv['imp_name'].' ('.$vv['imp_code'].')</td>';
			$message .= '</tr>';
			$forUpdateIds []= $vv['id'];
			
			/* XLSX START ***************************** */
			$item = array(
					$vv['article'],
					$vv['brand'],
					$vv['cc'],
					($vv['descr'].' '.$vv['descr_tecdoc']),
					($vv['time_delivery_descr'].' дн.'),
					$vv['price_purchase'],
					$vv['price'],
					date("d.m.Y",$vv['time_delivery_wait_dt']),
					$vv['imp_name'].' ('.$vv['imp_code'].')',
			);
			$page->fromArray($item, NULL, 'A'.$i);
			/* XLSX END ***************************** */
		}
		$message .= '</table>';
		
		/* XLSX START ***************************** */
		$title = "orderfile_".date("d-m-Y_H-i");
		$page->setTitle($title);
		$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$ffExlx = "../cache/".$title.".xlsx";
		$objWriter->save($ffExlx);
		/* XLSX END ***************************** */
		
		$data = array();
		$data ['list'] = $message;
		$email = SettingsModel::get('contact_email');
		if (EmailsModel::get('importers_message',$data,$getImp['email'],$email,$translates['admin.index.sysorder'],false,$ffExlx)){
			@unlink($ffExlx);
			
			if (count($forUpdateIds)>0)
			$db->post("UPDATE ".DB_PREFIX."cart SET imps_sent='1' WHERE id IN (".join(",", $forUpdateIds).");");
			
			$name = $getImp['name'].' ('.$getImp['code'].') '.$getImp['email'];
			$db->post("INSERT INTO w_importers_sents (`dt`,`message`,`importer`,`imp_code`) VALUES ('".mktime()."','".$message."','".$name."','".$getImp['code']."');");
			
			Logs::addLog(Acl::getAuthedUserId(),'Отправка заявки на заказ позиций поставщику '.$name,URL_NOW);
		}
		$this->redirectUrl("/staffcp/index/crm/#tab-3");
	}
	
	function imps_send_All($redirect=true) {
		
		require_once '../xreaders/readers/PHPExcel.php';
		$db = Register::get('db');
		$translates = Register::get('translates');
		
		$ImpAll = ImportersModel::getAll();
		if (isset($ImpAll) && count($ImpAll)>0){
			$j=0; foreach ($ImpAll as $getImp){ $j++; 
		
				$code = $getImp['code'];
				$imps = BillsModel::imps($code);
				if (count($imps)>0){
					
					/* XLSX START ***************************** */
					$phpexcel = new PHPExcel();
					$page = $phpexcel->setActiveSheetIndex(0);
					$arr_name = array(
							'Артикул',
							'Бренд',
							'Количество',
							'Наименование',
								
							'Срок',
							'Цена закупки',
							'Цена продажи',
							'Ожидаем (дата)',
							'Поставщик (склад)',
					);
					$page->fromArray($arr_name, NULL, 'A1');
					/* XLSX END ***************************** */
					
					$translates = Register::get('translates');
					$forUpdateIds = array();
					$message = '<table width="100%" border="1">';
					$message .= '<tr>';
					$message .= '<td><b>Артикул</b></td>';
					$message .= '<td><b>'.$translates['admin.index.brand'].'</b></td>';
					$message .= '<td><b>'.$translates['admin.details.box'].'</b></td>';
					$message .= '<td><b>'.$translates['admin.details.descr'].'</b></td>';
					
					$message .= '<td><b>Срок</b></td>';
					$message .= '<td><b>Цена закупки</b></td>';
					$message .= '<td><b>Цена продажи</b></td>';
					$message .= '<td><b>Ожидаем (дата)</b></td>';
					$message .= '<td><b>Поставщик</b></td>';
					
					$message .= '</tr>';
					$i=0; foreach ($imps as $vv){ $i++;
						
						$message .= '<tr>';
						$message .= '<td>'.$vv['article'].'</td>';
						$message .= '<td>'.$vv['brand'].'</td>';
						$message .= '<td>'.$vv['cc'].'</td>';
						$message .= '<td>'.$vv['descr'].' '.$vv['descr_tecdoc'].'</td>';
						
						$message .= '<td>'.$vv['time_delivery_descr'].' дн.</td>';
						$message .= '<td>'.$vv['price_purchase'].'</td>';
						$message .= '<td>'.$vv['price'].'</td>';
						$message .= '<td>'.date("d.m.Y",$vv['time_delivery_wait_dt']).'</td>';
						$message .= '<td>'.$vv['imp_name'].' ('.$vv['imp_code'].')</td>';
						
						$message .= '</tr>';
						$forUpdateIds []= $vv['id'];
						
						/* XLSX START ***************************** */
						$item = array(
							$vv['article'],
							$vv['brand'],
							$vv['cc'],
							($vv['descr'].' '.$vv['descr_tecdoc']),
							($vv['time_delivery_descr'].' дн.'),
							$vv['price_purchase'],
							$vv['price'],
							date("d.m.Y",$vv['time_delivery_wait_dt']),
							$vv['imp_name'].' ('.$vv['imp_code'].')',
						);
						$page->fromArray($item, NULL, 'A'.$i);
						/* XLSX END ***************************** */
					}
					$message .= '</table>';
					
					/* XLSX START ***************************** */
					$title = "orderfile_".date("d-m-Y_H-i");
					$page->setTitle($title);
					$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
					$ffExlx = "../cache/".$title."_".$j.".xlsx";
					$objWriter->save($ffExlx);
					/* XLSX END ***************************** */
					
					$data = array();
					$data ['list'] = $message;
					$email = SettingsModel::get('contact_email');
					if (EmailsModel::get('importers_message',$data,$getImp['email'],$email,$translates['admin.index.sysorder'],false,$ffExlx)){
						@unlink($ffExlx);
						
						if (count($forUpdateIds)>0)
							$db->post("UPDATE ".DB_PREFIX."cart SET imps_sent='1' WHERE id IN (".join(",", $forUpdateIds).");");
						
						$name = $getImp['name'].' ('.$getImp['code'].') '.$getImp['email'];
						$db->post("INSERT INTO w_importers_sents (`dt`,`message`,`importer`,`imp_code`) VALUES ('".mktime()."','".$message."','".$name."','".$getImp['code']."');");
						
						Logs::addLog(Acl::getAuthedUserId(),'Отправка заявки на заказ позиций поставщику '.$name,URL_NOW);
					}
				}		
			}
		}
		if ($redirect)
			$this->redirectUrl("/staffcp/index/crm/#tab-3");
	}
	
	function reports(){
		#STATUSES
		$this->getStatusesAll();
	}
	
	function exportorders(){
		$export = $this->request("export",false);
		if ($export){
			
			Logs::addLog(Acl::getAuthedUserId(),'Формирование отчета по статусам всех заказов',URL_NOW);
			
			$search = array();
			$search ['orderby']= ' cb.number DESC ';
			$search ['dt_from']= $export['dt_from'];
			$search ['dt_to']= $export['dt_to'];
			$search ['checked_statuses']= $export['status'];
			$search ['status_new']= $export['status_new'];
			
			$bills = BillsModel::getHistory($search);
			
			require_once '../xreaders/readers/PHPExcel.php';
			$phpexcel = new PHPExcel();
			$page = $phpexcel->setActiveSheetIndex(0);
			
			$arr_name = array(
				'Дата заказа',
				'Номер заказа',
				'ФИО',
				'Телефон',
				'E-mail',
				'Оплата и информация',
				'Доставка',
				'',
				'',
				'',
			);
			$page->fromArray($arr_name, NULL, 'A1');
			
			$i=1;
			$sumPurscheTotal = $sumTotal = $sumTotalDelivery = 0;
			$sumPursche = $sum = $j = $start = $billNumber = $sum =  0; if (isset($bills) && count($bills)>0){
				foreach ($bills as $res){ $j++;
					
					if ($billNumber != $res['bill_number']){
						$start = $sum = $sumPursche = 0;
					}
					
					/* Шапка 1 заказа */
					if ($start == 0){
						$i++;
						$page->fromArray(array(), NULL, 'A'.$i);
						
						$i++;
						$item = array(
							date("d.m.Y H:i",$res['bill_dt']),
							$res['bill_number'],
							$res['bill_f1'],
							$res['bill_f2'],
							$res['bill_f3'],
							strip_tags($res['bill_message']),
							strip_tags($res['bill_delivery']),
							'',
							'',
							'',
						);
						$page->fromArray($item, NULL, 'A'.$i);
						
						$i++;
						$arr_name = array(
							'',
							'',
							'Поставщик',
							'Артикул',
							'Бренд',
							'Описание',
							'Закупка',
							'Цена',
							'Количество',
							'Стоимость',
							'Доход',
						);
						$page->fromArray($arr_name, NULL, 'A'.$i);
					}
					
					
					/* Тело 1 заказа */
					$sum += $res['cc']*$res['price'];
					$sumPursche += $res['cc']*$res['price_purchase'];
					
					$sumTotal += $res['cc']*$res['price'];
					$sumPurscheTotal += $res['cc']*$res['price_purchase'];
					$sumTotalDelivery += $res['cc']*$res['price_purchase'];
					
					$i++;
					$item = array(
						'',
						'',
						$res['imp_name'],
						$res['article'],
						$res['brand'],
						strip_tags($res['descr_tecdoc']),
						PriceHelper::number($res['price_purchase']),
						PriceHelper::number($res['price']),
						$res['cc'],
						PriceHelper::number($res['cc']*$res['price']),
					);
					$page->fromArray($item, NULL, 'A'.$i);
	
					/* Конец 1 заказа */
					if ($res['ccItems'] == $j){
						$sumTotalDelivery += $res['bill_delivery_price'];
						
						$i++;
						$page->fromArray(array('','','','','','','','','Сумма:',PriceHelper::number($sum),''), NULL, 'A'.$i);
						
						$i++;
						$page->fromArray(array('','','','','','','','','Доставка Цена:',PriceHelper::number($res['bill_delivery_price']),''), NULL, 'A'.$i);
						$i++;
						$page->fromArray(array('','','','','','Сумма закупки:',PriceHelper::number($sumPursche),'','Итого:',PriceHelper::number($sum+$res['bill_delivery_price']),PriceHelper::number($sum-$sumPursche)), NULL, 'A'.$i);
						
						$i++;
						$page->fromArray(array(), NULL, 'A'.$i);
						
						$j=$sum=$sumPursche=0;
					}
	
					/* **** */
					$billNumber = $res['bill_number'];
					$start = 1;
				}
				
				$i++;
				$page->fromArray(array('','','','','','','','','Без учета доставки','','Доход'), NULL, 'A'.$i);
				$i++;
				$page->fromArray(array('','','','','','Итого по сумме закупки всех заказов:',PriceHelper::number($sumPurscheTotal),'','Итого по заказам:',PriceHelper::number($sumTotal),PriceHelper::number($sumTotal-$sumPurscheTotal)), NULL, 'A'.$i);
				$i++;
				$page->fromArray(array('','','','','','','','','С учетом доставки:',PriceHelper::number($sumTotalDelivery),''), NULL, 'A'.$i);
			}
			
			$title = "Orders_Export_".date("d-m-Y_H-i");
			$page->setTitle($title);
			$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$objWriter->save("../cache/orders-export-".date("d.m.Y_H.i").".xlsx");
			header("location: /cache/orders-export-".date("d.m.Y_H.i").".xlsx");
			exit;
		}
	}
	
	function exportorderssimplelist(){
		
		$export = $this->request("export",false);
		
		if ($export){
			
			Logs::addLog(Acl::getAuthedUserId(),'Формирование отчета за период ОБЩИЙ',URL_NOW);
			
			$search = array();
			$search ['orderby']= ' cb.number DESC ';
			$search ['done_dt_from']= $export['dt_from'];
			$search ['done_dt_to']= $export['dt_to'];
			$search ['dt_closed']= true;
			if ($this->isManager) {
				
				$office = UsersModel::getById($this->getManagerId());
				$search ['office_id']= $office['office_id'];
			}
			
			require_once '../xreaders/readers/PHPExcel.php';
			$phpexcel = new PHPExcel();
			$page = $phpexcel->setActiveSheetIndex(0);
			
			$currency = SettingsModel::get('currency');
			$currency_eur = SettingsModel::get('currency_eur');
			$currency_usd_eur = SettingsModel::get('currency_usd_eur');
			
			$arr_name = array('Курс доллара: ',$currency);
			$page->fromArray($arr_name, NULL, 'A1');
			
			$arr_name = array('Курс евро: ',$currency_eur);
			$page->fromArray($arr_name, NULL, 'A2');
			
			$arr_name = array('Доллар/Евро: ',$currency_usd_eur);
			$page->fromArray($arr_name, NULL, 'A3');
			
			$arr_name = array(
				'Дата заказа',
				'ФИО',
				'Номер заказа',
				'Поставщик',
				
				'Артикул',
				'Бренд',
				'Описание',
				'Кол-во',
				
				'Цена закупки',
				'Сумма закупки',
				
				'Цена продажи',
				'Сумма продажи',
				
				'Цена доставки',
				'Сумма заказа',
				'Доход',
				'Доход + доставка',
			);
			$page->fromArray($arr_name, NULL, 'A4');
			
			$sum1 = $sum2 = $sum3 = $sum4 = $sum5 = 0;
			$bills = BillsModel::getHistory($search);
			if (isset($bills) && count($bills)>0){
				$j=0; $i=5; $sum=0; foreach ($bills as $key=>$res){
					$j++;
					$sum += $res['cc']*$res['price'];
					$sum_purches += $res['cc']*$res['price_purchase'];
					$priceDelivery = $res['bill_delivery_price'];
					$sum1 += $res['cc']*$res['price_purchase'];
					$sum2 += $res['cc']*$res['price'];
					/* ******************************************* */
					$i++;
					$item = array(
						date("d.m.Y",$res['bill_dt']),
						$res['bill_f1'],
						$res['bill_number'],
						$res['imp_name'],
						$res['article'],
						$res['brand'],
						strip_tags($res['descr_tecdoc']),
						$res['cc'],
						PriceHelper::number($res['price_purchase']),
						PriceHelper::number($res['cc']*$res['price_purchase']),
						PriceHelper::number($res['price']),
						PriceHelper::number($res['cc']*$res['price']),
						(($res['bill_number'] != $bills[$key+1]['bill_number'])?PriceHelper::numberDoc($priceDelivery):''),
						(($res['bill_number'] != $bills[$key+1]['bill_number'])?PriceHelper::numberDoc($priceDelivery+$sum):''),
						(($res['bill_number'] != $bills[$key+1]['bill_number'])?PriceHelper::numberDoc($sum-$sum_purches):''),
						(($res['bill_number'] != $bills[$key+1]['bill_number'])?PriceHelper::numberDoc($priceDelivery+$sum-$sum_purches):''),
					);
					$page->fromArray($item, NULL, 'A'.$i);
					if ($res['bill_number'] != $bills[$key+1]['bill_number']){
						$sum4 += $sum-$sum_purches;
						$sum5 += $priceDelivery+$sum-$sum_purches;
					}
					if ($res['bill_number'] != $bills[$key+1]['bill_number']){
						$sum3 += $priceDelivery+$sum;
						$i++;
						$item = array('','','','','','','','','','','','','','',);
						$page->fromArray($item, NULL, 'A'.$i);
						$sum=$sum_purches=0;
						$priceDelivery = 0;
					}
					
					/* ******************************************* */
				}
			}
			
			$i++;
			$item = array('','','','','','','','','','Итого закупка','','Итого продажа','','Итого','Итого доход','Итого доход + доставка');
			$page->fromArray($item, NULL, 'A'.$i);
			
			$i++;
			$item = array('','','','','','','','','',PriceHelper::numberDoc($sum1),'',PriceHelper::numberDoc($sum2),'',PriceHelper::numberDoc($sum3),PriceHelper::numberDoc($sum4),PriceHelper::numberDoc($sum5));
			$page->fromArray($item, NULL, 'A'.$i);
			
			$title = "Simply_".date("d-m-Y_H-i");
			$page->setTitle($title);
			$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$objWriter->save("../cache/simply-export-".date("d.m.Y_H.i").".xlsx");
			header("location: /cache/simply-export-".date("d.m.Y_H.i").".xlsx");
			exit;
		}
	}
	
	
	
	function exportorderssimplelistpreorder(){
		
		$export = $this->request("export",false);
		if ($export){
			
			Logs::addLog(Acl::getAuthedUserId(),'Формирование отчета за период ПО ЗАКАЗАМ',URL_NOW);
			
			$search = array();
			$search ['orderby']= ' cb.number DESC ';
			$search ['done_dt_from']= $export['dt_from'];
			$search ['done_dt_to']= $export['dt_to'];
			$search ['dt_closed']= true;
			if ($this->isManager) {
				$office = UsersModel::getById($this->getManagerId());
				$search ['office_id']= $office['office_id'];
			}
			
			require_once '../xreaders/readers/PHPExcel.php';
			$phpexcel = new PHPExcel();
			
			$page = $phpexcel->setActiveSheetIndex(0);
			
			$currency = SettingsModel::get('currency');
			$currency_eur = SettingsModel::get('currency_eur');
			$currency_usd_eur = SettingsModel::get('currency_usd_eur');
			
			$currency = SettingsModel::get('currency');
			$currency_eur = SettingsModel::get('currency_eur');
			$currency_usd_eur = SettingsModel::get('currency_usd_eur');
			
			$arr_name = array('Курс доллара: ',$currency);
			$page->fromArray($arr_name, NULL, 'A1');
			
			$arr_name = array('Курс евро: ',$currency_eur);
			$page->fromArray($arr_name, NULL, 'A2');
			
			$arr_name = array('Доллар/Евро: ',$currency_usd_eur);
			$page->fromArray($arr_name, NULL, 'A3');
			
			$arr_name = array(
				'Артикул',
				'Бренд',
				'Описание',
				'Кол-во',
				'Цена закупки',
			);
			$page->fromArray($arr_name, NULL, 'A4');
			
			$bills = BillsModel::getHistory($search);
			if (isset($bills) && count($bills)>0){
				$i=5; foreach ($bills as $key=>$res){
					/* ******************************************* */
					$i++;
					$item = array(
						$res['article'],
						$res['brand'],
						strip_tags($res['descr_tecdoc']),
						$res['cc'],
						PriceHelper::number($res['price_purchase']),
					);
					$page->fromArray($item, NULL, 'A'.$i);
					/* ******************************************* */
				}
			}
			
			$title = "SimplyPre_".date("d-m-Y_H-i");
			$page->setTitle($title);
			$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$objWriter->save("../cache/simply-export-".date("d.m.Y_H.i").".xlsx");
			header("location: /cache/simply-export-".date("d.m.Y_H.i").".xlsx");
			exit;
		}
	}
	
	/* ********************** ORDERS SYSTEM ****************************** */
	
	/* ********************** SHOPPING SYSTEM ****************************** */
	/* PERSON ******************* */
	private function getAccountIdShopping(){
		return isset($_SESSION['shopping']['account_id'])?$_SESSION['shopping']['account_id']:false;
	}
	private function setAccountIdShopping($id){
		$_SESSION['shopping']['account_id']=(int)$id;
	}
	private function unsetAccountIdShopping(){
		unset($_SESSION['shopping']['account_id']);
	}
	/* CAR ********************** */
	private function getCarId(){
		return isset($_SESSION['shopping']['car_id'])?$_SESSION['shopping']['car_id']:false;
	}
	private function setCarId($id){
		$_SESSION['shopping']['car_id']=(int)$id;
	}
	private function unsetCarId(){
		unset($_SESSION['shopping']['car_id']);
	}
	private function getManufacturers(){
		$db = Register::get('db');
		$sql = "SELECT MFA_ID,MFA_BRAND FROM ".DB_PREFIX."manufacturers ORDER BY MFA_BRAND;";
		return $db->query($sql);
	}
	private function getBillData($id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills WHERE id='".(int)$id."';";
		return $db->get($sql);
	}
	private function getAllPersonalCars(){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars WHERE account_id='".(int)$this->getAccountIdShopping()."';";
		return $db->query($sql);
	}
	private function getInfoCarById(){
		$id = $this->getCarId();
		if (!$id)
			return array();
			
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars WHERE id='".(int)$id."';";
		$res = $db->get($sql);
		if (count($res)>0)
			return $res;
		else {
			$this->unsetCarId();
			return array();
		}
	}
	/* XBOX ********************** */
	private function xbox($id){
		$db = Register::get('db');
		$sql = "
			SELECT 
				cart.*,
				(
					SELECT 
						COUNT(cart2.scSID_group) 
					FROM ".DB_PREFIX."cart cart2 
					WHERE 
						cart.scSID_group=cart2.scSID_group AND 
						cart2.scSID='".mysql_real_escape_string($id)."'
				) rowspan
			FROM ".DB_PREFIX."cart cart 
			WHERE cart.scSID='".mysql_real_escape_string($id)."';";
		
		return $db->query($sql);
	}
	private function xboxGetDoneGroups($id){
		$db = Register::get('db');
		$sql = "SELECT DISTINCT scSID_group FROM ".DB_PREFIX."cart WHERE scSID='".mysql_real_escape_string($id)."';";
		return $db->query($sql);
	}
	private function getScSID(){
		return CartModel::get_scSID();
	}
	private function setScSID($scSID){
		CartModel::set_scSID($scSID);
	}
	private function unsetScSID(){
		CartModel::unset_scSID();
	}
	private function getImpSendId(){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."dic_statuses WHERE type='2';";
		$res = $db->get($sql);
		return $res['id'];
	}
	private function getImpSendIdDone(){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."dic_statuses WHERE type='1';";
		$res = $db->get($sql);
		return $res['id'];
	}
	/* ********************** */
	
	function setActiveShop(){
		$_SESSION['shopping']['active']=true;
	}
	function unsetActiveShop(){
		unset($_SESSION['shopping']['active']);
	}
	
	function shopping(){
		$this->layout = "screen";
		
		$this->setActiveShop();
		
		/* ************************************************ */
		$restore_id = $this->request("restore_id",false);
		if ($restore_id){
			$dataO = $this->getTempOrder();
			if ($dataO){
				$this->setAccountIdShopping($dataO['AccountIdShopping']);
				$this->setCarId($dataO['CarId']);
				$this->setInfo((array)json_decode($dataO['Info']));
				$this->setScSID($dataO['ScSID']);
				$_SESSION['shopping']['groups'] = (array)json_decode($dataO['Groups']);
				$this->delete_order_temp();
			}
			$this->redirectUrl('/staffcp/index/shopping/');
		}
		/* ************************************************ */
		
		$account_id = $this->request('account_id',0);
		if ($account_id){
			$this->setAccountIdShopping($account_id);
			$this->setScSID(mktime().'-'.rand(1000,9999));
			$this->redirectUrl('/staffcp/index/shopping/');
		}
		$unset_order = $this->request('unset_order',false);
		if ($unset_order){
			$this->unsetAccountIdShopping();
		}
		$selected_car = $this->request('selected_car',false);
		if ($selected_car){
			$this->setCarId($selected_car);
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Формирование заказа на клиента id:'.$account_id,URL_NOW);
		
		$action = $this->request("action",false);
		if ($action) {
			switch ($action){
				case 'order.temp':
					$this->save_order_to_temp();
					
					$this->unsetAccountIdShopping();
					$this->unsetCarId();
					$this->unsetInfo();
					$this->unsetScSID();
					$this->unsetGroupId();
					$this->unsetGroups();
					$this->setScSID(mktime().'-'.rand(1000,9999));
					$this->unsetActiveShop();
					
					Logs::addLog(Acl::getAuthedUserId(),'Перенос заказа в отложенные заказы',URL_NOW);
					
					$this->redirectUrl('/staffcp/');
					break;
				case 'added_item':
					$this->added_item();
					$this->redirectUrl('/staffcp/index/shopping/');
					break;
				case 'save_car':
					$this->save_car();
					$this->redirectUrl('/staffcp/index/shopping/');
					break;
				case 'delete_car':
					$car_id = $this->request('car_id');
					$this->delete_car($car_id);
					$this->redirectUrl('/staffcp/index/shopping/');
					break;
				case 'unsetcar':
					$this->unsetCarId();
					$this->redirectUrl('/staffcp/index/shopping/');
					break;
				case 'unsetperson':
					$this->unsetAccountIdShopping();
					$this->unsetCarId();
					$this->unsetInfo();
					$this->unsetScSID();
					$this->unsetGroupId();
					$this->unsetGroups();
					$this->setScSID(mktime().'-'.rand(1000,9999));
					$this->unsetActiveShop();
					
					$this->redirectUrl('/staffcp/');
					break;
				case 'save_info':
					$inf = $this->request('info');
					$this->setInfo($inf);
					$this->redirectUrl('/staffcp/index/shopping/');
					break;
				case 'refresh':
					$this->refresh();
					$this->redirectUrl('/staffcp/index/shopping/');
					break;
				case 'bill':
					$returnID = $this->save_order();
					
					$this->unsetAccountIdShopping();
					$this->unsetCarId();
					$this->unsetInfo();
					$this->unsetScSID();
					$this->unsetGroupId();
					$this->unsetGroups();
					$this->setScSID(mktime().'-'.rand(1000,9999));
					$this->unsetActiveShop();
					
					$this->redirectUrl('/staffcp/index/shopping_accept/?id='.$returnID);
					break;
				case 'refreshcar':
					$this->saveinfocar();
					break;
				case 'actionPrepare':
					$this->setscSIDGroup();
					break;
				case 'setgroupid':
					$this->setGroupId();
					break;
			}
		}
		
		$this->view->marks = $this->getManufacturers();
		$this->view->account_info = AccountsModel::getById($this->getAccountIdShopping());
		$this->view->personal_cars = $this->getAllPersonalCars();
		$this->view->selectedCar = $this->getInfoCarById();
		$this->view->xbox = $this->xbox($this->getScSID());
		$this->getStatusesAll();
		$this->view->impId = $this->getImpSendId();
		$this->view->info = $this->getInfo();
		$this->view->scSID = $this->getScSID();
		
		$this->view->GroupsAll = $this->getscSIDGroups();
		$this->view->Groups = $this->getscSIDGroupsIsset();
		$this->view->GroupId = $this->getGroupId();
		
		$this->view->deliveryList = DeliveriesModel::getAll();
		$this->view->ImportersModel = ImportersModel::getAll();
		$this->view->Merchants_listModel = Merchants_listModel::getFull();
		
		$this->render('index/shopping/index');
	}
	
	function shopping_accept(){ 
		$this->view->bill_id = (int)$this->request('id');
		$this->render('index/shopping/shopping_accept'); 
	}
	
	/* ~~~~~~~~~~~~~~~~~~~~ */
	
	private function getTempOrder(){
		$db = Register::get('db');
		$restore_id = $this->request("restore_id",false);
		$sql = "SELECT * FROM ".DB_PREFIX."cart_bills_temp WHERE id = '".(int)$restore_id."';";
		return $db->get($sql);
	}
	
	private function save_order_to_temp(){
		
		$db = Register::get('db');
		
		$data = array();
		$data['AccountIdShopping']= $this->getAccountIdShopping();
		$data['CarId']= json_encode($this->getCarId());
		$getInfo = $this->getInfo();
		$data['Info']= json_encode($getInfo);
		$data['ScSID']= $this->getScSID();
		$data['Groups']= json_encode($this->getscSIDGroupsIsset());
		
		$accountData = array();
		if ($this->getAccountIdShopping()){
			$accountData = AccountsModel::getById($this->getAccountIdShopping());
		}
		else {
			$accountData['name']=$getInfo['f1'];
			$accountData['phones']=$getInfo['f2'];
		}
		
		$db->post("
			INSERT INTO ".DB_PREFIX."cart_bills_temp 
				(`AccountIdShopping`,`CarId`,`Info`,`ScSID`,`Groups`,`save_dt`,`notice`,`name`,`phone`) 
			VALUES 
				(
					'".mysql_real_escape_string($data['AccountIdShopping'])."',
					'".mysql_real_escape_string($data['CarId'])."',
					'".mysql_real_escape_string($data['Info'])."',
					'".mysql_real_escape_string($data['ScSID'])."',
					'".mysql_real_escape_string($data['Groups'])."',
					'".mktime()."',
					'".mysql_real_escape_string($getInfo['notice'])."',
					'".mysql_real_escape_string($accountData['name'])."',
					'".mysql_real_escape_string($accountData['phones'])."'
				)
		;");
	}
	
	private function delete_order_temp(){
		$db = Register::get('db');
		$restore_id = $this->request("restore_id",false);
		$sql = "DELETE FROM ".DB_PREFIX."cart_bills_temp WHERE id = '".(int)$restore_id."';";
		$db->post($sql);
	}
	
	private function added_item(){
		
		$add = $this->request("add",false);
		$get_scSID = $this->getScSID();
		$groupId = $this->getGroupId();
		
		if ($add && $get_scSID){
			
			$count = $add['box'];
			
			$add['price'] = str_replace(" ","",$add['price']);
			$add['price'] = str_replace(",",".",$add['price']);
			
			$add['price_purchase'] = str_replace(" ","",$add['price_purchase']);
			$add['price_purchase'] = str_replace(",",".",$add['price_purchase']);
			
			$db = Register::get('db');
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
						`min`
					) VALUES (
						'{$get_scSID}',
						'".(int)$groupId."',
						'".mktime()."',
								
						'".(int)$add['importer_id']."',
						'".(int)$add['importer_id']."',
						'".(int)$count."',
						'detail',
								
						'".mysql_real_escape_string($add['price'])."',
						'".mysql_real_escape_string($add['price_purchase'])."',
								
						'".mysql_real_escape_string($add['article'])."',
						'".mysql_real_escape_string($add['brand'])."',
						'".mysql_real_escape_string($add['name'])."',
						'".(int)$add['importer_id']."',
						'1'
					);";
			
			$db->post($sql);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление позиции во временный заказ '.$add['brand'].' '.$add['article'],URL_NOW);
		}
	}
	
	private function setscSIDGroup(){
		$scSIDGroupRequest = $this->request("scSIDGroup");
		$scSIDGroup = $scSIDGroupRequest[$this->getScSID()];
		if (isset($scSIDGroup) && count($scSIDGroup)>0){
			unset($_SESSION['shopping']['groups']);
			$i=0; foreach ($scSIDGroup as $group){ $i++;
				$_SESSION['shopping']['groups'][$i] = $group;
			}
		}
	}
	private function getscSIDGroupsIsset(){
		$doneGroups = $this->xboxGetDoneGroups($this->getScSID());
		$inArray = array();
		if (isset($doneGroups)&&count($doneGroups)>0){
			foreach ($doneGroups as $DG){
				$inArray []= $DG['scSID_group'];
			}
		}
		$return = array();
		if (isset($_SESSION['shopping']['groups'])&&count($_SESSION['shopping']['groups'])>0){
			foreach ($_SESSION['shopping']['groups'] as $ID=>$NAME){
				if (!in_array($ID,$inArray)) {
					$return [$ID]= $NAME;
					if (empty($NAME))
						unset($return[$ID]);
				}
			}
		}
		return $return;
	}
	private function getscSIDGroups(){
		return (isset($_SESSION['shopping']['groups'])&&count($_SESSION['shopping']['groups'])>0)?$_SESSION['shopping']['groups']:array();
	}
	private function setGroupId(){
		$groupid = (int)$this->request("groupid");
		if ($groupid){
			$_SESSION['shopping']['group_id']=(int)$groupid;
		}
	}
	private function getGroupId(){
		return (isset($_SESSION['shopping']['group_id']) && $_SESSION['shopping']['group_id'])?$_SESSION['shopping']['group_id']:0;
	}
	private function unsetGroups(){
		unset($_SESSION['shopping']['groups']);
	}
	private function unsetGroupId(){
		unset($_SESSION['shopping']['group_id']);
	}
	/* ~~~~~~~~~~~~~~~~~~~~~ */
	
	private function saveinfocar(){
		if ($this->getCarId()) {
		$db = Register::get('db');
			$carinfo = $this->request("carinfo");
			$db->post("
				UPDATE ".DB_PREFIX."accounts_cars SET
					`car_name` = '".mysql_real_escape_string($carinfo['car_name'])."',
					`car_year` = '".mysql_real_escape_string($carinfo['car_year'])."',
					`car_kpp` = '".(int)$carinfo['car_kpp']."',
					`car_rul` = '".(int)$carinfo['car_rul']."',
					`car_cond` = '".(int)$carinfo['car_cond']."',
					`car_abs` = '".(int)$carinfo['car_abs']."',
					`car_quattro` = '".(int)$carinfo['car_quattro']."',
					`car_body` = '".(int)$carinfo['car_body']."',
					`car_vin` = '".mysql_real_escape_string($carinfo['car_vin'])."',
					`car_info` = '".mysql_real_escape_string($carinfo['car_info'])."'
				WHERE
					id='".(int)$this->getCarId()."';
			");
		}
	}
	
	private function refresh(){
		$db = Register::get('db');
		$xbox = $this->request('xbox');
		$price_purchase = $this->request('price_purchase');
		$price = $this->request('price');
		$count = $this->request('count');
		if (isset($price_purchase)&&count($price_purchase)>0){
			foreach ($price_purchase as $key=>$new_price_purchase){
				
				$inOrder = $xbox[$key];
				if ($inOrder == $key){
					
					$new_price = $price[$key];
					$new_count = $count[$key];
					$db->post("
						UPDATE ".DB_PREFIX."cart SET 
							`count`='".mysql_real_escape_string($new_count)."',
							`price_purchase`='".mysql_real_escape_string($new_price_purchase)."',
							`price`='".mysql_real_escape_string($new_price)."'
						WHERE 
							`id`='".(int)$key."';
						");
					
				} else {
					$db->post("DELETE FROM ".DB_PREFIX."cart WHERE id='".(int)$key."';");
				}
			}
		}
	}
	
	private function save_order(){
		
		$db = Register::get('db');
		$scSID = $this->getScSID();
		if ($scSID){
			
			$items_save_statuses = $this->request('items_save_statuses');
			$xbox = $this->request('xbox');
			
			if (isset($items_save_statuses)&&count($items_save_statuses)>0){
				foreach ($items_save_statuses as $key=>$iss){
					if (isset($xbox[$key]) && $xbox[$key]){
						$db->post("UPDATE ".DB_PREFIX."cart SET `status`='".(int)$iss."' WHERE id='".(int)$key."';");
					}
					else {
						$db->post("DELETE FROM ".DB_PREFIX."cart WHERE id='".(int)$key."';");
					}
				}
			}
			
			$cart_counter = SettingsModel::get('cartcounter');
			$car_id = $this->getCarId();
			$manager_id = $this->getManagerId();
			$account_id = $this->getAccountIdShopping();
			
			if ($account_id){
				$account_info = AccountsModel::getById($account_id);
			}
			else {
				$infoData = $this->getInfo();
				$account_info = array(
					'name'	=>	isset($infoData['f1'])?$infoData['f1']:'',
					'phones'	=>	isset($infoData['f2'])?$infoData['f2']:'',
					'email'	=>	isset($infoData['f3'])?$infoData['f3']:'',
					'message'	=>	isset($infoData['message'])?$infoData['message']:'',
					'address'	=>	isset($infoData['message'])?$infoData['message']:'',
				);
			}
			
			$infoData = $this->getInfo();
			$deliveryInfo = DeliveriesModel::getById($infoData['current_delivery']);
			$info = "Примечание: ".$infoData['info'].'<br>';
			$info .= "Доп.информация: ".$infoData['notice'];
			
			$infoManager = $this->getDataAboutOffice();
			$infoManager = ($this->view->office);
			
			$mktime = mktime();
			$sql = "
				INSERT INTO ".DB_PREFIX."cart_bills 
					(`scSID`,`account_id`,`car_id`,`manager_id`,`office_id`,`dt`,`number`,`delivery`,`delivery_price`,`f1`,`f2`,`f3`,`message`,`time_give_order`,`time_from`,`time_to`,`payment_name`,`delivery_addess`,`md5_hash`)
				VALUES
					(
						'".mysql_real_escape_string($scSID)."',
						'".mysql_real_escape_string($account_id)."',
						'".mysql_real_escape_string($car_id)."',
						'".mysql_real_escape_string($manager_id)."',
						'".(int)$infoManager['office_id']."',
						'".$mktime."',
						'".mysql_real_escape_string($cart_counter)."',
						
						'".mysql_real_escape_string($deliveryInfo['name'])."',
						'".mysql_real_escape_string($deliveryInfo['price'])."',
						
						'".mysql_real_escape_string($account_info['name'])."',
						'".mysql_real_escape_string($account_info['phones'])."',
						'".mysql_real_escape_string($account_info['email'])."',
						'".mysql_real_escape_string($info)."',
						'".mysql_real_escape_string(strtotime($infoData['time_give_order']))."',
								
						'".mysql_real_escape_string($infoData['time_from'])."',
						'".mysql_real_escape_string($infoData['time_to'])."',
						'".mysql_real_escape_string($infoData['current_payment'])."',
						'".mysql_real_escape_string($account_info['address'])."',
						'".mysql_real_escape_string(md5("o.".$cart_counter))."'
					);
				";
			$db->post($sql);
			$returnID = $db->lastInsertId();
			
			//$this->imps_send_All(false);
			
			/* NOTICE EMAIL * ****************************************** */
			
			$elements = CartModel::get($scSID);
			$delivery = $deliveryInfo;
			$merchant_type = "нет";
			$site = $_SERVER['SERVER_NAME'];
			
			$list = '<table cellpadding="10" cellspacing="0" border="0">';
			$list .= '<tr>';
			$list .= '<td><b>Наименование</b></td>';
			$list .= '<td><b>Цена</b></td>';
			$list .= '<td><b>Кол-во</b></td>';
			$list .= '<td><b>Стоимость</b></td>';
			$list .= '</tr>';
			$sum=0;
			$i=0;
			foreach ($elements as $dd){
			$i++;
				$colors = ($i%2)?"#f1f1f1":"";
				$list .= '<tr bgcolor="'.$colors.'">';
				$list .= '<td>'.$dd['name'].' '.$dd['brand'].' '.$dd['descr'].'</td>';
				$list .= '<td>'.PriceHelper::number($dd['price']).'</td>';
				$list .= '<td>'.$dd['cc'].'</td>';
				$list .= '<td>'.PriceHelper::number($dd['cc']*$dd['price']).'</td>';
				$list .= '</tr>';
				$sum += ($dd['cc']*$dd['price']);
			}
			if ($delivery){
				$list .= '<tr><td colspan="4" align="right"><b>Доставка</b>: '.PriceHelper::number($delivery['price']).'</td></tr>';
				$list .= '<tr><td colspan="4" align="right"><b>Сумма</b>: '.PriceHelper::number(($sum+$delivery['price'])).'</td></tr>';
			}
			else {
				$list .= '<tr><td colspan="4" align="right"><b>Сумма</b>: '.PriceHelper::number($sum).'</td></tr>';	
			}
			$list .= '</table>';
			
			$list .= '<h1>Доставка и оплата</h1>';
			$list .= '<p><strong>Доставка:</strong> '.$delivery['name'].'. Стоимость: '.PriceHelper::number($delivery['price']).'</p>';
			$list .= '<p><strong>Оплата:</strong> '.$merchant_type.'.</p>';
			
			$auto = AccountsModel::getByIdCarInfo($car_id);
			if (isset($auto) && count($auto)>0){
			$list .= '<h1>Автомобиль</h1>';
			$list .= '<table cellpadding="5" cellspacing="0" border="0">';
			$list .= '<tr bgcolor="#f1f1f1">';
				$list .= '<td><b>Марка / Модель / Тип / Год / Объем / Л.сил</b></td>';
				$list .= '<td>'.$auto['car_name'].'</td>';
			$list .= '</tr>';
			$list .= '<tr>';
				$list .= '<td><b>Точный год</b></td>';
				$list .= '<td>'.$auto['car_year'].'</td>';
			$list .= '</tr>';
			$list .= '<tr bgcolor="#f1f1f1">';
				$list .= '<td><b>КПП</b></td>';
				$list .= '<td>'.$auto['car_kpp'].'</td>';
			$list .= '</tr>';
			$list .= '<tr>';
				$list .= '<td><b>Усилитель руля</b></td>';
				$list .= '<td>'.$auto['car_rul'].'</td>';
			$list .= '</tr>';
			$list .= '<tr bgcolor="#f1f1f1">';
				$list .= '<td><b>Кондиционер</b></td>';
				$list .= '<td>'.(($auto['car_cond'])?'Да':'Нет').'</td>';
			$list .= '</tr>';
			$list .= '<tr>';
				$list .= '<td><b>ABS</b></td>';
				$list .= '<td>'.(($auto['car_abs'])?'Да':'Нет').'</td>';
			$list .= '</tr>';
			$list .= '<tr bgcolor="#f1f1f1">';
				$list .= '<td><b>Полный привод</b></td>';
				$list .= '<td>'.(($auto['car_quattro'])?'Да':'Нет').'</td>';
			$list .= '</tr>';
			$list .= '<tr>';
				$list .= '<td><b>Кузов</b></td>';
				$list .= '<td>'.($auto['car_body']).'</td>';
			$list .= '</tr>';
			$list .= '<tr bgcolor="#f1f1f1">';
				$list .= '<td><b>VIN</b></td>';
				$list .= '<td>'.($auto['car_vin']).'</td>';
			$list .= '</tr>';
			$list .= '<tr>';
				$list .= '<td><b>Дополнительно</b></td>';
				$list .= '<td>'.($auto['car_info']).'</td>';
			$list .= '</tr>';
			$list .= '</table>';
			}
			
			$form = array();
			
			$form ['name'] = $account_info['name'];
			$form ['phone'] = $account_info['phones'];
			$form ['email'] = $account_info['email'];
			
			$form ['date'] = date("d.m.Y H:i:s",$mktime);
			$form ['order_numer'] = $cart_counter;
			$form ['order']	= $list;
			$form ['delivery']= $delivery;
			$form ['message']= $account_info['address'].' / '.$infoData['info'];
			$form ['url']= '<a href="http://'.$site.'/cart/md5/key/'.md5("o.".$cart_counter).'" target="_blank">http://'.$site.'/cart/md5/key/'.md5("o.".$cart_counter).'</a>';
			$form ['sitename'] = $site;
			$email = SettingsModel::get('contact_email');
			EmailsModel::get('cart',$form,$account_info['email'],$email,'Заказ №'.$cart_counter.' ('.$site.')',false);
			
			Logs::addLog(Acl::getAuthedUserId(),'Сохранение заказа #'.$cart_counter,URL_NOW);
			
			/* NOTICE EMAIL * ****************************************** */
			
			/*
			#Установка позиций в выполненные
			$idDone = $this->getImpSendIdDone();
			reset($items_save_statuses);
			if (isset($items_save_statuses)&&count($items_save_statuses)>0){
				foreach ($items_save_statuses as $key=>$iss){
					if (isset($xbox[$key]) && $xbox[$key]){
						$db->post("UPDATE ".DB_PREFIX."cart SET `status`='".(int)$idDone."' WHERE id='".(int)$key."';");
					}
				}
			}
			*/
			
			$db->query("update ".DB_PREFIX."settings set value=value+1 where code='cartcounter';");
			
			return $cart_counter;
		}
		else {
			die('<pre>ERROR! Временный ключ истек для создания заказа, необходимо пересоздать заказ!</pre>');
		}
	}
	
	private function setInfo($data){
		$_SESSION['shopping']['info']=$data;
	}
	private function getInfo(){
		return isset($_SESSION['shopping']['info'])?$_SESSION['shopping']['info']:array();
	}
	private function unsetInfo(){
		unset($_SESSION['shopping']['info']);
	}
	
	private function save_car(){
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
				`car_info`
			)
		VALUES
			(
				'".(int)$this->getAccountIdShopping()."',
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
				'".mysql_real_escape_string($car['car_info'])."'
			)
		;";
		$db->post($sql);
		
		Logs::addLog(Acl::getAuthedUserId(),'Добавление автомобиля для клиента '.$car['mark'].' '.$car['model'].' '.$car['type'],URL_NOW);
	}
	private function delete_car($id){
		$db = Register::get('db');
		$sql = "DELETE FROM ".DB_PREFIX."accounts_cars WHERE id='".(int)$id."';";
		$db->post($sql);
		
		Logs::addLog(Acl::getAuthedUserId(),'Удаление автомобиля клиента id:'.$id,URL_NOW);
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
		$this->render('index/shopping/ajax_models');
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
		$this->render('index/shopping/ajax_types');
	}
	/* ********************** SHOPPING SYSTEM END ****************************** */
	
	/* ********************** PRINTING SYSTEM ****************************** */
	
	function printbill(){
		$this->layout = "print";
		$translates = Register::get('translates');
		$db = Register::get('db');
		
		$id = $this->request("id",false);
		$bill = BillsModel::fetchByIdBillNumber($id);
		
		$this->view->bill = $bill;
		$this->view->auto = AccountsModel::getByIdCarInfo($bill['car_id']);
		$this->view->scSID = BillsModel::getHistory(array("scSID"=>$bill['scSID'],"is_payback"=>1));
		
		$this->view->logo = SettingsModel::get('logo');
		
		Logs::addLog(Acl::getAuthedUserId(),'Распечатка заказа #'.$bill['number'],URL_NOW);
		$this->render('index/crm/printbill');
	}
	
	/* ********************** PRINTING SYSTEM END ****************************** */
	
	
	/* ********************** FAST ORDER SYSTEM ****************************** */
	
	public function fastorder(){
		$this->layout = "ajax";
		
		$action = $this->request("action",false);
		if ($action == 'confirm'){
			$db = Register::get('db');
			
			$scSID = mktime().'-'.rand(1000,9999);
			
			$add = $this->request("add",false);
			
			$contact = $this->request("contact",false);
			$name = $contact['name'];
			$phone = $contact['phone'];
			$email = $contact['email'];
			$message = $contact['message'];
			
			$current_delivery = $contact['current_delivery'];
			$current_payment = $contact['current_payment'];
			
			$cart_counter = SettingsModel::get('cartcounter');
			
			if (isset($add['importer_id']) && count($add['importer_id'])>0){
				
				/* * */
				foreach ($add['importer_id'] as $key=>$importer_id){
					
					$brand = $add['brand'][$key];
					$article = $add['article'][$key];
					$item_name = $add['name'][$key];
					$count = $add['count'][$key];
					$price = str_replace(",",".",$add['price'][$key]);
					$price_purchase = str_replace(",",".",$add['price_purchase'][$key]);
					
					$db->post("
						INSERT INTO ".DB_PREFIX."cart
						(`scSID`,`createDT`,`fk`,`wbs_id`,`count`,`type`,`price`,`price_purchase`,`article`,`brand`,`descr_tecdoc`,`import_id`)
						VALUES
						('".mysql_real_escape_string($scSID)."','".mktime()."','".(int)$importer_id."','".(int)$importer_id."','".(int)$count."','detail','".mysql_real_escape_string($price)."','".mysql_real_escape_string($price_purchase)."','".mysql_real_escape_string($article)."','".mysql_real_escape_string($brand)."','".mysql_real_escape_string($item_name)."','".(int)$importer_id."');
					");
				}
				/* * */
				
				$paymenyArr = Merchants_listModel::getById($current_payment);
				$paymeny_name = $paymenyArr['name'];
				
				$deliveryArr = DeliveriesModel::getById($current_delivery);
				$delivery_name = $deliveryArr['name'];
				$delivery_price = $deliveryArr['price'];
				
				$db->post("
					INSERT INTO ".DB_PREFIX."cart_bills
					(`scSID`,`dt`,`number`,`f1`,`f2`,`f3`,`message`,`delivery`,`delivery_price`,`md5_hash`,`payment_name`)
					VALUES
					(
						'".$scSID."',
						'".$scSID."',
						'".(int)$cart_counter."',
						'".mysql_real_escape_string($name)."',
						'".mysql_real_escape_string($phone)."',
						'".mysql_real_escape_string($email)."',
						'".mysql_real_escape_string($message)."',
						'".mysql_real_escape_string($delivery_name)."',
						'".mysql_real_escape_string($delivery_price)."',
						MD5('o.".(int)$cart_counter."'),
						'".mysql_real_escape_string($paymeny_name)."'
					);
				");
				
				$db->query("UPDATE ".DB_PREFIX."settings SET value=value+1 WHERE code='cartcounter';");

				Logs::addLog(Acl::getAuthedUserId(),'Создание быстрого заказа #'.$cart_counter,URL_NOW);
				
				$this->redirectUrl('/staffcp/index/crm/?search[number]='.$cart_counter);
			}
		}
		/* ************************** */
		
		$this->view->deliveryList = DeliveriesModel::getAll();
		$this->view->ImportersModel = ImportersModel::getAll();
		$this->view->Merchants_listModel = Merchants_listModel::getFull();
		
		$db = Register::get('db');
		$sql = "SELECT BRA_ID_GET,BRA_BRAND FROM ".DB_PREFIX."brands ORDER BY BRA_BRAND;";
		$this->view->brandsList = $db->query($sql);
		
		$this->render('index/fastorder/index');
	}
	
	/* ********************** FAST ORDER SYSTEM END ****************************** */
	
	public function bank_nbrb(){
		$this->layout = "ajax";
	}
	public function bank_cbrf(){
		$this->layout = "ajax";
	}
	public function bank_nbu(){
		$this->layout = "ajax";
	}
	public function bank_kz(){
		$this->layout = "ajax";
	}
	
	/* ********************** SIMPLE CHECKBOX SET VALUE ****************************** */
	
	public function setvalueajax(){
		$table = $this->request("table",false);
		
		$id = $this->request("id",false);
		$indexid = $this->request("indexid",false);
		
		$field = $this->request("field",false);
		$value = (int)$this->request("value",0);
		
		$db = Register::get('db');
		if ($table && $field && $indexid && $id){
			$sql = "
				UPDATE ".mysql_real_escape_string($table)." 
				SET `".mysql_real_escape_string($field)."` = '".mysql_real_escape_string($value)."' 
				WHERE ".mysql_real_escape_string($indexid)." = '".mysql_real_escape_string($id)."'
			;";
			$db->post($sql);
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Обновление данных в таблице '.$table.' поля '.$field,URL_NOW);
		
		exit();
	}
	
	function error404($exp=''){}
}
?>