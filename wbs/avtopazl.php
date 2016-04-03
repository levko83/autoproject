<?php

class avtopazl extends SearchController {
	
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
	}
	
	function unicon($str, $to_uni = false) {
		$cp = Array (
		"А" => "&#x410;", "а" => "&#x430;",
		"Б" => "&#x411;", "б" => "&#x431;",
		"В" => "&#x412;", "в" => "&#x432;",
		"Г" => "&#x413;", "г" => "&#x433;",
		"Д" => "&#x414;", "д" => "&#x434;",
		"Е" => "&#x415;", "е" => "&#x435;",
		"Ё" => "&#x401;", "ё" => "&#x451;",
		"Ж" => "&#x416;", "ж" => "&#x436;",
		"З" => "&#x417;", "з" => "&#x437;",
		"И" => "&#x418;", "и" => "&#x438;",
		"Й" => "&#x419;", "й" => "&#x439;",
		"К" => "&#x41A;", "к" => "&#x43A;",
		"Л" => "&#x41B;", "л" => "&#x43B;",
		"М" => "&#x41C;", "м" => "&#x43C;",
		"Н" => "&#x41D;", "н" => "&#x43D;",
		"О" => "&#x41E;", "о" => "&#x43E;",
		"П" => "&#x41F;", "п" => "&#x43F;",
		"Р" => "&#x420;", "р" => "&#x440;",
		"С" => "&#x421;", "с" => "&#x441;",
		"Т" => "&#x422;", "т" => "&#x442;",
		"У" => "&#x423;", "у" => "&#x443;",
		"Ф" => "&#x424;", "ф" => "&#x444;",
		"Х" => "&#x425;", "х" => "&#x445;",
		"Ц" => "&#x426;", "ц" => "&#x446;",
		"Ч" => "&#x427;", "ч" => "&#x447;",
		"Ш" => "&#x428;", "ш" => "&#x448;",
		"Щ" => "&#x429;", "щ" => "&#x449;",
		"Ъ" => "&#x42A;", "ъ" => "&#x44A;",
		"Ы" => "&#x42B;", "ы" => "&#x44B;",
		"Ь" => "&#x42C;", "ь" => "&#x44C;",
		"Э" => "&#x42D;", "э" => "&#x44D;",
		"Ю" => "&#x42E;", "ю" => "&#x44E;",
		"Я" => "&#x42F;", "я" => "&#x44F;"
		);
		
		
		if ($to_uni) {
			$str = strtr($str, $cp);
		} else {
			foreach ($cp as $c) {
				$cpp[$c] = array_search($c, $cp);
			}
			$str = strtr($str, $cpp);
		}
	
		return $str;
	} 
	
	function ccUrl($ARTICLE,$check=true){
		$ch = curl_init("http://avtopazl.by/services/details-search.xml?id=".FuncModel::stringfilter($ARTICLE)."&user_id=".$this->config['login']."&password=".$this->config['pass']."");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		if ($check){
			curl_setopt($ch,CURLOPT_TIMEOUT,30);
		}
		$d = curl_exec($ch);
		return $d;
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
		
		$d = '';
		$chK = $this->ccUrl($ARTICLE);
		if ($chK){
			$d = $this->ccUrl($ARTICLE,false);
		}
		$d = str_replace(array("&Scaron;","&ndash;","&hellip;","&rsquo","&micro;","&sbquo;","&deg;","&raquo;","&amp;","&gt;","&nbsp;","&trade;","&curren;"),"",$d);
		$d = $this->unicon($d);
		$d = str_replace('&','',$d);
		$d = mb_convert_encoding($d, 'UTF-8', 'auto');
		$xml = simplexml_load_string($d);
		
		if (isset($xml->products[0]) && count($xml->products[0])>0){
			$dataInDB = array();
			$i=0; foreach ($xml->products[0] as $key=>$dd){ $i++;
			
				$ID = "wbs-avtopazl-".$i;
				$IMPORT_ID = $this->config['importer_id'];
				$BRAND_ID = 0;
				$BRAND_NAME = strtoupper($dd->owner);
				$ARTICLE = strtoupper($dd->code);
				$PRICE = strtoupper($dd->price_by);
				$DESCR = strtoupper($dd->title);
				$BOX = strtoupper($dd->quantity).' (<b>'.$dd->quality.'%</b>)';;
				$DELIVERY = strtoupper($dd->day_1);
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
			return $dataInDB;
		}
	}
	function FindCatalog($detailNum=''){ 
		return array();
	}
}

?>