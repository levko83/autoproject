<?php

class Documents_paramsController  extends CmsGenerator {
	
	public $layout = 'global';
	
	public function index(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела настройки параметров для документов',URL_NOW);
		
		$this->view->title = $this->dataModel->getListTitle();
		$this->view->dataModel = $this->dataModel;
		
		$groupsSettings = $this->model->select()->group('`document`')->fetchAll();
		$groups = array();
		foreach ($groupsSettings as $row)
			$groups[$row['document']] = $this->model->select()->where('`document` = ?',$row['document'])->fetchAll(); 
		$this->view->groups = $groups;
		
		$this->addBreadCrumb('Значения документов','/staffcp/'.$this->dataModel->getModelName());
	}
	
	public function beforeAction() {
		parent::beforeAction();
		$this->addBreadCrumb('Значения документов','/staffcp/'.$this->dataModel->getModelName());
	}
}