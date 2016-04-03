<?php

class absautoru extends SearchController {

	public $imp_id;
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
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
		
		$ARTICLE = FuncModel::stringfilter($ARTICLE);
		$GROUP_ID = urldecode($GROUP_ID);
		
		$ch = curl_init('http://abs8.abs-auto.ru/?route=api/restful/get_price_list&login='.$this->config['login'].'&password='.$this->config['pass'].'&format=json&code='.$ARTICLE.'&producer='.$GROUP_ID);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$data = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($data, true);
		
		if (isset($result['result']) && isset($result['result']['error']) && $result['result']['error']){
			return array();
		}
		
		$dataInDB = array();
		if (isset($result['result']) && count($result['result'])>0){
			$i=0; foreach ($result['result'] as $dd){ $i++;
				
				$ID = "wbs-absautoru-".$i;
				$IMPORT_ID = $this->config['importer_id'];
				$BRAND_ID = strtoupper($dd['producer']);
				$BRAND_NAME = strtoupper($dd['producer']);
				$ARTICLE = strtoupper($dd['code']);
				$PRICE = str_replace(",","",str_replace(' р.','',$dd['price']));
				//$DESCR = $dd['caption'].' '.$dd['store'];
				$DESCR = $dd['caption'];
				$BOX = $dd['quantity'];
				$DELIVERY = $dd['delivery'];
				$WEIGHT = "";
				$IMG_URL = "";
				$MIN = $dd['minquantity'];
				
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
		}
		return $dataInDB;
	}
	function FindCatalog($detailNum='OC47'){ 
		
		$ch = curl_init('http://abs8.abs-auto.ru/?route=api/restful/get_producer_list&login='.$this->config['login'].'&password='.$this->config['pass'].'&format=json&code='.$detailNum);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$data = curl_exec($ch);
		curl_close($ch);
		$groups = json_decode($data, true);
		
		if (isset($groups['result']) && isset($groups['result']['error']) && $groups['result']['error']){
			return array();
		}
		
		$originalsSearch = array();
		if (isset($groups['result']) && count($groups['result'])>0){
			$i=0; foreach ($groups['result'] as $group){ $i++;
				$originalsSearch [$i]-> IMP_ID = $this->config['id'];
				$originalsSearch [$i]-> SUP_ID = strtoupper(str_replace('"','',$group['producer']));
				$originalsSearch [$i]-> ART_ARTICLE_NR = strtoupper($group['code']);
				$originalsSearch [$i]-> SUP_BRAND = strtoupper(str_replace('"','',$group['producer']));
				$originalsSearch [$i]-> TEX_TEXT = $group['caption'];
				$originalsSearch [$i]-> URL = '/search/wbs/?article='.urlencode($group['code']).'&brand='.urlencode($group['producer']);
				
				$originalsSearch [$i]= serialize($originalsSearch [$i]);
			}
			return $originalsSearch;
		}
	}
}

?>