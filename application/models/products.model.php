<?php

class ProductsModel extends Orm {
	
	public function __construct(){
		parent::__construct(DB_PREFIX.'products');
	}
	
	public static function getMinMaxPrice($id,$filters=array(),$MINMAX='MAX'){
		$db = Register::get('db');
		if (count($filters)>0) {
			$inSQL = array();
			$filter_ids = array();
			foreach ($filters as $filter_id=>$i){ // filter_id
				if (count($i)>0) {
					$vids = array();
					foreach ($i as $dd){ // value_id
						if ($dd) {
							$vids []= $dd;
						}
					}
					if (count($vids)>0) {
						$filter_ids []= $filter_id;
						$inSQL []= " ( FV.filter_id = '".(int)$filter_id."' AND FV2P.value_id IN (".join(",",$vids).") )";
					}
					unset($vids);
				}
			}
			if (isset($inSQL) && count($inSQL)>0) {
				$sql = "
					SELECT
						FV2P.product_id
					FROM ".DB_PREFIX."filters_values2products FV2P
					JOIN ".DB_PREFIX."filters_values FV ON FV.id=FV2P.value_id
					JOIN ".DB_PREFIX."products P ON (P.id=FV2P.product_id AND P.set_isset = 1)
					JOIN ".DB_PREFIX."cat CAT ON (CAT.id=P.fk AND CAT.is_active=1)
					WHERE
						(".join(" OR ", $inSQL).")
					GROUP BY FV2P.product_id
					HAVING COUNT(FV2P.product_id) = '".(int)count($filter_ids)."';";
				$fv2p = $db->query($sql);
			}
			$getpids = array();
			if (isset($fv2p) && count($fv2p)>0) {
				foreach ($fv2p as $item){
					$getpids []= $item['product_id'];
				}
			}
			if (isset($getpids) && count($getpids)>0) {
				$sql = "
					SELECT DISTINCT
						(IF (
							$MINMAX(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
							$MINMAX(PCP.result_price),
							IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
						)) sMM
					FROM ".DB_PREFIX."products PROD
					JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk
					LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=PROD.currency
					LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON PCP.product_id=PROD.id
					WHERE
						PROD.id IN (".join(", ", $getpids).") AND
						PROD.set_isset='1' AND
						CAT.is_active=1 AND
						PROD.is_body_module IN (0,".INSTALL_BODY_MODULE.")
					GROUP BY PROD.id
					HAVING sMM > 0
					ORDER BY sMM ".(($MINMAX == 'MAX')?'DESC':'ASC')." LIMIT 0,1;";
				return $db->get($sql);
			}
			return array();
		}
		else {
				
			$ISQL = "";
			if ($id){
				if (count($id)==1)
					$ISQL .= "PROD.fk = '".join(",",$id)."' AND";
				else
					$ISQL .= "PROD.fk IN (".join(",",$id).") AND";
			}
			$sql = "SELECT DISTINCT
						(IF (
							$MINMAX(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
							$MINMAX(PCP.result_price),
							IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
						)) sMM
					FROM ".DB_PREFIX."products PROD
					JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk
					LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=PROD.currency
					LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON PCP.product_id=PROD.id
					WHERE
						$ISQL
						PROD.set_isset='1' AND
						CAT.is_active=1 AND
						PROD.is_body_module IN (0,".INSTALL_BODY_MODULE.")
					GROUP BY PROD.id
					HAVING sMM > 0
					ORDER BY sMM ".(($MINMAX == 'MAX')?'DESC':'ASC')." LIMIT 0,1;";
			return $db->get($sql);
		}
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	
	public static function getByCat($id,$page=1,$per_page=10,$order="PROD.sort ASC",$filters=array(),$querysearch=false,$price_search=array()){
		
		$db = Register::get('db');
		
		if (count($filters)>0) {
			
			$inSQL = array();
			$filter_ids = array();
			foreach ($filters as $filter_id=>$i){ // filter_id
				if (count($i)>0) {
					$vids = array();
					foreach ($i as $dd){ // value_id
						if ($dd) {
							$vids []= $dd;
						}
					}
					if (count($vids)>0) {
						$filter_ids []= $filter_id;
						$inSQL []= " ( FV.filter_id = '".(int)$filter_id."' AND FV2P.value_id IN (".join(",",$vids).") )";
					}
					unset($vids);
				}
			}
			
			if (isset($inSQL) && count($inSQL)>0) {
				$sql = "
					SELECT  
						FV2P.product_id
					FROM ".DB_PREFIX."filters_values2products FV2P
					JOIN ".DB_PREFIX."filters_values FV ON FV.id=FV2P.value_id 
					JOIN ".DB_PREFIX."products P ON (P.id=FV2P.product_id AND P.set_isset = 1)
					JOIN ".DB_PREFIX."cat CAT ON (CAT.id=P.fk AND CAT.is_active=1)
					WHERE 
						(".join(" OR ", $inSQL).")
					GROUP BY FV2P.product_id
					HAVING COUNT(FV2P.product_id) = '".(int)count($filter_ids)."';";
				$fv2p = $db->query($sql);
			}
			
			$getpids = array();
			if (isset($fv2p) && count($fv2p)>0) {
				foreach ($fv2p as $item){
					$getpids []= $item['product_id'];
				}
			}
			
			if (isset($getpids) && count($getpids)>0) {
				
				$HAVING = "";
				if ((isset($price_search['from']) && $price_search['from']) && 
				(isset($price_search['to']) && $price_search['to'])){
					$HAVING = "HAVING sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
				}
				elseif ((isset($price_search['from']) && $price_search['from'])){
					$HAVING = "HAVING sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price > 0";
				}
				elseif ((isset($price_search['to']) && $price_search['to'])){
					$HAVING = "HAVING sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
				}
				
				$sql = "
					SELECT DISTINCT
						
						PROD.id,PROD.name,PROD.content,PROD.img1,PROD.price,PROD.article,PROD.is_image_noresize,PROD.is_product_view,
						CAT.name cat,
						IF (IF (
							MIN(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
							MIN(PCP.result_price),
							IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
						),IF (
							MIN(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
							MIN(PCP.result_price),
							IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
						),".($HAVING?0:999999999).") sort_price
						
					FROM ".DB_PREFIX."products PROD
					JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk
					LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=PROD.currency
					LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON PCP.product_id=PROD.id
					WHERE 
						PROD.id IN (".join(", ", $getpids).") AND 
						PROD.set_isset='1' AND  
						CAT.is_active=1 AND 
						PROD.is_body_module IN (0,".INSTALL_BODY_MODULE.")
					GROUP BY PROD.id
					$HAVING
					ORDER BY 
						".addslashes($order).",PROD.name,PROD.img1 DESC
					LIMIT 
						".(($page - 1)*$per_page).", ".$per_page.";";
				
// 				echo('<pre>');
// 				var_dump($sql);
// 				echo('</pre>');
// 				exit();
				
				return $db->query($sql);
			}
			
			return array();
		}
		else {
			
			$ISQL = "";
			if ($id){
				if (count($id)==1)
					$ISQL .= "PROD.fk = '".join(",",$id)."' AND";
				else
					$ISQL .= "PROD.fk IN (".join(",",$id).") AND";
			}
			if (isset($querysearch) && $querysearch){
				$ISQL .= " 
				(
					(MATCH (PROD.name,PROD.content) AGAINST ('".$querysearch."' IN BOOLEAN MODE)) OR 
					PROD.id LIKE '".$querysearch."' OR 
					PROD.name LIKE '%".str_replace(" ", "%", $querysearch)."%' OR 
					CONCAT(CAT.name,' ',PROD.name) LIKE '%".str_replace(" ", "%", $querysearch)."%'
				) AND ";
			}
			
			$HAVING = "";
			if ((isset($price_search['from']) && $price_search['from']) && 
			(isset($price_search['to']) && $price_search['to'])){
				$HAVING = "HAVING sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
			}
			elseif ((isset($price_search['from']) && $price_search['from'])){
				$HAVING = "HAVING sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price > 0";
			}
			elseif ((isset($price_search['to']) && $price_search['to'])){
				$HAVING = "HAVING sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
			}
			
			$sql = "SELECT 
					
						PROD.id,PROD.name,PROD.content,PROD.img1,PROD.price,PROD.article,PROD.is_image_noresize,PROD.is_product_view,CAT.name cat,
						IF (
							IF (
								MIN(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
								MIN(PCP.result_price),
								IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
							),IF (
								MIN(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
								MIN(PCP.result_price),
								IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
						),".($HAVING?0:999999999).") sort_price
					
					FROM ".DB_PREFIX."products PROD
					JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk
					LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=PROD.currency
					LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON PCP.product_id=PROD.id
					WHERE 
						$ISQL
						PROD.set_isset='1' AND 
						CAT.is_active=1 AND 
						PROD.is_body_module IN (0,".INSTALL_BODY_MODULE.")
					GROUP BY PROD.id
					$HAVING
					ORDER BY 
						".addslashes($order).",PROD.name,PROD.img1 DESC
					LIMIT 
						".(($page - 1)*$per_page).", ".$per_page.";";
			
// 			echo('<pre>');
// 			var_dump($sql);
// 			echo('</pre>');
// 			exit();
			
			return $db->query($sql);
		}
	}
	
	public static function getByCatPaging($id,$filters=array(),$querysearch=false,$price_search=array()) {
		
		$db = Register::get('db');
		
		if (count($filters)>0) {
			
			$inSQL = array();
			$filter_ids = array();
			foreach ($filters as $filter_id=>$i){ // filter_id
				if (count($i)>0) {
					$vids = array();
					foreach ($i as $dd){ // value_id
						if ($dd) {
							$vids []= $dd;
						}
					}
					if (count($vids)>0) {
						$filter_ids []= $filter_id;
						$inSQL []= " ( FV.filter_id = '".(int)$filter_id."' AND FV2P.value_id IN (".join(",",$vids).") )";
					}
					unset($vids);
				}
			}
			
			if (isset($inSQL) && count($inSQL)>0) {
				
				$HAVING = "";
				if ((isset($price_search['from']) && $price_search['from']) && 
				(isset($price_search['to']) && $price_search['to'])){
					$HAVING = "AND sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
				}
				elseif ((isset($price_search['from']) && $price_search['from'])){
					$HAVING = "AND sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price > 0";
				}
				elseif ((isset($price_search['to']) && $price_search['to'])){
					$HAVING = "AND sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
				}
				
				$sql = "
					SELECT  
						FV2P.product_id,
						IF (IF (
							MIN(PCP.result_price) >= IF(P.currency,(P.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(P.price)),
							MIN(PCP.result_price),
							IF(P.currency,(P.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(P.price))
						),IF (
							MIN(PCP.result_price) >= IF(P.currency,(P.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(P.price)),
							MIN(PCP.result_price),
							IF(P.currency,(P.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(P.price))
						),".($HAVING?0:999999999).") sort_price 
					FROM ".DB_PREFIX."filters_values2products FV2P
					JOIN ".DB_PREFIX."filters_values FV ON FV.id=FV2P.value_id 
					JOIN ".DB_PREFIX."products P ON (P.id=FV2P.product_id AND P.set_isset = 1)
					JOIN ".DB_PREFIX."cat CAT ON (CAT.id=P.fk AND CAT.is_active=1)
					LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=P.currency
					LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON PCP.product_id=P.id
					WHERE 
						(".join(" OR ", $inSQL).")
					GROUP BY 
						FV2P.product_id
					HAVING 
						COUNT(FV2P.product_id) = '".(int)count($filter_ids)."' $HAVING;";
	 			
				return count($db->query($sql));
			}
			return 0;
		}
		else {

			$ISQL = "";
			if ($id){
				if (count($id)==1)
					$ISQL = "PROD.fk = '".join(",",$id)."' AND";
				else
					$ISQL = "PROD.fk IN (".join(",",$id).") AND";
			}
			if (isset($querysearch) && $querysearch){
				$ISQL .= " 
				(
					(MATCH (PROD.name,PROD.content) AGAINST ('".$querysearch."' IN BOOLEAN MODE)) OR 
					PROD.id LIKE '".$querysearch."' OR PROD.name LIKE '%".str_replace(" ", "%", $querysearch)."%' OR 
					CONCAT(CAT.name,' ',PROD.name) LIKE '%".str_replace(" ", "%", $querysearch)."%'
				) AND ";
			}
				
			$HAVING = "";
			if ((isset($price_search['from']) && $price_search['from']) && 
			(isset($price_search['to']) && $price_search['to'])){
				$HAVING = "HAVING sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
			}
			elseif ((isset($price_search['from']) && $price_search['from'])){
				$HAVING = "HAVING sort_price >= '".mysql_real_escape_string($price_search['from'])."' AND sort_price > 0";
			}
			elseif ((isset($price_search['to']) && $price_search['to'])){
				$HAVING = "HAVING sort_price <= '".mysql_real_escape_string($price_search['to'])."' AND sort_price > 0";
			}
			
			$sql = "
				SELECT 
					PROD.id,
					IF (IF (
						MIN(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
						MIN(PCP.result_price),
						IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
					),IF (
						MIN(PCP.result_price) >= IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)),
						MIN(PCP.result_price),
						IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price))
					),".($HAVING?0:999999999).") sort_price 
				FROM ".DB_PREFIX."products PROD 
				LEFT JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk 
				LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=PROD.currency
				LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON PCP.product_id=PROD.id
				WHERE 
					$ISQL 
					PROD.set_isset='1' AND 
					PROD.is_body_module IN (0,".INSTALL_BODY_MODULE.") AND 
					CAT.is_active=1
				GROUP BY PROD.id
				$HAVING;";
			
// 			echo('<pre>');
// 			var_dump($sql);
// 			echo('</pre>');
// 			exit();
			
			return count($db->query($sql));
		}
	}
	
	/* *********************************************************************** */
	
	public static function set_index($limit=12){
		$db = Register::get('db');
		$sql = "SELECT 
					PROD.id,PROD.name,PROD.content,PROD.img1,PROD.price,PROD.article,PROD.is_image_noresize,
					CAT.name cat,
					IF(PROD.currency,(PROD.price*REPLACE((IF(CURR.rate>0,CURR.rate,1)),',','.')),(PROD.price)) sort_price
				FROM ".DB_PREFIX."products PROD
				JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk
				LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=PROD.currency
				WHERE 
					PROD.set_index='1' AND 
					PROD.set_isset='1' AND 
					CAT.is_active=1 AND 
					PROD.is_body_module IN (0,".INSTALL_BODY_MODULE.")
				ORDER BY PROD.name,PROD.img1 DESC;";
		return $db->query($sql);
	}
	
	public static function getById($id) {
		$model = new ProductsModel();
		return $model->select()->where("id='".(int)$id."' AND set_isset=1")->fetchOne();
	}
	
	public static function getIN($ids) {
		$db = Register::get('db');
		$sql = "SELECT
					PROD.*,CAT.name cat
				FROM ".DB_PREFIX."products PROD
				LEFT JOIN ".DB_PREFIX."cat CAT ON CAT.id=PROD.fk
				WHERE 
					PROD.id IN (".join(",",$ids).") AND 
					PROD.set_isset='1' AND 
						CAT.is_active=1 AND PROD.is_body_module IN (0,".INSTALL_BODY_MODULE.")
				ORDER BY 
					PROD.sort ASC,name,img1;";
		return $db->query($sql);
	}
	
	public static function getAllPrices($id,$view=true,$dataReturn=false){
		
		$model = new ProductsModel();
		
		$db = Register::get('db');
		
		$prices = $fullPrices = $resProduct = array();	
		
		$accountCookie = AccountsModel::getByCookie();
		$accountFetchid = isset($_SESSION['account']['id'])?$_SESSION['account']['id']:$accountCookie;
		$account = AccountsModel::getById($accountFetchid);
		
		$sql = "
			SELECT 
				PROD.price,
				PROD.brand_name,
				CURR.rate as currency
			FROM ".DB_PREFIX."products PROD
			LEFT JOIN ".DB_PREFIX."currencies CURR ON CURR.id=PROD.currency
			WHERE 
				PROD.id='".(int)$id."';";
		$product = $db->get($sql);
		$price = $product['price'];
		$currency = $product['currency'];
		
		if ((float)$price > 0){
			if ($currency){
				$currency_universal = str_replace(",",".",$currency);
				$prices []= $price*$currency_universal;
				if ($dataReturn){
					$resProduct []= array("IMPORT_ID"=>0,"BRAND_NAME"=>"","BOX"=>"","DELIVERY"=>"","PRICE"=>$price*$currency_universal);
				}
			}
			else {
				$prices []= $price;
				if ($dataReturn){
					$resProduct []= array("IMPORT_ID"=>0,"BRAND_NAME"=>"","BOX"=>"","DELIVERY"=>"","PRICE"=>$price);
				}
			}
		}
		
		$sql = "
			SELECT 
				D.IMPORT_ID,
				D.PRICE,
				D.BOX,
				D.DELIVERY,
				D.BRAND_NAME,
				
				P2I.p2i_product_id,
				P2I.p2i_key article,
				P2I.p2i_key_brand brand,
				I.code,
				I.id,
				
				PCP.dt_update,
				PCP.id dt_update_id,
				
				PCP.price pcp_price,
				PCP.quant pcp_quant,
				PCP.importer_id pcp_importer_id
				
			FROM w_products2importers P2I 
			JOIN w_details D ON 
				P2I.p2i_importer_id=D.IMPORT_ID AND 
				(
					(P2I.p2i_key=D.ARTICLE AND P2I.p2i_key_brand=D.BRAND_NAME) OR 
					(P2I.p2i_key=D.ARTICLE_DEFAULT AND P2I.p2i_key_brand=D.BRAND_NAME)
				)
			JOIN ".DB_PREFIX."importers I ON I.id = P2I.p2i_importer_id
			LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON 
				PCP.product_id = P2I.p2i_product_id AND
				PCP.importer_id = I.id
			WHERE 
				P2I.p2i_product_id='".(int)$id."' AND 
				P2I.p2i_remote_server = 0
			;";
		$res = $db->query($sql);
		if (isset($res)){
			if (count($res)>0){
				$resTempConnect = array();
				foreach ($res as $row){
					
					$settime = strtotime(date("d-m-Y 10:00:00"));
					$temp = false;
					if ($row['dt_update']){
						$temp = true;
						if (strtotime(date("d-m-Y 10:00:00")) >= $row['dt_update']){
							//если дата сейчас больше установ. 10:00 дня то обновляем кеш
							$temp = false;
							$settime = strtotime(date("d-m-Y 15:00:00"));
						}
						elseif (strtotime(date("d-m-Y 15:00:00")) >= $row['dt_update']){
							//если дата сейчас больше установ. 15:00 дня то обновляем кеш
							$temp = false;
							$settime = strtotime("+1 day", strtotime(date("d-m-Y 15:00:00")));
						}
					}
					if (!$temp){
						if ($row['dt_update_id']){
							ProductsModel::update_connect_price($row['dt_update_id'],$row['p2i_product_id'],$row['id'],$row['PRICE'],$row['BOX'],$settime,$row['brand']);
						} else {
							ProductsModel::add_connect_price($row['p2i_product_id'],$row['id'],$row['PRICE'],$row['BOX'],$settime,$row['brand']);
						}
					}
				}
			} else {
				$products_connect_prices = new Orm(DB_PREFIX."products_connect_prices");
				$count_pcp = $products_connect_prices->select()->fields('id')->fetchAll();
				if (count($count_pcp)>0)
					$products_connect_prices->delete('product_id='.$id);
			}
		}
		
		
		$res = array_merge((array)$res,(array)$resProduct);
		
		/* ********************** */
		/* ** Цены с сервера **** */
		if (PRICE_FROM_SERVER){
			$sql = "
				SELECT 
					
					P2I.p2i_product_id,
					P2I.p2i_key article,
					P2I.p2i_key_brand brand,
					I.code,
					I.id,
					
					PCP.dt_update,
					PCP.id dt_update_id,
					
					PCP.price pcp_price,
					PCP.quant pcp_quant,
					PCP.importer_id pcp_importer_id
					
				FROM ".DB_PREFIX."products2importers P2I
				JOIN ".DB_PREFIX."importers I ON I.id = P2I.p2i_importer_id
				
				LEFT JOIN ".DB_PREFIX."products_connect_prices PCP ON 
					PCP.product_id = P2I.p2i_product_id AND
					PCP.importer_id = I.id
					
				WHERE 
					P2I.p2i_product_id = '".(int)$id."' AND 
					P2I.p2i_remote_server = 1
				;";
			
// 			echo('<pre>');
// 			var_dump($sql);
// 			echo('</pre>');
// 			exit();
			
			$q = $db->query($sql);
			if (isset($q) && count($q)>0){
				foreach ($q as $row){
					
					//обновить если время больше 10 дня утра
					//strtotime(date("d-m-Y 10:00:00")) ?1414134000
					
					//обновить если время больше 15 дня - далее установить дату след дня на 10 утра
					//strtotime(date("d-m-Y 15:00:00"))
					
					$settime = strtotime(date("d-m-Y 10:00:00"));
					$temp = false;
					if ($row['dt_update']){
						$temp = true;
						if (strtotime(date("d-m-Y 10:00:00")) >= $row['dt_update']){
							//если дата сейчас больше установ. 10:00 дня то обновляем кеш
							$temp = false;
							$settime = strtotime(date("d-m-Y 15:00:00"));
						}
						elseif (strtotime(date("d-m-Y 15:00:00")) >= $row['dt_update']){
							//если дата сейчас больше установ. 15:00 дня то обновляем кеш
							$temp = false;
							$settime = strtotime("+1 day", strtotime(date("d-m-Y 15:00:00")));
						}
					}
					
					if ($temp){
						
						$res []= array(
							'PRICE'	=>	$row['pcp_price'],
							'IMPORT_ID'	=> $row['pcp_importer_id'],
							'BOX'	=>	$row['pcp_quant'],
							'DELIVERY'	=>	false,
							'BRAND_NAME'	=>	$row['brand'],
						);
						
					} else {

						/* ******** */
						$getServerPrices = ProductsModel::getServerPrice($row['code'],$row['article'],$row['brand']);
						if (isset($getServerPrices) && count($getServerPrices)>0){
							foreach ($getServerPrices as $onePrice){
								$onePrice = (array)$onePrice;
								$res []= array(
										'PRICE'	=>	$onePrice['Price'],
										'IMPORT_ID'	=> $row['id'],
										'BOX'	=>	$onePrice['Quant'],
										'DELIVERY'	=>	false,
										'BRAND_NAME'	=>	$row['brand'],
								);

								if ($row['dt_update_id']){
									ProductsModel::update_connect_price($row['dt_update_id'],$row['p2i_product_id'],$row['id'],$onePrice['Price'],$onePrice['Quant'],$settime,$row['brand']);
								} else {
									ProductsModel::add_connect_price($row['p2i_product_id'],$row['id'],$onePrice['Price'],$onePrice['Quant'],$settime,$row['brand']);
								}
							}
						}
						else {
							ProductsModel::delete_connect_price($row['dt_update_id']);
						}
						/* ******** */
					}
				}
			}
		}
		/* ** Цены с сервера *** */
		/* ********************* */
		
		$base = new BaseController();
		$userId = Acl::getAuthedUserId();
		$acl = new Acl($userId);
		$view_prices_type = Register::get('view-prices-type');
		# Надстройка для отлова максимальной/минимальной цены
		#$view_prices_type == 1 = MAX
		#$view_prices_type == 2 = MIN
		
		if (isset($res) && count($res)>0){
			$triger = false; $min=$max=0; $tmp_p=array();
			foreach ($res as $dd){
				
				$inPrice = $dd['PRICE'];
				$inBrand = $dd['BRAND_NAME'];
				$importer = ImportersModel::getById($dd['IMPORT_ID']);
				$outPrices = OutpriceModel::generate($importer,$account,$inPrice,$inBrand);
				$outPrices['inPrice'] = $inPrice;
				$startPrice = $outPrices['startPrice']; #Закупка
				$resultPRICE = $outPrices['resultPRICE']; #Продажа
				
				if (isset($importer['currency']) && $importer['currency']){
					$p_start = ($startPrice*$importer['currency']);
					$p = ($resultPRICE*$importer['currency']);
					if ((float)$p > 0){
						$prices []= $p;
					}
				}
				else {
					$p_start = ($startPrice);
					$p = ($resultPRICE);
					if ((float)$p > 0){
						$prices []= $p;
					}
				}
				
				if ($dataReturn){
					
					if (!$acl->isSuper && !$base->getShopping()){
						
						if ($view_prices_type == 1){
							$triger = true;
							if ($p >= $max || count($res)==1) {
								$tmp_p = array("importer_id"=>$importer['id'],"importer"=>$importer['name_price'],"box"=>$dd['BOX'],"time"=>($dd['DELIVERY']?$dd['DELIVERY']:$importer['delivery']),"price"=>$p,"purches"=>$p_start,"fullData"=>$outPrices,"importerData"=>$importer);								
							}
							$max = $p;
						}
						elseif ($view_prices_type == 2){
							$triger = true;
							if ($min == 0) {
								$min = $p;
							}
							if ($p <= $min || count($res)==1) {
								$tmp_p = array("importer_id"=>$importer['id'],"importer"=>$importer['name_price'],"box"=>$dd['BOX'],"time"=>($dd['DELIVERY']?$dd['DELIVERY']:$importer['delivery']),"price"=>$p,"purches"=>$p_start,"fullData"=>$outPrices,"importerData"=>$importer);
							}
						}
						else{
							$fullPrices []= array("importer_id"=>$importer['id'],"importer"=>$importer['name_price'],"box"=>$dd['BOX'],"time"=>($dd['DELIVERY']?$dd['DELIVERY']:$importer['delivery']),"price"=>$p,"purches"=>$p_start,"fullData"=>$outPrices,"importerData"=>$importer);
						}
					}
					else{
						$fullPrices []= array("importer_id"=>(isset($importer['id'])?$importer['id']:0),"importer"=>(isset($importer['name_price'])?$importer['name_price']:''),"box"=>$dd['BOX'],"time"=>($dd['DELIVERY']?$dd['DELIVERY']:(isset($importer['delivery'])?$importer['delivery']:'')),"price"=>$p,"purches"=>$p_start,"fullData"=>$outPrices,"importerData"=>(isset($importer)?$importer:array()));
					}
				}
			}
			
			if ($triger) {
				$fullPrices []= $tmp_p;
			}
		}
		
		if ($dataReturn){
			return $fullPrices;
		}
		elseif ($view) {
			
			if (!$acl->isSuper && !$base->getShopping()){
				if ($view_prices_type == 1){
					if (count($prices)>0) {
						return array("max"=>max($prices),"min"=>max($prices));
					}
					else {
						return array();
					}
				}elseif ($view_prices_type == 2){
					if (count($prices)>0) {
						return array("min"=>min($prices),"max"=>min($prices));
					}
					else {
						return array();
					}
				}
			}
			
			$pp = (count($prices)>0)?array("min"=>min($prices),"max"=>max($prices)):array();
			return $pp;
		}
		else {
			asort($prices);
			return $prices;
		}
	}
	
	public static function extra($price,$extra) {
		return $price+($price*$extra/100);
	}
	
	function getPNodes($id=0){
		$db = Register::get('db');
		$sql = "
			SELECT
				P.*
			FROM ".DB_PREFIX."products2products P2P
			JOIN ".DB_PREFIX."products P ON P2P.product_id_node=P.id
			WHERE
				P2P.product_id='".(int)$id."'
		";
		return $db->query($sql);
	}
	
	private static function add_connect_price($product_id=0,$importer_id=0,$price=0,$quant=0,$dt_update=0,$brand=false){
		
		$importer = ImportersModel::getById($importer_id);
		$outPrices = OutpriceModel::generate($importer,array(),$price,$brand);
		$resultPRICE = ($importer['currency'])?($outPrices['resultPRICE']*$importer['currency']):$outPrices['resultPRICE'];
		
		$db = Register::get('db');
		$sql = "
			INSERT INTO ".DB_PREFIX."products_connect_prices 
				(`product_id`,`importer_id`,`price`,`quant`,`dt_update`,`result_price`) 
			VALUES 
				('".(int)$product_id."','".(int)$importer_id."','".mysql_real_escape_string($price)."','".mysql_real_escape_string($quant)."','".(int)$dt_update."','".mysql_real_escape_string($resultPRICE)."');";
		$db->post($sql);
	}
	private static function update_connect_price($id=0,$product_id=0,$importer_id=0,$price=0,$quant=0,$dt_update=0,$brand=false){
		
		$importer = ImportersModel::getById($importer_id);
		$outPrices = OutpriceModel::generate($importer,array(),$price,$brand);
		$resultPRICE = ($importer['currency'])?($outPrices['resultPRICE']*$importer['currency']):$outPrices['resultPRICE'];
		
		$db = Register::get('db');
		$sql = "
			UPDATE ".DB_PREFIX."products_connect_prices 
			SET 
				`price`='".mysql_real_escape_string($price)."',
				`quant`='".mysql_real_escape_string($quant)."',
				`dt_update`='".(int)$dt_update."',
				`result_price`='".mysql_real_escape_string($resultPRICE)."'
			WHERE `id`='".(int)$id."';";
		$db->post($sql);
	}
	private static function delete_connect_price($id=0){
		$db = Register::get('db');
		$sql = "
			DELETE FROM ".DB_PREFIX."products_connect_prices 
			WHERE `id`='".(int)$id."';";
		$db->post($sql);
	}
	
	/* ************************************************* */
	
	private static function getServerPrice($code=false,$article=false,$brand=false){
		try {
			
			if ($code && $article && $brand){
				
				$options = array('soap_version'=>SOAP_1_2,'exceptions'=>true,'trace'=>1,'cache_wsdl'=>WSDL_CACHE_NONE);
			    $client = new SoapClient(SOAP, $options);
			    $ACCESS_C = array('SERVER'=>$_SERVER,'KEY'=>KEY);
			    $results = $client->ViewDetail(
				array(
					'request'=>array(
						'ACCESS_C'=>$ACCESS_C,
						'FUNCTION'=>'getPriceForShop',
						'PARAMS'=>array(
							'code_importer'=>$code,
							'article'=>$article,
							'brand'=>$brand
						),
					)
				));
				return json_decode($results);
			}
			
		} catch (Exception $e) {
			die("<h2>Exception Error! The server is unavailable.</h2>");
		}
	}
	/* *** */
	
}

?>