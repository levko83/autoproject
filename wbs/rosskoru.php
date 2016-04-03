<?php

class rosskoru extends SearchController {
	
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
	}
	
	function searchAll($serialize=true,$account=array()) {
		
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
		
		$client = new SoapClient('http://nk.rossko.ru/service/v1/GetSearch?wsdl',array('soap_version'=>SOAP_1_2,'encoding'=>'UTF-8')); 
		$result = $client->GetSearch(array(
			'KEY1'=>$this->config['pass'],
			'KEY2'=>$this->config['pass2'],
			'TEXT'=>FuncModel::stringfilter($ARTICLE)
			)
		);
		
		$RSSP = array();
		if (isset($result->SearchResults->SearchResult->PartsList->Part) && count($result->SearchResults->SearchResult->PartsList->Part) == 1){
			$RSSP []= $result->SearchResults->SearchResult->PartsList;
		}
		else {
			$RSSP = $result->SearchResults->SearchResult->PartsList;
		}
		
		$Array = array();
		if (count($RSSP)>0){
			$dataInDB = array();
			$i=0;
			
				foreach ($RSSP as $dd1){ $i++;
				foreach ($dd1 as $dd){
				$dd = (array)$dd;
				
				$combCLP2 = array();
				if (isset($dd['StocksList']->Stock) && count($dd['StocksList']->Stock) == 1){
					$combCLP2 []= $dd['StocksList']->Stock;
				}
				else {
					$combCLP2 = $dd['StocksList']->Stock;
				}
				
				if (isset($combCLP2) && count($combCLP2)>0){
					foreach ($combCLP2 as $CSS2){
						$CSS2 = (array)$CSS2;
						
						$Array []= array(
							'BRAND_NAME'=>$dd['Brand'],
							'ARTICLE'=>$dd['PartNumber'],
							'DESCR'=>$dd['Name'],
							
							'PRICE'=>$CSS2['Price'],
							'BOX'=>$CSS2['Count'],
							'DELIVERY'=>$CSS2['DeliveryTime'],
						);
						
					}
				}
				
				$combCLP = array();
				if (isset($dd['CrossesList']->Part) && count($dd['CrossesList']->Part) == 1){
					$combCLP []= $dd['CrossesList']->Part;
				}
				else {
					$combCLP = $dd['CrossesList']->Part;
				}
				
				if (isset($combCLP) && count($combCLP)>0){
					foreach ($combCLP as $CrossesList){
						$CrossesList = (array)$CrossesList;
						
//						var_dump($CrossesList);
//						var_dump(count($CrossesList['StocksList']->Stock));
//						echo('<br><br>');
						
						if (isset($CrossesList['StocksList']->Stock) && count($CrossesList['StocksList']->Stock)>0){
							$PP = array();
							if (count($CrossesList['StocksList']->Stock) == 1){
								$PP []= $CrossesList['StocksList']->Stock;
							}
							else {
								$PP = $CrossesList['StocksList']->Stock;
							}
							foreach ($PP as $CSS){
								$CSS = (array)$CSS;
								
								$Array []= array(
									'BRAND_NAME'=>$CrossesList['Brand'],
									'ARTICLE'=>$CrossesList['PartNumber'],
									'DESCR'=>$CrossesList['Name'],
									
									'PRICE'=>$CSS['Price'],
									'BOX'=>$CSS['Count'],
									'DELIVERY'=>$CSS['DeliveryTime'],
								);
								
							}
						}
					}
				}
			}}
				
			$ab1 = $ab2 = $ab3 = $ab4 = $ab5 = '';
			if (count($Array)>0){
				
			usort($Array,'array_sort_brand_wbs');
			
			$newArray = array();
			foreach ($Array as $key=>$Ar){
				if (
					$ab1 != $Ar['BRAND_NAME'] && 
					$ab2 != $Ar['ARTICLE'] &&
					$ab3 != $Ar['PRICE'] &&
					$ab4 != $Ar['BOX'] &&
					$ab5 != $Ar['DELIVERY']
				){
					$newArray []= $Ar;
				}
				$ab1 = $Ar['BRAND_NAME'];
				$ab2 = $Ar['ARTICLE'];
				$ab3 = $Ar['PRICE'];
				$ab4 = $Ar['BOX'];
				$ab5 = $Ar['DELIVERY'];
			}
			$Array = $newArray;
				
			foreach ($Array as $key=>$Ar){
				
				$ID = "wbs-rosskoru-".$key;
				$IMPORT_ID = $this->config['importer_id'];
				$BRAND_ID = 0;
				
				$BRAND_NAME = strtoupper($Ar['BRAND_NAME']);
				$ARTICLE = strtoupper($Ar['ARTICLE']);
				$PRICE = strtoupper($Ar['PRICE']);
				$DESCR = $Ar['DESCR'];
				$BOX = $Ar['BOX'];
				$DELIVERY = $Ar['DELIVERY'];
				
				$WEIGHT = "";
				$IMG_URL = "";
			
				/* ***************** */
				if (isset($INCORRECT_NAMES) && in_array($BRAND_NAME,$INCORRECT_NAMES)) {
					$BRAND_NAME = (isset($CORRECT_NAMES[$BRAND_NAME])?$CORRECT_NAMES[$BRAND_NAME]:$BRAND_NAME);
				}
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
					if(preg_match('/('.$str_check.')/i', $BRAND_NAME) && (FuncModel::stringfilter($ARTICLE) == FuncModel::stringfilter($ARTICLE_REGET))){
						$ORIGINAL = 1;
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
			
			}}
			
//			echo('<pre>');
//			echo('<br><br>');
//			echo('<br><br>');
//			echo('<br><br>');
//			echo('<br><br>');
//			var_dump($Array);
//			exit();
			
			return $dataInDB;
		}
	}
	function FindCatalog($detailNum=''){ 
		return array();
	}
}
	
function array_sort_brand_wbs($x,$y){
	$lft = $x['BRAND_NAME'];
	$rgt = $y['BRAND_NAME'];
	if ($lft == $rgt){ return 0; }
	return ($lft < $rgt) ? -1 : 1;
}

?>