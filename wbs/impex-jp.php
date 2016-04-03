<?php

class impexjp extends SearchController {
	
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
	}
	
	function searchAll($serialize=true,$account=array()) {
		
		$ARTICLE = isset($_REQUEST['article'])?$_REQUEST['article']:false;
		$GROUP_ID = isset($_REQUEST['brand'])?$_REQUEST['brand']:false;
		$Q = ($this->config['is_groups'])?$GROUP_ID:$ARTICLE;
		$GROUP_ID = isset($_REQUEST['option'][$this->config['id']])?($_REQUEST['option'][$this->config['id']]):$Q;
		
		$data = array();
	
		$result = file_get_contents("http://www.impex-jp.com/api/parts/search.html?part_no=".FuncModel::stringfilter($ARTICLE));
		$result = json_decode($result);
		$result = (array)$result;
		
		if (isset($result['original_parts']) && count($result['original_parts'])>0){
			foreach ($result['original_parts'] as $dd){
				
				$dd = (array)$dd;
				
				$data []= array(
					'BRAND_NAME' => $dd['mark'],
					'ARTICLE' => $dd['part'],
					'PRICE' => $dd['price_rub'],
					'DESCR' => $dd['name_rus'].' '.$dd['name_eng'],
					'BOX' => '+',
					'DELIVERY' => 0,
					'WEIGHT' => $dd['weight'],
				);
			}
		}
		
		if (isset($result['replacement_parts']) && count($result['replacement_parts'])>0){
			foreach ($result['replacement_parts'] as $dd){
				
				$dd = (array)$dd;
				
				$data []= array(
					'BRAND_NAME' => $dd['mark'],
					'ARTICLE' => $dd['part'],
					'PRICE' => $dd['price_rub'],
					'DESCR' => $dd['name_rus'].' '.$dd['name_eng'],
					'BOX' => '+',
					'DELIVERY' => 0,
					'WEIGHT' => $dd['weight'],
				);
			}
		}
		
		if (count($data)>0){
			$dataInDB = array();
			$i=0; foreach ($data as $dd){ $i++;
			
				$ID = "wbs-impex-".$i;
				$IMPORT_ID = $this->config['importer_id'];
				$BRAND_ID = 0;
				$BRAND_NAME = strtoupper($dd['BRAND_NAME']);
				$ARTICLE = strtoupper($dd['ARTICLE']);
				$PRICE = strtoupper($dd['PRICE']);
				$DESCR = $dd['DESCR'];
				$BOX = strtoupper($dd['BOX']);
				$DELIVERY = strtoupper($dd['DELIVERY']);
				$WEIGHT = $dd['WEIGHT'];
				$IMG_URL = "";
			
				
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
		
		
		$result = file_get_contents("http://www.impex-jp.com/api/parts/search.html?part_no=".FuncModel::stringfilter($detailNum));
		$result = json_decode($result);
		$result = (array)$result;
		
		$originalsSearch = array();
		if (isset($result['original_parts']) && count($result['original_parts'])>0){
			$i=0; foreach ($result['original_parts'] as $dd){ $i++;
				
				$dd = (array)$dd;
				
				$originalsSearch [$i]-> IMP_ID = $this->config['id'];
				$originalsSearch [$i]-> SUP_ID = $dd['part'];
				$originalsSearch [$i]-> ART_ARTICLE_NR = strtoupper($dd['part']);
				$originalsSearch [$i]-> SUP_BRAND = strtoupper($dd['mark']);
				$originalsSearch [$i]-> TEX_TEXT = strtoupper($dd['name_rus'].' '.$dd['name_eng']);
				$originalsSearch [$i]-> URL = '/search/wbs/?article='.strtoupper($dd['part']).'&brand='.urlencode(strtoupper($dd['mark']));
				
				$originalsSearch [$i]= serialize($originalsSearch [$i]);
			}
		}
		return $originalsSearch;
	}
}

?>