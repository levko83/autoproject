<?php

class ShopimportController  extends CmsGenerator {
	
	public $layout = 'global';
	
	function index(){
	}
	
	function parse(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Импорт товаров в раздел магазин c помощью xlsx файла',URL_NOW);
		
    	$db = Register::get('db');
    	if ($_FILES['file']['name']) {
			if (move_uploaded_file($_FILES['file']['tmp_name'], '../cache/'.$_FILES['file']['name'])) {
				$file = '../cache/'.$_FILES['file']['name'];
				$ext = explode(".", basename($file));
				$ext = array_pop($ext);
				if ($ext == 'xlsx'){
					require_once '../xreaders/readers/simplexlsx.class.php';
					$xlsx = new SimpleXLSX($file);
					$filter_id = $i = $i1 = 0;
					foreach( $xlsx->rows() as $r ) {
						
						$category_id 		= (int)mysql_real_escape_string($r[0]);
						$product_name 		= mysql_real_escape_string($r[1]);
						$brief		 		= nl2br(mysql_real_escape_string($r[2]));
						$content	 		= nl2br(mysql_real_escape_string($r[3]));
						
						$img1	 			= mysql_real_escape_string($r[4]);
						$img2	 			= mysql_real_escape_string($r[5]);
						$img3	 			= mysql_real_escape_string($r[6]);
						
						$img1				= ($img1)?('inside-placeholder-').$img1:'';
						$img2				= ($img2)?('inside-placeholder-').$img2:'';
						$img3				= ($img3)?('inside-placeholder-').$img3:'';
						
						$price	 			= mysql_real_escape_string($r[7]);
						$currency	 		= mysql_real_escape_string($r[8]);
						$sort	 			= mysql_real_escape_string($r[9]);
						
						$price_imp_code		= mysql_real_escape_string($r[10]);
						$price_impoter_id 	= $this->findImporterByCode($price_imp_code);
						$price_article		= mysql_real_escape_string($r[11]);
						$price_brand		= mysql_real_escape_string($r[12]);
						
						$seo_title 			= mysql_real_escape_string($r[13]);
						$seo_kwords 		= mysql_real_escape_string($r[14]);
						$seo_descr 			= mysql_real_escape_string($r[15]);
						
						$article 			= mysql_real_escape_string($r[16]);
						$url 				= mysql_real_escape_string($r[17]);
						$filter_id_static	= @(int)mysql_real_escape_string($r[18]);
						
						if($i1 == 0) {
							$filter_id = $this->updateFiltersArr($r,$filter_id_static);
						}
						$i1=1;
						
						if ($category_id && $product_name) {
						$i++;
						
							#UPDATE
							$chk_id = $this->findProduct($category_id,$product_name);
							if ($chk_id){
								$prod_id = $this->product_update($chk_id,$category_id,$product_name,$brief,$content,$img1,$img2,$img3,$price,$currency,$sort,$seo_title,$seo_kwords,$seo_descr,$article,$url);
							}
							#INSERT
							else {
								$prod_id = $this->product_insert($category_id,$product_name,$brief,$content,$img1,$img2,$img3,$price,$currency,$sort,$seo_title,$seo_kwords,$seo_descr,$article,$url);
							}
							
							#ADD P2I
							if ($price_impoter_id && $price_brand && $price_article){
								$this->add_products2importers($prod_id,$price_impoter_id,$price_article,$price_brand);
							}
							
							$this->updateFilterValues($r, $prod_id);
							#exit();
						}
					
						if ($filter_id_static && !empty($prod_id))
							$this->product_update_filter_id($prod_id,$filter_id_static);
					}
				}
				echo "<h1>Operation done! Thanks! Imported rows: ".($i)."</h1>";
			}
		}
		else {
			echo "<h1>Error! Internal error!</h1>";
		}
		exit();
	}
	
	private $cat = array();
	
	private function findProduct($category_id,$name){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."products WHERE `fk`='".(int)$category_id."' AND `name` LIKE '".mysql_real_escape_string($name)."';";
		$res = $db->get($sql);
		return $res['id'];
	}
	
	private function getIdCurrency($code=null){
		$currency = new Orm(DB_PREFIX.'currencies');
		$get = $currency->select()->fields('id')->where('code=?',$code)->fetchOne();
		return $get['id'];
	}
	
	private function product_insert($category_id='',$product_name='',$brief='',$content='',$img1='',$img2='',$img3='',$price='',$currency='',$sort='',$seo_title='',$seo_kwords='',$seo_descr='',$article='',$url=''){
		
		$currency = $this->getIdCurrency($currency);
		
		$db = Register::get('db');
		$sql = "
		INSERT INTO ".DB_PREFIX."products 
			(`fk`,`name`,`brief`,`content`,`img1`,`img2`,`img3`,`price`,`sort`,`set_isset`,`title`,`kwords`,`descr`,`article`,`url`,`currency`) 
		VALUES 
			('".$category_id."','".$product_name."','".$brief."','".$content."','".$img1."','".$img2."','".$img3."','".$price."''".$currency."','".$sort."','1','".$seo_title."','".$seo_kwords."','".$seo_descr."','".$article."','".$url."','".$currency."');";
		$db->post($sql);
		return $db->lastInsertId();
	}
	
	private function product_update($product_id=0,$category_id='',$product_name='',$brief='',$content='',$img1='',$img2='',$img3='',$price='',$currency='',$sort='',$seo_title='',$seo_kwords='',$seo_descr='',$article='',$url=''){
		
		$currency = $this->getIdCurrency($currency);
		
		$db = Register::get('db');
		$sql = "
		UPDATE ".DB_PREFIX."products SET 
			`fk`='".$category_id."',
			`name`='".$product_name."',
			".(($brief)?("`brief`='".$brief."',"):"")."
			".(($content)?("`content`='".$content."',"):"")."
			".(($img1)?("`img1`='".$img1."',"):"")."
			".(($img2)?("`img2`='".$img2."',"):"")."
			".(($img3)?("`img3`='".$img3."',"):"")."
			".(($price)?("`price`='".$price."',"):"")."
			".(($currency)?("`currency`='".$currency."',"):"")."
			".(($sort)?("`sort`='".$sort."',"):"")."
			".(($seo_title)?("`title`='".$seo_title."',"):"")."
			".(($seo_kwords)?("`kwords`='".$seo_kwords."',"):"")."
			".(($seo_descr)?("`descr`='".$seo_descr."',"):"")."
			".(($article)?("`article`='".$article."',"):"")."
			".(($url)?("`url`='".$url."',"):"")."
			`set_isset`=1
		WHERE
			`id`='".(int)$product_id."';";
		$db->post($sql);
		return $product_id;
	}
	
	private function add_products2importers($p2i_product_id=0,$p2i_importer_id=0,$p2i_key='',$p2i_key_brand=''){
		$db = Register::get('db');
		
		if ($p2i_product_id && $p2i_importer_id && $p2i_key){
			$sql = "DELETE FROM ".DB_PREFIX."products2importers WHERE p2i_product_id='".(int)$p2i_product_id."';";
			$db->post($sql);
			
			$sql = "
				INSERT INTO ".DB_PREFIX."products2importers 
					(`p2i_product_id`,`p2i_importer_id`,`p2i_key`,`p2i_key_brand`) 
				VALUES 
					('".(int)$p2i_product_id."','".(int)$p2i_importer_id."','".mysql_real_escape_string($p2i_key)."','".mysql_real_escape_string($p2i_key_brand)."');";
			$db->post($sql);
		}
	}
	
	private function findImporterByCode($code=false){
		$db = Register::get('db');
		if ($code){
			$sql = "SELECT id FROM ".DB_PREFIX."importers WHERE `code` = '".mysql_real_escape_string($code)."';";
			$res = $db->get($sql);
			return $res['id'];
		}
		return 0;
	}
	
	private function product_update_filter_id($product_id=0,$view_id=0){
		$db = Register::get('db');
		$sql = "UPDATE ".DB_PREFIX."products SET `filter_id`='".(int)$view_id."' WHERE `id`='".(int)$product_id."';";
		$db->post($sql);
		return $product_id;
	}
	
	
	/* ************************************************** */
	// FILTERS
	
	private function getFilterByName($name,$view_id=0){
		$db = Register::get('db');
		$sql = "SELECT id,view_id FROM ".DB_PREFIX."filters where name = '".mysql_real_escape_string($name)."' AND view_id = '".(int)$view_id."';";
		return $db->get($sql);
	}
	
	private function addFilter($name,$view_id=0){
		$db = Register::get('db');
		$filter = array();
		if ($name && $view_id) {
			$sql = "
				INSERT INTO ".DB_PREFIX."filters 
					(`view_id`,`name`,`is_active`) 
				VALUES 
					('".(int)$view_id."','".mysql_real_escape_string($name)."','1');";
			$db->post($sql);
			$filter['id'] = $db->lastInsertId();
			return $filter;
		}
		$filter['id'] = 0;
		return $filter; 
	}
	
	private function updateFiltersArr($row,$filter_id_static=0){
		if (max(array_keys($row)) > 18) {
			
			for ($i=19; $i <= max(array_keys($row)); $i++) { 
				$filter = $this->getFilterByName($row[$i],$filter_id_static);
				if(!$filter) {
					$filter = $this->addFilter($row[$i],$filter_id_static);
				}
				$this->filters_arr[$i] = array('id' => $filter['id'], 'name' => $row[$i]);
			}
			return $filter['view_id'];
		}
	}
	
	// FILTER VALUES
	private function updateFilterValues($row, $prod_id){
		
		if (max(array_keys($row)) > 18) {
			
			for ($i=19; $i <= max(array_keys($row)); $i++) { 
				if($row[$i] !=''){
					
					$filterValId = $this->getFilterValue($row[$i], $this->filters_arr[$i]['id']);
					
					if(!$filterValId){
						$filterValId = $this->addFilterValue($row[$i], $this->filters_arr[$i]['id']);
					}
					
					if(is_array($filterValId)) 
						$filterValId = $filterValId['id'];
					
					$res = $this->getValues2products($filterValId, $prod_id);
					
					if(!$res) 
						$this->addValues2products($filterValId, $prod_id);
				}
			}
		}
	}
	
	private function addFilterValue($name, $filter_id){
		if ($name && $filter_id) {
			
			$db = Register::get('db');
			$sql = "
				INSERT INTO ".DB_PREFIX."filters_values 
					(`name`,`filter_id`,`is_active`) 
				VALUES 
					('".mysql_real_escape_string($name)."','".(int)$filter_id."','1');";
				$db->post($sql);
			return $db->lastInsertId();
		}
		else
			return 0;
	}
	
	private function getFilterValue($name, $filter_id){
		$db = Register::get('db');
		$sql = "SELECT id FROM ".DB_PREFIX."filters_values where name = '".mysql_escape_string($name)."' and filter_id = '".(int)$filter_id."';";
		return $db->get($sql);
	}
	
	// VALUES2PRODUCTS
	private function addValues2products($value_id, $product_id){
		$db = Register::get('db');
		if ($value_id && $product_id) {
			
			$sql = "
				INSERT INTO ".DB_PREFIX."filters_values2products 
					(`value_id`,`product_id`) 
				VALUES 
					(".(int)$value_id.",".(int)$product_id.");";
			$db->post($sql);
			return $db->lastInsertId();	
		}
		else
			return 0;
	}
	
	private function getValues2products($value_id, $product_id){
		$db = Register::get('db');
		$sql = "
			select id from ".DB_PREFIX."filters_values2products 
			where value_id =".(int)$value_id." and product_id = ".(int)$product_id.";";
		return $db->get($sql);
	}
}

?>