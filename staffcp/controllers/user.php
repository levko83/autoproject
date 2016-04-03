<?php

class UserController  extends CmsGenerator {

	public $layout = 'global';
	
	public function index(){
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - администраторы',URL_NOW);
		$this->prepareIndexData();
	}

	public function add(){
		$this->prepareAddData();
		$this->view->rights = $this->getRights();
	}

	public function edit(){
		$this->prepareEditData();
		$this->view->rights = $this->getRights($this->view->indexValue);
	}

	public function save(){
		
		$form = $this->request('form');
		$form = $this->trimA($form);
				
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
		
		if (empty($id)){
			
			$id = $this->model->insert($form);
			
			$sql = "SELECT id FROM ".DB_PREFIX."_user WHERE id=LAST_INSERT_ID();";
			$db = Register::get('db');
			$id = $db->get($sql);
			$id = $id['id'];
			
			Logs::addLog(Acl::getAuthedUserId(),'Добавление нового пользователя зоны администрирования',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование пользователя зоны администрирования id:'.$id,URL_NOW);
		}
		
		$rightsModel = new Orm(DB_PREFIX.'_user2rights','id');
		$rightsModel->delete('user_id = '.$id);
		
		$rights = $this->request('rights');
		
		if (!empty($rights)) {
			foreach ($rights as $rightId=>$right){
				$right['user_id'] = $id;
				$right['right_id'] = $rightId;
				$right['active'] = !empty($right['active'])?1:0;
				$right['add'] = !empty($right['add'])?1:0;
				$right['list'] = empty($right['list'])?'all':$right['list'];
				$right['edit'] = empty($right['edit'])?'all':$right['edit'];
				$rightsModel->insert($right);
			}
		}
		
		$this->redirect('index',$this->dataModel->getModelName());
	}

	public function delete(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Удаление пользователя зоны администрирования',URL_NOW);
		
		if (Acl::getAuthedUserId() != 1)
			parent::delete();
		
		$this->redirect('index',$this->dataModel->getModelName());
	}

	public function beforeAction(){
		parent::beforeAction();
	}
	
	private function getRights($userId = 0){
		$acl = new Acl();
		return $acl->getRights($userId);
	}
	
	public function items(){
		
		$this->layout = 'ajax';
		
		$table = $this->request('table');
		$index = $this->request('index');
		$name = $this->request('name');
		$cond = $this->request('cond');
		
		$sql = "SELECT * FROM ".$table;
		if (!empty($cond))
			$sql .= ' WHERE '.$cond;
			
		$db = Register::get('db');
		$data = $db->query($sql);
		
		$items = array();
		foreach ($data as $row)
			$items[] = array('id'=>$row[$index], 'name' => $row[$name]);
			
		$this->view->items = $items; 
	}
}