<?php

class partsibru extends SearchController {

	public $imp_id;
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
	}
	
	function callPartsib($funcName, $paramsHash){
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		
		$paramsString = "";
		foreach($paramsHash as $key=>$val)
			$paramsString .= "&".$key."=".$val;
			
		curl_setopt($ch, CURLOPT_URL, 'https://partsib.ru/service.php?p='.$funcName.$paramsString);
		
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Internet Shop X5');
		$data = curl_exec($ch);
		curl_close($ch);
		
		return json_decode($data);
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
		
		
		$auth = $this->callPartsib("auth", array("username"=>$this->config['login'], "password"=>$this->config['pass']));
		if (isset($auth->rows[0]->sessionId) && $auth->rows[0]->sessionId){
			$sessionId = $auth->rows[0]->sessionId;
			
			$items = $this->callPartsib("searchByArticle",array("PHPSESSID"=>$sessionId, "nomenclature_id"=>$GROUP_ID));
			
//			echo('<pre>');
					
			$dataInDB = array();
			if ($items->rows && count($items->rows)>0){
				$i=0; foreach ($items->rows as $article){ $i++;
					$dd = (array)$article;
					
//					var_dump($dd);
//					exit();
					
					$ID = "wbs-partsibru-".$dd['id'];
					$IMPORT_ID = $this->config['importer_id'];
					$BRAND_ID = strtoupper($dd['brand']);
					$BRAND_NAME = strtoupper($dd['brand']);
					$ARTICLE = strtoupper($dd['article']);
					$PRICE = str_replace(",",".",$dd['price']);
					$DESCR = $dd['title'].' / '.$dd['wherestore'];
					$BOX = $dd['quantity'];
					$DELIVERY = $dd['delivery'];
					$WEIGHT = "";
					$IMG_URL = "";
					$MIN = "";
					
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
							
//							echo ('<pre>');
//							echo ($d);
//							var_dump($d);
							
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
			
//			exit();
			
			return $dataInDB;
		}
	}
	function FindCatalog($detailNum='50610TA0A10'){ 
	
		$auth = $this->callPartsib("auth", array("username"=>$this->config['login'], "password"=>$this->config['pass']));
		if (isset($auth->rows[0]->sessionId) && $auth->rows[0]->sessionId){
			$sessionId = $auth->rows[0]->sessionId;
			
			$items = $this->callPartsib("searchByArticle",array("PHPSESSID"=>$sessionId, "article"=>$detailNum));
	
			$originalsSearch = array();
			if (isset($items->rows) && count($items->rows)>0){
				
//				echo('<pre>');
//				var_dump($items->rows);
				
				$i=0; foreach ($items->rows as $group){ $i++;
					$group = (array)$group;
					
					$originalsSearch [$i]-> IMP_ID = $this->config['id'];
					$originalsSearch [$i]-> SUP_ID = str_replace('"','',$group['nomenclature_id']);
					$originalsSearch [$i]-> ART_ARTICLE_NR = strtoupper($detailNum);
					$originalsSearch [$i]-> SUP_BRAND = strtoupper(str_replace('"','',$group['brand']));
					$originalsSearch [$i]-> TEX_TEXT = $group['title'].' ('.$group['wherestore'].')';
					$originalsSearch [$i]-> URL = '/search/wbs/?article='.urlencode($detailNum).'&brand='.urlencode($group['nomenclature_id']);
					
					$originalsSearch [$i]= serialize($originalsSearch [$i]);
				}
				return $originalsSearch;
			}
			
		}
		else {
			die('Partsib.ru wrong login/password');
		}
	}
}

?>