<?php

class ManufacturersController  extends CmsGenerator {
	
	
	public function index() {
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - список марок автомобилей',URL_NOW);
		
		$this->prepareIndexData();
		$this->render('manufacturers/list');
	}
	
	public function prepareIndexData()
	{
		$db = Register::get('db');
		
		$sql = "SELECT COUNT(*) CC FROM ".DB_PREFIX."manufacturers;";
		$res = $db->get($sql);
		$this->view->cc_all = $res['CC'];
		
		$this->view->title = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();

		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles = $fieldTitles;

		$this->view->addUrl = '/staffcp/'.$this->modelName.'/add/';
		$this->view->addTitle = $this->dataModel->getAddTitle();
		
		$listIds = $this->view->acl->getListIds($this->controller);
		
		$this->view->link_all = true;
		if (isset($_REQUEST['all'])){
			$this->view->link_all = false;
			$this->view->data = $this->model->select()->fetchAll();
		}
		else {
			$this->view->data = $this->model->select()->where("MY_ACTIVE=1")->fetchAll();	
		}
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
	}
}

?>