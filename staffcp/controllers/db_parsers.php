<?php

class Db_parsersController  extends CmsGenerator {
	
	public $layout = 'global';
	var $brand = array();
	
	function index() {
		$this->redirectUrl("/staffcp/harvesterclaas/#tab-2");
		exit();
	}
	/* GENERAL FUNCTIONS */
	var $arrayArticles = array();
	function findCrossByArticleBrand($ARTICLE,$BRAND){
		if (isset($this->arrayArticles[md5($ARTICLE.$BRAND)]) && $this->arrayArticles[md5($ARTICLE.$BRAND)])
			return $this->arrayArticles[md5($ARTICLE.$BRAND)];
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."details_db__crosses WHERE ARTICLE LIKE '".addslashes($ARTICLE)."' AND BRAND LIKE '".addslashes($BRAND)."' LIMIT 0,1;";
		$res = $db->get($sql);
		$this->arrayArticles[md5($ARTICLE.$BRAND)]=$res;
		return $res;
	}
	function extra($price,$extra) {
		return $price+($price*$extra/100);
	}
	function findImp($code){
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."importers` WHERE `code`='".$code."';";
		$get = $db->get($sql);
		if (count($get)>0) {
			return $get;
		}
		else {
			echo 'Not found importer by code '.$code;
			exit();
		}
	}
	function findBrand($BRAND){
		if ($this->brand[$BRAND])
			return $this->brand[$BRAND];
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."brands WHERE BRA_MFC_CODE LIKE '".$BRAND."' OR BRA_BRAND LIKE '".$BRAND."' LIMIT 0,1;";
		$res = $db->get($sql);
		$this->brand[$BRAND]=$res;
		return $res;
	}
	function stringfilter($str) {
		return str_replace(array('!','@','#','$','%','^','&','*','(',')','_','+','=','-','~','`','"',"'",' ','№','%',';',':','[',']','{','}','*','?','/','\'','|','.',',','<','>'),'',$str);
	}
	
	/* MOTEX ************************************************************************************************* */
	function motex() {
		if (isset($_FILES['upload_file']['tmp_name'])) {
			/* CODAS */
			$IMPCODE = strtolower($this->request("IMPCODE"));
			$db = Register::get('db');
			$getIMP = $this->findImp($IMPCODE);
			require_once('ycsvparser.class.php');
			/* UND */
			
			/* 2.9+ */
			$parse = $this->request("parse");
			if (isset($parse['delprice']) && $parse['delprice'] == 1){
				mysql_query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='".(int)$getIMP['id']."';");
				mysql_query("DELETE FROM ".DB_PREFIX."details_cross WHERE IMPORT_ID='".(int)$getIMP['id']."';");
			}
			$currency = $parse['currency'];
			$use_crosses = (isset($parse['use_crosses'])&&$parse['use_crosses'])?1:0;
			/* 2.9+ */
			
			$mtime = microtime();
			$i=$j=0;
			$ycsv = new ycsvParser($_FILES['upload_file']['tmp_name'],false);
			
			while ($record = $ycsv->getRecord()) {
				$res = $ycsv->parseRecord($record);
				
				$NUMBER = iconv("windows-1251","UTF-8",trim($res[0]));#KEY
				$BOX = addslashes(trim($res[1]));
				$PRICE = str_replace(",",".",$res[2]);
				
				$itemsplit 		= explode("_",$NUMBER);
				$ARTICLE 	= $this->stringfilter(trim($itemsplit[0]));
				$BRAND 		= trim($itemsplit[1]);
				
				$IMG_URL = '';
				$DESCR = '';
				
				$i++;
				$findBRND = $this->findBrand(($BRAND));
				
				/* 2.9+ */
				$PRICE_UNI	= (($currency)?$PRICE*$currency:$PRICE);
				$PRICE_UNI	= ceil($this->extra($PRICE_UNI,$getIMP['discount']));
				if (isset($use_crosses) && $use_crosses == 1){
					$GETCROSS = $this->findCrossByArticleBrand($ARTICLE,$BRAND);
					if (empty($DESCR)){
						$DESCR = $GETCROSS['DESCR'];
					}
					if (empty($IMG_URL) && $GETCROSS['IMG']){
						$IMG_URL = 'http://'.$_SERVER['SERVER_NAME'].'/media/files/crosses/'.$GETCROSS['IMG'];
					}
				}
				/* 2.9+ */
				
				if ($PRICE) {
					$db->post("INSERT INTO ".DB_PREFIX."details (`IMPORT_ID`,`BRAND_ID`,`BRAND_NAME`,`ARTICLE`,`PRICE`,`PRICE_UNI`,`DESCR`,`BOX`,`DELIVERY`,`IMG_URL`,`ONLY_FOR_SHOP`) VALUES ('".$getIMP['id']."','".addslashes($findBRND['BRA_ID_GET'])."','".addslashes($BRAND)."','".addslashes($ARTICLE)."','".mysql_real_escape_string($PRICE)."','".mysql_real_escape_string($PRICE_UNI)."','".mysql_real_escape_string($DESCR)."','".mysql_real_escape_string($BOX)."','','".mysql_real_escape_string($IMG_URL)."','".(int)$getIMP['ONLY_FOR_SHOP']."');");
					$lastID = $db->lastInsertId();
					
					if (isset($use_crosses) && $use_crosses == 1){
						$OEN = $GETCROSS['OEN'];
						$CROSS_ARRAY = explode(',',$OEN);
						foreach ($CROSS_ARRAY as $key=>$val) {
							if ($val && strlen($val)>3){
								$db->post("INSERT INTO ".DB_PREFIX."details_cross (`IMPORT_ID`,`detail_id`,`cross`) VALUES ('".(int)$getIMP['id']."','".(int)$lastID."','".addslashes($this->stringfilter($val))."');");
							}
						}
					}
				}
			}
			
			echo '<h1>MOTEX</h1>';
			echo $i." processed rows.<br/>";
			
			/* TIME */
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tstart = $mtime;
			$mtime = microtime();
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tend = $mtime;
			echo("<br/> Sec:");
			echo(ceil($tend - $tstart));
			exit();
		}
	}
	
	/* NGOROD ************************************************************************************************* */
	function ngorod() {
		
		if (isset($_FILES['upload_file']['tmp_name'])) {
			/* CODAS */
			$IMPCODE = strtolower($this->request("IMPCODE"));
			$db = Register::get('db');
			$getIMP = $this->findImp($IMPCODE);
			require_once('ycsvparser.class.php');
			/* UND */
			
			/* 2.9+ */
			$parse = $this->request("parse");
			if (isset($parse['delprice']) && $parse['delprice'] == 1){
				mysql_query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='".(int)$getIMP['id']."';");
				mysql_query("DELETE FROM ".DB_PREFIX."details_cross WHERE IMPORT_ID='".(int)$getIMP['id']."';");
			}
			$currency = $parse['currency'];
			$use_crosses = (isset($parse['use_crosses'])&&$parse['use_crosses'])?1:0;
			/* 2.9+ */
			
			$mtime = microtime();
			$i=$j=0;
			$ycsv = new ycsvParser($_FILES['upload_file']['tmp_name'],false);
			while ($record = $ycsv->getRecord()) {
				$res = $ycsv->parseRecord($record);
				$i++;
				
				$row_data = addslashes(trim(iconv("windows-1251","UTF-8",$res[0])));
				$data = explode("^",$row_data);
				
				$BRAND = $data[0];
				$ARTICLE = $this->stringfilter($data[1]);
				$DESCR = $data[2];
				$PRICE = str_replace(",",".",strtolower(addslashes(trim(iconv("windows-1251","UTF-8",$data[3])))));
				$BOX = $data[5];
				
				$IMG_URL = '';
				
				$findBRND = $this->findBrand(($BRAND));
				
				/* 2.9+ */
				$PRICE_UNI	= (($currency)?$PRICE*$currency:$PRICE);
				$PRICE_UNI	= ceil($this->extra($PRICE_UNI,$getIMP['discount']));
				if (isset($use_crosses) && $use_crosses == 1){
					$GETCROSS = $this->findCrossByArticleBrand($ARTICLE,$BRAND);
					if (empty($DESCR)){
						$DESCR = $GETCROSS['DESCR'];
					}
					if (empty($IMG_URL) && $GETCROSS['IMG']){
						$IMG_URL = 'http://'.$_SERVER['SERVER_NAME'].'/media/files/crosses/'.$GETCROSS['IMG'];
					}
				}
				/* 2.9+ */
				
				if ($PRICE){
					$db->post("INSERT INTO ".DB_PREFIX."details (`IMPORT_ID`,`BRAND_ID`,`BRAND_NAME`,`ARTICLE`,`PRICE`,`PRICE_UNI`,`DESCR`,`BOX`,`DELIVERY`,`IMG_URL`,`ONLY_FOR_SHOP`) VALUES ('".addslashes($getIMP['id'])."','".addslashes($findBRND['BRA_ID_GET'])."','".addslashes($BRAND)."','".addslashes($ARTICLE)."','".mysql_real_escape_string($PRICE)."','".mysql_real_escape_string($PRICE_UNI)."','".addslashes($DESCR)."','".addslashes($BOX)."','','".mysql_real_escape_string($IMG_URL)."','".(int)$getIMP['ONLY_FOR_SHOP']."');");
					$lastID = $db->lastInsertId();
					
					if (isset($use_crosses) && $use_crosses == 1){
						$OEN = $GETCROSS['OEN'];
						$CROSS_ARRAY = explode(',',$OEN);
						foreach ($CROSS_ARRAY as $key=>$val) {
							if ($val && strlen($val)>3){
								$db->post("INSERT INTO ".DB_PREFIX."details_cross (`IMPORT_ID`,`detail_id`,`cross`) VALUES ('".(int)$getIMP['id']."','".(int)$lastID."','".addslashes($this->stringfilter($val))."');");
							}
						}
					}
				}
			}
			
			echo '<h1>NGorod</h1>';
			echo $i." processed rows.<br/>";
			
			/* TIME */
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tstart = $mtime;
			$mtime = microtime();
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tend = $mtime;
			echo("<br/> Sec:");
			echo(ceil($tend - $tstart));
			exit();
		}
	}
		
	/* ASMETAL ************************************************************************************************* */
	function asmetal() {
		
		if (isset($_FILES['upload_file']['tmp_name'])) {
			/* CODAS */
			$IMPCODE = strtolower($this->request("IMPCODE"));
			$db = Register::get('db');
			$getIMP = $this->findImp($IMPCODE);
			require_once('ycsvparser.class.php');
			/* UND */
						
			$BRANDNAME = $this->request("BRANDNAME");
			$TYPESAVE = $this->request("TYPESAVE",0);
			
			/* 2.9+ */
			$parse = $this->request("parse");
			if (isset($parse['delprice']) && $parse['delprice'] == 1){
				mysql_query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='".(int)$getIMP['id']."';");
				mysql_query("DELETE FROM ".DB_PREFIX."details_cross WHERE IMPORT_ID='".(int)$getIMP['id']."';");
			}
			$currency = $parse['currency'];
			/* 2.9+ */
			
			$mtime = microtime();
			$i=$j=0;
			$ycsv = new ycsvParser($_FILES['upload_file']['tmp_name'],false);
			while ($record = $ycsv->getRecord()) {
				$res = $ycsv->parseRecord($record);
				$i++;
				
				$col1 = $res[0];#NOT USE
				
				$col2 = $res[1];#ART + NAME
				$ART_ARRAY = explode(" ",$col2);
				$ARTICLE = iconv("windows-1251","UTF-8",$ART_ARRAY[0]);
				$NAME = $col2;
				
				$col3 = $res[2];#CROSS
				$CROSS = explode("\n",$col3);
				
				$col4 = $res[3];#DESCR
				$DESCR = iconv("windows-1251","UTF-8",$NAME." ".$col4);
				
				$col5 = $res[4];#BOX
				$BOX = $col5;
				
				$col6 = $res[5];#PRICE
				$PRICE = str_replace(" ","",str_replace(",",".",addslashes(trim($col6))));
				
				/* 2.9+ */
				$PRICE_UNI	= (($currency)?$PRICE*$currency:$PRICE);
				$PRICE_UNI	= ceil($this->extra($PRICE_UNI,$getIMP['discount']));
				/* 2.9+ */
				
				$db->post("INSERT INTO ".DB_PREFIX."details (`IMPORT_ID`,`BRAND_ID`,`BRAND_NAME`,`ARTICLE`,`PRICE`,`PRICE_UNI`,`DESCR`,`BOX`,`DELIVERY`,`IS_CROSS`,`ONLY_FOR_SHOP`) VALUES ('".addslashes($getIMP['id'])."','0','".addslashes($BRANDNAME)."','".addslashes($this->stringfilter($ARTICLE))."','".mysql_real_escape_string($PRICE)."','".mysql_real_escape_string($PRICE_UNI)."','".addslashes($DESCR)."','".addslashes($BOX)."','".addslashes($getIMP['delivery'])."','1','".(int)$getIMP['ONLY_FOR_SHOP']."');");
				$lastID = $db->lastInsertId();
				
				if (count($CROSS)>0){
					foreach ($CROSS as $k=>$v){
						if ($v)
							$db->post("INSERT INTO ".DB_PREFIX."details_cross (`IMPORT_ID`,`detail_id`,`cross`) VALUES ('".($getIMP['id'])."','".$lastID."','".addslashes($this->stringfilter($v))."');");
					}
				}
			}
			echo '<h1>Asmetal</h1>';
			echo $i." processed rows.<br/>";
			
			/* TIME */
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tstart = $mtime;
			$mtime = microtime();
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tend = $mtime;
			echo("<br/> Sec:");
			echo(ceil($tend - $tstart));
			exit();
		}
	}
	
	/* SHATE-M ************************************************************************************************* */
	
	function shatem() {
		$db = Register::get('db');
		require_once('ycsvparser.class.php');
		if (isset($_FILES['upload_file']['tmp_name']) && isset($_REQUEST['switcher']) && $_REQUEST['switcher']=='full') {
			/* CODAS */
			$IMPCODE = strtolower($this->request("IMPCODE"));
			$getIMP = $this->findImp($IMPCODE);
			/* UND */
			
			/* 2.9+ */
			$parse = $this->request("parse");
			if (isset($parse['delprice']) && $parse['delprice'] == 1){
				mysql_query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='".(int)$getIMP['id']."';");
				mysql_query("DELETE FROM ".DB_PREFIX."details_cross WHERE IMPORT_ID='".(int)$getIMP['id']."';");
			}
			$currency = $parse['currency'];
			$use_crosses = (isset($parse['use_crosses'])&&$parse['use_crosses'])?1:0;
			/* 2.9+ */
			
			$mtime = microtime();
			$i=$j=0;
			$ycsv = new ycsvParser($_FILES['upload_file']['tmp_name'],false);
			
			while ($record = $ycsv->getRecord()) {
				$res = $ycsv->parseRecord($record);
				
				$NUMBER = explode("_",trim($res[0]));#KEY
				$ARTICLE = $this->stringfilter($NUMBER[0]);
				$BRAND = $NUMBER[1];
				$BOX = (int)addslashes(trim($res[1]));
				$EUR = str_replace(",",".",addslashes(trim($res[2])));
				$BLR = str_replace(",",".",addslashes(trim($res[6])));
				$PRICE = (isset($_REQUEST['switch_price']) && $_REQUEST['switch_price']==1)?$BLR:$EUR;
				$DESCR = '';
				$IMG_URL = '';
				
				$findBRND = $this->findBrand($BRAND);
				
				if ($PRICE && $BOX>0) { $i++;
				
					/* 2.9+ */
					$PRICE_UNI	= (($currency)?$PRICE*$currency:$PRICE);
					$PRICE_UNI	= ceil($this->extra($PRICE_UNI,$getIMP['discount']));
					if (isset($use_crosses) && $use_crosses == 1){
						$GETCROSS = $this->findCrossByArticleBrand($ARTICLE,$BRAND);
						if (empty($DESCR)){
							$DESCR = $GETCROSS['DESCR'];
						}
						if (empty($IMG_URL) && $GETCROSS['IMG']){
							$IMG_URL = 'http://'.$_SERVER['SERVER_NAME'].'/media/files/crosses/'.$GETCROSS['IMG'];
						}
					}
					/* 2.9+ */

					$db->post("INSERT INTO ".DB_PREFIX."details (`IMPORT_ID`,`BRAND_ID`,`BRAND_NAME`,`ARTICLE`,`PRICE`,`PRICE_UNI`,`DESCR`,`BOX`,`DELIVERY`,`IMG_URL`,`IS_CROSS`,`ONLY_FOR_SHOP`) VALUES ('".(int)$getIMP['id']."','".addslashes($findBRND['BRA_ID_GET'])."','".addslashes($BRAND)."','".addslashes($ARTICLE)."','".(mysql_real_escape_string($PRICE))."','".(mysql_real_escape_string($PRICE_UNI))."','".addslashes($DESCR)."','".addslashes($BOX)."','','".addslashes($IMG_URL)."','1','".(int)$getIMP['ONLY_FOR_SHOP']."');");
					$lastID = $db->lastInsertId();

					if (isset($use_crosses) && $use_crosses == 1){
						$OEN = $GETCROSS['OEN'];
						$CROSS_ARRAY = explode(',',$OEN);
						foreach ($CROSS_ARRAY as $key=>$val) {
							if ($val && strlen($val)>3){
								$db->post("INSERT INTO ".DB_PREFIX."details_cross (`IMPORT_ID`,`detail_id`,`cross`) VALUES ('".(int)$getIMP['id']."','".(int)$lastID."','".addslashes($this->stringfilter($val))."');");
							}
						}
					}
				}
			}
			
			echo '<h1>ShateM</h1>';
			echo $i." found and processed in the database.";
			
			/* TIME */
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tstart = $mtime;
			$mtime = microtime();
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tend = $mtime;
			echo("<br/> Sec:");
			echo(ceil($tend - $tstart));
			exit();
		}
	}
	/* ************************************************************************************************* */
	
	
	/* ORIGINALS GROUPS ************************************************************************************************* */
	function addKoff(){
		$add = $this->request("add");
		$db = Register::get('db');
		$sql = "INSERT INTO ".DB_PREFIX."details_db__koefficient (`BRAND`,`GROUP`,`OPT`,`ROZ`) VALUES ('".mysql_real_escape_string($add['BRAND'])."','".mysql_real_escape_string($add['GROUP'])."','".mysql_real_escape_string($add['OPT'])."','".mysql_real_escape_string($add['ROZ'])."');";
		$db->post($sql);
	}
	function getBrandsRows(){
		$brand = mysql_real_escape_string($this->request("brand"));
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."details_db__koefficient WHERE `BRAND` LIKE '".$brand."';";
		return $db->query($sql);
	}
	function updateOriginals(){
		$db = Register::get('db');
		$BRAND = $this->request("BRAND");
		$GROUP = $this->request("GROUP");
		$OPT = $this->request("OPT");
		$ROZ = $this->request("ROZ");
		$DEL = $this->request("DEL");
		if (isset($GROUP) && count($GROUP)>0){
			foreach ($GROUP as $key=>$dd){
				if (isset($DEL[$key]) && $DEL[$key] == 1){
					$db->post("DELETE FROM ".DB_PREFIX."details_db__koefficient WHERE id='".(int)$key."';");
				}
				else {
					$db->post("
						UPDATE ".DB_PREFIX."details_db__koefficient
						SET
							`BRAND`='".mysql_real_escape_string($BRAND[$key])."',
							`GROUP`='".mysql_real_escape_string($dd)."',
							`OPT`='".mysql_real_escape_string($OPT[$key])."',
							`ROZ`='".mysql_real_escape_string($ROZ[$key])."' 
						WHERE id='".(int)$key."';
					");
				}
			}
		}
	}
	function originals() {
		$db = Register::get('db');
		$sql = "SELECT DISTINCT BRAND FROM ".DB_PREFIX."details_db__koefficient;";
		$this->view->BRANDS = $db->query($sql);
		
		$action = $this->request("action",false);
		if ($action){
			switch ($action){
				case 'add_koff':
					$this->addKoff();
					break;
				case 'view_brand':
					$this->view->edit_brand = $this->getBrandsRows();
					break;
				case 'editing':
					$this->updateOriginals();
					break;
			}
		}
		
		if (isset($_FILES['upload_file']['tmp_name'])) {
			
			$WEIGHT_PRICE = (floatval($this->request('WEIGHT',0)));
			$group = $this->request("group");
			if (isset($group)&&count($group)>0)
				$this->prepareGroups($group);
			
			/* CODAS */
			$IMPCODE = strtolower($this->request("IMPCODE"));
			$getIMP = $this->findImp($IMPCODE);
			require_once('ycsvparser.class.php');
			/* UND */
			
			$BRANDNAME = $this->request("BRANDNAME");
			$TYPESAVE = $this->request("TYPESAVE",0);
			$TYPEPRICE = $this->request("TYPEPRICE",0);
			
			if ($TYPESAVE) {
				mysql_query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='".(int)$getIMP['id']."';");
				mysql_query("DELETE FROM ".DB_PREFIX."details_cross WHERE IMPORT_ID='".(int)$getIMP['id']."';");
			}
			
			$mtime = microtime();
			$i=$j=0;
			$ycsv = new ycsvParser($_FILES['upload_file']['tmp_name'],false);
			while ($record = $ycsv->getRecord()) {
				$res = $ycsv->parseRecord($record);
				$i++;
				
				$ARTICLE = addslashes(trim(iconv("windows-1251","UTF-8",$res[0])));
				$DESCR = addslashes(trim(iconv("windows-1251","UTF-8",$res[1])));
				$GROUP = addslashes(trim(iconv("windows-1251","UTF-8",$res[3])));
				$WEIGHT = addslashes(trim(iconv("windows-1251","UTF-8",$res[4])));
				
				$findBRND = $this->findBrand(($BRANDNAME));
				
				$PRICE = str_replace(array("br"),"",str_replace(",",".",strtolower(addslashes(trim(iconv("windows-1251","UTF-8",$res[2]))))));
				$percent = $this->prepareProcent($BRANDNAME,$GROUP);
				$OPT = $percent['OPT'];
				$ROZ = $percent['ROZ'];
				
				$PRICE_RES = 0;
				if ($TYPEPRICE == 1) {
					if ($OPT){
						$PRICE_RES = $PRICE*$OPT + (str_replace(",",".",$WEIGHT) * $WEIGHT_PRICE);
					}
					else {
						$PRICE_RES = $PRICE + (str_replace(",",".",$WEIGHT) * $WEIGHT_PRICE);	
					}
				}
				else {
					if ($ROZ){
						$PRICE_RES = ($PRICE*$ROZ) + (str_replace(",",".",$WEIGHT) * $WEIGHT_PRICE);
						
						/*var_dump($PRICE);
						var_dump($ROZ);
						var_dump(($PRICE*$ROZ));
						var_dump(str_replace(",",".",$WEIGHT));
						var_dump($WEIGHT_PRICE);
						var_dump((str_replace(",",".",$WEIGHT) * $WEIGHT_PRICE));
						var_dump($PRICE_RES);
						exit();*/
					}
					else {
						$PRICE_RES = $PRICE + (str_replace(",",".",$WEIGHT) * $WEIGHT_PRICE);	
					}
				}
				
				$db->post("INSERT INTO ".DB_PREFIX."details (`IMPORT_ID`,`BRAND_ID`,`BRAND_NAME`,`ARTICLE`,`PRICE`,`DESCR`,`BOX`,`DELIVERY`,`IS_CROSS`,`ONLY_FOR_SHOP`,`WEIGHT`) VALUES ('".addslashes($getIMP['id'])."','".addslashes($findBRND['BRA_ID_GET'])."','".addslashes($BRANDNAME)."','".addslashes($this->stringfilter($ARTICLE))."','".mysql_real_escape_string($PRICE_RES)."','".addslashes($DESCR)."','','".addslashes($getIMP['delivery'])."','0','".(int)$getIMP['ONLY_FOR_SHOP']."','".mysql_real_escape_string($WEIGHT)."');");
				
			}
			
			echo '<h1>Originals</h1>';
			echo $i." processed rows.<br/>";
			
			/* TIME */
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tstart = $mtime;
			$mtime = microtime();
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$tend = $mtime;
			echo("<br/> Sec:");
			echo(ceil($tend - $tstart));
			exit();
		}
	}
	function ajax_originals_groups(){
		
		$this->layout = "ajax";
		
		$brand = mysql_real_escape_string($this->request("brand"));
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."details_db__koefficient WHERE `BRAND` LIKE '".$brand."';";
		$res = $db->query($sql);
		
		if (isset($res)&&count($res)>0){
			echo '<table border="0" align="center">';
				echo '<tr>';
					echo '<td><b>Brand</b></td>';
					echo '<td><b>Group</b></td>';
					echo '<td><b>Koeff (opt)</b></td>';
					echo '<td><b>Koeff (roz)</b></td>';
				echo '</tr>';
				foreach ($res as $dd){
				echo '<tr>';
					echo '<td>'.($dd['BRAND']).'</td>';
					echo '<td><input type="text" name="group[group]['.$dd['id'].']" value="'.($dd['GROUP']).'" style="width:50px;"/></td>';
					echo '<td><input type="text" name="group[opt]['.$dd['id'].']" value="'.($dd['OPT']).'" style="width:50px;"/></td>';
					echo '<td><input type="text" name="group[roz]['.$dd['id'].']" value="'.($dd['ROZ']).'" style="width:50px;"/></td>';
				echo '</tr>';
				}
			echo '</table>';
		} else {
			echo 'Clear! No data!';
		}
		
		exit();
	}
	function prepareGroups($data){
		$db = Register::get('db');
		if (isset($data['group'])&&count($data['group'])>0){
			$i=0;
			foreach ($data['group'] as $kk=>$dd){
				$i++;
				$sql = "UPDATE `".DB_PREFIX."details_db__koefficient` SET `GROUP`='".mysql_real_escape_string($dd)."',`OPT`='".mysql_real_escape_string($data['opt'][$kk])."',`ROZ`='".mysql_real_escape_string($data['roz'][$kk])."' WHERE id='".($kk)."';";
				$db->post($sql);
			}
		}
	}
	private function prepareProcent($BRAND,$GROUP){
		$db = Register::get('db');
		$sql = "SELECT * FROM `".DB_PREFIX."details_db__koefficient` WHERE `BRAND` LIKE '%".mysql_real_escape_string($BRAND)."%' AND `GROUP` LIKE '%".mysql_real_escape_string($GROUP)."%';";
		$res = $db->get($sql);
		return $res;
	}
	private function getGroups(){
		$db = Register::get('db');
		$sql = "SELECT DISTINCT BRAND FROM ".DB_PREFIX."details_db__koefficient;";
		return $db->query($sql);
	}
	/* ************************************************************************************************* */
}
?>