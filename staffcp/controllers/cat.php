<?php

class CatController  extends CmsGenerator {
	
	static $bread_crumbs = array();
	var $nexts = array();
	
	function index() {
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела магазин - каталог',URL_NOW);
		$this->prepareIndexData();
		$this->render('cat/list');
	}
	
	public function prepareIndexData(){
		
		$parent = $this->request("parent",0);
		$this->view->parent = $parent;
		
		$this->view->title = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();

		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles = $fieldTitles;

		$this->view->addUrl = '/staffcp/'.$this->modelName.'/add/';
		$this->view->addTitle = $this->dataModel->getAddTitle();
		
		$this->view->data = $this->model->select()->where("parent=? AND is_body_module IN (0,".INSTALL_BODY_MODULE.")",(int)$parent)->fetchAll();
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();

		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
		/* bread crumbs for catalog */
		$this->getLevelsBack($parent);
		$bread_crumbs = @array_reverse($this->bread_crumbs);
		
		if (count($bread_crumbs)>0)
		{
			foreach ($bread_crumbs as $bb){
				$this->addBreadCrumb($bb['name'],'/staffcp/'.$this->dataModel->getModelName().'/?parent='.$bb['id']);
			}
		}
	}
	
	public function add()
	{
		$this->prepareAddData();
		$this->render('cat/add');
	}

	public function prepareAddData()
	{
		$parent = $this->request("parent",0);
		
		$this->view->tabs = $this->dataModel->getAddTabs();
		$this->view->tabFields = array();
		$tabFields = array();
		foreach ($this->view->tabs as $tabName)
		{
			$tabFields[$tabName] = $this->dataModel->getAddTabFields($tabName);
		}
		$this->view->tabFields = $tabFields;
		$this->view->dataModel = $this->dataModel;

		$this->view->listTitle = $this->dataModel->getListTitle();
		$this->view->listUrl = '/staffcp/'.$this->dataModel->getModelName().'/';
		$this->view->title = $this->dataModel->getAddTitle();
		
		if ($parent>0) $this->view->parent = $this->getCatName($parent);

		$this->addBreadCrumb($this->dataModel->getAddTitle(),'#');
		/* bread crumbs for catalog */
		$this->getLevelsBack($parent);
		$bread_crumbs = @array_reverse($this->bread_crumbs);
		
		if (count($bread_crumbs)>0)
		{
			foreach ($bread_crumbs as $bb){
				$this->addBreadCrumb($bb['name'],'/staffcp/'.$this->dataModel->getModelName())."?parent=".$bb['id'];
			}
		}
	}
	
	public function edit()
	{
		$this->prepareEditData();
		$this->render('cat/edit');
	}

	public function prepareEditData()
	{
		$parent = $this->request("id",0);
		
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$id = mysql_real_escape_string($id);
		$data = $this->model->select()->where($indexField." = '$id'")->fetchOne();
	
		if (empty($data['id']))
			$this->error404();
		$this->dataModel->setValues($data);
		$this->view->tabs = $this->dataModel->getEditTabs();
		$this->view->tabFields = array();
		$tabFields = array();
		foreach ($this->view->tabs as $tabName)
		{
			$tabFields[$tabName] = $this->dataModel->getEditTabFields($tabName);
		}
		$this->view->tabFields = $tabFields;
		$this->view->dataModel = $this->dataModel;

		$this->view->listTitle = $this->dataModel->getListTitle();
		$this->view->listUrl = '/staffcp/'.$this->dataModel->getModelName().'/';
		$this->view->title = $this->dataModel->getEditTitle();

		$this->view->indexField = $indexField;
		$this->view->indexValue = $id;

		$this->addBreadCrumb($this->dataModel->getEditTitle(),'#');
		/* bread crumbs for catalog */
		$this->getLevelsBack($parent);
		$bread_crumbs = @array_reverse($this->bread_crumbs);
		
		if (count($bread_crumbs)>0)
		{
			foreach ($bread_crumbs as $bb){
				$this->addBreadCrumb($bb['name'],'/staffcp/'.$this->dataModel->getModelName())."?parent=".$bb['id'];
			}
		}
	}
	
	/**
     * Save action
     */
	public function save() {
		$form = $this->request('form');
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
		$form = $this->trimA($form);
		
		if (isset($form['id']) && $form['id']){
			if ($form['id'] == $form['parent']){
				$form['parent'] = 0;
			}
		}
		
		if (empty($id)) {
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Создание категории раздела магазин',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование категории раздела магазин id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$form['parent']);
	}
	
	/**
     * Delete action
     */
	public function delete() {
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$parent = $this->request("parent",0);
		if (!empty($id))
		{
			$this->model->delete(array($indexField => $id));
			
			/* DEL CATS */
			$this->nextPath($id);
			$nexts = $this->nexts;
			$nexts = array_unique($nexts);
			$db = Register::get('db');
			$sql = "DELETE FROM `".DB_PREFIX."cat` WHERE `id` IN (".join(",",$nexts).");";
			$db->post($sql);
			
			/* DEL PRODUCTS */
			$sql = "DELETE FROM `".DB_PREFIX."products` WHERE `fk` IN (".join(",",$nexts).");";
			$db->post($sql);
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Удаление категории раздела магазин id:'.$id,URL_NOW);
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$parent);
	}
	
	/**
     * Delete list action
     */
	public function delete_list()
	{
		$indexField = $this->dataModel->getIndexField();
		$ids = $this->request("delete_list",0);
		$parent = $this->request("delete_parent",0);
		if (!empty($ids)) {
			foreach ($ids as $id) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
					
					/* DEL CATS */
					$this->nextPath($id);
					$nexts = $this->nexts;
					$nexts = array_unique($nexts);
					$db = Register::get('db');
					$sql = "DELETE FROM `".DB_PREFIX."cat` WHERE `id` IN (".join(",",$nexts).");";
					$db->post($sql);
					
					/* DEL PRODUCTS */
					$sql = "DELETE FROM `".DB_PREFIX."products` WHERE `fk` IN (".join(",",$nexts).");";
					$db->post($sql);
				}
			}	
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Удаление списка категорий раздела магазин id:'.join(",", $ids),URL_NOW);
		$this->redirect('index',$this->dataModel->getModelName(),'parent='.$parent);
	}
	
	/**
	 * Next Path
	 */
	private function nextPath($id) {
		$this->nexts []= $id;
		$count = CatController::countCats($id);
		if ($count>0) {
			$db = Register::get('db');
			$sql = "SELECT * FROM `".DB_PREFIX."cat` WHERE `parent`='".(int)$id."' AND is_body_module IN (0,".INSTALL_BODY_MODULE.");";
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
	public static function countCats($id){
		$db = Register::get('db');
		$sql = "select count(*) as cc from ".DB_PREFIX."cat where parent='".(int)$id."';";
		$res = $db->get($sql);
		return $res['cc'];
	}
	
	/**
	 * Counts Products
	 */
	public static function countProducts($id){
		$db = Register::get('db');
		$sql = "select count(*) as cc from ".DB_PREFIX."products where fk='".(int)$id."';";
		$res = $db->get($sql);
		return $res['cc'];
	}
	
	/**
	 * Bread crumbs
	 */
	public function getLevelsBack($id) {
		$ids = $this->getCatName($id);
		if (!empty($ids['name'])){
			$this->tree []= (int)$ids['id'];
			$this->bread_crumbs []= array("id"=>(int)$ids['id'],"name"=>$ids['name']);
			if (!empty($ids['parent'])) {
				CatController::getLevelsBack($ids['parent']);
			}
		}
	}
	
	public function getCatName($id){
		$db = Register::get('db');
		$sql = "select id,name,parent from ".DB_PREFIX."cat where id='".(int)$id."';";
		return $db->get($sql);
	}
	
	public function beforeAction(){
		parent::beforeAction();
	}
	
	public function beforeRender(){
		parent::beforeRender();
	}
	
	
	public function export(){
	
		$new = array();
		$fk = $_GET['id'];
		
		Logs::addLog(Acl::getAuthedUserId(),'Экспорт товаров категории раздела магазин id:'.$fk,URL_NOW);
		
		$db = Register::get('db');
		
		$sql = "select id, fk, name, brief, content, img1, img2, img3, price, sort, null as zdf, null as wdsd, null as asd, kwords, title, descr, article, url from ".DB_PREFIX."products where fk =".$fk.";";
		$res = $db->query($sql);		
		
		$sql2 = "select f.name, f.id  from ".DB_PREFIX."products p left join ".DB_PREFIX."filters f on(f.id = p.filter_id) where fk =".$fk." group by filter_id;";
		$arr_filters = $db->query($sql2);
		unset($arr_filters[0]);
	
		require_once '../xreaders/readers/PHPExcel.php';
		$phpexcel = new PHPExcel();
		$page = $phpexcel->setActiveSheetIndex(0);
	
		$arr_name = array(
			'ID Категория товара',
			'Название товара',
			'Краткое описание',
			'Полное описание',
			'Изображение 1 (название файла)',
			'Изображение 2 (название файла)',
			'Изображение 3 (название файла)',
			'Цена',
			'Сортировка',
			'Код поставщика: Привязка товара к прайсу цен',
			'Артикул привязки: Привязка товара к прайсу цен',
			'Бренд привязки: Привязка товара к прайсу цен',
			'SEO: Заголовок',
			'SEO: Ключевые слова',
			'SEO: Описание',
			'Артикул для поиска по артикулу',
			'Адрес редиректа'
		);
	
		if (isset($arr_filters) && count($arr_filters)>0){
			foreach ($arr_filters as $filt) {
				$arr_name[] = $filt['name'];
			}
		}
		$page->fromArray($arr_name, NULL, 'A1');
	
		$i = 2;
		if (isset($res) && count($res)>0){
			foreach ($res as $item) {
				$new = array();
				foreach ($arr_filters as $filt) {
					$sql = "select v.name from ".DB_PREFIX."filters_values v join ".DB_PREFIX."filters_values2products vp on (vp.value_id = v.id and vp.product_id = ".$item['id'].") where v.filter_id = ".$filt['id'].";";	
					$reslt = $db->get($sql);
				$new[] = $reslt['name'];
			}
			unset($item['id']);
			
			$item = array_merge($item, $new);
			$page->fromArray($item, NULL, 'A'.$i);
			$i++;
			}
		}
		$page->setTitle("Export Catalog");
		$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$objWriter->save("../cache/export.xlsx");
		header('location: /cache/export.xlsx');
		exit;
	}
}
?>