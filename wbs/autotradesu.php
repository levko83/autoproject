<?php

class autotradesu extends SearchController {
	
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
	}
	
	private function method_request($params=''){
	
		$login = $this->config['login'];
		$password = $this->config['pass'];
		$salt = "1>6)/MI~{J";
		$hash = md5($login.md5($password).$salt);
		$string = '{
			"auth_key": "'.$hash.'",
			'.$params.'
		}';
		$url = "https://api2.autotrade.su/?json";
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "data=".$string);
		$data = curl_exec($ch);
		curl_close($ch);
	
		return json_decode($data);
	}
	
	function searchAll($serialize=true,$account=array()) {
						
		$ARTICLE = isset($_REQUEST['article'])?$_REQUEST['article']:false;
		$GROUP_ID = isset($_REQUEST['brand'])?$_REQUEST['brand']:false;
		$Q = ($this->config['is_groups'])?$GROUP_ID:$ARTICLE;
		$GROUP_ID = isset($_REQUEST['option'][$this->config['id']])?($_REQUEST['option'][$this->config['id']]):$Q;
		
// 		$storages = array();
// 		$params = '
// 			"method": "getStoragesList"
// 		';
// 		$data = $this->method_request($params);
// 		echo('<pre>');
// 		var_dump($data);
//		echo('</pre>');
		
// 		$params = '
// 			"method": "getItemsByQuery",
// 			"params": {
// 				"storages": ['.join(",", $storages).'],
// 		        "items": {';
// 		$j=0; foreach ($articlesListQueries as $articleRequest){ $j++;
// 			$params .= '"'.$articleRequest.'": 1'. ((count($articlesListQueries) == $j)?'':',')."\r\n";
// 		}
// 		$params .= '}
// 		    }
// 		';
// 		$data = $this->method_request($params);
// 		if (isset($data->items)){
// 			foreach ($data->items as $getArticle){
// 				$getArticle = (array)$getArticle;
// 				if (isset($getArticle['stocks']) && count($getArticle['stocks'])){
// 					foreach ($getArticle['stocks'] as $stokes){ $i++;
// 					$stokes = (array)$stokes;
// 					$detailsList []= array(
// 							'ID' => "wbs-at-".$i,
// 							'IMPORT_ID' => 0,
// 							'BRAND_ID' => $getArticle['brand'],
// 							'BRAND_NAME' => $getArticle['brand'],
// 							'ARTICLE' => $getArticle['article'],
// 							'PRICE' => $getArticle['price'],
// 							'DESCR' => ($getArticle['name'].' '.$stokes['legend']),
// 							'BOX' => strtoupper($stokes['quantity_unpacked']),
// 							'DELIVERY' => '',
// 							'WEIGHT' => '',
// 							'IMG_URL' => '',
// 					);
// 					}
// 				}
// 			}
// 		}
		
		$article = $ARTICLE;
		$i=0;
		$detailsList = array();
		
		$articlesListQueries = array($article);
		
 		$params = '
 			"method": "getReplacesAndCrosses",
 		    "params": {
 		        "article": "'.$article.'"
 		    }
 		';
 		$data = $this->method_request($params);
 		if (isset($data->itemsReplace) && count($data->itemsReplace)>0){
 			foreach ($data->itemsReplace as $crossreplaces){
 				$crossreplaces = (array)$crossreplaces;
 				$articlesListQueries []= $crossreplaces['article'];
 			}
 		}
 		if (isset($data->itemsCross) && count($data->itemsCross)>0){
 			foreach ($data->itemsCross as $crossreplaces){
 				$crossreplaces = (array)$crossreplaces;
 				$articlesListQueries []= $crossreplaces['article'];
 			}
 		}
		
		$params = '
			"method": "getItemsByQuery",
			"params": {
				"q": ["'.join('","', $articlesListQueries).'"],
				"cross": 1,
				"replace": 1,
				"strict": 0,
				"with_stocks_and_prices": 1,
				"with_delivery": 1,
		        "page": 1,
		        "limit": 999
		    }
		';
		$data = $this->method_request($params);
		
		if (isset($data->items) && count($data->items)>0){
			foreach ($data->items as $dd){
				$dd = (array)$dd;
		
				if (isset($dd["stocks"]) && count($dd["stocks"])>0){
					foreach ($dd["stocks"] as $stock){ $i++;
						$stock = (array)$stock;
//						if(in_array($stock['id'],array(4))){
							$detailsList [$dd['brand_name'].$dd['article'].$dd['price']]= array(
								'ID' => "wbs-at-".$dd['id'],
								'BRAND_ID' => $dd['brand_name'],
								'BRAND_NAME' => $dd['brand_name'],
								'ARTICLE' => $dd['article'],
								'PRICE' => $dd['price'],
								'DESCR' => ($dd['name']),
								//'BOX' => ($stock['quantity_unpacked']),
								'BOX' => 5,
								'DELIVERY' => ($stock['delivery_period']),
							);
//						}
					}
				}
			}
		}
		
// 		echo('<pre>');
// 		var_dump($detailsList);
// 		echo('</pre>');
		
		$tmp = $detailsList;
		
		if (isset($tmp) && count($tmp)>0){
			$dataInDB = array();
			$i=0; foreach ($tmp as $dd){
								
				$ID = $dd['ID'];
				$IMPORT_ID = $this->config['importer_id'];
				$BRAND_ID = $dd['BRAND_ID'];
				$BRAND_NAME = $dd['BRAND_NAME'];
				$ARTICLE = $dd['ARTICLE'];
				$PRICE = $dd['PRICE'];
				$DESCR = $dd['DESCR'];
				$BOX = $dd['BOX'];
				$DELIVERY = $dd['DELIVERY'];
				$WEIGHT = "";
				$IMG_URL = "";
			
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
			}
		}
					
		return $dataInDB;
	}
	
	function FindCatalog($detailNum=''){ 
		return array();
	}
}

?>