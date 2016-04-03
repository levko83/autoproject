<?php

class Filters_valuesController  extends CmsGenerator {
	
	public function index() {
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр значения характеристик раздела магазин',URL_NOW);
		
		$this->prepareIndexData();
		
		$db = Register::get('db');
		
		$sql = "SELECT * FROM ".DB_PREFIX."filters_views ORDER BY name;";
		$this->view->filters_views = $db->query($sql);
		
		$sql = "SELECT * FROM ".DB_PREFIX."filters ORDER BY name;";
		$this->view->filters = $db->query($sql);
		
		$this->render('filters_values/list');
	}
	
	public function prepareIndexData(){
		
		$this->view->title = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();
		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles = $fieldTitles;
		$this->view->addUrl = '/staffcp/'.$this->modelName.'/add/?type_id='.(int)$type_id;
		$this->view->addTitle = $this->dataModel->getAddTitle();
		$listIds = $this->view->acl->getListIds($this->controller);
			
		$filter = $this->request("filter");
		if ($filter)
			$this->view->data = $this->model->select()->where("filter_id=?",(int)$filter)->fetchAll();
		else 
			$this->view->data = $this->model->select()->fetchAll();

		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
	}
}

?>