<?php
class DetailsModel extends Orm {
	public function __construct() {
		parent::__construct(DB_PREFIX.'details');
	}
	/* Оптимизация */
	public static function findQUERYOnce($ID,$NAME,$ART_ID,$selfWBS=false,$accessWBS=array()) {
		
		$NEW_ID = array();
		if (isset($ID) && count($ID)>0){
			foreach ($ID as $dd){
				$NEW_ID []= mysql_real_escape_string($dd);
			}
		}
		$ID = $NEW_ID;
		
		$iSQL = "";
		if ($selfWBS){
			if (count($accessWBS)>0)
				$iSQL = " AND DETAILS.IMPORT_ID IN (".join(",",$accessWBS).") ";
			else 
				return array();
		}
		
		$db = Register::get('db');
		
		$sql = "
			SELECT 
				DETAILS.*,
				BRANDS.BRA_BRAND MATCH_BRAND
			FROM `".DB_PREFIX."details` DETAILS
			LEFT JOIN `".DB_PREFIX."brands` BRANDS ON BRANDS.BRA_ID_GET = DETAILS.BRAND_ID AND DETAILS.BRAND_ID != 0
			WHERE 
				(DETAILS.BRAND_ID IN ('".join("','",$ID)."') OR DETAILS.BRAND_NAME IN ('".join("','",$NAME)."') OR BRANDS.BRA_BRAND IN ('".join("','",$NAME)."'))
				AND DETAILS.ARTICLE IN ('".join("','",$ART_ID)."') 
				AND DETAILS.PRICE>'0' 
				AND DETAILS.ONLY_FOR_SHOP='0'
				$iSQL
			GROUP BY
				DETAILS.IMPORT_ID,
				DETAILS.ARTICLE,
				DETAILS.BRAND_NAME,
				DETAILS.PRICE
			;";
		
		$result = $db->query($sql);
		return $result;
	}
	
	public static function getById($id) {
		$model = new DetailsModel();
		return $model->select()->where("id=?",(int)$id)->fetchOne();
	}
	/* ******** MARGINS ******** */
	public static function getDiscountnamesImporterMargin($dn_id=0,$importer_id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."accounts_margin2discountnames` WHERE `discountname_id`='".(int)$dn_id."' AND `importer_id`='".(int)$importer_id."';";
		return $db->get($sql);
	}
	public static function getOfficeImporterMargin($office_id=0,$importer_id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."offices_margin2office` WHERE `office_id`='".(int)$office_id."' AND `importer_id`='".(int)$importer_id."';";
		return $db->get($sql);
	}
	public static function getAccountImporterMargin($account_id=0,$importer_id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."accounts_margin2account` WHERE `account_id`='".(int)$account_id."' AND `importer_id`='".(int)$importer_id."';";
		return $db->get($sql);
	}
	public static function getExtraMarginId($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."margins WHERE id='".(int)$id."';";
		return $db->get($sql);
	}
	public static function getExtraByPriceMargin($import_id,$price){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."margins_fromto WHERE margin_id='".(int)$import_id."' AND (`from` <= '".mysql_real_escape_string($price)."' AND `to` >= '".mysql_real_escape_string($price)."');";
		return $db->get($sql);
	}
	public static function getExtraByBrandMargin($margin_id,$brand_id){
		$db = Register::get('db');
		$sql = "SELECT IMB.*,IMB.brand AS brand_name FROM ".DB_PREFIX."margins_brands IMB WHERE IMB.margin_id='".(int)$margin_id."' AND IMB.brand LIKE '".mysql_real_escape_string($brand_id)."';";
		return $db->get($sql);
	}
	public static function MaxMinPrices($SETPRICES=array(),$status=0){
		if ($status == 1){
			
			/* 1 ********************************************** */
			if (isset($SETPRICES) && count($SETPRICES)>0){
				$return = array();
				foreach ($SETPRICES as $K_BRAND => $V_ARTICLE){
					foreach ($V_ARTICLE as $K_ARTICLE => $MAX_ARTICLE){
						
						$max = 0; $save_max = array();
						for ($i=0;$i<count($MAX_ARTICLE);$i++){
							if ($max == 0){
								$max = ceil($MAX_ARTICLE[$i]['RESULT_PRICE_SALE']);
							}
							if ($max <= ceil($MAX_ARTICLE[$i]['RESULT_PRICE_SALE'])){
								$save_max = $MAX_ARTICLE[$i];
							}
						}
						$return [$K_BRAND][$K_ARTICLE] []= $save_max;
						
					}
				}
				return $return;
			}
			else {
				return array();
			}
			/* 1 ********************************************** */
		}
		elseif ($status == 2){
			
			/* 2 ********************************************** */
			if (isset($SETPRICES) && count($SETPRICES)>0){
				$return = array();
				foreach ($SETPRICES as $K_BRAND => $V_ARTICLE){
					foreach ($V_ARTICLE as $K_ARTICLE => $MIN_ARTICLE){
						
						$min = 0; $save_min = array();
						for ($i=0;$i<count($MIN_ARTICLE);$i++){
							if ($min == 0){
								$min = ceil($MIN_ARTICLE[$i]['RESULT_PRICE_SALE']);
							}
							if ($min >= ceil($MIN_ARTICLE[$i]['RESULT_PRICE_SALE'])){
								$min = ceil($MIN_ARTICLE[$i]['RESULT_PRICE_SALE']);
								$save_min = $MIN_ARTICLE[$i];
							}
						}
						$return [$K_BRAND][$K_ARTICLE] []= $save_min;
						
					}
				}
				return $return;
			}
			else {
				return array();
			}
			/* 2 ********************************************** */
		}
		
		return $SETPRICES;
	}
	
	public static function SortMinMaxPrice($data,$typeSort='article',$obj=true,$timeDelivery=false){

		if (isset($data) && count($data)>0){
			
			if ($obj){
				
				if ($timeDelivery){
					$sortKeyValue = array();
					foreach ($data as $price){
						$price = unserialize($price);
						if (isset($price->DB_DELIVERY) && ($timeDelivery >= (int)$price->DB_DELIVERY)){
							$sortKeyValue []= $price;	
						}
					}
				}
				else {
					$sortKeyValue = array();
					foreach ($data as $price){
						$price = unserialize($price);
						$sortKeyValue []= $price;
					}
				}
				
				if ($typeSort == 'max'){
					usort($sortKeyValue,'compare_price_sort_obj_max2min');
				}
				elseif ($typeSort == 'delivery'){
					usort($sortKeyValue,'array_sort_obj_delivery');
				}
				elseif ($typeSort == 'article'){
					usort($sortKeyValue,'array_sort_obj_article');
				}
				elseif ($typeSort == 'brand'){
					usort($sortKeyValue,'array_sort_obj_brand');
				}
				else {
					usort($sortKeyValue,'compare_price_sort_obj_min2max');
				}
				
				$return = array();
				if (isset($sortKeyValue) && count($sortKeyValue)>0){
					
					$ifEmpty = array();
					foreach ($sortKeyValue as $out){
						
						$ifEmpty []= serialize($out);
						
						if (count($sortKeyValue)>1){
							if (isset($out->MY_PRICE['RESULT_PRICE_SALE']) && $out->MY_PRICE['RESULT_PRICE_SALE']){
								$return []= serialize($out);
							}
						}
						else {
							$return []= serialize($out);
						}
					}
				}
				
				if (count($return) <= 0) {
					return $ifEmpty;
				}
				
				return $return;
				
			}
			else {
				
				if ($timeDelivery){
					$dataS = array();
					foreach ($data as $price){
						if ($timeDelivery >= (int)$price['DELIVERY']){
							$dataS []= $price;
						}
					}
				} else {
					$dataS = $data;
				}
				
				if ($typeSort == 'max'){
					usort($dataS,'compare_price_sort_arr_max2min');
				}
				elseif ($typeSort == 'delivery'){
					usort($dataS,'array_sort_delivery');
				}
				elseif ($typeSort == 'article'){
					usort($dataS,'array_sort_article');
				}
				elseif ($typeSort == 'brand'){
					usort($dataS,'array_sort_brand');
				}
				else{
					usort($dataS,'compare_price_sort_arr_min2max');
				}
				
				return $dataS;
			}
			
		}
		else {
			return $data;
		}
	}
}

/* ******************************************************************************** */

function array_sort_obj_article($x,$y){
	
	if (!isset($x->MY_PRICE['IMPORTER_DATA']) || !isset($y->MY_PRICE['IMPORTER_DATA']))
		return 0;
	
	$xS = (int)($x->MY_PRICE['IMPORTER_DATA']['sort'].$x->MY_PRICE['RESULT_PRICE_SALE']);
	$x_price = (strlen($xS) < 10)?str_repeat(0,(10-strlen($xS))).$xS:$xS;
	
	$yS = (int)($y->MY_PRICE['IMPORTER_DATA']['sort'].$y->MY_PRICE['RESULT_PRICE_SALE']);
	$y_price = (strlen($yS) < 10)?str_repeat(0,(10-strlen($yS))).$yS:$yS;
	
	$lft = (int)$x->MY_PRICE['IMPORTER_DATA']['sort'].'_'.FuncModel::stringfilter($x->ART_ARTICLE_NR_CLEAR).'_'.FuncModel::stringfilter($x->SUP_BRAND).'_'.$x_price;
	$rgt = (int)$y->MY_PRICE['IMPORTER_DATA']['sort'].'_'.FuncModel::stringfilter($y->ART_ARTICLE_NR_CLEAR).'_'.FuncModel::stringfilter($y->SUP_BRAND).'_'.$y_price;
	
	if ($lft == $rgt){ return 0; }
	return ($lft < $rgt) ? -1 : 1;
}
function array_sort_obj_brand($x,$y){
	
	if (!isset($x->MY_PRICE['IMPORTER_DATA']) || !isset($y->MY_PRICE['IMPORTER_DATA']))
		return 0;
	
	$xS = (int)($x->MY_PRICE['IMPORTER_DATA']['sort'].$x->MY_PRICE['RESULT_PRICE_SALE']);
	$x_price = (strlen($xS) < 10)?str_repeat(0,(10-strlen($xS))).$xS:$xS;
	
	$yS = (int)($y->MY_PRICE['IMPORTER_DATA']['sort'].$y->MY_PRICE['RESULT_PRICE_SALE']);
	$y_price = (strlen($yS) < 10)?str_repeat(0,(10-strlen($yS))).$yS:$yS;
	
	$lft = ((int)$x->MY_PRICE['IMPORTER_DATA']['sort']).'_'.FuncModel::stringfilter($x->SUP_BRAND).'_'.FuncModel::stringfilter($x->ART_ARTICLE_NR_CLEAR).'_'.$x_price;
	$rgt = ((int)$y->MY_PRICE['IMPORTER_DATA']['sort']).'_'.FuncModel::stringfilter($y->SUP_BRAND).'_'.FuncModel::stringfilter($y->ART_ARTICLE_NR_CLEAR).'_'.$y_price;
	
	if ($lft == $rgt){ return 0; }
	return ($lft < $rgt) ? -1 : 1;
}
function array_sort_obj_delivery($x,$y){
	
	if (!isset($x->MY_PRICE['IMPORTER_DATA']) || !isset($y->MY_PRICE['IMPORTER_DATA']))
		return 0;

	$deliveryDaysX = (int)(($x->DB_DELIVERY)?($x->DB_DELIVERY):($x->MY_PRICE['IMPORTER_DATA']['delivery']));
	$deliveryDaysY = (int)(($y->DB_DELIVERY)?($y->DB_DELIVERY):($y->MY_PRICE['IMPORTER_DATA']['delivery']));
	
	$xS = (int)($x->MY_PRICE['IMPORTER_DATA']['sort'].$x->MY_PRICE['RESULT_PRICE_SALE']);
	$x_price = (strlen($xS) < 10)?str_repeat(0,(10-strlen($xS))).$xS:$xS;
	
	$yS = (int)($y->MY_PRICE['IMPORTER_DATA']['sort'].$y->MY_PRICE['RESULT_PRICE_SALE']);
	$y_price = (strlen($yS) < 10)?str_repeat(0,(10-strlen($yS))).$yS:$yS;

	$lft = ((int)$deliveryDaysX).$x_price;
	$rgt = ((int)$deliveryDaysY).$y_price;

	if ($lft == $rgt){ return 0; }
	return ($lft < $rgt) ? -1 : 1;
}

function array_sort_article($x,$y){
	
	$xS = (int)($x['MY_PRICE']['IMPORTER_DATA']['sort'].$x['MY_PRICE']['RESULT_PRICE_SALE']);
	$x_price = (strlen($xS) < 10)?str_repeat(0,(10-strlen($xS))).$xS:$xS;
	
	$yS = (int)($y['MY_PRICE']['IMPORTER_DATA']['sort'].$y['MY_PRICE']['RESULT_PRICE_SALE']);
	$y_price = (strlen($yS) < 10)?str_repeat(0,(10-strlen($yS))).$yS:$yS;
	
	$lft = ((int)$x['MY_PRICE']['IMPORTER_DATA']['sort']).'_'.FuncModel::stringfilter($x['ART_ARTICLE_NR_CLEAR']).'_'.strtoupper(FuncModel::stringfilter($x['SUP_BRAND'])).'_'.$x_price;
	$rgt = ((int)$y['MY_PRICE']['IMPORTER_DATA']['sort']).'_'.FuncModel::stringfilter($y['ART_ARTICLE_NR_CLEAR']).'_'.strtoupper(FuncModel::stringfilter($y['SUP_BRAND'])).'_'.$y_price;
	
	if ($lft == $rgt){ return 0; }
	return ($lft < $rgt) ? -1 : 1;
}
function array_sort_brand($x,$y){
	
	$xS = (int)($x['MY_PRICE']['IMPORTER_DATA']['sort'].$x['MY_PRICE']['RESULT_PRICE_SALE']);
	$x_price = (strlen($xS) < 10)?str_repeat(0,(10-strlen($xS))).$xS:$xS;
	
	$yS = (int)($y['MY_PRICE']['IMPORTER_DATA']['sort'].$y['MY_PRICE']['RESULT_PRICE_SALE']);
	$y_price = (strlen($yS) < 10)?str_repeat(0,(10-strlen($yS))).$yS:$yS;
	
	$lft = ((int)$x['MY_PRICE']['IMPORTER_DATA']['sort']).'_'.strtoupper(FuncModel::stringfilter($x['SUP_BRAND'])).'_'.FuncModel::stringfilter($x['ART_ARTICLE_NR_CLEAR']).'_'.$x_price;
	$rgt = ((int)$y['MY_PRICE']['IMPORTER_DATA']['sort']).'_'.strtoupper(FuncModel::stringfilter($y['SUP_BRAND'])).'_'.FuncModel::stringfilter($y['ART_ARTICLE_NR_CLEAR']).'_'.$y_price;
	
	if ($lft == $rgt){ return 0; }
	return ($lft < $rgt) ? -1 : 1;
}
function array_sort_delivery($x,$y){
	
	$deliveryDaysX = (int)(($x['DB_DELIVERY'])?$x['DB_DELIVERY']:$x['MY_PRICE']['IMPORTER_DATA']['delivery']);
	$deliveryDaysY = (int)(($y['DB_DELIVERY'])?$y['DB_DELIVERY']:$y['MY_PRICE']['IMPORTER_DATA']['delivery']);
	
	$xS = (int)($x['MY_PRICE']['RESULT_PRICE_SALE']);
	$x_price = (strlen($xS) < 10)?str_repeat(0,(10-strlen($xS))).$xS:$xS;
	
	$yS = (int)($y['MY_PRICE']['RESULT_PRICE_SALE']);
	$y_price = (strlen($yS) < 10)?str_repeat(0,(10-strlen($yS))).$yS:$yS;
	
	$lft = $deliveryDaysX.$x_price;
	$rgt = $deliveryDaysY.$y_price;
	
	if ($lft == $rgt){ return 0; }
	return ($lft < $rgt) ? -1 : 1;
}

/* ******************************************************************************** */

function compare_price_sort_obj_max2min($x,$y){
	
	if ($x->MY_PRICE['RESULT_PRICE_SALE'] == $y->MY_PRICE['RESULT_PRICE_SALE'])
		return 0;
	else if ($x->MY_PRICE['RESULT_PRICE_SALE'] > $y->MY_PRICE['RESULT_PRICE_SALE'])
		return -1;
	else
		return 1; 
}
function compare_price_sort_obj_min2max($x,$y){
	if ($x->MY_PRICE['RESULT_PRICE_SALE'] == $y->MY_PRICE['RESULT_PRICE_SALE'])
		return 0;
	else if ($x->MY_PRICE['RESULT_PRICE_SALE'] < $y->MY_PRICE['RESULT_PRICE_SALE'])
		return -1;
	else
		return 1; 
}

function compare_price_sort_arr_max2min($x,$y){
	if ($x['RESULT_PRICE_SALE'] == $y['RESULT_PRICE_SALE'])
		return 0;
	else if ($x['RESULT_PRICE_SALE'] > $y['RESULT_PRICE_SALE'])
		return -1;
	else
		return 1; 
}
function compare_price_sort_arr_min2max($x,$y){
	
	/*echo('<pre>');
	var_dump($x['RESULT_PRICE_SALE']);
	exit();*/
	
	if ($x['RESULT_PRICE_SALE'] == $y['RESULT_PRICE_SALE'])
		return 0;
	else if ($x['RESULT_PRICE_SALE'] < $y['RESULT_PRICE_SALE'])
		return -1;
	else
		return 1; 
}

?>