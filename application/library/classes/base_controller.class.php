<?php

class BaseController extends Controller {

	public $accountRate = 0;
	public $accountCurrency = null;
	public $accountData = array();
	
	public $curMoney = 'BYR';
	public $lang = LANG;
	public $breadcrumbs = array('Home' => '/');
	public $layout = 'home';
	
	private $mtime = 0;
	private $priceSort = 'article';
	private $deliverytimeSort = false;
	
	public $iaccess = false;
	public $iaccess_listAccess = array();
	public $iaccess_listAccessCodes = array();
	
	public $activeCountBills = 0;
	
	public $scSIDInstall = '';
	
	function __construct() {
		// $this->set_zone_install();
		$this->scSIDInstall = time().'-'.rand(1000,9999);
		$this->mtime = microtime();
		
		// if (isset($_REQUEST['mobile_disable'])){
			// $_SESSION['mobile_disable'] = true;
		// }
		
		// if (!isset($_SESSION['mobile_disable']))
			// $this->mobile();
		
		if (isset($_SESSION['simpleview']) || isset($_GET['simpleview'])) {
			$this->layout = "simple";
		}
	}
	
	/***************************************************************************/
	function setCurMoney($curr='EUR'){
		$_SESSION['__cur_money']=$curr;
	}
	function getCurMoney(){
		if ($this->accountCurrency){
			return $this->accountCurrency;
		}
		return (isset($_SESSION['__cur_money']) && $_SESSION['__cur_money'])?$_SESSION['__cur_money']:$this->curMoney;
	}
	function getExchangeMoney(){
		$rates = Register::get('rates');
		$rate = $rates[$this->getCurMoney()];
		if ($rate)
			return $rate;
		return 1;
	}
	/***************************************************************************/
	function setPriceSort($set=''){
		switch ($set){
			case 'min': $set = 'min'; break;
			case 'max': $set = 'max'; break;
			case 'delivery': $set = 'delivery'; break;
			case 'article': $set = 'article'; break;
			case 'brand': $set = 'brand'; break;
			default: $set = 'article'; break;
		}
		$_SESSION['__price_sort']=$set;
	}
	function getPriceSort(){
		if (isset($_REQUEST['sort']) && $_REQUEST['sort']) {
			return $_REQUEST['sort'];
		}
		return isset($_SESSION['__price_sort'])?$_SESSION['__price_sort']:$this->priceSort;
	}
	function setDeliveryTime($set=''){
		switch ($set){
			case '1': $set = 1; break;
			case '3': $set = 3; break;
			case '5': $set = 5; break;
			case '8': $set = 8; break;
			case '10': $set = 10; break;
			default: $set = false; break;
		}
		$_SESSION['__deliverytime_sort']=$set;
	}
	function getDeliveryTime(){
		return isset($_SESSION['__deliverytime_sort'])?$_SESSION['__deliverytime_sort']:$this->deliverytimeSort;
	}
	/***************************************************************************/
	
	function getTempListArticles(){
		return (isset($_SESSION['_temp_list_articles']) && count($_SESSION['_temp_list_articles']))?array_unique($_SESSION['_temp_list_articles']):array();
	}
	function setTempArticle($article=false){
		//unset($_SESSION['_temp_list_articles']);
		if ($article){
			$_SESSION['_temp_list_articles'][]= strtoupper(FuncModel::stringfilter($article));
		}
	}
	
	/***************************************************************************/
	
	public function beforeAction(){
		
		$this->view->_controller = $this->controller;
		$this->view->_action = $this->action;
		$this->view->_seo = SeoModel::getById($this->controller);
		
		/* ******************* SCSID ******************* */
		$get_scSID = CartModel::get_scSID();
		if (!$get_scSID){
			CartModel::set_scSID(CartModel::findNewScSID($this->scSIDInstall));
		}
		
		/* ******************* ACL ******************* */
		$this->getAccountIdShopping();
		$this->getCarId();
		$userId = Acl::getAuthedUserId();
		$acl = new Acl($userId);
		$this->acl = $acl;
		$this->view->acl = $acl->isSuper;
		$this->view->shopping_person = $this->shopping_person;
		$this->view->shopping_car = $this->shopping_car;
		
		/* purch MARGIN manager/admin */
		if (isset($acl->purchase_margin) && $acl->purchase_margin){
			Register::set("purchase_margin",str_replace(",",".",$acl->purchase_margin));
		} else {
			Register::set("purchase_margin",false);
		}
		
		/* ******************* ACCOUNT ******************* */
		if ($this->acl && $this->shopping_person) {
			$accountFetchid = $this->shopping_person;
		}
		else {
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		}
		if ($accountFetchid) {
				
			/* ACCOUNT DATA */
			$account = AccountsModel::getById($accountFetchid);
			$this->accountData = $account;
			// print("<pre>");
// print_r($account["name"]);
			// $this->accountCurrency = $account['currencyCode'];
			// $this->accountRate = ($account['currencyRate'])?$account['currencyRate']:1;
			$this->view->account = $this->accountData;
			
			Register::set('account',$this->accountData);
			// Register::set('accountRate',$this->accountRate);
				
			/* Список заказов */
			$this->view->totalOrders = AccountsModel::getCountActiveLost($accountFetchid,true,true);
			$billsCC = AccountsModel::getCountActiveLost($accountFetchid);
			$this->view->bills = $billsCC;
			$this->activeCountBills = $billsCC;
				
			/* Функция мой склад */
			$activatedWH = $this->activatedWarehouse();
			$this->view->activatedWH = $activatedWH;
			if ($activatedWH){
				/* Список цен для фунции мой склад */
				$cc = $this->getMyDetailsCount($accountFetchid);
				$this->view->mydetailsCC = $cc['CC'];
				/* Список закаов для функции мой склад */
				$this->view->myorderWH = $this->getOrdersForMe($accountFetchid);
			}
				
			$this->view->alerts_vins = $this->getAlerts($account['id'],'vin');
			$this->view->alerts_statuses = $this->getAlerts($account['id'],'status');
				
			Register::set("mode_view_articles",0);
			if ($this->accountData['fields_importer'])
				Register::set("mode_view_fields_importer", $this->accountData['fields_importer']);
			else 
				Register::set("mode_view_fields_importer", SettingsModel::get('mode_view_fields_importer'));
		}
		else {
			
			Register::set("mode_view_articles", SettingsModel::get('mode_view_articles'));
			Register::set("mode_view_fields_importer", SettingsModel::get('mode_view_fields_importer'));
		}
		
		/* unset cache data from search request */
		if (!in_array($this->controller,array('search'))){
			unset($_SESSION['__cached_search_result']);
		}
		
		/* temp articles set & get */
		$this->setTempArticle(isset($_REQUEST['article'])?$_REQUEST['article']:false);
		$getTempListArticles = $this->getTempListArticles();
		$this->view->getTempListArticles = $getTempListArticles;

		/* ********************************************************** */
		/* Simple View For Add item in cart by adminmode */
		$this->simplesearcher();
		
		/* ********************************************************** */
		/* Rounds for moneys result */
		// $this->getCurrenciesRound();
		
		/* ********************************************************** */
		// $set_money = (isset($_REQUEST['set_money']) && $_REQUEST['set_money'])?$_REQUEST['set_money']:false;
		// if ($set_money) {
			// $this->setCurMoney($set_money);
			// $this->redirectUrl($_SERVER['HTTP_REFERER']);
		// }
		// Register::set("curExchange",$this->getExchangeMoney());
		// Register::set("curExchange","");
		// Register::set("curExchangeType",$this->getCurMoney());
		// $this->view->curExchangeType = ($set_money)?$set_money:$this->getCurMoney();
		
		/* Сортировка цен */
		$sort = $this->request("sort",false);
		$sSortPriceView = ($sort)?$sort:$this->getPriceSort();
		$this->view->sSortPriceView = $sSortPriceView;
		Register::set('sSortPriceView',$sSortPriceView);
		
		/* Сортировка поставки */
		$time = $this->request("time",false);
		$this->view->sSortDeliveryView = ($time)?$time:$this->getDeliveryTime();
		
		// $this->view->banner = BannernetworkModel::viewBanner(1);
		// $this->view->banner3 = BannernetworkModel::viewBanner(3);
		// $this->view->banner4 = BannernetworkModel::viewBanner(4);
		// $this->view->banner5 = BannernetworkModel::viewBanner(5);
		// $this->view->banner6 = BannernetworkModel::viewBanner(6);
		
		/* ******************* OFFICE ******************* */
			/*	
		if (isset($_REQUEST['city-disagree'])){
			$_SESSION['city_geo_status_disable'] = true;
		}
		
		$officeSetId = false;
		$this->view->select_geo_office = true;
		if (!isset($_SESSION['city_geo_status_disable'])){
			if (!$this->getOfficeId()){
			
				require_once('extensions/sxGeo/SxGeo.php');
				$SxGeo = new SxGeo('extensions/sxGeo/SxGeoCity.dat');
				$geoData = $SxGeo->getCityFull($_SERVER['REMOTE_ADDR']);
				
				$cityId = 0;
				if (isset($geoData['city']['name_ru'])){
					
					$this->view->geo_country = $geoData['country']['iso'];
					$this->view->geo_region = $geoData['region']['name_ru'];
					$this->view->geo_city = $geoData['city']['name_ru'];
					
					if (isset($_REQUEST['city-agree'])){
						
						$cityId = Dic_citiesModel::find($geoData['city']['name_ru']);
						$officeSetId = OfficesModel::getAllByCityId($cityId);
						$officeSetId = current($officeSetId);
						$officeSetId = $officeSetId['id'];
						
						if (!$officeSetId){
							$officeSetId = OfficesModel::getDefaultOfficeId();
							$officeSetId = $officeSetId['id'];
						}
						
						$_SESSION['city_geo_status_disable'] = true;
						$this->view->select_geo_office = false;
					} else {
						
						$cityId = Dic_citiesModel::find($geoData['city']['name_ru']);
						$checkOfficeIsset = OfficesModel::getAllByCityId($cityId);
						$checkOfficeIsset = current($checkOfficeIsset);
						if (!$officeSetId){
							$this->view->select_geo_office = false;
						}
					}
				}
			} else {
				$this->view->select_geo_office = false;
			}
		} else {
			$this->view->select_geo_office = false;
		}
		
		$office_id = $this->request("office_id",$officeSetId);
		if ($office_id){
			$this->setOfficeId($office_id);
		}
		
		$officeunset = $this->request("office-unset",false);
		if ($officeunset){
			$this->unsetOfficeId();
		}
		Register::set("getOfficeIdParam",$this->getOfficeId());
		
		$this->view->cityIsset = $this->getCityIsset();
		$officeIsset = $this->getOfficeIsset();
		$this->view->officeIsset = $officeIsset;
		$this->view->officesHome = $this->view->offices = $this->getAllOffices();
		$this->getOffices();
		
		$iAccess = false;
		$this->view->acl_isset_shopping = false;
		$this->view->acl_isset = false;
		if ($this->acl && $this->shopping_person) {
			$this->view->acl_isset_shopping = true;
			$this->view->acl_isset = true;
			// $this->view->selectedCar = $this->getInfoCarById();
			$this->view->account_info = AccountsModel::getById($this->shopping_person);
		}
		elseif ($this->acl && $this->getShopping()){
			$this->view->acl_isset_shopping = true;
			$this->view->acl_isset = true;
			Register::set("mode_view_articles",0);
		}
		elseif (isset($account['purchase_active']) && $account['purchase_active'] == 1){
			$this->view->acl_isset = true;
			$iAccess = true;
		}
		else {
			$iAccess = true;
		}
		
		$this->view->hide_left_menu = false;
		if ($this->view->acl_isset && $this->view->acl_isset_shopping){
			$this->view->hide_left_menu = true;
		}
		
		if ($iAccess) {
			if (isset($this->accountData) && count($this->accountData)>0){
				if (isset($this->accountData['is_limit_active']) && $this->accountData['is_limit_active']) {
					$this->iaccess = true;
					$this->iaccess_listAccess = AccountsModel::getIAccessLimits($this->accountData['id']);
					$this->iaccess_listAccessCodes = AccountsModel::getIAccessLimitsCodes($this->iaccess_listAccess);
				}
				elseif (isset($this->accountData['dis_limit_active']) && $this->accountData['dis_limit_active']) {
					$this->iaccess = true;
					$this->iaccess_listAccess = AccountsModel::getIAccessLimitsDiscountsnames($this->accountData['discountname_id']);
					$this->iaccess_listAccessCodes = AccountsModel::getIAccessLimitsCodes($this->iaccess_listAccess);
				}
				elseif (isset($this->accountData['office_id']) && $this->accountData['office_id']){
					if(isset($officeIsset['is_limit_active']) && $officeIsset['is_limit_active']){
						$this->iaccess = true;
						$this->iaccess_listAccess = AccountsModel::getIAccessLimitsOffices($this->accountData['office_id']);
						$this->iaccess_listAccessCodes = AccountsModel::getIAccessLimitsCodes($this->iaccess_listAccess);
					}
				}
			}
			elseif (isset($officeIsset['is_limit_active']) && $officeIsset['is_limit_active']){
				$this->iaccess = true;
				$this->iaccess_listAccess = AccountsModel::getIAccessLimitsOffices($officeIsset['id']);
				$this->iaccess_listAccessCodes = AccountsModel::getIAccessLimitsCodes($this->iaccess_listAccess);
			}
			else{
				$this->iaccess = true;
				$this->iaccess_listAccess = ImportersModel::getUnsignedAccounts('id');
				$this->iaccess_listAccessCodes = ImportersModel::getUnsignedAccounts('code');
			}
		}
		*/
		/* ********************************************************************* */
		/* ********************************************************************* */
		
		/* shopping cart */
		$get_scSID = CartModel::get_scSID();
		$this->view->xcart = CartModel::xbox($get_scSID);
		$this->view->xcarttotalsum = CartModel::xboxTotalSum($get_scSID);
		
		/* catalog */
		$this->view->catalog_main = CatModel::getFirstLevel();
		$this->view->catalog_homeview = CatModel::getHomeView();
		
		/* application */
		$this->view->manufacturers = ManufacturersModel::getAll();
		$this->view->informations = PageModel::getAllList();

		/* settings */
		$settings = SettingsModel::getParams(array(
				// 'currency','currency_eur','currency_rur','currency_usd_eur','analytics','welcome','logo','footer','header',
				// 'favicon','left_code','header_contacts','set_width_percent','set_width_percent','color_header_ahover_other',
				// 'color_cart_border','color_content_a','header_img','switch_on_off_shop','switch_on_off_shop_msg',
				// 'background_img_repeat','background_img_static','color_item_border','color_item_bg','color_item_bg_search_buttons',
				// 'color_bhead','color_blnk','footer_info','contacts'
				'contact_email', 'aboutus', 'contacts', 'welcome', 'analytics', 'header', 'header_contacts', 'switch_on_off_shop_msg', 'show_zero_prices', 'registration_confirm',
		));
		if (isset($settings)&&count($settings)>0){
			foreach ($settings as $set){
				$this->view->$set['code'] = $set['value'];
			}
		}
		/*
		$settings = SettingsModel::getParams(
			array(
				'pview_calc','pview_news','pview_articles','pview_faq','pview_pricelist','pview_brands','pview_maps',
				'pview_vins','pview_currency','pview_head','pview_footer','pview_office',
				'pview_polmostrow','pview_indexproducts',
			)
		);
		if (isset($settings)&&count($settings)>0){
			foreach ($settings as $set){
				$this->view->$set['code'] = $set['value'];
			}
		}
		Register::set("view-prices-type",SettingsModel::get('view-prices-type'));
		*/
		/* go on the site when is disable */
		if (isset($_REQUEST['close'])){
			$_SESSION['_disable_off_shop'] = true;
		}
		if (isset($_SESSION['_disable_off_shop']) && $_SESSION['_disable_off_shop']){
			$this->view->switch_on_off_shop = false;
		}
		
		/* CALLME */
		// $this->view->callme_template = CallmeModel::get('template');
		
		/* SOCIAL ONLINE */
		// $this->social_access();
			
		/* LANGS */
		$this->issetLang();
		
		// Simple View Template disable Feedback Call
		$this->view->feedbackCaller = (isset($_SESSION['simpleview']))?false:true;
		
		//var_dump($this->layout);
	}
	public function beforeRender(){
		global $application;
		$this->view->bread = $this->getBreadcrumbs();
		$this->view->queriesDebug = $this->queriesDebug();
		$this->view->totalTime = $this->totaltime();
	}
	function setPathDirect(){
		$_SESSION['path_direct'] = str_replace("&ajax=true", "", $_SERVER['REQUEST_URI']);
	}
	function totaltime() {
		$mtime = explode(" ",$this->mtime);
		$mtime = (isset($mtime[1])?$mtime[1]:0) + $mtime[0];
		$tstart = $mtime;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = (isset($mtime[1])?$mtime[1]:0) + $mtime[0];
		$tend = $mtime;
		return number_format(($tend-$tstart),5,'.','');
	}	
	function getBreadcrumbs(){
		$content = '';
		$count = count($this->breadcrumbs);
		if ($count == 1)
			return '';
		$i = 1;
		foreach ($this->breadcrumbs as $title => $url){
			$i++;
			if ($i <= $count)
				$content .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'.$url.'" itemprop="url"><span itemprop="title">'.$title.'</span></a> &rarr; </span>';
			else 
				$content .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'.$_SERVER['REQUEST_URI'].'" itemprop="url"><span itemprop="title">'.$title.'</span></a></span>';
		}
		$content .= '';
		return $content;
	}
	public function mobile(){
		if (IS_MOBILE){
			if (!isset($_GET['ui'])){
				$iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
				$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
				$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
				$berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
				$ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
				$mobile = strpos($_SERVER['HTTP_USER_AGENT'],"Mobile");
				$symb = strpos($_SERVER['HTTP_USER_AGENT'],"Symbian");
				$operam = strpos($_SERVER['HTTP_USER_AGENT'],"Opera M");
				$htc = strpos($_SERVER['HTTP_USER_AGENT'],"HTC_");
				$fennec = strpos($_SERVER['HTTP_USER_AGENT'],"Fennec");
				$winphone = strpos($_SERVER['HTTP_USER_AGENT'],"WindowsPhone");
				$wp7 = strpos($_SERVER['HTTP_USER_AGENT'],"WP7");
				$wp8 = strpos($_SERVER['HTTP_USER_AGENT'],"WP8");
		
				$ipad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
				if (!$ipad) {
					if ($iphone || $android || $palmpre || $ipod || $berry || $mobile || $symb || $operam || $htc || $fennec || $winphone || $wp7 || $wp8 === true) {
						header("location: http://".$_SERVER['SERVER_NAME'].'?ui=mobile');
						exit();
					}
				}
			}
		}
	}
	
	/* ******************************************************************* */
	/* ACCOUNT FUNCTIONS */
	private function getAlerts($account_id=0,$type=false){
		if ($account_id && $type) {
			$db = Register::get('db');
			$sql = "SELECT COUNT(*) CC FROM `".DB_PREFIX."accounts_alerts` WHERE `account_id`='".(int)$account_id."' AND `type`='".mysql_real_escape_string($type)."';";
			$res = $db->get($sql);
			return $res['CC'];
		}
		return false;
	}
	public function unsetAlerts($account_id=0,$type=false){
		if ($account_id && $type) {
			$db = Register::get('db');
			$sql = "DELETE FROM `".DB_PREFIX."accounts_alerts` WHERE `account_id`='".(int)$account_id."' AND `type`='".mysql_real_escape_string($type)."';";
			$db->post($sql);
		}
	}
	function activatedWarehouse(){
		#1 - deactivated
		#0 - activated
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$account = AccountsModel::getById($accountFetchid);
		if ($account['is_active_warehouse'])
			return true;
		else 
			return false;
	}
	function getMyDetailsCount($id){
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) CC FROM `".DB_PREFIX."details` WHERE `IS_ACCOUNT`='".(int)$id."';";
		return $db->get($sql);
	}
	function getOrdersForMe($id=0){
		$db = Register::get('db');
		$sql = "
				SELECT 
					cart.* 
				FROM ".DB_PREFIX."cart cart
				LEFT JOIN ".DB_PREFIX."dic_statuses ds ON (ds.id=cart.status)
				WHERE 
					cart.IS_ACCOUNT='".(int)$id."' AND ds.type='2'
		;";
		return $db->query($sql);
	}

	/* ******************************************************************* */
	// Add item for Order
	function simplesearcher(){
		if (isset($_GET['simpleview']) && $_GET['simpleview']) {
			$_SESSION['simpleview'] = true;
			$_SESSION['simpleview_shopping_active'] = true;
			if (isset($_GET['simple_account_id']) && $_GET['simple_account_id']) {
				$_SESSION['simpleview_shopping_account_id'] = (int)$_GET['simple_account_id'];
			}
			if (isset($_GET['simple_car_id']) && $_GET['simple_car_id']) {
				$_SESSION['simpleview_shopping_car_id'] = (int)$_GET['simple_car_id'];
			}
			if (isset($_GET['simple_scsid']) && $_GET['simple_scsid']) {
				$_SESSION['simple_scsid'] = $_GET['simple_scsid'];
			}
		}
	}
	
	/* ******************************************************************* */
	/* FOR ADMIN MODUL DO A BILL FOR A PERSON (private methods) */
	var $acl = false;
	var $shopping_person = false;
	var $shopping_car = false;
	
	private function getCarId(){
		if (isset($_SESSION['shopping']['car_id'])) {
			$this->shopping_car = $_SESSION['shopping']['car_id'];
			return $this->shopping_car;
		}
		if (isset($_SESSION['simpleview_shopping_car_id'])){
			$this->shopping_car = $_SESSION['simpleview_shopping_car_id'];
			return $this->shopping_car;
		}
		return false;
	}
	private function getAccountIdShopping(){
		if (isset($_SESSION['shopping']['account_id'])){
			$this->shopping_person = $_SESSION['shopping']['account_id'];
			return $this->shopping_person;
		}
		if (isset($_SESSION['simpleview_shopping_account_id'])){
			$this->shopping_person = $_SESSION['simpleview_shopping_account_id'];
			return $this->shopping_person;
		}
	}
	function getShopping(){
		if (isset($_SESSION['shopping']['active'])){
			return true;
		}
		if (isset($_SESSION['simpleview_shopping_active'])){
			return true;
		}
		return false;
	}
	function getInfoCarById(){
		$id = $this->getCarId();
		if (!$id)
			return array();
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_cars WHERE id='".(int)$id."';";
		$res = $db->get($sql);
		if (count($res)>0)
			return $res;
		else {
			return array();
		}
	}
	/* END! FOR ADMIN MODUL DO A BILL FOR A PERSON (private methods) */
	
	/* ****************** OFFICES ****************** */
	function getOffices(){
		if (isset($this->accountData) && isset($this->accountData['city']) && $this->accountData['city']) {
			$offices_account = OfficesModel::getAllByCityId($this->accountData['city']);
			if (!$offices_account){
				$offices_account = $this->getAllOffices();
			}
			$this->view->offices_account = $offices_account;
		}		
	}
	private function setCityId($id=0){
		$_SESSION['_city_id']=(int)$id;
	}
	function getCityId(){
		if (isset($_SESSION['_city_id'])&&$_SESSION['_city_id']){
			return $_SESSION['_city_id'];
		}
		/*elseif (isset($this->accountData) && count($this->accountData)>0){
			return $this->accountData['city'];
		}*/
		else {
			$db = Register::get('db');
			$sql = "SELECT id FROM ".DB_PREFIX."dic_cities WHERE is_default='1';";
			$res = $db->get($sql);
			return $res['id'];
		}
	}
	function getCityIsset(){
		$id = $this->getCityId();
		if ($id){
			$db = Register::get('db');
			$sql = "SELECT name FROM ".DB_PREFIX."dic_cities WHERE id='".(int)$id."';";
			return $db->get($sql);
		}
		else 
			return array();
	}
	private function setOfficeId($id=0){
		$_SESSION['_office_id']=(int)$id;
		
		$db = Register::get('db');
		$sql = "SELECT city_id FROM ".DB_PREFIX."offices WHERE id='".(int)$id."';";
		$res = $db->get($sql);
		
		$this->setCityId($res['city_id']);
	}
	private function unsetOfficeId(){
		unset($_SESSION['_office_id']);
		unset($_SESSION['_city_id']);
	}
	public function getOfficeId(){
		if (isset($_SESSION['_office_id'])&&$_SESSION['_office_id']){
			return (int)$_SESSION['_office_id'];
		}
		elseif (isset($this->accountData['office_id']) && $this->accountData['office_id']){
			return (int)$this->accountData['office_id'];
		}
		else {
			return 0;
		}
	}
	function getOfficeIsset(){
		$id = $this->getOfficeId();
		if ($id){
			$db = Register::get('db');
			$sql = "
					SELECT 
						O.id,O.name,O.contacts,O.is_limit_active,
						DC.name city_name
					FROM ".DB_PREFIX."offices O
					LEFT JOIN ".DB_PREFIX."dic_cities DC ON DC.id = O.city_id
					WHERE O.id='".(int)$id."';";
			return $db->get($sql);
		}
		else 
			return array();
	}
	function getAllOffices(){
		$db = Register::get('db');
		$sql = "SELECT CITY.id,CITY.name,(SELECT COUNT(*) FROM ".DB_PREFIX."offices OFFICE WHERE OFFICE.city_id=CITY.id) cc FROM ".DB_PREFIX."dic_cities CITY WHERE CITY.is_active='1' HAVING cc>0 ORDER BY CITY.sort ASC,CITY.name;";
		return $db->query($sql);
	}
	/* ****************** OFFICES ****************** */
	
	/* ********************** LANGS ********************** */
	function issetLang() {
		$setlang = $this->request("lang","");
		if (!isset($_SESSION['setLang']) || empty($_SESSION['setLang'])) {
			$_SESSION['setLang'] = LANG;
		}
		if ($setlang) {
			$_SESSION['setLang'] = addslashes($setlang);
			$this->redirectUrl('/');
		}
		$this->lang = $_SESSION['setLang'];
		$langs = Register::get('langs');
		$this->view->lang = $this->lang;
		
		$this->view->langs = $langs;
		$this->view->setLang = $langs[$this->lang];
		$this->view->translates = Register::get('translates');
	}
	function getLangId(){
		$LANGS = Register::get('langs');
		$SET_LANG = isset($_SESSION['setLang'])?$_SESSION['setLang']:LANG;
		$LAND_ID = $LANGS[$SET_LANG];
		return $LAND_ID['ID'];
	}
	/* ********************** SOCIAL ********************** */
	function social_access() {
		if (isset($_POST['token'])) {
			if (NOTICE){
				$this->redirectUrl('/account/deny/');
			}
			else {
				$s = file_get_contents('http://ulogin.ru/token.php?token='.($_POST['token']).'&host='.$_SERVER['HTTP_HOST']);
				$user = json_decode($s, true);
				$db = Register::get('db');
				$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE social_identity='".md5($user['identity'].$s)."';";
				$social_identity = $db->get($sql);
				$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE email='".$user['email']."';";
				$social_email = $db->get($sql);
				if (count($social_email)>0) {
					$this->socialSignIn($social_email);
				}
				elseif (count($social_identity)>0) {
					$this->socialSignIn($social_identity);
				}
				else {
					$reg = array();
					$reg['social_identity']	= md5($user['identity'].$s);
					$reg['email']	= ($user['email'])?$user['email']:$user['identity'];
					$reg['pass1']	= ($user['email'])?$user['email']:$user['identity'];
					$reg['name']	= $user['first_name']." ".$user['last_name'];
					$reg['phones']	= "";
					$reg['country']	= $user['country'];
					$reg['city']	= $user['city'];
					$reg['address']	= "";
					AccountsModel::add($reg);
					$this->socialSignIn($reg);
				}
			}
		}
	}
	function socialSignIn($res) {
		$translates = Register::get('translates');
		$_SESSION['errors'] = array();
		$account = AccountsModel::signin($res['email'],(($res['pass'])?$res['pass']:$res['pass1']));
		if (count($account)>0) {
			$_SESSION['account'] = $account;
			AccountsModel::lastLogin($_SESSION['account']['id']);
			$this->redirectUrl('/account/');
			exit();
		}
		else {
			$_SESSION['errors'] []= $translates['front.err.signin'];
			$this->redirectUrl('/account/signin/');	
			exit();
		}
	}
	
	
	private function setSesssion() {
		$db = Register::get('db');
		$id_session = session_id(); 
		$sql = "SELECT id FROM ".DB_PREFIX."session WHERE id_session = '".$id_session."';";
		$res = $db->get($sql);
		if (count($res)>0) {
			$db->post("UPDATE ".DB_PREFIX."session SET putdate = NOW() WHERE id_session = '".$id_session."';");
		} else {
			$db->post("INSERT INTO ".DB_PREFIX."session (`id_session`,`putdate`,`ip`) VALUES ('".$id_session."', NOW(),'".$_SERVER['REMOTE_ADDR']."');");
		}
		$db->post("DELETE FROM ".DB_PREFIX."session WHERE putdate < NOW() -  INTERVAL '1' MINUTE");
		$count = "select count(*) as cc from ".DB_PREFIX."session;";
		$res = $db->get($count);
		$this->view->online = $res['cc'];
	}
	
	public function queriesDebug(){
		$db = Register::get('db');
		return $db->queries_cc;
	}
	
	/* ********************************************************* */
	
	private function getCurrenciesRound(){
		
		$db = Register::get('db');
		
		$sql = "SELECT `code`,`round`,`selectName`,`is_active`,`rate`,`nf` FROM ".DB_PREFIX."currencies;";
		$res = $db->query($sql);
		$vals = $valNames = $rates = $nf = array();
		if (isset($res) && count($res)>0) {
			foreach ($res as $dd){
				
				$rates [$dd['code']]= $dd['rate'];
				$vals [$dd['code']]= $dd['round'];
				$nf [$dd['code']]= $dd['nf'];
				
				if ($dd['is_active'])
					$valNames [$dd['code']]= $dd['selectName'];
			}
		}
		Register::set("rounds",$vals);
		Register::set("rates",$rates);
		Register::set("nf",$nf);
		
		if ($this->accountCurrency)
			Register::set("currenciesNames",array($this->accountCurrency => $valNames[$this->accountCurrency]));
		else 
			Register::set("currenciesNames",$valNames);
		
		$currencies = new Orm(DB_PREFIX.'currencies');
		$res = $currencies->select()->where('is_default=?',1)->fetchOne();
		Register::set("roundDefault",$res['round']);
		
		$currencies = new Orm(DB_PREFIX.'currencies');
		$res = $currencies->select()->where('is_main_currency=?',1)->fetchOne();
		$this->curMoney = $res['code'];
	}
	
	/* ********************************************************* */
	
	
}
?>