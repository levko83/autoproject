<?php

class SearchController  extends BaseController {
	
	public $layout = 'home';
	
	var $mtime = '';	
	var $imp_accounts = array();
	var $imp_accounts_price = array();
	var $lastArticlesOrders = array();
	var $priceSort;
	var $deliverytimeSort;
	var $view;
	
	private $keys_exist_wbs = array();
	private $replacementBrands = array();
	
	/* static method not from db */
	public static function getBrandsCorrects($brand=false){
		switch ($brand){
			case 'LEMFORDER': $brand = 'LEMFÖRDER'; break;
			case 'CITROEN': $brand = 'CITROËN'; break;
		}
		return $brand;
	}
	/* * * * * * * * * * * * * * * * * * */
	
	function __construct() {
		$this->mtime = microtime();
		$this->priceSort = $this->getPriceSort();
		$this->deliverytimeSort = $this->getDeliveryTime();
		if (isset($_SESSION['simpleview']) || isset($_GET['simpleview'])) {
			$this->layout = "simple";
		}
	}
	function totaltime() {
		$mtime = explode(" ",$this->mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tstart = $mtime;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tend = $mtime;
		$totaltime = ($tend - $tstart);
		
		$this->view->totaltime = $totaltime;
		
		return $totaltime;
	}
	public function findimage(){
		$this->layout = "ajax";
		$q = $this->request("q");
		$res = file_get_contents("http://ajax.googleapis.com/ajax/services/search/images?v=1.0&start=5&q=".rawurlencode('запчасти '.$q));
		$res = json_decode($res);
		$rnd = rand(0,5);
		if( isset($res->responseData->results[0]->url) && $res->responseData->results[0]->url){
			echo '<html>';
			echo '<head></head>';
			echo '<body style="margin:0px; padding:0px;">';
					echo '<center><img src="'.$res->responseData->results[0]->url.'"></center>';
			echo '</body>';
			echo '</html>';
		}
		exit();
	}
	
	function wbs_correct_brandnames(){
		$db = Register::get('db');
		$sql = "SELECT incorrect,correct FROM ".DB_PREFIX."wbs_correct_brands;";
		return $db->query($sql);
	}
	
	function ImportersModel_getById($id,$price=array()){
		if (isset($price['IS_ACCOUNT']) && $price['IS_ACCOUNT']) {
			if(isset($this->imp_accounts_price[$price['IS_ACCOUNT']]))
				return $this->imp_accounts_price[$price['IS_ACCOUNT']];
			$account = AccountsModel::getById($price['IS_ACCOUNT']);
			$res = array("id"=>$account['id'],"code"=>"account","name"=>$account['name'],"name_price"=>"u".$account['id'],"discount"=>$account['warehouse_extra'],"money_type"=>0,"rate"=>0,"delivery"=>0,"email"=>$account['email']);
			$this->imp_accounts_price[$price['IS_ACCOUNT']]=$res;
			return $res;
		}
		else {
			if ($id){
				if(isset($this->imp_accounts[$id])){
					return $this->imp_accounts[$id];
				}
				$db = Register::get('db');
				$sql = "
					SELECT 
						I.`id`,I.`code`,I.`name`,I.`name_price`,I.`discount`,
						IF(IOP.`delivery`,(IOP.`delivery`+I.`delivery`),I.`delivery`) as delivery,
						I.`color`,I.`margin_id`,I.`info`,I.`price_date_update`,I.`sort`,I.`only_preorder`,
						C.`currency` currecyName,
						C.`view` money_type,
						C.`rate` currency,
						C.`round`
					FROM ".DB_PREFIX."importers I
					LEFT JOIN ".DB_PREFIX."currencies C ON C.id=I.currency_id
					LEFT JOIN ".DB_PREFIX."importers_offices_params IOP ON IOP.imp_id=I.id AND IOP.office_id = '".(int)Register::get('getOfficeIdParam')."'
					WHERE 
						I.id = '".(int)$id."' OR I.code = '".mysql_real_escape_string($id)."';";
				$res = $db->get($sql);
				$this->imp_accounts[$id]=$res;
				return $res;
			}
			return array();
		}
		return array();
	}
	
	public function preload(){
		$this->view->article = urlencode($this->request("article",""));
		$this->render('search/ajax_preload');
	}
	
	public function ajax_window(){
		$this->render('account/signin_window');
	}
	
	function getCartOfLastOrdersForColor(){
		if ($this->accountData){
			$db = Register::get('db');
			$sql = "
				SELECT DISTINCT C.article 
				FROM ".DB_PREFIX."cart C 
				LEFT JOIN ".DB_PREFIX."cart_bills CB ON CB.scSID=C.scSID
				WHERE 
					CB.account_id = '".(int)$this->accountData['id']."' AND C.article NOT LIKE ''
				LIMIT 0,20;";
			$res = $db->query($sql);
			if (isset($res) && count($res)>0){
				foreach ($res as $dd){
					$this->lastArticlesOrders []= FuncModel::stringfilter($dd['article']);
				}
			}
		}
		Register::set('LAC_COLOR_SET',$this->lastArticlesOrders);
	}
	
	private function setReplaceBrands(){
		$db = Register::get('db');
		$sql = "SELECT BRA_BRAND FROM ".DB_PREFIX."brands WHERE is_replace_brand = '1';";
		$res = $db->query($sql);
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$this->replacementBrands []= $dd['BRA_BRAND'];
			}
		}
	}
	
	public function artlookup() {
		
		$ART_ID = str_replace("Example:","",mysql_real_escape_string($_REQUEST['article']));
		$BRANDFILTER = mysql_real_escape_string((isset($_REQUEST['brand'])?$_REQUEST['brand']:''));
		// echo $ART_ID;
		if ($ART_ID)
			$this->get_list_artlookup($ART_ID,$BRANDFILTER);
		else 
			$this->view->data = array();
	}
	
	function get_list_artlookup($NUMBER,$BRANDFILTER='') {
			// echo $NUMBER;
			
			// $lang = $_SESSION["setLang"];
			// echo $lang;
			$data = explode(" ", $NUMBER);
			$wh = "";
				foreach ($data as $ids)
				{
					// $wh .= " OR supplier_name LIKE '".mysql_real_escape_string($ids)."' OR art_nr LIKE '".mysql_real_escape_string($ids)."' OR name_".mysql_real_escape_string($lang)." LIKE '".mysql_real_escape_string($ids)."' ";
					$wh .= " OR supplier_name LIKE '".mysql_real_escape_string($ids)."' OR art_nr LIKE '".mysql_real_escape_string($ids)."' OR supplier_name LIKE '".mysql_real_escape_string($NUMBER)."' OR art_nr LIKE '".mysql_real_escape_string($NUMBER)."' ";
					// $wh .= " OR art_nr LIKE '".mysql_real_escape_string($ids)."' ";
				}
				$wh = substr($wh, 4);
				// echo $wh;
				if (!empty($wh)) 
				{
					$zero_prices = SettingsModel::get('show_zero_prices');
					if (!$zero_prices){
						$wh = "price > 0 and (".$wh.")";
					}
				}
				$data_without_cost = array();
				
			if (strlen($wh) > 1) {
					$db = Register::get('db');
					$sql = " SELECT * from ".DB_PREFIX."products where {$wh} order by art_nr ASC limit 0,100;";
					// $sql = " SELECT * from ".DB_PREFIX."products where tecdoc_id='997367' OR tecdoc_id='997368' OR tecdoc_id='997369';";
					$fv2p = $db->query($sql);
					if (isset($fv2p) && count($fv2p)>0) {
						foreach ($fv2p as $item){
							$data_without_cost [] = $item;
						}
					}
			}
			
			$this->view->data_without_cost = $data_without_cost;
				
			// $this->totaltime();
	}
	
	private function saved_groups($article=null, $groups=array()){
		unset($_SESSION['__CSR_saved_groups']);
		$article = strtoupper($article);
		$ret = array();
		if (isset($groups) && count($groups)>0){
			foreach ($groups as $group){
				$group = unserialize($group);
				$ret ['<b>'.$group->SUP_BRAND.'</b><small>'.mb_substr($group->TEX_TEXT, 0, 20, 'UTF-8').'...</small>']= $group->URL;
			}
		}
		$_SESSION['__CSR_saved_groups'][FuncModel::stringfilter($article)] = $ret;
	}
	private function get_saved_groups($NUMBER=null){
		$NUMBER = strtoupper($NUMBER);
		return isset($_SESSION['__CSR_saved_groups'][FuncModel::stringfilter($NUMBER)])?$_SESSION['__CSR_saved_groups'][FuncModel::stringfilter($NUMBER)]:array();
	}
	
	
	
	private function find_wbs_groups($str,$array){
		//Удаляем то что уже нашли из веб-сервисов групп
		//$array = $this->unsetItemIfFound($array); - отключено, потому что удаляла группа, которая была определена и когда шел синоним группы перезаписывала
		
		/* Не забываем про ситуацию о группах, когда они имеют одинаковое вхождение, например NISSAN и NISSANMOTOROIL - тогда стоит (!isset($result[$str])) */
		list($str_check,) = explode(" ",$str);
		$result = array();
		foreach($array as $key=>$item){
			$item->SUP_BRAND = self::getBrandsCorrects($item->SUP_BRAND);
			if ($item->SUP_BRAND){
				if(@preg_match('/('.$str_check.')/i', $item->SUP_BRAND) || is_integer(strpos($str_check,$item->SUP_BRAND))){
					if (!isset($result[$str])){
						$result [$str][$item->IMP_ID]= $item->SUP_ID;
						$this->keys_exist_wbs []= $key;
					}
				}
			}
		}
		return $result;
	}
	
	private function unsetItemIfFound($array){
		$this->keys_exist_wbs = array_unique($this->keys_exist_wbs);
		if (isset($this->keys_exist_wbs) && count($this->keys_exist_wbs)>0){
			foreach ($this->keys_exist_wbs as $key){
				unset($array[$key]);
			}
		}
		return $array;
	}
	
	/* ********************************** */
	
	private function fetch_wbs_groups(){
		
		$ARTICLE = str_replace("Example:","",mysql_real_escape_string((isset($_REQUEST['article'])?$_REQUEST['article']:'')));
		$GROUP_ID = mysql_real_escape_string((isset($_REQUEST['brand'])?$_REQUEST['brand']:''));
		
		$db = Register::get('db');
		if ($this->iaccess) {
			if (count($this->iaccess_listAccess)>0) {
				$sql = "SELECT * FROM ".DB_PREFIX."wbs WHERE is_groups='1' AND is_active='1' AND importer_id IN (".join(",",$this->iaccess_listAccess).");";
				$res = $db->query($sql);
			}
		}
		else {
			$sql = "SELECT * FROM ".DB_PREFIX."wbs WHERE is_groups='1' AND is_active='1';";
			$res = $db->query($sql);
		}
		
		$array = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				require_once('wbs/'.$dd['file']);
				$obj = new $dd['class'];
				$gropus = $obj->FindCatalog($ARTICLE);
				if (isset($gropus) && $gropus && count($gropus)>0){
					foreach ($gropus as $group){
						$item = unserialize($group);
						$array []= $item;
					}
				}
			}
		}
		return $array;
	}
	
	function getAccountData(){
		if ($this->acl && $this->shopping_person) {
			$accountFetchid = $this->shopping_person;
		}
		else {
			$accountCookie = AccountsModel::getByCookie();
			$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;	
		}
		if ($accountFetchid) {
			return AccountsModel::getById($accountFetchid);
		}
		else 
			return array();
	}
	
	private function wbsPricesFetch($serialize=true){
		
		/* Цены от веб-сервиса */
		$wbsPricesFetch = array();
		if ($this->iaccess) {
			$wbsServices = WbsModel::activatedForWbs($this->iaccess_listAccess);
		}
		else {
			$wbsServices = WbsModel::activated();
		}
		if (isset($wbsServices) && count($wbsServices)>0){
			foreach ($wbsServices as $service){
				
				if (isset($_REQUEST['ws-'.$service['id']]) && $_REQUEST['ws-'.$service['id']]){
					$_REQUEST['option'][$service['id']]=$_REQUEST['ws-'.$service['id']];
				}
				
				require_once('wbs/'.$service['file']);
				$obj = new $service['class'];
				$result = $obj->searchAll($serialize);
				$wbsPricesFetch = array_merge((array)$wbsPricesFetch,(array)$result);
			}
		}
		return $wbsPricesFetch;
	}
	
	function wbs(){
		
		$this->view->algoritm = true;
		$this->view->findsDescription = false;
						
		/* Список артикулов */
		$wbsPricesFetch = $this->wbsPricesFetch();
		$wbsPricesFetch = DetailsModel::SortMinMaxPrice($wbsPricesFetch,$this->priceSort,true,$this->deliverytimeSort);
		$this->view->data_with_cost = $wbsPricesFetch;
		
		/* ГРУППЫ ДЛЯ ВОЗВРАТА */
		$NUMBER = str_replace("Например:","",mysql_real_escape_string($_REQUEST['article']));
		$this->view->saved_groups = $this->get_saved_groups($NUMBER);
		
		$this->render('search/artlookup');
	}
	
	function details(){

// 		if (isset($_SESSION['simpleview']) || isset($_GET['simpleview'])) {
// 			$this->layout = "simple";
// 		}
		
		$db = Register::get('db');
		
		$ART_ID = isset($_REQUEST['article'])?mysql_real_escape_string($_REQUEST['article']):'';
		$BRANDFILTER = isset($_REQUEST['brand'])?mysql_real_escape_string($_REQUEST['brand']):'';
		
		$iSQLAccess = null;
		if ($this->iaccess){
			$iSQLAccess = "AND IMPORT_ID IN (".join(",", $this->iaccess_listAccess).")";
		}
		
		$sql = "SELECT 
					* 
				FROM `".DB_PREFIX."details` 
				WHERE 
					`ARTICLE` LIKE '".addslashes(FuncModel::stringfilter($ART_ID))."' 
					AND BRAND_NAME LIKE '".mysql_real_escape_string($BRANDFILTER)."' 
					$iSQLAccess
				GROUP BY 
					ARTICLE,BRAND_NAME,IMPORT_ID,PRICE;";
		$dataInDB = $db->query($sql);
		
		/* lets see a cross table */
		$cA = $cB = array();
		$sql = "SELECT SEARCH_ARTICLE,BRAND FROM ".DB_PREFIX."details_db__crosses WHERE CROSS_ARTICLE LIKE '".addslashes(FuncModel::stringfilter($ART_ID))."' AND CROSS_BRAND LIKE '".mysql_real_escape_string($BRANDFILTER)."';";
		$crosses = $db->query($sql);
		if (isset($crosses) && count($crosses)>0){
			foreach ($crosses as $cross){
				$cA []= addslashes(FuncModel::stringfilter($cross['SEARCH_ARTICLE']));
				$cB []= addslashes($cross['BRAND']);
			}
			
			$sql = "SELECT 
						* 
					FROM `".DB_PREFIX."details` 
					WHERE 
						`ARTICLE` IN ('".join("','",$cA)."') 
						AND BRAND_NAME IN ('".join("','",$cB)."')
						$iSQLAccess
					;";
			$dataInDB_MERGE = $db->query($sql);
			
			if (isset($dataInDB_MERGE) && count($dataInDB_MERGE)>0){
				foreach ($dataInDB_MERGE as $DIM){
					$dataInDB []= $DIM;
				}
			}
		}
		
		$result = array();
		if (isset($dataInDB) && count($dataInDB)>0){
			foreach ($dataInDB as $value){
				$IMPORTER_DATA = $this->ImportersModel_getById($value['IMPORT_ID'],$value);
				$OUTPRICE_DATA = OutpriceModel::generate($IMPORTER_DATA,$this->accountData,$value['PRICE'],$value['BRAND_NAME']);
				$result []= array_merge(
					(array)$value,
					array(
						'ART_ARTICLE_NR_CLEAR'	=>	$value['ARTICLE'],
						'SUP_BRAND'	=>	strtoupper($value['BRAND_NAME']),
							
						'IMPORTER_DATA'	=>	$IMPORTER_DATA,
						'OUTPRICE_DATA'	=>	$OUTPRICE_DATA,
						'RESULT_PRICE_SALE'	=>	(($IMPORTER_DATA['currency'])?($IMPORTER_DATA['currency']*$OUTPRICE_DATA['resultPRICE']):$OUTPRICE_DATA['resultPRICE']),
					)
				);
			}
		}
		
		#3 /* Цены от веб-сервиса */
		$wbsPricesFetch = $this->wbsPricesFetch(false);
		$prices = array_merge((array)$result,(array)$wbsPricesFetch);
		$prices = DetailsModel::SortMinMaxPrice($prices,$this->getPriceSort(),false,$this->deliverytimeSort);
		$this->view->dataInDB = $prices;
		
		/* ГРУППЫ ДЛЯ ВОЗВРАТА */
		$NUMBER = str_replace("Например:","",mysql_real_escape_string($_REQUEST['article']));
		$this->view->saved_groups = $this->get_saved_groups($NUMBER);
		
		$this->view->algoritm = false;
		$this->render("search/artlookup");
	}
	function findBrandNotExist($NUMBER,$BRANDS=array(),$all=false){
		
		$noBRAND = $noBRAND2 = array(); $iBRAND = '';
		if (isset($BRANDS) && count($BRANDS)>0){
			foreach ($BRANDS as $BRAND){
				if ($BRAND->ORIGINAL == 1)
					$noBRAND []= addslashes($BRAND->SUP_BRAND);
			}
			$noBRAND = array_unique($noBRAND);
			if (count($noBRAND)>0){
				foreach ($noBRAND as $NB){
					$xxx = explode(" ", $NB);
					if (isset($xxx) && count($xxx)>1){
						foreach ($xxx as $x1){
							$noBRAND2 []= " (DETAILS.BRAND_NAME NOT LIKE '".mysql_real_escape_string($x1)."') ";
						}
					}
					else
						$noBRAND2 []= " (DETAILS.BRAND_NAME NOT LIKE '".mysql_real_escape_string($NB)."') ";
				}
				$iBRAND = " AND (" . join(" AND ", $noBRAND2) . ")";
			}
		}
		
// 		$noBRAND = array(); $iBRAND = '';
// 		if (isset($BRANDS) && count($BRANDS)>0){
// 			foreach ($BRANDS as $BRAND){
// 				if ($BRAND->ORIGINAL == 1)
// 				$noBRAND []= addslashes($BRAND->SUP_BRAND);
// 			}
// 			$noBRAND = array_unique($noBRAND);
// 			$iBRAND = " AND DETAILS.BRAND_NAME NOT IN ('".join("','",$noBRAND)."') ";
// 		}
		
		$db = Register::get('db');
		
// 		if ($all)
// 			/*AND BRANDS.BRA_ID IS NULL*/
// 			$sql = "
// 			SELECT 
// 				* 
// 			FROM `".DB_PREFIX."details` DETAILS
// 			LEFT JOIN `".DB_PREFIX."brands` BRANDS ON (BRANDS.BRA_ID = DETAILS.BRAND_ID)
// 			WHERE 
// 				DETAILS.ARTICLE LIKE '".addslashes(FuncModel::stringfilter($NUMBER))."' 
// 				AND DETAILS.ONLY_FOR_SHOP='0' 
// 				AND ((BRANDS.BRA_ID != BRANDS.BRA_ID_GET) OR BRANDS.BRA_ID IS NULL)
// 			GROUP BY DETAILS.ARTICLE,DETAILS.BRAND_NAME;";
// 		else
			$sql = "
			SELECT 
				* 
			FROM `".DB_PREFIX."details` DETAILS
			LEFT JOIN `".DB_PREFIX."brands` BRANDS ON (BRANDS.BRA_ID = DETAILS.BRAND_ID)
			WHERE 
				DETAILS.ARTICLE LIKE '".addslashes(FuncModel::stringfilter($NUMBER))."' 
				AND DETAILS.ONLY_FOR_SHOP='0' 
				$iBRAND
			GROUP BY DETAILS.ARTICLE,DETAILS.BRAND_NAME;";
		
		$groups = $db->query($sql);
		
		$originalsSearchRet = array();
		if (isset($groups) && count($groups)>0){
			$i=0; foreach ($groups as $group){ $i++;
				$originalsSearch = (object)array(
					'IMP_ID' => $group['IMPORT_ID'],
					'SUP_ID' => $group['BRAND_NAME'],
					'ART_ARTICLE_NR' => $group['ARTICLE'],
					'SUP_BRAND' => $group['BRAND_NAME'],
					'TEX_TEXT' => $group['DESCR'],
					'URL' => '/search/details/?article='.urlencode($group['ARTICLE']).'&brand='.urlencode($group['BRAND_NAME']),
					'ORIGINAL' => 1
				);
				$originalsSearchRet []= serialize($originalsSearch);
			}
		}
		return $originalsSearchRet;
	}
	
	private function findInDB($NUMBER) {
		
		$iARTICLES = $iBRANDS_NAME = $return = array();
		
		$db = Register::get('db');
		$sql = "
			SELECT 
				DETAILS.*,
				BRANDS.BRA_BRAND MATCH_BRAND
			FROM `".DB_PREFIX."details` DETAILS
			LEFT JOIN `".DB_PREFIX."brands` BRANDS ON BRANDS.BRA_ID_GET = DETAILS.BRAND_ID
			LEFT JOIN `".DB_PREFIX."importers` IMP ON IMP.id = DETAILS.IMPORT_ID
			WHERE 
				DETAILS.ARTICLE IN ('".join("','",array($NUMBER))."') 
				AND DETAILS.PRICE>'0' 
				AND DETAILS.ONLY_FOR_SHOP='0'
			GROUP BY
				DETAILS.IMPORT_ID,
				DETAILS.ARTICLE,
				DETAILS.BRAND_NAME,
				DETAILS.PRICE
		";
		$res = $db->query($sql);
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$return []= $dd;
				
				$iARTICLES []= $dd['ARTICLE'];
				$iBRANDS_NAME []= $dd['BRAND_NAME'];
			}
		}
		
		if (count($iARTICLES)>0 && count($iBRANDS_NAME)>0){
			
			$sql = "
			SELECT
				DISTINCT SEARCH_ARTICLE,ARTICLE,BRAND,DESCR,IMG
			FROM ".DB_PREFIX."details_db__crosses
			WHERE
				(CROSS_BRAND IN ('".join("','",$iBRANDS_NAME)."') AND CROSS_ARTICLE IN ('".join("','",$iARTICLES)."'))
				AND
				(BRAND NOT IN ('".join("','",$iBRANDS_NAME)."') AND SEARCH_ARTICLE NOT IN ('".join("','",$iARTICLES)."'))
			GROUP BY SEARCH_ARTICLE,BRAND;";
			$crosses = $db->query($sql);
			$TEMP_ARTICLES = $TEMP_BRANDS = array();
			if (isset($crosses) && count($crosses)>0){
				foreach ($crosses as $tma){
					$TEMP_ARTICLES []= $tma['SEARCH_ARTICLE'];
					$TEMP_BRANDS []= $tma['BRAND'];
				}
			}
			
			$sql = "SELECT * FROM `".DB_PREFIX."details` WHERE ARTICLE IN ('".join("','",$TEMP_ARTICLES)."') AND BRAND_NAME IN ('".join("','",$TEMP_BRANDS)."');";
			$resultCrosses = $db->query($sql);
			if (isset($resultCrosses) && count($resultCrosses)>0){
				foreach ($resultCrosses as $RSC){
					$return []= $RSC;
				}
			}
		}
		
		return $return;
	}
	
	private function findCrossesConnector($data=array(),$dataTree=array()){
		
		$trigger = false;
		$iARTICLES = $iBRANDS_NAME = array();
		$moreSQL = array();
		
		if (isset($data) && count($data)>0){
			$trigger = 'article';
			foreach ($data as $dd){
				$iARTICLES []= FuncModel::stringfilter($dd->ART_ARTICLE_NR);
				$iBRANDS_NAME []= addslashes($dd->SUP_BRAND);
				
				//Если в выдаче всего 1 артикул то зачем отсекать, будет искать
				if (isset($data) && count($data)>1){
					$moreSQL []= " (BRAND != '".addslashes($dd->SUP_BRAND)."' AND SEARCH_ARTICLE != '".FuncModel::stringfilter($dd->ART_ARTICLE_NR)."' ) ";
				}
			}
		}
		if (isset($dataTree) && count($dataTree)>0){
			$trigger = 'manufactree';
			foreach ($dataTree as $dd){
				$iARTICLES []= FuncModel::stringfilter($dd->DETAIL[0]->ART_ARTICLE_NR);
				$iBRANDS_NAME []= addslashes($dd->DETAIL[0]->SUP_BRAND);
				
				//Если в выдаче всего 1 артикул то зачем отсекать, будет искать
				if (isset($data) && count($data)>1){
					$moreSQL []= " (BRAND != '".addslashes($dd->DETAIL[0]->SUP_BRAND)."' AND SEARCH_ARTICLE != '".FuncModel::stringfilter($dd->DETAIL[0]->ART_ARTICLE_NR)."' ) ";
				}
			}
		}
		
		if (count($iARTICLES)>0 && count($iBRANDS_NAME)>0){
			
			$db = Register::get('db');
			
			$sql = "
			SELECT
				DISTINCT SEARCH_ARTICLE,ARTICLE,BRAND,DESCR,IMG
			FROM ".DB_PREFIX."details_db__crosses
			WHERE
				(CROSS_BRAND IN ('".join("','",$iBRANDS_NAME)."') AND CROSS_ARTICLE IN ('".join("','",$iARTICLES)."'))
				".((count($moreSQL)>0)?" AND (".join(" OR ", $moreSQL).")":"")."
			GROUP BY SEARCH_ARTICLE,BRAND;";
// 			var_dump($sql); 
			$res = $db->query($sql);
			
			$ret = array();
			if (isset($res) && count($res)>0){
				foreach ($res as $item){
					
					if ($trigger == 'article'){
						$ret []= (object)array(
							'ART_ID'	=>	0,
							'SUP_ID'	=>	0,
							'SUP_BRAND'	=>	$item['BRAND'],
							'ART_ARTICLE_NR'	=>	$item['ARTICLE'],
							'ART_ARTICLE_NR_CLEAR'	=>	$item['SEARCH_ARTICLE'],
							'TEX_TEXT'	=>	$item['DESCR'],
							'ARL_KIND'	=>	3,
							'ORIGINAL'	=>	0,
							'SUP_UNICODE_BRAND'	=>	array(),
							'CRITERIA'	=>	array(),
							'PATH_IMAGES'	=>	array((object)array('PATH'=>HTTP_ROOT.'/media/files/crosses/'.$item['IMG'])),
							'PATH_LOGOS'	=>	array(),
							'PRICES'	=>	array(),
						);
					}
					/* ************* */
					
					if ($trigger == 'manufactree'){
						$ret []= (object)array(
							'LA_ART_ID'	=>	0,
							'ART_ID'	=>	0,
							'DETAIL'	=>	array(
								(object)array(
									'ART_ARTICLE_NR'	=>	$item['ARTICLE'],
									'BRA_ID'	=>	0,
									'SUP_BRAND'	=>	$item['BRAND'],
									'ART_COMPLETE_DES_TEXT'	=>	$item['DESCR'],
								),
							),
							'CRITERIA'	=>	array(),
							'PATH_IMAGES'	=>	array((object)array('PATH'=>HTTP_ROOT.'/media/files/crosses/'.$item['IMG'])),
							'PATH_LOGOS'	=>	array(),
							'PRICES'	=>	array(),
						);
					}
					/* ************* */
				}
			}
			return $ret;
			/* *** */
		}
		
		return array();
	}
	
	/** ******************************************************************************************* */
	/* ******************** ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ******************* */
	/** ******************************************************************************************* */
	
	public function index() {

		$PAGE = $this->request("page",1);
		$MFA_ID = addslashes($this->request("MFA_ID",0));
		$MOD_ID = addslashes($this->request("MOD_ID",0));
		$TYP_ID = addslashes($this->request("TYP_ID",0));
		$STR_ID = (int)($this->request("STR_ID",0));
		$this->view->STR_ID = $STR_ID;
		
		if ($MFA_ID && $MOD_ID && $TYP_ID) {
			
			$this->view->hide_left_menu = true;
			
			$exp = explode("-",$MFA_ID);
			$MFA_ID = array_pop($exp);
			$exp = explode("-",$MOD_ID);
			$MOD_ID = array_pop($exp);
			$exp = explode("-",$TYP_ID);
			$TYP_ID = array_pop($exp);
			
			$MFA = ManufacturersModel::getByID($MFA_ID);
			$this->view->MFA = $MFA;
			
			$this->get_one_models($MOD_ID); #1
			$this->get_one_types($TYP_ID); #2
			$this->search_tree($TYP_ID,10001); #3
			
			$MOD = ($this->view->MOD);
			$TYP = ($this->view->TYP);
			
			$breads = array();
			$breads = $this->getBreadCrumbsByCatalog($TYP_ID,$STR_ID); #4
			if (count($breads)>0)
				$breads = array_reverse($breads);
			$this->view->car_breadcrumbs = $breads;
			
			/* SEO */
			$crumbs = array();
			if (count($breads)>0){
				foreach ($breads as $crumb){
					$crumbs []= $crumb->NAME;
				}
			}
			$translates = Register::get('translates');
			$catName = array_pop($crumbs);
			$this->view->_seo = array(
				"title"=>($catName?$catName.' '.$translates['f.dlya'].' ':'').$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT." ".$TYP[0]->TYP_CDS_TEXT,
				"kwords"=>($catName?$catName.' '.$translates['f.dlya'].' ':'').$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT." ".$TYP[0]->TYP_CDS_TEXT,
				"descr"=>($catName?$catName.' '.$translates['f.dlya'].' ':'').$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT." ".$TYP[0]->TYP_CDS_TEXT,
				"masina"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT." ".$TYP[0]->TYP_CDS_TEXT
			);
			$this->view->catName = $catName;
			/* SEO */
			
			if ($STR_ID) {
				$this->Search_tree2Model($TYP_ID,$STR_ID,$PAGE); #5
				// $this->view->not_view_left_menu = true;   
			}
			
			$this->totaltime();
			$this->render("search/car_searchtree");
		}
		elseif ($MFA_ID && $MOD_ID) {
			
			///
			$exp = explode("-",$MFA_ID);
			$MFA_ID = array_pop($exp);
			$exp = explode("-",$MOD_ID);
			$MOD_ID = array_pop($exp);
	
			$MFA = ManufacturersModel::getByID($MFA_ID);
			$this->view->MFA = $MFA;
		
			$this->get_one_models($MOD_ID);
			$this->get_list_types($MOD_ID);
			$MOD = ($this->view->MOD);
			$this->view->_seo = array(
				"title"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT,
				"kwords"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT,
				"descr"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT
			);
			
			$this->totaltime();
			$this->render("search/car_typies");
			
		}
		else if ($MFA_ID) {
			$exp = explode("-",$MFA_ID);
			$MFA_ID = array_pop($exp);

			$this->get_list_models($MFA_ID);
			$MFA = ManufacturersModel::getByID($MFA_ID);
			$this->view->MFA = $MFA;
			$this->view->_seo = array(
				"title"=>($MFA['title'])?$MFA['title']:(($MFA['name'])?$MFA['name']:$MFA['MFA_BRAND']),
				"kwords"=>($MFA['kwords'])?$MFA['kwords']:(($MFA['name'])?$MFA['name']:$MFA['MFA_BRAND']),
				"descr"=>($MFA['descr'])?$MFA['descr']:(($MFA['name'])?$MFA['name']:$MFA['MFA_BRAND'])
			);
			
			$this->totaltime();
			$this->render("search/car_models");
		}
	}
	public function getnr() {

		$zu2 = addslashes($this->request("ZU2",0));
		$zu3 = addslashes($this->request("ZU3",0));
		if ($zu2 && $zu3) {
			$ids = $this->get_list_vin_ids($zu2, $zu3); 
			if (count($ids) > 0) {
				$MFA_ID = $ids[0]->MOD_MFA_ID;
				$MOD_ID = $ids[0]->MOD_ID;
				
				$MFA = ManufacturersModel::getByID($MFA_ID);
			
				$this->view->MFA = $MFA;
				
				$this->get_one_models($MOD_ID); //getnr
				$data = $this->get_list_vin($zu2, $zu3); 
				
				$MOD = ($this->view->MOD);
				header("Location: /auto/".AliasViewHelper::doTraslitSearchAuto($MFA['MFA_BRAND'])."-".$MFA['MFA_ID']."/".AliasViewHelper::doTraslitSearchAuto($MOD[0]->MOD_CDS_TEXT)."-".$MOD[0]->MOD_ID."/".AliasViewHelper::doTraslitSearchAuto($data[1][0]->SHORT_DES)."-".$data[1][0]->TYP_ID."");
				$this->view->_seo = array(
					"title"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT,
					"kwords"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT,
					"descr"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT
				);
				// print("<pre>");
				// print_r($data);
				// die($data);
			}
		}
			$this->totaltime();
			$this->render("search/car_vin");
			
	}
	
	function getBreadCrumbsByCatalog($TYP_ID,$STR_ID) {
		try {
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->get_images(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'TYP_ID'=>(int)$TYP_ID,
					'STR_ID'=>(int)$STR_ID,
					'LANG_ID'=>$LANG_ID
				)
			));
			$data = json_decode($results);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				return $data;
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
	}
			
	/* GET MODELS BY ID */
	function get_list_models($MFA_ID='') {
		$MFA_ID = (int)$this->request("id",$MFA_ID);
		try {
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->ModelsModel_query(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'MFA_ID'=>(int)$MFA_ID,
					'LANG_ID'=>(int)$LANG_ID
				)
			));
			$data = json_decode($results);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				
				$models = array();
				if (isset($data) && count($data)>0){
					foreach ($data as $row){
						
						$getLetter = $row->LETTER;
						$strWithoutChars = preg_replace('/[^0-9]/', '', $getLetter);
						if ($strWithoutChars)
							$getLetter = "№";
						
						$models [$getLetter][]= $row; 
					}
					$this->view->data = $models;
				}
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
		$this->totaltime();
	}
	private function switcherDT($start,$stop,$from,$to,$key){
		if ($stop && $stop >= $from && $stop <= $to) {
			return $key;
		}
		
		if (!$stop){
			if ($start >= $from && $start <= $to) {
				return $key;
			}
		}
		return false;
	}
	/* *********** */
	
	function get_one_models($MOD_ID) {
		
		$MOD_ID = (int)$this->request("id",$MOD_ID);
		try {
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->ModelsModel_getById(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'MOD_ID'=>(int)$MOD_ID,
					'LANG_ID'=>$LANG_ID
				)
			));
			$data = json_decode($results);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				$this->view->MOD = $data;
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
	}
	
	function get_list_types($MOD_ID) {
		
		$MOD_ID = (int)$this->request("id",$MOD_ID);
		try {
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->TypesModel_query(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'MOD_ID'=>(int)$MOD_ID,
					'LANG_ID'=>$LANG_ID
				)
			));
			$data = json_decode($results);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				// print("<pre>");
				// print_R($data);
				$this->view->tabs_cur = 0;
				$tabs = $tabs_clone = array();
				if (isset($data) && count($data)>0) {
					foreach ($data as $dd) {
						$engine = explode(" ",$dd->TYP_CDS_TEXT);
						$tabs []= $engine[0];
						$tabs_clone []= FuncModel::stringfilter($engine[0]);
					}
					$translates = Register::get('translates');
					$tabs 		= array_merge(array($translates['f.vse']),array_unique($tabs));
					$tabs_clone = array_merge(array($translates['f.vse']),array_unique($tabs_clone));
				}
				$this->view->tabs = $tabs;
				
				$convertData = array();
				if (isset($data) && count($data)>0){
					foreach ($data as $type) {
						
						$engine = explode(" ",$type->TYP_CDS_TEXT);
						$tt = FuncModel::stringfilter($engine[0]);
						$key = array_search($tt,$tabs_clone);
						
						$convertData [$key][]= $type;
						$convertData [0][]= $type;
					}
				}
				
				$this->view->data = $convertData;
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
		
	}
	function get_list_vin($zu2, $zu3) {
		$zu2 = $this->request("ZU2",$zu2);
		$zu3 = $this->request("ZU3",$zu3);
		try {
			
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER);
			    $LANG_ID = $this->getLangId();
			    $results = $client->ViewDetail(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'LANG_ID'=>$LANG_ID,
						'zu2'=>$zu2,
						'zu3'=>$zu3,
						'FUNCTION'=>'getnr',
					)
				));
				// echo $results;
			$data = json_decode($results);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				// print_R($data);
				$this->view->tabs_cur = 0;
				$tabs = $tabs_clone = array();
				if (isset($data) && count($data)>0) {
					foreach ($data as $dd) {
						$engine = explode(" ",$dd->SHORT_DES);
						$tabs []= $engine[0];
						$tabs_clone []= FuncModel::stringfilter($engine[0]);
					}
					$tabs 		= array_merge(array("Все"),array_unique($tabs));
					$tabs_clone = array_merge(array("Все"),array_unique($tabs_clone));
				}
				$this->view->tabs = $tabs;
				
				$convertData = array();
				if (isset($data) && count($data)>0){
					foreach ($data as $type) {
						
						$engine = explode(" ",$type->SHORT_DES);
						$tt = FuncModel::stringfilter($engine[0]);
						$key = array_search($tt,$tabs_clone);
						
						$convertData [$key][]= $type;
						$convertData [0][]= $type;
					}
				}
				
				$this->view->data = $convertData;
				return $convertData;
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
		
	}
	function get_list_vin_ids($zu2, $zu3) {
		$zu2 = $this->request("ZU2",$zu2);
		$zu3 = $this->request("ZU3",$zu3);
		try {
			
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER);
			    $LANG_ID = $this->getLangId();
			    $results = $client->ViewDetail(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'LANG_ID'=>$LANG_ID,
						'zu2'=>$zu2,
						'zu3'=>$zu3,
						'FUNCTION'=>'getnr',
					)
				));
				// echo $results;
			$data = json_decode($results);
			return $data;
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
		
	}
	
	function get_one_types($TYP_ID) {
		
		$TYP_ID = (int)$this->request("id",$TYP_ID);
		try {
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->TypesModel_getById(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'TYP_ID'=>(int)$TYP_ID,
					'LANG_ID'=>$LANG_ID
				)
			));
			$data = json_decode($results);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				$this->view->TYP = $data;
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
	}
	
	function gestnr() {
		
			$MFA_ID = "587";
			$MOD_ID = "5398";
			
			$MFA = ManufacturersModel::getByID($MFA_ID);
			$this->view->MFA = $MFA;
			
			$this->get_one_models($MOD_ID);
			$this->get_list_types($MOD_ID);
			
			$MOD = ($this->view->MOD);
			$this->view->_seo = array(
				"title"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT,
				"kwords"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT,
				"descr"=>$MFA['MFA_BRAND']." ".$MOD[0]->MOD_CDS_TEXT
			);
			
			$this->totaltime();
			$this->render("search/car_typies");
	}
	
	function search_tree($TYP_ID,$STR_ID) {
		
		try {
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->searchTree(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'TYP_ID'=>(int)$TYP_ID,
					'STR_ID'=>(int)$STR_ID,
					'LANG_ID'=>$LANG_ID
				)
			));
			// echo $TYP_ID." = ";
			// echo $STR_ID;
			$data = json_decode($results);
			// print("<pre>");
			// print_r($data);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				
				$this->totaltime();
				$this->view->search_tree = $data;
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
	}
	
	function Search_tree2Model($TYP_ID,$STR_ID,$PAGE=1) {
		
		try {
		    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->Search_by_autoModel_query(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'TYP_ID'=>(int)$TYP_ID,
					'STR_ID'=>(int)$STR_ID,
					'PAGE'=>(int)$PAGE,
					'LANG_ID'=>$LANG_ID
				)
			));
			$data = json_decode($results);
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				// print("<pre>");
				// print_r($data[0]);
				
				$wh = "";
				foreach ($data[0] as $ids)
				{
					$wh .= " OR tecdoc_id='".$ids."'";
				}
				$wh = substr($wh, 4);
				// echo $wh;
				if (!empty($wh)) 
				{
					$zero_prices = SettingsModel::get('show_zero_prices');
					if (!$zero_prices){
						$wh = "price > 0 and (".$wh.")";
					}
				}
				$data_without_cost = array();
				
				if (strlen($wh) > 1) {
					$db = Register::get('db');
					$sql = " SELECT * from ".DB_PREFIX."products where {$wh};";
					// $sql = " SELECT * from ".DB_PREFIX."products where tecdoc_id='997367' OR tecdoc_id='997368' OR tecdoc_id='997369';";
					$fv2p = $db->query($sql);
					if (isset($fv2p) && count($fv2p)>0) {
						foreach ($fv2p as $item){
							$data_without_cost [] = $item;
						}
					}
				}
				
				
				$this->view->data_without_cost = $data_without_cost;
				$this->view->algoritm = true;
				
				$this->totaltime();
			}
			} catch (Exception $e) {
		    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }
		}
	}
	
	function makeStick($str) {
		$param = '';
		foreach ($str as $name=>$item) {
			$param .= $item->CRITERIA_DES_TEXT." ".$item->CRITERIA_VALUE_TEXT." ";
	    }
	    $front = 1;
	    if (strpos($param,"перед") === false) {
	    	$front = 0;
	    }
	    $rear = 1;
	    if (strpos($param,"зад") === false) {
	    	$rear = 0;
	    }
	    
	    if ($front || $rear)
		    return array("front"=>$front,"rear"=>$rear);
		else 
			return array();
	}

	/* ******************************** */
	
	public function car() {
		
		$DATA = $this->request("form");
		#var_dump($DATA);
		if ($DATA['MFA_ID'] && $DATA['MOD_ID']) {
			
			$MFA_ID = $DATA['MFA_ID'];
			$MOD_ID = $DATA['MOD_ID'];
			$YEAR = $DATA['YEAR_ID'];
			$FUEL = $DATA['FUEL_ID'];
			
			$this->view->MFA = ManufacturersModel::getByID($MFA_ID);
			$this->get_one_models($MOD_ID);
			
			try {
			    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER);
			    $LANG_ID = $this->getLangId();
			    $results = $client->TypesModel_query(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'MOD_ID'=>(int)$MOD_ID,
						'YEAR'=>(int)$YEAR,
						'FUEL'=>(int)$FUEL,
						'LANG_ID'=>$LANG_ID
					)
				));
				$data = json_decode($results);
				if (is_string($data)) {
					echo $data;
				}
				else if ($data) {
										
					$this->view->tabs_cur = 0;
					$tabs = $tabs_clone = array();
					if (isset($data) && count($data)>0) {
						foreach ($data as $dd) {
							$engine = explode(" ",$dd->TYP_CDS_TEXT);
							$tabs []= $engine[0];
							$tabs_clone []= FuncModel::stringfilter($engine[0]);
						}
						$tabs = array_unique($tabs);
					}
					$this->view->tabs = $tabs;
					
					$convertData = array();
					if (isset($data) && count($data)>0){
						foreach ($data as $type) {
							
							$engine = explode(" ",$type->TYP_CDS_TEXT);
							$tt = FuncModel::stringfilter($engine[0]);
							$key = array_search($tt,$tabs_clone);
							
							$convertData [$key][]= $type;
						}
					}
					
					$this->view->data = $convertData;
					
				}
			} catch (Exception $e) {
			    echo "<h2>Exception Error!</h2>";
			    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
			    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
			    }			    
			}
			
			$this->totaltime();
			$this->render("search/car_typies");
			
		} else {
			$this->redirectUrl("/");
		}
	}
	
	/* ******************** ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ******************* */
	
	function fetchAllImages($array) {
		
		/* */
		try {
			$options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
		    $client = new SoapClient(SOAP, $options);
		    $ACCESS_C = array('SERVER'=>$_SERVER);
		    $LANG_ID = $this->getLangId();
		    $results = $client->get_images(
			array(
				'request'=>array(
					'ACCESS_C'=>$ACCESS_C,
					'ARRAY'=>$array,
					'LANG_ID'=>$LANG_ID
				)
			));
			
			$data = json_decode($results);
			
			if (is_string($data)) {
				echo $data;
			}
			else if ($data) {
				$this->view->fetchall_images = $data;
			}
		} catch (Exception $e) {
		    echo "<h2>Exception Error!</h2>";
		    if (isset($_REQUEST['debug']) && $_REQUEST['debug']==='!') {
		    	echo '<pre>'.print_r($e->getMessage()).'</pre>';
		    }			    
		}
		/* */
	}
	
	/* ******************** ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ******************* */
	/**
	 * INDEX SEARCH CAR
	 */
	function searchGetMods(){
		$translates = Register::get('translates');
		$this->layout = "ajax";
		$id = (int)$this->request("car_id",false);
		if ($id){
			try {
			    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER);
			    $LANG_ID = $this->getLangId();
			    $results = $client->ViewDetail(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'LANG_ID'=>$LANG_ID,
						'FUNCTION'=>'MiniCarSearchGetMods',
						'PARAMS'=>array(
							'CAR_ID'=>$id
						),
					)
				));
				$data = json_decode($results);
				if (is_string($data)) {
					echo $data;
				} else if ($data) {
					
					// $ret = '<select class="chosen-select" name="s[mod]" id="smod" onchange="get_type5(this.value);">';
					$ret = '<select id="carmodell" class="form-control" name="s[mod]" onchange="get_volumes(this.value);">';
					$ret .= '<option>'.$translates['selmodel'].'</option>';
					if (isset($data) && count($data)>0){
						foreach ($data as $key=>$row){
							$ret .= '<optgroup label="'.$key.'">';
								if (isset($row) && count($row)>0){
									foreach ($row as $r){
										$nrfrom = strlen($r->YFROM) - 2;
										$nrfromlast = substr($r->YFROM, $nrfrom);
										// echo $nrfromlast."<br>";
										$nrfrom = $nrfromlast.".".substr($r->YFROM, 0, -2);
										// $r->YFROM = substr($r->YFROM, 0, -2);
										$nrto = strlen($r->YTO) - 2;
										$nrtolast = substr($r->YTO, $nrfrom);
										if (!empty($r->YTO)) $nrto = $nrtolast.".".substr($r->YTO, 0, -2);
										else $nrto = "...";
										// echo $nrtolast."<br>";
										// $r->YTO = 	substr($r->YTO, 0, -2);
										$value = base64_encode($r->MOD_ID."$$".$r->YFROM."$$".$r->YTO);
										$ret .= '<option value="'.$value.'">'.$r->MODIF.' ('.$nrfrom.' - '.$nrto.')</option>';
									}
								}
							$ret .= '</optgroup>';
							// $ret .= '<script type="text/javascript">
									// $(document).ready(function(){
										// $("#carmodell").chosen({no_results_text: "Oops, nothing found!"}); 
									// });
									// </script>';
						}
					}
					$ret .= '</select>';
					echo($ret);
				}
				
			} catch (Exception $e) {
			    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
			}
		}
		exit();
	}
	function searchGetYears(){
		$this->layout = "ajax";
		$id = (int)$this->request("mod_id",false);
		if ($id){
			try {
			    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER);
			    $LANG_ID = $this->getLangId();
			    $results = $client->ViewDetail(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'LANG_ID'=>$LANG_ID,
						'FUNCTION'=>'MiniCarSearchGetYears',
						'PARAMS'=>array(
							'MOD_ID'=>$id
						),
					)
				));
				$data = json_decode($results);
				if (is_string($data)) {
					echo $data;
				} else if ($data) {
					$FROM = isset($data->YFROM)?$data->YFROM:false;
					$TO = isset($data->YTO)?$data->YTO:date("Y");
					if ($FROM && $TO){
						$ret = '<select class="chosen-select" name="s[year]" id="syear" onchange="get_fuel4(\''.$id.'\',this.value);">';
						$ret .= '<option>Выберите год</option>';
						for ($i=$TO;$i>=$FROM;$i--){
							$ret .= '<option value="'.$i.'">'.$i.'</option>';
						}
						$ret .= '</select>';
						$ret .= '<script type="text/javascript">
								$(document).ready(function(){
									$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"}); 
								});
								</script>';
						echo($ret);
					}
				}
				
			} catch (Exception $e) {
			    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
			}
		}
		exit();
	}
	/*function sqlesc($x) {
						   if (get_magic_quotes_gpc()) { $x = stripslashes($x); };
						   if (!is_numeric($x)) { $x = "'" . mysql_real_escape_string($x) . "'"; };
						   return $x;
						}*/
						
	function update_brands(){
		$this->layout = "ajax";
		
		$db = Register::get('db');
		
		/*
		
			$brands = "SPIDAN,HELLA,ATE,MANN-FILTER,PIERBURG,LuK,EBERSPACHER,REINZ,ELRING,BERU,PAGID,WALKER,NGK,BILSTEIN,SWF,VALEO,RUVILLE,VARTA,CV PSH,ERNST,BOSCH,CONTITECH,SACHS,GATES,KNECHT,LEMFORDER,VAN WEZEL,MONROE,BOSAL,DAYCO,CHAMPION,SPAHN GLUHLAMPEN,SKF,WESTFALIA,HERTH+BUSS JAKOPARTS,GOETZE,TYC,BREMBO,DENSO,OSRAM,HERTH+BUSS ELPARTS,PHILIPS,WAHLER,AKS DASIS,HENGST FILTER,VDO,KYB,ZIMMERMANN,FRIESEN,DELPHI,SCHLIECKMANN,METZGER,MAGNETI MARELLI,FEBI BILSTEIN,SNR,ULO,HJS,NK,OPTIMAL,MAPCO,MOOG,JOHNS,AJUSA,CORTECO,MEYLE,SWAG,JAPANPARTS,FACET,TRW,VAICO,PRESTO,LESJOFORS,VEMO,KAMOKA,FAG,LPR,INA,NRF,A.B.S.,CASTROL,AIRTEX,LIQUI MOLY,ERA,IPSA,AVA QUALITY COOLING,ZF Parts,FILTRON,KAGER,MAHLE ORIGINAL,SACHS (ZF SRE),TOPRAN,WALKER PRODUCTS,K&N Filters,FRIGAIR,PRASCO,BLUE PRINT,TEAMEC,ALKAR,BARUM,ASMET,SIGAM,VEMA,ABE,Magnum Technology,THERMOTEC,YAMATO,LAUBER,JC PREMIUM,PASCAL,KANACO,BTA,NEXUS";
		
		$arr = explode(",", $brands);
		
		foreach ($arr as $brand)
		{
			$sql = "SELECT * from BRANDS where BRA_BRAND='".mysql_real_escape_string($brand)."';";
			$res = mysql_query($sql) or die(mysql_error()." - ".__line__);
			
				while ($dd = mysql_Fetch_assoc($res)){
					$img = strtolower(str_replace(" ", "_", $dd['BRA_BRAND']));
					
					$sqli = "INSERT INTO `w_brands` (BRA_ID, BRA_ID_GET, BRA_MFC_CODE, BRA_BRAND, BRA_MF_NR, BRA_CONTENT, BRA_IMG, BRA_ACTIVE, title, kwords, descr, is_replace_brand) VALUES ('".mysql_real_escape_string($dd['BRA_ID'])."','".mysql_real_escape_string($dd['BRA_ID'])."','".mysql_real_escape_string($dd['BRA_MFC_CODE'])."','".mysql_real_escape_string($dd['BRA_BRAND'])."','".mysql_real_escape_string($dd['BRA_MF_NR'])."','".mysql_real_escape_string($dd['BRA_BRAND'])."','".mysql_real_escape_string($img)."',1,'".mysql_real_escape_string($dd['BRA_BRAND'])."','".mysql_real_escape_string($dd['BRA_BRAND'])."','".mysql_real_escape_string($dd['BRA_BRAND'])."',0);";
					mysql_query($sqli) or die(mysql_error()." - ".__line__);
				}
			
			
		}


		
		*/
		
		
		
		
			// $sql = "SELECT * from BRANDS;";
			// $res = $db->query($sql);
			// if (isset($res) && count($res)>0){
				// foreach ($res as $dd){
					// echo " OR ART_SUP_ID='".$dd["BRA_ID"]."'";
				// }
			// }
		
	}
	function searchGetFuel(){
		$this->layout = "ajax";
		
	
		$id = (int)$this->request("mod_id",false);
		$year = (int)$this->request("year",false);
		if ($id){
			try {
			    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER);
			    $LANG_ID = $this->getLangId();
			    $results = $client->ViewDetail(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'LANG_ID'=>$LANG_ID,
						'FUNCTION'=>'MiniCarSearchGetFuel',
						'PARAMS'=>array(
							'MOD_ID'=>$id,
							'YEAR'=>$year
						),
					)
				));
				$data = json_decode($results);
				
				if (is_string($data)) {
					echo $data;
				} else if ($data) {
					
					if (isset($data) && count($data)>0){
						$ret = '<select class="chosen-select" name="s[fuel]" id="sfuel" onchange="get_volumes(\''.$id.'\',\''.$year.'\',this.value);">';
						$ret .= '<option>Выберите топливо</option>';
						foreach ($data as $d){
							$ret .= '<option value="'.base64_encode($d->TYP_FUEL_DES_TEXT).'">'.mb_strtolower($d->TYP_FUEL_DES_TEXT, "UTF-8").'</option>';
						}
						$ret .= '</select>';
						// $ret .= '<script type="text/javascript">
								// $(document).ready(function(){
									// $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"}); 
								// });
								// </script>';
						echo($ret);
					}
					
				}
			} catch (Exception $e) {
			    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
			}
		}
		exit();
	}
	function searchGetTypes(){
		$translates = Register::get('translates');
		$this->layout = "ajax";
		$id = $this->request("mod_id",false);
		
		// $year = (int)$this->request("year",false);
		// $fuel = base64_decode($this->request("fuel",false));
		if ($id){
					$MOD_ID = base64_decode($id);
		    $MOD_ID = explode("$$", $MOD_ID);
	    $MODID = $MOD_ID[0];
		$YEARF = $MOD_ID[1];
		$YEART = $MOD_ID[2];
		//echo   $MODID;
	
	
			$this->get_one_models($MODID);
			$this->get_list_types($MODID);
			$MOD = ($this->view->MOD);

			$this->totaltime();
			$this->render("search/car_typenew");
		
			
		/*	try {
			    $options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER);
			    $LANG_ID = $this->getLangId();
			    $results = $client->ViewDetail(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'LANG_ID'=>$LANG_ID,
						'MOD_ID'=>$id,
						'FUNCTION'=>'MiniCarSearchGetType',
					)
				));
				// echo $results;
				$data = json_decode($results);
				if (is_string($data)) {
					echo $data;
				} else if ($data) {
					if (isset($data) && count($data)>0){
						$ret = '<select id="carob" class="form-control" name="s[type]" onchange="gotocatalog(this.value);">';
						$ret .= '<option>'.$translates['objname'].'</option>';
							foreach ($data as $d){
							//	var_dump($d);
							$ret .= '<option value="'.$d->TYP_ID.'">'.$d->TYP_CDS_TEXT.' ('.$d->TYP_KW_FROM.'Kw / '.$d->TYP_HP_FROM.'hp)</option>';
							}
						$ret .= '</select>';
						// $ret .= '<script type="text/javascript">
								// $(document).ready(function(){
									// $("#carob").chosen({no_results_text: "Oops, nothing found!"}); 
								// });
								// </script>';
						echo($ret);
					} else {
						$ret = '<select id="carob" class="form-control" name="s[type]" onchange="">';
						$ret .= '<option>'.$translates['objname'].'</option>';
						$ret .= '<option>-</option>';
						$ret .= '</select>';
						echo($ret);
					}
					
				} else {
						$ret = '<select id="carob" class="form-control" name="s[type]" onchange="">';
						$ret .= '<option>'.$translates['objname'].'</option>';
						$ret .= '</select>';
						echo($ret);
				}
			} catch (Exception $e) {
			    echo "<h2>Exception Error! The server is unavailable.".__line__."</h2>";
			} */
		}
		exit();
	}
	
	public function remembercar(){
		
		$this->layout = "ajax";
		require_once 'application/templates/default/elements/product.phtml';
		
		$mark_id = $this->request("mark_id",null);
		$mark_txt = $this->request("mark_txt",null);
		$model_id = $this->request("model_id",null);
		$model_txt = $this->request("model_txt",null);
		$year = $this->request("year",null);
		$fuel = base64_decode($this->request("fuel",null));
		$engine_id = $this->request("engine_id",null);
		$engine_txt = $this->request("engine_txt",null);
		
		try {
			$client = new SoapClient(SOAP, array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE));
			$results = $client->searchTree(array('request'=>array('ACCESS_C'=>array('SERVER'=>$_SERVER,'KEY'=>KEY),'TYP_ID'=>(int)$engine_id,'STR_ID'=>10001,'LANG_ID'=>$this->getLangId())));
			$tree = json_decode($results);
			if (!is_string($tree)) {

				$_SESSION['__remembercar']=array();
				$_SESSION['__remembercar']['mark_id']=$mark_id;
				$_SESSION['__remembercar']['mark_txt']=$mark_txt;
				$_SESSION['__remembercar']['model_id']=$model_id;
				$_SESSION['__remembercar']['model_txt']=$model_txt;
				$_SESSION['__remembercar']['year']=$year;
				$_SESSION['__remembercar']['fuel']=$fuel;
				$_SESSION['__remembercar']['engine_id']=$engine_id;
				$_SESSION['__remembercar']['engine_txt']=$engine_txt;
				$_SESSION['__remembercar']['tree']=$tree;
				
				$this->view->rememberCar = (isset($_SESSION['__remembercar']) && $_SESSION['__remembercar'])?$_SESSION['__remembercar']:array();
			}
		} catch (Exception $e){ }
	}
	
	public function unremembercar(){
		unset($_SESSION['__remembercar']);
		$this->redirectUrl('/');
	}
	
	/* ******************** ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ******************* */
	
	function beforeAction(){
		parent::beforeAction();
		
		if (isset($this->accountData['id']) && $this->accountData['id']){
			Register::set('lamp_search_notes_exist',SearchnotepadModel::getSearchNotesSimple($this->accountData['id']));
		} else {
			Register::set('lamp_search_notes_exist',array());
		}
		
		$this->setPathDirect();
		$this->getCartOfLastOrdersForColor();
		
		$ajax = $this->request("ajax",false);
		if ($ajax) {
			$this->layout = "ajax";
			$this->view->ajax_params = true;
		}
		
// 		$this->view->hide_left_menu = true;
	}

	function beforeRender() {
		parent::beforeRender();
	}
}
?>