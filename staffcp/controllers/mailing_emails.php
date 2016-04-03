<?php

class Mailing_emailsController  extends CmsGenerator {
	
	/**
     * Save action
     */
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
		if (empty($id))
		{
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление подписчика',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование подписчика id:'.$id,URL_NOW);
		}
		$this->redirect('index','mailing','#tab-2');
	}
	
	/**
     * Delete action
     */
	public function delete(){
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		if (!empty($id))
		{
			$this->model->delete(array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Удаление подписчика id:'.$id,URL_NOW);
		}
		$this->redirect('index','mailing','#tab-2');
	}
	
	function beforeAction() {
		parent::beforeAction();
	}
	
	function beforeRender() {
		parent::beforeRender();
	}
}