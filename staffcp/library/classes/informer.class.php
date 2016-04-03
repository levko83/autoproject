<?php

class Informer {
	
	const MONEY_FILE = '../cache/money.xml';
	const MONEY_URL = 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate=';
	public static function money(){
		if (@date('d.m.y',@filectime(self::MONEY_FILE)) != date('d.m.y')){
			@copy(self::MONEY_URL.date('m/d/Y'), self::MONEY_FILE );
		}
		$content = file_get_contents(self::MONEY_FILE);
		$currency = array('USD','EUR','RUB');
		$moneyData = array();
		foreach ($currency as $cur) {
			if (preg_match('#<CharCode>'.$cur.'</CharCode>.*?<Rate>(.*?)</Rate>#is',$content, $match)) {
				$moneyData[$cur] = $match[1];
			}
		}
		return $moneyData;
	}
	
	const MONEY_FILE_KZ = '../cache/money_kz.xml';
	const MONEY_URL_KZ = 'http://www.nationalbank.kz/rss/rates_all.xml';
	public static function money_KZ(){
		if (@date('d.m.y',@filectime(self::MONEY_FILE_KZ)) != date('d.m.y')){
			@copy(self::MONEY_URL_KZ, self::MONEY_FILE_KZ );
		}
		$content = file_get_contents(self::MONEY_FILE_KZ);
		$currency = array('USD','EUR','RUB');
		$moneyData = array();
		foreach ($currency as $cur) {
			if (preg_match('#<title>'.$cur.'</title>.*?<description>(.*?)</description>#is',$content, $match)) {
				$moneyData[$cur] = $match[1];
			}
		}
		return $moneyData;
	}
	
	/* ********************************************************************* */
	
	const MONEY_FILE_CBRF = '../cache/money_cbrf.xml';
	const MONEY_URL_CBRF = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=';
	public static function money_CBRF(){
		
		if (@date('d.m.y',@filectime(self::MONEY_FILE_CBRF)) != date('d.m.y')){
			
			@copy(self::MONEY_URL_CBRF.date('d/m/Y'), self::MONEY_FILE_CBRF);
		}
		
		$content = file_get_contents(self::MONEY_FILE_CBRF);
		
		$currency = array('USD','EUR','RUB');
		$moneyData = array();
		foreach ($currency as $cur) {
			if (preg_match('#<CharCode>'.$cur.'</CharCode>.*?<Value>(.*?)</Value>#is',$content, $match)) {
				$moneyData[$cur] = str_replace(",",".",$match[1]);
			}
		}
		return $moneyData;
	}
	
	/* ********************************************************************* */
	
	const MONEY_FILE_NBU = '../cache/money_nbu.xml';
	const MONEY_URL_NBU = 'http://bank-ua.com/export/currrate.xml';
	public static function money_NBU(){
		
		$datenow = date("d.m.Y");
		if (@date('d.m.y',@filectime(self::MONEY_FILE_NBU)) != date('d.m.y')){
			@copy(self::MONEY_URL_NBU, self::MONEY_FILE_NBU);
		}
		
		$content = file_get_contents(self::MONEY_FILE_NBU);
		$currency = array('USD','EUR','RUB');
		$moneyData = array();
		foreach ($currency as $cur) {
			if (preg_match('#<char3>'.$cur.'</char3>.*?<size>(.*?)</size>.*?<rate>(.*?)</rate>#is',$content, $match)) {
				$moneyData[$cur] = str_replace(",",".",$match[2]/$match[1]);
			}
		}
		
		return $moneyData;
	}
	
	/* ********************************************************************* */
	
	const WEATHER_FILE = '../../cache/weather.xml';
	const WEATHER_URL = 'http://informer.gismeteo.by/xml/33008_1.xml';
	
	const FUEL_FILE = '../../cache/fuel.xml';
	const FUEL_URL = 'http://www.bka.by/ru/info/2road/fuel-in-europe/?print=1';
	
	public static function weather()
	{
		if (date('d.m.y',filectime(self::WEATHER_FILE)) != date('d.m.y'))
		{
			copy(self::WEATHER_URL, self::WEATHER_FILE );
		}
		$content = file_get_contents(self::WEATHER_FILE);
		$period = array(
			'утром'	=> 1,
			'днем'	=> 2,
			'вечером' => 3,
			'ночью'	=> 0,
		);
		$weatherData = array();
		foreach ($period as $name=>$tod) {
			if (preg_match('#FORECAST.*?tod="'.$tod.'".*?TEMPERATURE\s+max="(.*?)"\s+min="(.*?)"#is',$content, $match)) {
				$weatherData[$name] = $match[1] .'°С - '.$match[2].'°С';
			}
		}
		return $weatherData;
	}
	
	public static function fuel()
	{
		if (date('d.m.y',filectime(self::FUEL_FILE )) != date('d.m.y'))
		{
			copy(self::FUEL_URL , self::FUEL_FILE );
		}
		$content = file_get_contents(self::FUEL_FILE);
		$content = iconv('windows-1251','utf-8',$content);
		$fuelData = array();
		if (preg_match('#<strong>Лукойл</strong></td><td>&nbsp;(.*?)</td><td>(.*?)\s</td><td>&nbsp;(.*?)</td><td>(.*?)&nbsp;</td>#is',$content, $match)) {
			$fuelData['АИ-95'] = $match[1];
			$fuelData['АИ-92'] = $match[2];
			$fuelData['АИ-80'] = $match[3];
			$fuelData['ДТ'] = $match[4];
		}
	
		return $fuelData;
	}
	
}

?>