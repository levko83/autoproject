<?php

class BannernetworkController  extends CmsGenerator {
	
	public $layout = 'global';
	
	function index() {
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр баннерой сети',URL_NOW);
		$this->prepareIndexDataBanners();
		$this->prepareIndexDataPlaces();
		$this->stats();
	}
	
	public function prepareIndexDataBanners()
	{
		$this->dataModel = new CmsGeneratorConfig('bannernetwork');
		$this->model = new CmsGeneratorModel($this->dataModel);
		
		$this->view->title_banners = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();

		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles_banners = $fieldTitles;
		
		$this->view->addUrl_banners = '/staffcp/bannernetwork/add/';
		$this->view->addTitle_banners = $this->dataModel->getAddTitle();

		$listIds = $this->view->acl->getListIds($this->controller);
		
		$this->view->data_banners = $this->model->select()->fetchAll();
		
		$this->view->dataModel_banners = $this->dataModel;
		$this->view->indexField_banners = $this->dataModel->getIndexField();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
	}
	
	public function prepareIndexDataPlaces()
	{
		$this->dataModel = new CmsGeneratorConfig('bannernetwork_places');
		$this->model = new CmsGeneratorModel($this->dataModel);
		
		$this->view->title_places = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();

		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles_places = $fieldTitles;
		
		$this->view->addUrl_places = '/staffcp/bannernetwork_places/add/';
		$this->view->addTitle_places = $this->dataModel->getAddTitle();

		$listIds = $this->view->acl->getListIds($this->controller);
		
		$this->view->data_places = $this->model->select()->fetchAll();
		
		$this->view->dataModel_places = $this->dataModel;
		$this->view->indexField_places = $this->dataModel->getIndexField();
	}
	
	public function save()
	{
		
		$form = $this->request('form');
	
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
			
		$form = $this->trimA($form);
		
		/* generation alias */
		if (isset($form['code'])&&empty($form['code'])) {
			
			$form['code'] = strtolower($this->doTraslit($form['name']));
			$form['code'] = substr($form['code'],0,100);
		}
		
		if (isset($form['view_count_minus'])&&(empty($form['view_count_minus'])||$form['view_count_minus']==0)) {
			$form['view_count_minus'] = $form['view_count'];
		}
		
		if (empty($id))
		{
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление баннера',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование баннера id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	
	function stats() {
		$db = Register::get('db');
		
		$sql = "select bb.*,bp.name as place,bp.width,bp.height from ".DB_PREFIX."bannernetwork bb left join ".DB_PREFIX."bannernetwork__places bp on (bb.zone=bp.id) order by bb.dt DESC;";
		//var_dump($sql);
		$this->view->stats = $db->query($sql);
	}
	
	function beforeAction() {
		parent::beforeAction();
	}
	
	function beforeRender() {
		parent::beforeRender();
	}
}