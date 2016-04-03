<?php

//error_reporting(E_ALL | E_STRICT);
//ini_set('display_errors', 'On');

class lazukautocom extends SearchController {
	
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
		
		try {
			
			$detailNum = FuncModel::stringfilter($ARTICLE);
			
			$url = "http://lazukauto.com/test/hs/services/getprice?dogovor_id=".rawurlencode($this->config['login_uid'])."&passw=".rawurlencode($this->config['pass'])."&art=".rawurlencode($detailNum)."&pr=3&brand=".rawurlencode($GROUP_ID);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); 
			$ex = curl_exec($curl);
			$ex = nl2br($ex);
			$result = explode("<br />",$ex);
			
			if (isset($result) && count($result)>0) {
				unset($result[0]);
				
				$dataInDB = array();
				$i=0; foreach ($result as $dd){ $i++;
				
					$dd = explode("	",$dd);
					
					$ID = "wbs-lazuk-".$i;
					$IMPORT_ID = $this->config['importer_id'];
					$BRAND_ID = 0;
					$BRAND_NAME = strtoupper($dd[3]);
					$ARTICLE = strtoupper($dd[1]);
					$PRICE = strtoupper(str_replace(array(chr(194).chr(160)," "),"",str_replace(",",".",$dd[6])));
					$DESCR = $dd[4].' / '.$dd[11];
					$BOX = $dd[7];
					$DELIVERY = $dd[13];
					$WEIGHT = $dd[10];
					$IMG_URL = "";
				
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
				}
				if (isset($result['Info']['Errors']) && count($result['Info']['Errors'])>0) {
					echo join(', ', $result['Info']['Errors']);
				}

				return $dataInDB;
			}
			
		} catch (Exception $exp) {
		}
		
	}
	function FindCatalog($detailNum=''){ 
		
		try {
		
			$detailNum = FuncModel::stringfilter($detailNum);
			$url = "http://lazukauto.com/test/hs/services/getbrands/?dogovor_id=".$this->config['login_uid']."&passw=".$this->config['pass']."&art=".$detailNum."&pr=3";
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); 
			$ex = curl_exec($curl);
			$ex = nl2br($ex);
			$groups = explode("<br />",$ex);
			
			$originalsSearch = $originalsSearchRet = array();
			if (isset($groups) && count($groups)>0){
				unset($groups[0]);
				$i=0; foreach ($groups as $group){ $i++;
					if (trim($group)){
				
						$group = str_replace("\r\n",'',$group);
						$group = str_replace("\n",'',$group);
						$group = str_replace("\r",'',$group);
						
						$array = array(
							'IMP_ID' => $this->config['id'],
							'SUP_ID' => strtoupper($group),
							'ART_ARTICLE_NR' => strtoupper($detailNum),
							'SUP_BRAND' => strtoupper($group),
							'TEX_TEXT' => "",
							'URL' => '/search/wbs/?article='.trim($detailNum).'&brand='.trim($group),
						);
						$originalsSearch = NULL;
						$originalsSearch [$i]= (object)$array;
						$originalsSearchRet []= serialize($originalsSearch[$i]);
					}
				}
				return $originalsSearchRet;
			}
		
		} catch (Exception $e){
		}
	}
}

?>