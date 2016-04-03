<?php

class stels extends SearchController {
	
	private $config;
	
	function __construct(){
		$config = WbsModel::getConfig(basename(__FILE__));
		$this->config = $config;
		$this->imp_id = $config['id'];
	}
	
	function searchAll($serialize=true,$account=array()){
		
		$SOAP = new soap_transport_stels();
		
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
		
		$parsed_data = array(
				'session_guid' => '',
				'session_id' => $this->config['login_uid'],
				'session_login' => $this->config['login'],
				'session_password' => $this->config['pass'],
				'productid' => $GROUP_ID,
				'stocksonly' => 0,
				'instock' => true,
				'showcross' => true,
				'periodmin' => 0,
				'periodmax' => 40,
		);
		if ($SOAP->validateData($parsed_data, $errors)) {
			$requestXMLstring = $this->createSearchRequestXMLStep2($parsed_data);
			$responceXML = $SOAP->query('SearchOfferStep2', array('SearchParametersXml' => $requestXMLstring), $errors);
			if ($responceXML) {
				$attr = $responceXML->rows->attributes();
				$data['session_guid'] = (string)$attr['SessionGUID'];
				$result = $SOAP->parseSearchResponseXML($responceXML);
				
				if (isset($result) && count($result)>0){
					$dataInDB = array();
					$i=0; foreach ($result as $row){
					$i++;
					
						//echo('<pre>');
						//var_dump($row);
						//echo('</pre>');
					
						$DELIVERY = ($row['PeriodMin']+$IMPORTER_DATA['delivery']).'/'.($row['PeriodMax']+$IMPORTER_DATA['delivery']);
						if (($row['PeriodMin']+$IMPORTER_DATA['delivery']) == ($row['PeriodMax']+$IMPORTER_DATA['delivery'])){
							$DELIVERY = ($row['PeriodMax']+$IMPORTER_DATA['delivery']);
						}
					
						$ID = "wbs-stels-".$row['Reference'];
						$IMPORT_ID = $this->config['importer_id'];
						$BRAND_ID = 0;
						$BRAND_NAME = strtoupper($row['AnalogueManufacturerName']);
						$ARTICLE = strtoupper($row['AnalogueCode']);
						$PRICE = $row['Price'];
						$DESCR = $row['ProductName'].' / '.$row['OfferName'];
						$BOX = $row['Quantity'];
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
							if(
								(preg_match('/('.$str_check.')/i', $BRAND_NAME) && (FuncModel::stringfilter($ARTICLE) == FuncModel::stringfilter($ARTICLE_REGET)))
								||
								($row['GroupTitle'] == "")
							){
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
								'DB_DELIVERY' => ($DELIVERY),
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
													'DELIVERY' 		=> ($DELIVERY),
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
								'DELIVERY' 		=> ($DELIVERY),
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
		}
	}
	function FindCatalog($detailNum=''){ 
		try {
			$SOAP = new soap_transport_stels();
			
			$errors = array();
			/* STEP 1 ****** */
			$parsed_data = array(
				'session_guid' => '',
				'session_id' => $this->config['login_uid'],
				'session_login' => $this->config['login'],
				'session_password' => $this->config['pass'],
				'search_code' => $detailNum,
			);
			if ($SOAP->validateData($parsed_data, $errors)) {
				$requestXMLstring = $this->createSearchRequestXML($parsed_data);
				$responceXML = $SOAP->query('SearchOfferStep1', array('SearchParametersXml' => $requestXMLstring), $errors);
				if ($responceXML) {
					$attr = $responceXML->rows->attributes();
					$data['session_guid'] = (string)$attr['SessionGUID'];
					$groups = $SOAP->parseSearchResponseXML($responceXML);
					if (isset($groups) && count($groups)>0){
						
						if (count($groups) == 1){
							$groups = array($groups);
						}
						
						$CORRECT_BRANDS = $this->wbs_correct_brandnames();
						$INCORRECT_NAMES = $CORRECT_NAMES = array();
						if (isset($CORRECT_BRANDS) && count($CORRECT_BRANDS)>0){
							foreach ($CORRECT_BRANDS as $INCORRECT){
								$INCORRECT_NAMES []= $INCORRECT['incorrect'];
								$CORRECT_NAMES [$INCORRECT['incorrect']]= $INCORRECT['correct'];
							}
						}
		
						$originalsSearchRet = array();
						$i=0; foreach ($groups as $group){ $i++;
						
							if (count($group) == 1){
								$group = $group[0];
							}
						
							$BRAND_NAME = strtoupper($group['ProducerName']);
							if (isset($INCORRECT_NAMES) && in_array($BRAND_NAME,$INCORRECT_NAMES)) {
								$BRAND_NAME = (isset($CORRECT_NAMES[$BRAND_NAME])?$CORRECT_NAMES[$BRAND_NAME]:$BRAND_NAME);
							}
							$originalsSearch = (object)array(
								'IMP_ID' => $this->config['id'],
								'SUP_ID' => strtoupper($group['ProductID']),
								'ART_ARTICLE_NR' => strtoupper($group['CodeAsIs']),
								'SUP_BRAND' => $BRAND_NAME,
								'TEX_TEXT' => $group['ProductName'],
								'URL' => '/search/wbs/?article='.$group['CodeAsIs'].'&brand='.$group['ProductID'].'&ws-'.$this->config['id'].'='.$group['ProductID'],
							);
							$originalsSearchRet []= serialize($originalsSearch);
						}
						
						return $originalsSearchRet;
					}
					return array();
				}
				return array();
			}
			return array();
		} catch (Exception $e) {}
	}
	
	function createSearchRequestXML($data) {
		$session_info = $data['session_guid'] ? 'SessionGUID="'.$data['session_guid'].'"' : 'UserLogin="'.base64_encode($data['session_login']).'" UserPass="'.base64_encode($data['session_password']).'"';
		$xml = '
		<root>
			<SessionInfo ParentID="'.$data['session_id'].'" '.$session_info.'/>
			<Search>
				<Key>'.$data['search_code'].'</Key>
			</Search>
		</root>
		';
		return $xml;
	}
	function createSearchRequestXMLStep2($data) {
		$session_info = $data['session_guid'] ? 'SessionGUID="'.$data['session_guid'].'"' : 'UserLogin="'.base64_encode($data['session_login']).'" UserPass="'.base64_encode($data['session_password']).'"';
		$xml = '
		<root>
			<SessionInfo ParentID="'.$data['session_id'].'" '.$session_info.'/>
			<Search>
				<ProductID>'.$data['productid'].'</ProductID>
				<StocksOnly>'.$data['stocksonly'].'</StocksOnly>
				<InStock>'.$data['instock'].'</InStock>
				<ShowCross>'.$data['showcross'].'</ShowCross>
				<PeriodMin>'.$data['periodmin'].'</PeriodMin>
				<PeriodMax>'.$data['periodmax'].'</PeriodMax>
			</Search>
		</root>
		';
		return $xml;
	}
}

class soap_transport_stels {
	private $_wsdl_uri = 'https://allautoparts.ru/WEBService/SearchService.svc/wsdl?wsdl';
	private static $_soap_client = false;
	private static $_inited = false;

	public function init(&$errors) {
		if(!self::$_inited) {
			try {
				if (self::$_soap_client = @new SoapClient($this->_wsdl_uri, array('soap_version' => SOAP_1_1)))
					self::$_inited = true;
			}
			catch (Exception $e) {
				$errors[] = 'Произошла ошибка связи с сервером Автостэлс. '.$e->getMessage();
				return false;
			}
		}
		return self::$_inited;
	}

	public function query($method, $requestData, &$errors) {
		if (!$this->init($errors)) {
			$errors[] = 'Ошибка соединения с сервером Автостэлс: Не может быть инициализирован класс SoapClient';
			return false;
		}
		$result =  self::$_soap_client->$method($requestData);
		$resultKey = $method.'Result';
		try {
			$XML = new SimpleXMLElement($result->$resultKey);
		}
		catch (Exception $e) {
			$errors[] = 'Ошибка сервиса Автоселс: полученные данные не являются корректным XML';
			return false;
		}
		if(isset($XML->error)) {
			$errors[] = 'Ошибка сервиса Автоселс: '.(string)$XML->error->message;
			if ((string)$XML->error->stacktrace)
				$errors[] = 'Отладочная информация: '.(string)$XML->error->stacktrace;
			return false;
		}
		$this->close();
		return $XML;
	}

	public function close() {
		if( self::$_inited ) {
			self::$_inited = false;
			self::$_soap_client = false;
		}
	}
	
	function generateRandom($maxlen = 32) {
		$code = '';
		while (strlen($code) < $maxlen) {
			$code .= mt_rand(0, 9);
		}
		return $code;
	}
	
	function validateData(&$data, &$errors) {
		if (!$data['session_id'])
			$errors[] = 'Необходимо указать ID входа для работы с сервисом';
		if ((!$data['session_login'] || !$data['session_password']) && !$data['session_guid'])
			$errors[] = 'Необходимо ввести логин и пароль'.$data['session_guid'];
		return count($errors) ? false : true;
	}
	
	function parseSearchResponseXML($xml) {
		$data = array();
		foreach($xml->rows->row as $row) {
			$_row = array();
			foreach($row as $key => $field) {
				$_row[(string)$key] = (string)$field;
			}
			$_row['Reference'] = $this->generateRandom(9);
			$data[] = $_row;
		}
		return $data;
	}
}
?>