<?php

class pixtinautoru extends SearchController {
	
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
			$url = "http://shop.pixtinauto.ru/api/search/".$this->config['login_uid']."/".$detailNum."/".$GROUP_ID."/";
			$groups = file_get_contents($url);
			$groups = mb_convert_encoding($groups, 'UTF-8', 'auto');
			$groups = simplexml_load_string($groups);
			
			$groups1 = (isset($groups->main_prices) && count($groups->main_prices)>0)?$groups->main_prices:array();
			$result1 = (array)$groups1;
			$result1 = (array)(isset($result1['price']) && count($result1['price'])>0)?$result1['price']:array();
			
			$groups2 = (isset($groups->original_replaces) && count($groups->original_replaces)>0)?$groups->original_replaces:array();
			$result2 = (array)$groups2;
			$result2 = (array)(isset($result2['price']) && count($result2['price'])>0)?$result2['price']:array();
			
			$result = array_merge((array)$result1,(array)$result2);
			$result = (array)$result;
			
			/* **** */
			$newResult = array();
			if (isset($result['id']) && $result['id']){
				$newResult []= $result;
			}
			else {
				$newResult = $result;
			}
			$result = $newResult;
			/* **** */
			
			if (isset($result) && count($result)>0) {
				
				$dataInDB = array();
				$i=0; foreach ($result as $dd){ $i++;
				
					$dd = (array)$dd;
					
//					var_dump($dd);
//					echo('<br><br>');
					
					$ID = "wbs-avtotoru-".$dd['id'];
					$IMPORT_ID = $this->config['importer_id'];
					$BRAND_ID = 0;
					$BRAND_NAME = strtoupper($dd['brand']);
					$ARTICLE = strtoupper($dd['number']);
					$PRICE = strtoupper($dd['price_rub']);
					$DESCR = strtoupper($dd['name'].' '.$dd['supplier']);
					$BOX = strtoupper($dd['count']);
					$DELIVERY = strtoupper($dd['max_time']);
					$WEIGHT = "";
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
//			var_dump($exp->getMessage()); exit(); 
		}
		
	}
	function FindCatalog($detailNum=''){ 
		
		try {
		
			$detailNum = FuncModel::stringfilter($detailNum);
			
			$url = "http://shop.pixtinauto.ru/api/search/".$this->config['login_uid']."/".$detailNum."/";
			$groups = file_get_contents($url);
			$groups = mb_convert_encoding($groups, 'UTF-8', 'auto');
			$groups = simplexml_load_string($groups);
			
			$groups = (array)$groups;
			$originalsSearch = $originalsSearchRet = array();
			if (isset($groups['item']) && count($groups['item'])>0){
				$i=0; foreach ($groups['item'] as $group){ $i++;
				
					$group = (array)$group;
				
					$originalsSearch = array();
					$originalsSearch [$i]-> IMP_ID = $this->config['id'];
					$originalsSearch [$i]-> SUP_ID = strtoupper($group['brand']);
					$originalsSearch [$i]-> ART_ARTICLE_NR = strtoupper($group['number']);
					$originalsSearch [$i]-> SUP_BRAND = strtoupper($group['brand']);
					$originalsSearch [$i]-> TEX_TEXT = strtoupper($group['name']);
					$originalsSearch [$i]-> URL = strtoupper('/search/wbs/?article='.$group['number'].'&brand='.$group['brand']);
					$originalsSearchRet []= serialize($originalsSearch[$i]);
				}
				return $originalsSearchRet;
			}
		
		} catch (Exception $e){
		}
	}
}

?>