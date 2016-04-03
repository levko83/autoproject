<?php

class ToController  extends CmsGenerator {
	
	public function index() {
		$this->prepareIndexData();
		$this->render('to/catalog');
	}
	
	public function prepareIndexData(){
		
		$type_id = $this->request("type_id",0);
		if (!$type_id)
			$this->redirectUrl('/staffcp/to_cars/');
		
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
			
		$this->view->data = $this->model->select()->where("type_id=?",(int)$type_id)->fetchAll();

		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		
		$car = $this->getCar($type_id);
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - '.$this->dataModel->getListTitle().' '.$this->controller.'/'.$this->action,URL_NOW);
		
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
		$this->addBreadCrumb($car['CA'],'/staffcp/to_models/?car_id='.$car['CAR_ID']);
		$this->addBreadCrumb($car['MO'],'/staffcp/to_types/?model_id='.$car['MODEL_ID']);
		$this->addBreadCrumb($car['TY'],'/staffcp/to/?type_id='.$car['TYPE_ID']);
	}
	
	private function getCar($id){
		$db = Register::get('db');
		$sql = "SELECT 
					CAR.id CAR_ID,
					MODEL.id MODEL_ID,
					TYPE.id TYPE_ID,
					CAR.NAME CA,
					MODEL.NAME MO,
					TYPE.NAME TY
				FROM `".DB_PREFIX."to_types` TYPE
				LEFT JOIN `".DB_PREFIX."to_models` MODEL ON TYPE.model_id=MODEL.id
				LEFT JOIN `".DB_PREFIX."to_cars` CAR ON CAR.id=MODEL.car_id 
				WHERE TYPE.id='".(int)$id."';";
		return $db->get($sql);
	}
	
	public function save() {
		$form = $this->request('form');
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
		$form = $this->trimA($form);

		if (!$form['alias']){
			$form['alias']=strtolower($this->doTraslit($form['descr'].'_'.$form['article']).'_'.$form['id']);
		}
		
		if (empty($id)){
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление детали запчастей ТО',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование детали запчастей ТО id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'type_id='.(isset($form['type_id'])?$form['type_id']:''));
	}
	
	public function delete(){
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		
		$getCatById = ToModel::getCatById($id);
		
		if (!empty($id)){
			$this->model->delete(array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Удаление детали запчастей ТО id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'type_id='.$getCatById['type_id']);
	}
	
	public function delete_list(){
		$indexField = $this->dataModel->getIndexField();
		$ids = $this->request("delete_list",0);
		
		$getCatById = ToModel::getCatById($ids[0]);
		
		if (!empty($ids)) {
			foreach ($ids as $id) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
				}
			}
			Logs::addLog(Acl::getAuthedUserId(),'Удаление списка деталий запчастей ТО',URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),'type_id='.$getCatById['type_id']);
	}
}

?>