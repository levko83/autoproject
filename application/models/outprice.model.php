<?php

class OutpriceModel extends Orm {
	
	public static function generate($importer,$account,$inPrice,$inBrand) {
		$obj = new OutpriceModel();
		
// 		echo('<pre>');
// 		var_dump($importer);
// 		echo('</pre>');
		
		$processmargin = array();
		
		#Проценка по офису, берем office_id для наценочной системы поставщика
		$officeID = Register::get('getOfficeIdParam');
		
		#Скидка поставщика
		$importer_discount = isset($importer['discount'])?$importer['discount']:0;
		
		#ПОИСК ГРУППЫ НАЦЕНКИ ДЛЯ ЦЕНЫ
		#Скидка пользователя
		$account_extra = $accounts_margin2account = 0;
		if (isset($account) && count($account)>0){
			
			if ($account['is_firm']){
				$account_extra = (float)$account['firm_discount'];
			} else {
				$account_extra = (float)$account['discount'];
			}
			
			#Наценка на пользователя
			$findNewMargin = DetailsModel::getAccountImporterMargin($account['id'],$importer['id']);
			if (isset($findNewMargin) && count($findNewMargin)>0){
				$processmargin []= '(проценка через клиента)';
				$accounts_margin2account = $findNewMargin['margin_id'];
			}
			else {
				
				//getDiscountnamesImporterMargin
				if ($account['discountname_id']) {
					$findNewMarginDN = DetailsModel::getDiscountnamesImporterMargin($account['discountname_id'],$importer['id']);
					if (isset($findNewMarginDN) && count($findNewMarginDN)>0){
						$processmargin []= '(проценка через группу клиента)';
						$accounts_margin2account = $findNewMarginDN['margin_id'];
					}
				}
				else {
					$findNewMarginOffice = DetailsModel::getOfficeImporterMargin($officeID,$importer['id']);
					if (isset($findNewMarginOffice) && count($findNewMarginOffice)>0){
						$processmargin []= '(проценка через офис)';
						$accounts_margin2account = $findNewMarginOffice['margin_id'];
					}
				}
			}
		}
		else {
			if ($officeID){
				$findNewMarginOffice = DetailsModel::getOfficeImporterMargin($officeID,$importer['id']);
				if (isset($findNewMarginOffice) && count($findNewMarginOffice)>0){
					$processmargin []= '(проценка через офис)';
					$accounts_margin2account = $findNewMarginOffice['margin_id'];
				}
			}
		}
		
		if ($accounts_margin2account) {
			#Наценка на пользователя
			$margin = DetailsModel::getExtraMarginId($accounts_margin2account);
			$extra = $margin['extra'];
			$margin_id = $margin['id'];
			#$account_extra = 0;
		}
		elseif (isset($importer['margin_id']) && $importer['margin_id']) {
			$processmargin []= '(проценка через поставщика)';
			$margin = DetailsModel::getExtraMarginId($importer['margin_id']);
			$extra = $margin['extra'];
			$margin_id = $margin['id'];
		}
		else {
			$margin = $extra = $margin_id = 0;
		}
		
		#НА ВХОДЕ ИМЕЕМ НАЦЕНОЧНУЮ ГРУППУ ПОСТАВЩИКА ИЛИ ПОЛЬЗОВАТЕЛЯ
		#группа найдена
		if ($margin_id) {
			
			#дополнительный процент по бренду
			$extraBrand = DetailsModel::getExtraByBrandMargin($margin_id,$inBrand);
			$extraBrandVal = $extraBrand['margin'];
			if ($extraBrand['dynamic']){
				$extraBrandValExtra = 0;
			}
			else {
				$extraBrandValExtra = $extraBrand['extra'];
			}
		}
		else {
			$extraBrandVal = $extraBrandValExtra = 0;
		}

		#ЕСЛИ ЕСТЬ СКИДКА НАЦЕНКА ПО БРЕНДУ, ТО СКИДКУ ПОСТАВЩИКА НЕ ИСПОЛЬЗУЕМ
		if ($extraBrandVal){
			$startPrice = $obj->extra($inPrice,$extraBrandVal);
		}
		else {
			$startPrice = $obj->extra($inPrice,$importer_discount);
		}

		#ТУТ ПОЛУЧАЕМ ВХОДНУЮ ЦЕНУ ДЛЯ НАЦЕНКИ -> $startPrice
		if ($margin_id) {
			#наценка диапазона
			$dynamicExtra = DetailsModel::getExtraByPriceMargin($margin_id,$startPrice);
			$dynamicExtraVal = $dynamicExtra['margin'];
		}
		else {
			$dynamicExtraVal = 0;
		}
		
		#ТОЛЬКО НАЦЕНКА БРЕНДА
		if ($extraBrandValExtra){
			$resultPRICE = $obj->extra($startPrice,$extraBrandValExtra);
		}
		#ТОЛЬКО НАЦЕНКА ДИАПАЗОНА
		elseif ($dynamicExtraVal){
			$resultPRICE = $obj->extra($startPrice,$dynamicExtraVal);
		}
		else {
			$resultPRICE = $obj->extra($startPrice,$extra);
		}
		
		$resultPRICE_whithout_account = $resultPRICE;
		
		#СКИДКА НАЦЕНКА НА ПОЛЬЗОВАТЕЛЯ, РАБОТАЕТ ТОГДА КОГДА НА НЕМ НЕТ ЦЕННОЧНОЙ ГРУППЫ, ИНАЧЕ ВХОД = 0
		$resultPRICE_withoutAccount = $resultPRICE;
		$resultPRICE = $obj->extra($resultPRICE,$account_extra);
		
		/* Берем закупочную цену и прибавляем процент относительно менеджера, чтоб не знал закупку */
		$purchase_margin = Register::get('purchase_margin');
		if ($purchase_margin){
			$startPrice = $obj->extra($startPrice,$purchase_margin);
		}
		
		return array(
			'importer_discount'		=>	$importer_discount,
			'margin_name'			=>	$margin['name'].' '.join(",",$processmargin),
						
			'extraBrand_brand_name'	=>	(isset($extraBrand['brand_name'])?$extraBrand['brand_name']:''),
			'extraBrand_margin'		=>	$extraBrandVal,
			'extraBrandVal'			=>	$extraBrandVal,
			
			'extraBrandValExtra'	=>	$extraBrandValExtra,
			'dynamicExtra_margin'	=>	(isset($dynamicExtra['margin'])?$dynamicExtra['margin']:''),
			'extra'					=>	$extra,
			
			'account_extra'			=>	$account_extra,
			'account_extra_without_baseprice'			=>	$resultPRICE_withoutAccount,
			
			'startPrice'			=>	$startPrice,
			'resultPRICE'			=>	$resultPRICE,
			'resultPRICE'			=>	$resultPRICE,
			'resultPRICE_whithout_account'	=>	$resultPRICE_whithout_account,
		);
	}
	
	function getMoneyType($id){
		$translates = Register::get('translates');
		switch ($id) {
			case 0: $type = '$'; break;
			case 1: $type = '&euro;'; break;
			case 2: $type = $translates['front.money']; break;
			default:0;
		}
		return $type;
	}
	function extra($price,$extra) {
		return $price+($price*$extra/100);
	}
	function percent($price,$extra){
		return $price*$extra/100;
	}
}
?>