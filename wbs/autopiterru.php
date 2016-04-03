<?php

class autopiter extends SearchController {

	public $imp_id;
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
	}

	function connect(){ 
		$client = new SoapClient('http://service.autopiter.ru/price.asmx?WSDL',array('soap_version'=>SOAP_1_2,'encoding'=>'UTF-8')); 
		$result = $client->IsAuthorization(); 
		if (!$result->IsAuthorizationResult) { 
			$result = $client->Authorization(array('UserID'=>$this->config['login'],'Password'=>$this->config['pass'],'Save'=>true)); 
		}
		return $client;
	}
	
	function getPriceId($itemCatalogId){ 
		$client = $this->connect();
		
		try { 
			$details = $client->GetPriceId(array ('ID' => $itemCatalogId, 'IdArticleDetail' => -1, 'FormatCurrency' => 'РУБ', 'SearchCross' => true)); 
		} catch (Exception $e) { return false; }
		if (isset($details->GetPriceIdResult) && !$details->GetPriceIdResult) { return false; }
		return (isset($details->GetPriceIdResult->BasePriceForClient)?$details->GetPriceIdResult->BasePriceForClient:''); 
	}
	
	function getPriceId2($itemCatalogId){ 
		$client = $this->connect();
	 
		try { 
			$details = $client->GetPriceId(array ('ID' => $itemCatalogId, 'IdArticleDetail' => -1, 'FormatCurrency' => 'РУБ', 'SearchCross' => false)); 
		} catch (Exception $e) { return false; }
		if (isset($details->GetPriceIdResult) && !$details->GetPriceIdResult) { return false; }
		return (isset($details->GetPriceIdResult->BasePriceForClient)?$details->GetPriceIdResult->BasePriceForClient:''); 
	}
	
	function searchAll($serialize=true,$account=array()){
		
		$CORRECT_BRANDS = $this->wbs_correct_brandnames();
		$INCORRECT_NAMES = $CORRECT_NAMES = array();
		if (isset($CORRECT_BRANDS) && count($CORRECT_BRANDS)>0){
			foreach ($CORRECT_BRANDS as $INCORRECT){
				$INCORRECT_NAMES []= $INCORRECT['incorrect'];
				$CORRECT_NAMES [$INCORRECT['incorrect']]= $INCORRECT['correct'];
			}
		}
		
		/* ***************************** */
		
		$ARTICLE_REGET = isset($_REQUEST['article'])?$_REQUEST['article']:false;
		$BRAND_REGET_ID = isset($_REQUEST['brand'])?$_REQUEST['brand']:false;
		$BrandsModel = BrandsModel::getById($BRAND_REGET_ID);
		if ($BrandsModel){
			$eval = $BrandsModel['BRA_BRAND'];
		}else{
			$eval = $BRAND_REGET_ID;
		}
		
		/* ***************************** */
		
		$ARTICLE = isset($_REQUEST['article'])?$_REQUEST['article']:false;
		$GROUP_ID = isset($_REQUEST['brand'])?$_REQUEST['brand']:false;
		$Q = ($this->config['is_groups'])?$GROUP_ID:$ARTICLE;
		$GROUP_ID = isset($_REQUEST['option'][$this->config['id']])?($_REQUEST['option'][$this->config['id']]):$Q;
		
		$result = $this->getPriceId($GROUP_ID);
		if (!$result) {
			$result = $this->getPriceId2($GROUP_ID);
		}
		
		if ($result){
			if (count($result)==1){ 
				$result = array($result);
			}
			if (count($result)>0){
				$dataInDB = array();
				$i=0; foreach ($result as $dd){ $i++;
				
					$ID 		= "wbs-autopiter-".$dd->ID;
					$IMPORT_ID 	= $this->config['importer_id'];
					$BRAND_ID 	= 0;
					$BRAND_NAME = strtoupper($dd->NameOfCatalog);
					$ARTICLE 	= strtoupper($dd->Number);
					$PRICE 		= $dd->SalePrice;
					$DESCR 		= @$dd->NameRus.' '.str_replace(' ','',@$dd->CitySupply)." ".str_replace(' ','',@$dd->CountrySupply);
					$BOX 		= $dd->NumberOfAvailable;
					$DELIVERY 	= $dd->NumberOfDaysSupply;
					$WEIGHT 	= "";
					$IMG_URL 	= "";
					
					if (isset($INCORRECT_NAMES) && in_array($BRAND_NAME,$INCORRECT_NAMES)) {
						$BRAND_NAME = (isset($CORRECT_NAMES[$BRAND_NAME])?$CORRECT_NAMES[$BRAND_NAME]:$BRAND_NAME);
					}
				
					/* ***************** */
					$isBOX = true;
					if ($this->config['param_typeview'] == 1){
						if ((int)$BOX > 0 || $BOX === 'есть' || $BOX === '+')
							$isBOX = true;
						else 
							$isBOX = false;
					}
				
					if ($isBOX){
						
						$IMPORTER_DATA = $this->ImportersModel_getById($IMPORT_ID);
						$OUTPRICE_DATA = OutpriceModel::generate($IMPORTER_DATA,($account)?$account:$this->getAccountData(),$PRICE,$BRAND_NAME);
						
						$ORIGINAL = 0;
						list($str_check,) = explode(" ",$eval);
						if(
							(
								preg_match('/('.$str_check.')/i', $BRAND_NAME) && 
								(FuncModel::stringfilter($ARTICLE) == FuncModel::stringfilter($ARTICLE_REGET))
							) || (
								($str_check == $BRAND_NAME) && 
								(FuncModel::stringfilter($ARTICLE) == FuncModel::stringfilter($ARTICLE_REGET))
							) || (
								is_int(strpos($eval, $BRAND_NAME)) && 
								(FuncModel::stringfilter($ARTICLE) == FuncModel::stringfilter($ARTICLE_REGET))
							)
						){
							$ORIGINAL = 1;
							$BRAND_NAME = $eval;
						}
						
						$d= array(
							'ORIGINAL'	=>	$ORIGINAL,
							'ART_ID' => 0,
							'DB_IMPORT_ID' => $IMPORT_ID,
							'SUP_ID' => $BRAND_ID,
							'SUP_BRAND' => $BRAND_NAME,
							'ART_ARTICLE_NR' => $ARTICLE,
							'ART_ARTICLE_NR_CLEAR' => FuncModel::stringfilter($ARTICLE),
							'DB_PRICE' => $PRICE,
							'TEX_TEXT' => $DESCR,
							'SUP_UNICODE_BRAND'=>'',
							'CRITERIA'=>array(),
							'PATH_IMAGES'=>array(),
							'PATH_LOGOS'=>array(),
							'PRICES'=>array(),
							'FR'=>array(),
							
							'DB_ID' => $ID,
							'DB_IMPORT_ID' => $IMPORT_ID,
							'DB_BRAND_ID' => $BRAND_ID,
							'DB_BRAND_NAME' => $BRAND_NAME,
							'DB_ARTICLE' => $ARTICLE,
							'DB_PRICE' => $PRICE,
							'DB_DESCR' => $DESCR,
							'DB_BOX' => $BOX,
							'DB_DELIVERY' => ($DELIVERY+$IMPORTER_DATA['delivery']),
							'DB_WEIGHT' => $WEIGHT,
							'DB_IMG_URL' => $IMG_URL,
							
							'MY_PRICE'=>array(	'ID' 			=> $ID,
												'IMPORT_ID' 	=> $IMPORT_ID,
												'BRAND_ID' 		=> $BRAND_ID,
												'BRAND_NAME' 	=> $BRAND_NAME,
												'ARTICLE' 		=> $ARTICLE,
												'PRICE' 		=> $PRICE,
												'DESCR' 		=> $DESCR,
												'BOX' 			=> $BOX,
												'DELIVERY' 		=> ($DELIVERY+$IMPORTER_DATA['delivery']),
												'WEIGHT' 		=> $WEIGHT,
												'IMG_URL' 		=> $IMG_URL,
												'IMPORTER_DATA'	=>	$IMPORTER_DATA,
												'OUTPRICE_DATA'	=>	$OUTPRICE_DATA,
												'RESULT_PRICE_SALE'	=>	(($IMPORTER_DATA['currency'])?($IMPORTER_DATA['currency']*$OUTPRICE_DATA['resultPRICE']):$OUTPRICE_DATA['resultPRICE']),
												),
												
							'ID' 			=> $ID,
							'IMPORT_ID' 	=> $IMPORT_ID,
							'BRAND_ID' 		=> $BRAND_ID,
							'BRAND_NAME' 	=> $BRAND_NAME,
							'ARTICLE' 		=> $ARTICLE,
							'PRICE' 		=> $PRICE,
							'DESCR' 		=> $DESCR,
							'BOX' 			=> $BOX,
							'DELIVERY' 		=> ($DELIVERY+$IMPORTER_DATA['delivery']),
							'WEIGHT' 		=> $WEIGHT,
							'IMG_URL' 		=> $IMG_URL,
							'IS_CROSS'		=>	0,
							'IS_ACCOUNT'	=>	0,
							'IMPORTER_DATA'	=>	$IMPORTER_DATA,
							'OUTPRICE_DATA'	=>	$OUTPRICE_DATA,
							'RESULT_PRICE_SALE'	=>	(($IMPORTER_DATA['currency'])?($IMPORTER_DATA['currency']*$OUTPRICE_DATA['resultPRICE']):$OUTPRICE_DATA['resultPRICE']),
						);
						
						if ($serialize){
							$d = (object)$d;
							$dataInDB []= serialize($d);
						}
						else {
							$dataInDB []= ($d);	
						}
					}
					/* ***************** */
				}
				return $dataInDB;
			}
		}
	}
	function FindCatalog($detailNum='50610TA0A10'){ 
		
		try {
			$client = $this->connect();
		
			$catalogObj = $client->FindCatalog(array('ShortNumberDetail' => $detailNum));
			if (!isset($catalogObj->FindCatalogResult)) { 
		  		return false;
			}
			$groups = (isset($catalogObj->FindCatalogResult->SearchedTheCatalog)?$catalogObj->FindCatalogResult->SearchedTheCatalog:false); 
			if (!$groups)
				return false;
			$originalsSearch = array();
			
//			echo('<pre>');
//			var_dump($groups);
//			echo('</pre>');
			
			if (isset($groups) && count($groups)>0){
				
				if (count($groups) == 1){
					$groups = array($groups);
				}
				
				$CORRECT_BRANDS = $this->wbs_correct_brandnames();
				$INCORRECT_NAMES = $CORRECT_NAMES = array();
				if (isset($CORRECT_BRANDS) && count($CORRECT_BRANDS)>0){
					foreach ($CORRECT_BRANDS as $INCORRECT){
						$INCORRECT_NAMES []= $INCORRECT['incorrect'];
						$CORRECT_NAMES [$INCORRECT['incorrect']]= $INCORRECT['correct'];
					}
				}

				$originalsSearchRet = array();
				$i=0; foreach ($groups as $group){ $i++;
				
					$BRAND_NAME = strtoupper($group->Name);
					if (isset($INCORRECT_NAMES) && in_array($BRAND_NAME,$INCORRECT_NAMES)) {
						$BRAND_NAME = (isset($CORRECT_NAMES[$BRAND_NAME])?$CORRECT_NAMES[$BRAND_NAME]:$BRAND_NAME);
					}
					$originalsSearch = (object)array(
						'IMP_ID' => $this->config['id'],
						'SUP_ID' => strtoupper($group->id),
						'ART_ARTICLE_NR' => strtoupper($group->ShortNumber),
						'SUP_BRAND' => $BRAND_NAME,
						'TEX_TEXT' => $group->NameDetail,
						'URL' => '/search/wbs/?article='.$group->ShortNumber.'&brand='.$group->id,
					);
					$originalsSearchRet []= serialize($originalsSearch);
				}
				
				return $originalsSearchRet;
			}
		}
		catch (Exception $e){
// 			var_dump($e->getMessage());
		}
	}
}

?>