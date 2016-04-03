<?php

class ProductsController  extends CmsGenerator {
	
	var $nexts = array();
	
	public function save_articles(){
		$db = Register::get('db');
		$parent = $this->request("parent",0);
		$articles = $this->request("articles",false);
		if (isset($articles) && count($articles)>0) {
			$sql = "";
			foreach ($articles as $product_id=>$article){
				$sql = "
					UPDATE ".DB_PREFIX."products 
					SET article = '".mysql_real_escape_string($article)."' 
					WHERE id = '".(int)$product_id."';";
				$db->post($sql);
			}
			Logs::addLog(Acl::getAuthedUserId(),'Установка артикулов списка товаров раздела магазин',URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$parent);
	}
	
	public function save_prices(){
		$db = Register::get('db');
		$parent = $this->request("parent",0);
		$prices = $this->request("prices",false);
		if (isset($prices) && count($prices)>0) {
			$sql = "";
			foreach ($prices as $product_id=>$price){
				$sql = "
					UPDATE ".DB_PREFIX."products 
					SET price = '".mysql_real_escape_string($price)."' 
					WHERE id = '".(int)$product_id."';";
				$db->post($sql);
			}
			Logs::addLog(Acl::getAuthedUserId(),'Установка цен списка товаров раздела магазин',URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$parent);
	}
	
	/**
	 * Index action
	 */
	function index() {
		
		$action = $this->request("action",false);
		if ($action){
			$ids = $this->request("delete_list",0);
			$parent = $this->request("parent",0);
			switch ($action){
				case 'delete_list': $this->delete_list(); break;
				case 'save_articles': $this->save_articles(); break;
				case 'save_prices': $this->save_prices(); break;
			}
		}
		
		$this->prepareIndexData();
		$this->render('products/list');
	}
	
	function ajax_nodes_products(){
		$this->layout = 'ajax';
		$id = (int)$this->request("id",0);
		echo $this->getProducts($id);
		exit();
	}
	function ajax_nodes(){
		$this->layout = 'ajax';
		$id = (int)$this->request("id",0);
		echo $this->getCatalog($id);
		exit();
	}
	private function getCatalog($id=0){
		$db = Register::get('db');
		$sql = "SELECT C1.*,(SELECT COUNT(*) FROM ".DB_PREFIX."cat C2 WHERE C1.id = C2.parent) CC FROM ".DB_PREFIX."cat C1 WHERE C1.parent = '".(int)$id."';";
		$res = $db->query($sql);
		
		$this->getLevelsBack($id);
		$html = '';
		if (isset($this->bread_crumbs) && $this->bread_crumbs){
			foreach ($this->bread_crumbs as $bread){
				$html .= '<a href="" onclick="get_next_catalog('.$bread['id'].');return false;">'.$bread['name'].'</a> &raquo; ';
			}
			$html .= '<a href="" onclick="get_next_catalog(0);return false;">Каталог</a>';
		}
		
		$html .= '<ul>';
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$html .= '<li><a href="#" onclick="get_next_catalog('.$dd['id'].');return false;">'.$dd['name'].($dd['CC']?' ('.$dd['CC'].')':'').'</a></li>';
			}
		}
		$html .= '</ul>';
		
		return $html;
	}
	private function getProducts($id=0){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."products WHERE fk = '".(int)$id."';";
		$res = $db->query($sql);
		$html .= '<ul>';
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$html .= '<li>ID: '.$dd['id'].' | '.$dd['name'].' | <a href="#" onclick="$(this).remove();appendnode(\''.$dd['id'].'\',\''.str_replace('"','', ($dd['name'])).'\');return false;">добавить</a></li>';
			}
		}
		$html .= '</ul>';
		
		return $html;
	}
	
	public function prepareIndexData() {
		$parent = $this->request("parent",0);
		$this->view->parent = $parent;
		
		$this->view->title = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();
		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles = $fieldTitles;
		$this->view->addUrl = '/staffcp/'.$this->modelName.'/add/?parent='.$parent;
		$this->view->addTitle = $this->dataModel->getAddTitle();
		$listIds = $this->view->acl->getListIds($this->controller);
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр списка товаров категории id:'.$parent,URL_NOW);
		
		$set_index = $this->request("set_index",false);
		if ($set_index) {
			$this->view->data = $this->model->select()->where("set_index=1")->fetchAll();
		} 
		elseif ($parent) {
			$this->view->data = $this->model->select()->where("fk=?",(int)$parent)->fetchAll();
		}
		else {
				/* *** */
				$search = $this->request("search",false);
				$this->view->search = $search;
				$search_artnr = $this->request("search_artnr",false);
				$this->view->search_artnr = $search_artnr;
				
				$from = $this->request("from",false);
				$this->view->from = $from;
				$to = $this->request("to",false);
				$this->view->to = $to;
				$bez = $this->request("bez",false);
				$this->view->bez = $bez;
				
				if (trim($search)) {
					
					$this->view->tableajax = true;
					$db = Register::get('db');
					// $sql = "SHOW COLUMNS FROM `".$this->dataModel->getTable()."`;";
					// $colums = $db->query($sql);
					$dataColums = array();
					// if (isset($colums) && count($colums)>0) {
						// foreach ($colums as $dd){
							// $dataColums []= '`'.$dd['Field'].'`';
						// }
						$dataColums = array("`id`", "`name_ru`",  "`art_nr`", );
						// $searchString = join(" LIKE '%".mysql_real_escape_string(trim($search))."%' OR ", $dataColums).
						// " LIKE '%".mysql_real_escape_string(trim($search))."%'";
						// $searchString = " id LIKE '%".mysql_real_escape_string(trim($search))."%' OR art_nr LIKE '%".mysql_real_escape_string(trim($search))."%' OR name_ru LIKE '%".mysql_real_escape_string(trim($search))."%' ";
						// $searchString = "`id` LIKE '%".mysql_real_escape_string(trim($search))."%' OR `art_nr` LIKE '%".mysql_real_escape_string(trim($search))."%' OR `supplier_name` LIKE '%".mysql_real_escape_string(trim($search))."%'";
						// $searchString = "`id` LIKE '%".mysql_real_escape_string(trim($search))."%'";
						$searchString = "`id` = '".mysql_real_escape_string(trim($search))."'";
						$this->view->data = 
						$this->model->select()->where($searchString)->fetchAll();
					// }
				} elseif (trim($search_artnr)) {
					
					$this->view->tableajax = true;
						$searchString = "`art_nr` LIKE '%".mysql_real_escape_string(trim($search_artnr))."%'";
						$this->view->data = 
						$this->model->select()->where($searchString)->fetchAll();
					
				} elseif (trim($from) && trim($to)) {
					$this->view->tableajax = true;
					$searchString = " id >= '".mysql_real_escape_string(trim($from))."' and id <= '".mysql_real_escape_string(trim($to))."' ";
					$this->view->data = 
					$this->model->select()->where($searchString)->fetchAll();
				} else {
					/* if the rows more then 100 items, switch on ajax search template, because a lot of items stopping view page faster */
					$per_page = 100;
					$page = (int)$this->request("page",1);
					$this->view->currentPage = $page;
						
					if (trim($bez)) $numRows = $this->model->select()->fields("COUNT(*) cc")->where("`price` > '0'")->fetchOne();
					else $numRows = $this->model->select()->fields("COUNT(*) cc")->fetchOne();
					$Rows = isset($numRows['cc'])?$numRows['cc']:0;
					$this->view->totalPage = (int)(($Rows - 1) / $per_page) + 1;
					$this->view->total = $Rows;
						
					if ($Rows > $per_page) {
					
						$this->view->tableajax = true;
						$dataModelPage = ($page - 1)*$per_page;
						
						if (trim($bez)) $this->view->data = $this->model->select()->where("`price` > '0'")->limit($dataModelPage,$per_page)->order("id ASC")->fetchAll();
						else $this->view->data = $this->model->select()->limit($dataModelPage,$per_page)->order("id ASC")->fetchAll();
					
					}
					else {
						
						if (trim($bez)) $this->view->data = $this->model->select()->where("`price` > '0'")->fetchAll();
						else $this->view->data = $this->model->select()->fetchAll();
					}
				}
				/* *** */
		}
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();

		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
		
		/* bread crumbs for catalog */
		$this->getLevelsBack($parent);
		$bread_crumbs = @array_reverse($this->bread_crumbs);
		if (count($bread_crumbs)>0){
			foreach ($bread_crumbs as $bb){
				$this->addBreadCrumb($bb['name'],'/staffcp/cat/?parent='.$bb['id']);
			}
		}
	}
		
	/**
     * Add action
     */
	public function add() {
		$this->getFiltersViews();
		$this->prepareAddData();
		$this->render('products/add');
	}
	
	/**
     * Edit action
     */
	public function edit() {
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$id = mysql_real_escape_string($id);
		
		// $this->getFiltersViews();
		
		// $db = Register::get('db');
	/*	$sql = "SELECT * FROM ".DB_PREFIX."products WHERE id='".(int)$id."';";
		$product = $db->get($sql);
		$this->view->filter_view_selected = $product['filter_id'];
		$this->ajax($product['filter_id'],false,$id);*/
		
		// $this->view->listPriceImporters = $this->listPriceImporters($id);
		// $this->view->p2p_catalog = $this->getCatalog();
		// $this->view->pnodes = $this->getPnodes($id);
		
		$this->prepareEditData();
		$this->render('products/edit');
	}
	
	public function edit_importers() {
		$id = $this->request("id",false);
		$this->layout = "ajax";
		$this->view->listPriceImporters = $this->listPriceImporters($id);
		$this->render("products/tab-importers");
	}
	
	/**
     * Save action
     */
	public function save($params='') {
		$db = Register::get("db");
		$form = $this->request('form');
		
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
			
		$form = $this->trimA($form);
		
		$value_id = $this->request("value_id",array());
		$value = $this->request("value",array());
		$p2i_key = $this->request("p2i_key",array());
		$p2i_key_brand = $this->request("p2i_key_brand",array());
		$p2i_remote_server = $this->request("p2i_remote_server",array());
		
		if (empty($id)) {
			$this->model->insert($form);
			$product_id = $db->lastInsertId();
			$this->save_filters_values($product_id,$value_id,$value);
			$this->p2i_update($product_id,$p2i_key,$p2i_key_brand,$p2i_remote_server);
			$this->savePnodes($product_id);
			
			Logs::addLog(Acl::getAuthedUserId(),'Добавление товара id:'.$product_id.' раздела магазин',URL_NOW);
			
		} else {
			$this->model->update($form,array($indexField => $id));
			$this->save_filters_values($id,$value_id,$value);
			$this->p2i_update($id,$p2i_key,$p2i_key_brand,$p2i_remote_server);
			$this->savePnodes($id);
			
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование товара id:'.$id.' раздела магазин',URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$form['fk']);
	}
	
	public function set_isset(){
		$translates = Register::get('translates');
		$this->layout = "ajax";
		$id = $this->request("id");
		$db = Register::get('db');
		$sql = "select * from ".DB_PREFIX."products where id='".(int)$id."';";
		$res = $db->get($sql);
		if ($res['set_isset']==1) {
			$this->view->message = $translates['admin.products.status1'];
			$sql = "update ".DB_PREFIX."products set set_isset='0' where id='".(int)$id."'";
		}
		else {
			$this->view->message = $translates['admin.products.status2'];
			$sql = "update ".DB_PREFIX."products set set_isset='1' where id='".(int)$id."'";
		}
		Logs::addLog(Acl::getAuthedUserId(),'Отключение/Включение товара id:'.$id.' раздела магазин',URL_NOW);
		$db->post($sql);
	}
	
	public function set_price(){
		$translates = Register::get('translates');
		$this->layout = "ajax";
		$id = $this->request("id");
		$price = $this->request("price");
		
		$db = Register::get('db');
		$sql = "update ".DB_PREFIX."products set price='".$price."' where id='".(int)$id."';";
		$db->query($sql);
		$this->view->message = $translates['admin.products.update'];
		
		Logs::addLog(Acl::getAuthedUserId(),'Установка цены товара id:'.$id.' раздела магазин',URL_NOW);
	}
	
	/**
     * Delete action
     */
	public function delete(){
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$parent = $this->request("parent",0);
		if (!empty($id)){
			$this->model->delete(array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Удаление товара id:'.$id.' раздела магазин',URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$parent);
	}
	
	/**
     * Delete list action
     */
	public function delete_list(){
		$indexField = $this->dataModel->getIndexField();
		$ids = $this->request("delete_list",0);
		$parent = $this->request("parent",0);
		if (!empty($ids)) {
			foreach ($ids as $id) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
				}
			}
			Logs::addLog(Acl::getAuthedUserId(),'Удаление списка товаров раздела магазин',URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$parent);
	}
	public function delete_all(){
		$indexField = $this->dataModel->getIndexField();
		// $ids = $this->request("delete_list",0);
		// $parent = $this->request("parent",0);
		// if (!empty($ids)) {
			/*for($id = 0; $id < 20000; $id++) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
				}
			}*/
			
			// Logs::addLog(Acl::getAuthedUserId(),'Удаление списка товаров раздела магазин',URL_NOW);
		// }
		// $this->redirect('index',$this->dataModel->getModelName(),'parent='.$parent);
	}
	
	public function getLevelsBack($id) {
		$ids = $this->getCatName($id);
		if ($ids['name']){
			$this->tree []= (int)$ids['id'];
			$this->bread_crumbs []= array("id"=>(int)$ids['id'],"name"=>$ids['name']);
			if ($ids['parent']) {
				$this->getLevelsBack($ids['parent']);
			}
		}
	}
	
	public function getCatName($id) {
		$db = Register::get('db');
		$sql = "SELECT id,name,parent FROM ".DB_PREFIX."cat WHERE id='".(int)$id."';";
		return $db->get($sql);
	}
	
	private function savePnodes($id=0){
		$db = Register::get('db');
		$db->post("DELETE FROM ".DB_PREFIX."products2products WHERE product_id='".(int)$id."';");
		
		$pnodes = $this->request("pnodes",array());
		if (isset($pnodes) && count($pnodes)>0){
			$q = "";
			$i=0;foreach ($pnodes as $id_node){$i++;
				$q .= "('".(int)$id."','".(int)$id_node."')";
				if ($i != count($pnodes))
					$q .= ",";
			}
			$sql = "INSERT INTO ".DB_PREFIX."products2products (`product_id`,`product_id_node`) VALUES ".($q).";";
			$db->post($sql);
		}
	}
	private function getPnodes($id){
		$db = Register::get('db');
		$sql = "SELECT 
					p.id,p.name 
				FROM ".DB_PREFIX."products2products p2p 
				JOIN ".DB_PREFIX."products p ON p.id=p2p.product_id_node 
				WHERE p2p.product_id = '".(int)$id."';";
		return $db->query($sql);
	}
	
	/* ************************************************ */
	
	function listPriceImporters($product_id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."importers;";
		$res = $db->query($sql);
		$formatted = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$fetch = $this->getByPosition($dd['id'],$product_id);
				if ($fetch)
					$formatted []= array_merge($dd,$fetch);
				else 
					$formatted []= array_merge($dd);
			}
		}
		return $formatted;
	}
	
	function getByPosition($importer_id,$product_id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."products2importers WHERE p2i_importer_id='".(int)$importer_id."' AND p2i_product_id='".(int)$product_id."';";
		return $db->get($sql);
	}
	
	function p2i_update($product_id,$data,$data_brand,$remote_server){
		
		//echo('<pre>');
		//var_dump($remote_server);
		//echo('</pre>');
		
		$db = Register::get('db');
		if (isset($data) && count($data)>0){
			$db->post("DELETE FROM ".DB_PREFIX."products2importers WHERE p2i_product_id='".mysql_real_escape_string($product_id)."';");
			foreach ($data as $key=>$val){
				if ($val){
					$p2i_key_brand = $data_brand[$key];
					$p2i_remote_server = (isset($remote_server[$key]) && $remote_server[$key])?1:0;
					
					//var_dump($remote_server[$key],$p2i_remote_server);
					
					$db->post("INSERT INTO ".DB_PREFIX."products2importers (`p2i_product_id`,`p2i_importer_id`,`p2i_key`,`p2i_key_brand`,`p2i_remote_server`) VALUES ('".mysql_real_escape_string($product_id)."','".(int)$key."','".mysql_real_escape_string($val)."','".mysql_real_escape_string($p2i_key_brand)."','".mysql_real_escape_string($p2i_remote_server)."');");
				}
			}
		}
		//exit();
	}
	
	/* ************************************************ */
	public function getFiltersViews() {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."filters_views;";
		$this->view->filters_views = $db->query($sql);
	}
	
	function ajax($id=0,$ajax=true,$product_id=0) {
		
		if ($ajax)
			$this->layout = "ajax";
		
		$ajax_id = $this->request("ajax_id",$id);
		
		if ($ajax_id){

			$db = Register::get('db');
			
			$filters = array();
			$q = $db->query("SELECT * FROM ".DB_PREFIX."filters_values2products WHERE product_id='".$product_id."';");
			if (isset($q) && count($q)>0){
				$notID = array(); foreach ($q as $dd){
					$sql = "
						SELECT 
							f.*
						FROM ".DB_PREFIX."filters f 
						JOIN ".DB_PREFIX."filters_values fv ON fv.filter_id=f.id
						WHERE 
							f.view_id='".(int)$ajax_id."' AND fv.id='".(int)$dd['value_id']."'
						ORDER BY 
							f.sort,f.name;";
					$filter = $db->get($sql);
					if ($filter['id'])
					$notID []= $filter['id'];
					
					$filters []= array_merge((array)$filter,array('selvalue'=>$dd['value_id']));
				}
				
				if (isset($notID) && count($notID)>0){
					$sql = "
							SELECT 
								f.*
							FROM ".DB_PREFIX."filters f 
							WHERE 
								f.view_id='".(int)$ajax_id."' AND f.id NOT IN (".join(",",array_unique($notID)).")
							ORDER BY 
								f.sort,f.name;";
					$whichNotInGroup = $db->query($sql);
					$filters = array_merge((array)$filters,(array)$whichNotInGroup);
				}
			}
			else {
				$sql = "
						SELECT 
							f.*
						FROM ".DB_PREFIX."filters f 
						WHERE 
							f.view_id='".(int)$ajax_id."'
						ORDER BY 
							f.sort,f.name;";
				$filters = $db->query($sql);
			}
			
			$this->view->filters = $filters;
		}			
	}
	
	function save_filters_values($product_id,$values_id,$values) {
		$db = Register::get('db');
		if (isset($values_id) && count($values_id)>0){
			$db->query("DELETE FROM ".DB_PREFIX."filters_values2products WHERE product_id='".(int)$product_id."';");
			foreach ($values_id as $key=>$values_array){
				foreach ($values_array as $id=>$value){
					if (isset($values[$key][$id]) && !empty($values[$key][$id])) {
						$value_id = $this->addNewValue($key,$values[$key][$id]);
						if ($value_id)
						$this->addNode($product_id,$value_id);
					}
					else {
						$this->addNode($product_id,$value);
					}
				}
				
			}
		}
	}
	function addNode($product_id,$value_id) {
		$db = Register::get('db');
		if ($value_id) {
			$sql = "INSERT INTO ".DB_PREFIX."filters_values2products (`value_id`,`product_id`) VALUES ('".(int)$value_id."','".(int)$product_id."');";
			$db->query($sql);
		}
	}
	function addNewValue($filter_id,$value) {
		if ($filter_id && $value){
			$db = Register::get('db');
			$sql = "INSERT INTO ".DB_PREFIX."filters_values (`filter_id`,`name`,`is_active`) VALUES ('".addslashes($filter_id)."','".addslashes($value)."','1');";
			$db->post($sql);
			return $db->lastInsertId();
		}
		else 
			return false;
	}
	
	/* ************************************************ */
	/**
	 * Next Path
	 */
	private function nextPath($id) {
		$this->nexts []= $id;
		$count = $this->countCats($id);
		if ($count>0) {
			$db = Register::get('db');
			$sql = "SELECT * FROM `".DB_PREFIX."cat` WHERE `parent`='".(int)$id."';";
			$res = $db->query($sql);
			foreach ($res as $dd) {
				$this->nexts []= $dd['id'];
				$this->nextPath($dd['id']);
			}
		}		
	}
	/**
	 * Count Cats
	 */
	private function countCats($id){
		$db = Register::get('db');
		$sql = "SELECT COUNT(*) AS cc FROM ".DB_PREFIX."cat WHERE parent='".(int)$id."';";
		$res = $db->get($sql);
		return $res['cc'];
	}
	function csv() {
		$db = Register::get('db');
		
		$id = $this->request("id",0);
		if (!$id)
			$this->redirectUrl('/staffcp/cat/');
		
		Logs::addLog(Acl::getAuthedUserId(),'Выгрузка товаров в формат csv категории id:'.$id,URL_NOW);
	
		$this->nextPath($id);
		
		if (count($this->nexts)>0) {
			$sql = "SELECT id,name,price FROM ".DB_PREFIX."products WHERE `fk` IN (".join(",",$this->nexts).") ORDER BY `fk`,`name`;";
			$sql = mysql_query($sql);
			$file = '../extensions/export/products.csv';
			$f = fopen($file, 'w');
		    while($data = mysql_fetch_row($sql)){
				fputcsv($f, $data, ';');
			}
			fclose($f);
			header("Content-type: application/csv");
			header("Content-Disposition: attachment;Filename=products.csv");
// 			header('Location: /'.$file);
			readfile($file);
			unlink($file);
			exit();
		}
		else {
			$this->redirectUrl('/staffcp/cat/');
		}
	}
	public function updatecsv(){
		$db = Register::get('db');
		
		$parent = $this->request("parent");
		$place = $this->request("place");
		
		Logs::addLog(Acl::getAuthedUserId(),'Обновление цен товаров в формат csv категории id:'.$parent,URL_NOW);
		
		if (isset($_FILES['csv_price']['tmp_name'])) {
			$ext = $this->getExtension($_FILES['csv_price']['name']);
			if ($ext != 'csv')
				$this->redirectUrl('/staffcp/'.$place.'/?parent='.$parent.'&st=file_error');
			
			require_once('ycsvparser.class.php');
			
			$ycsv = new ycsvParser($_FILES['csv_price']['tmp_name'],false);	
			
			while ($record = $ycsv->getRecord()) {
				$res = $ycsv->parseRecord($record);
				
				$ID = trim($res[0]);
				$NAME = addslashes(trim($res[1]));
				$PRICE = str_replace(",",".",$res[2]);
				
				$db->post("UPDATE `".DB_PREFIX."products` SET `price`='".addslashes($PRICE)."' WHERE `id`='".(int)$ID."';");
			}
			$this->redirectUrl('/staffcp/'.$place.'/?parent='.$parent.'&st=true');
		}
		else 
			$this->redirectUrl('/staffcp/'.$place.'/?parent='.$parent.'&st=false');
	}
	private function getExtension($filename) {
		return substr(strrchr($filename, '.'), 1);
	}
	function search(){
		
		
		$id = $this->request("product_id",0);
		$db = Register::get('db');
		$sql = "SELECT id,fk FROM ".DB_PREFIX."products WHERE id = '".(int)$id."';";
		$res = $db->get($sql);
		
		Logs::addLog(Acl::getAuthedUserId(),'Поиск товара в разделе каталог магазина по id:'.$id,URL_NOW);
		
		if ($res){
			$this->redirectUrl("/staffcp/products/edit/?parent=".$res['fk']."&id=".$res['id']);
			exit();
		}
		else { 
			$this->redirectUrl("/staffcp/cat/");
			exit();
		}
	}
}
?>