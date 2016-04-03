<?php

class autoeuroru extends SearchController {
	
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
		
		$ARTICLE = isset($_REQUEST['article'])?$_REQUEST['article']:false;
		$GROUP_ID = isset($_REQUEST['brand'])?$_REQUEST['brand']:false;
		$Q = ($this->config['is_groups'])?$GROUP_ID:$ARTICLE;
		$GROUP_ID = isset($_REQUEST['option'][$this->config['id']])?($_REQUEST['option'][$this->config['id']]):$Q;
		
		try {
			
			/* START */
			$config = array(
				'server' => 'http://online.autoeuro.ru/ae_server/srv_main.php',
				'client_name' => $this->config['login'],
				'client_pwd' => $this->config['pass'],
			);
			$aeClient = new AutoeuroClient($config);
			$data = $aeClient->getData('Get_Element_Details',array($GROUP_ID,$ARTICLE,1));
// 			echo('<pre>');
// 			var_dump($data);
// 			exit();
			/* STOP */
		
			if (isset($data) && count($data)>0) {
				
				$dataInDB = array();
				$i=0; foreach ($data as $dd){ $i++;
				
					$ID = "wbs-autoeuro-".$i;
					$IMPORT_ID = $this->config['importer_id'];
					$BRAND_ID = 0;
					$BRAND_NAME = strtoupper($dd['maker']);
					$ARTICLE = strtoupper($dd['code']);
					$PRICE = strtoupper($dd['price']);
					$DESCR = iconv("windows-1251", "utf-8", $dd['name'].' '.$dd['unit']);
					$BOX = strtoupper($dd['amount']);
					$DELIVERY = strtoupper($dd['order_time']);
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
				if (isset($result['Info']['Errors']) && count($result['Info']['Errors'])>0) {
					echo join(', ', $result['Info']['Errors']);
				}

				return $dataInDB;
			}
			
		} catch (Exception $exp) {}
		
	}
	function FindCatalog($detailNum=''){
		
		/* START */
		$config = array(
			'server' => 'http://online.autoeuro.ru/ae_server/srv_main.php',
			'client_name' => $this->config['login'],
			'client_pwd' => $this->config['pass'],
		);
		$aeClient = new AutoeuroClient($config);
		$data = $aeClient->getData('Search_By_Code',array($detailNum,1));
		/* STOP */
		
		$originalsSearch = array();
		if (isset($data) && count($data)>0){
			$i=0; foreach ($data as $group){ $i++;
			
				$originalsSearch [$i]-> IMP_ID = $this->config['id'];
				$originalsSearch [$i]-> SUP_ID = strtoupper(str_replace('"','',$group['maker']));
				$originalsSearch [$i]-> ART_ARTICLE_NR = strtoupper($group['code']);
				$originalsSearch [$i]-> SUP_BRAND = strtoupper(str_replace('"','',$group['maker']));
				$originalsSearch [$i]-> TEX_TEXT = iconv("windows-1251", "utf-8", $group['name']);
				$originalsSearch [$i]-> URL = '/search/wbs/?article='.urlencode($group['code']).'&brand='.urlencode($group['maker']);
				
				$originalsSearch [$i]= serialize($originalsSearch [$i]);
			}
			return $originalsSearch;
		}
	}
}

class AutoeuroClient {

	var $version = '1.0.0.0';
	var $code_method = 'base64_';
	var $server,$client_id,$client_name,$client_pwd;
	var $homedir;

	function AutoeuroClient($config) {
// 		$this->homedir = dirname(__FILE__).'/';
// 		$config = include($this->homedir.'includes/cli_config.php');
		foreach ($config as $key => $value)
			$this->$key = $value;
	}

	function getData($proc,$parm=false) {
		if(!$parm) $parm = array();
		$command = array('proc_id'=>$proc,'parm'=>$parm);
		$auth = array('client_name'=>$this->client_name,'client_pwd'=>$this->client_pwd);
		$data = array('command'=>$command,'auth'=>$auth);
		$data = $this->sendPost($this->server,$data);
		return $data;
	}
	function sendPost($url,$data) {
		$data = array('postdata'=>serialize($data));
		$data = array_map($this->code_method.'encode',$data);
		$data = http_build_query($data);
		$post = $this->genPost($url,$data);
		$url = parse_url($url);
		$fp = @fsockopen($url['host'], 80, $errno, $errstr, 30); 
		if (!$fp) return false;
		$responce = '';
		fwrite($fp,$post); 
		while ( !feof($fp) )
			$responce .= fgets($fp);
		fclose($fp);
		$responce = $this->NormalizePostResponce($responce);
		return $responce;
	}
	function genPost($url,$data) {
		$url = parse_url($url);
		$post = 'POST '.@$url['path']." HTTP/1.0\r\n"; 
		$post .= 'Host: '.$url['host']."\r\n"; 
		$post .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$post .= "Accept-Charset: windows-1251\r\n";
		$post .= 'Content-Length: '.strlen($data)."\r\n\r\n";
		$post .= $data;
		return $post;
	}
	function NormalizePostResponce($responce) {
		$responce = explode("\r\n\r\n",$responce);
		$responce = array_pop($responce);
		$responce = array_map($this->code_method.'decode',array($responce));
		$responce = unserialize($responce[0]);
		return $responce;
	}

}
?>