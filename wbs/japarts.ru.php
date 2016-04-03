<?php

class japartsru extends SearchController {
	
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
				
		$article = isset($_REQUEST['article'])?$_REQUEST['article']:false;
		
		try { 
			
			$url = "http://www.japarts.ru/?id=ws;action=search;login=".$this->config['login'].";pass=".$this->config['pass'].";detailnum=".FuncModel::stringfilter($article).";cross=1;";
			$result = file_get_contents($url);
			$result = iconv('WINDOWS-1251', 'UTF-8', $result);
			$result = str_replace(array("[","]"), "", $result);
			$arrayList = explode("},{", $result);
			
			$res = array();
			foreach ($arrayList as $dd){
				$dd = '{'.$dd.'}';
				$dd = str_replace('{{', '{', $dd);
				$dd = str_replace('}}', '}', $dd);
				$dd = '['.$dd.']';
				$dd = json_decode($dd);
				$res []= $dd[0]; 
			}
			
			switch (json_last_error()) {
// 				case JSON_ERROR_NONE:
// 					echo ' - Ошибок нет';
// 					break;
				case JSON_ERROR_DEPTH:
					echo ' - Достигнута максимальная глубина стека';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					echo ' - Некорректные разряды или не совпадение режимов';
					break;
				case JSON_ERROR_CTRL_CHAR:
					echo ' - Некорректный управляющий символ';
					break;
				case JSON_ERROR_SYNTAX:
					echo ' - Синтаксическая ошибка, не корректный JSON';
					break;
				case JSON_ERROR_UTF8:
					echo ' - Некорректные символы UTF-8, возможно неверная кодировка';
					break;
// 				default:
// 					echo ' - Неизвестная ошибка';
// 					break;
			}
			
			if (isset($res) && count($res)>0){
				
				if (count($res) == 1){
					$aData = array($res);
				}
				else {
					$aData = $res;
				}
				
				$dataInDB = array();
				$i=0; foreach ($aData as $dd){ $i++;
				
					$dd = (array)$dd;
				
					$ID = "wbs-japartsry-".$i;
					$IMPORT_ID = $this->config['importer_id'];
					$BRAND_ID = 0;
					$BRAND_NAME = strtoupper($dd['makename']);
					$ARTICLE = strtoupper($dd['detailnum']);
					$PRICE = $dd['pricerur'];
					$DESCR = $dd['detailname'].' '.$dd['country'];
					$BOX = $dd['quantity'];
					$DELIVERY = $dd['timegar'];
					$WEIGHT = "";
					$IMG_URL = "";
				
					if (isset($INCORRECT_NAMES) && in_array($BRAND_NAME,$INCORRECT_NAMES)) {
						$BRAND_NAME = (isset($CORRECT_NAMES[$BRAND_NAME])?$CORRECT_NAMES[$BRAND_NAME]:$BRAND_NAME);
					}
					
					/* ***************** */
					$isBOX = true;
					if ($this->config['param_typeview'] == 1){
						if ((int)$BOX > 0)
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
				return $dataInDB;
			}
			
		} catch (Exception $e) { 
			return false;
		}
	}
	function FindCatalog($detailNum=''){ 
		return array();
	}
}

?>