<?php

class SettingsController  extends CmsGenerator {
	
	public $layout = 'global';
	
	public function index(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - настройки',URL_NOW);
		
		$translates = Register::get('translates');
		$this->view->title = $this->dataModel->getListTitle();
		$this->view->dataModel = $this->dataModel;
		$groupsSettings = $this->model->select()->group('`group`')->fetchAll();
		$groups = array();
		foreach ($groupsSettings as $row)
			$groups[$row['group']] = $this->model->select()->where('`group` = ?',$row['group'])->fetchAll(); 
		$this->view->groups = $groups;
		$this->addBreadCrumb($translates['admin.settings.name'],'/staffcp/'.$this->dataModel->getModelName());
	}
	
	public function edit(){
		$id = $this->request('id',0);
		$data = $this->model->select()->where('id = ?', $id)->fetchOne();
		if (empty($data))
			$this->error404();
		$this->view->setting = $data;
		$this->addBreadCrumb($data['name'],'#');
	}
	
	public function save(){
		$form = $this->request('form');
		$id = 0;
		if (!empty($form['id']))
			$id = $form['id'];
		unset($form['id']);
		$setting = $this->model->select()->where('id = ?', $id)->fetchOne();
		$typeClass = ucfirst($setting['type']).'Type';
		if ($setting['type'] == 'file'){
			$typeSettings = array(
				'type'	=> 'file',
				'base_dir'	=> 'settings/'
			);
		} elseif ($setting['type'] == 'image') {
			$typeSettings = array(
				'type'	=> 'file',
				'base_dir'	=> 'settings/',
				'images' => array(	
					'small'		=> '50x0',		
					'middle'	=> '100x0',		
					'big'		=> '200x0',
					'normal'	=> '300x0',
					'full'		=> '500x0',		
					'full2'		=> '650x0',		
				),
			);
			
		} else {
			$typeSettings = array(
				'type' => $setting['type'],
			);
		}
		
		$type = new $typeClass('value',$typeSettings,$setting['value']);
		$type->table = $this->dataModel->fields['id']->table;
		
		$this->dataModel->fields['value'] = $type;
		$this->model->update($form,array('id' => $id));
		
		Logs::addLog(Acl::getAuthedUserId(),'Обновление настройки id:'.$id,URL_NOW);
		$this->redirect('index','settings');
	}
	
	public function beforeAction() {
		parent::beforeAction();
		$translates = Register::get('translates');
		$this->addBreadCrumb($translates['admin.settings.name'],'/staffcp/'.$this->dataModel->getModelName());
	}
}