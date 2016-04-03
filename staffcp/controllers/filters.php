<?php

class FiltersController  extends CmsGenerator {
	
	public function index() {
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела характеристик раздела магазин',URL_NOW);
		
		$this->prepareIndexData();
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."filters_views ORDER BY name;";
		$this->view->filters_views = $db->query($sql);
		
		$this->render('filters/list');
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
			
		$filter_view_id = $this->request('filter_view_id');
		if ($filter_view_id)
			$this->view->data = $this->model->select()->where("view_id=?",(int)$filter_view_id)->fetchAll();
		else
			$this->view->data = $this->model->select()->fetchAll();

		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
	}
}

?>