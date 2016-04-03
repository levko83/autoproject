<?php

class LangsController  extends CmsGenerator {
	
	public $layout = 'global';
	
	function index() {
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - перевод сайта',URL_NOW);
		$this->render('langs/set');
	}
	function set(){
	}
	function view(){
		$this->prepareIndexData();
	}
	public function prepareIndexData() {
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
		$this->view->data = $this->model->select()->where("side=?",(int)$this->setLangSide)->fetchAll();
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		$this->view->sololang = $this->request('lang',false);
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
	}
	public function save(){
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
		if (empty($id)){
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление нового перевода',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование названия в переводе id:'.$id,URL_NOW);
		}
		$this->redirect('view',$this->dataModel->getModelName());
	}
	public function multisave(){
		
		$db = Register::get('db');
		
		$lang_ru = $this->request("lang_ru",array());
		$lang_en = $this->request("lang_en",array());
		$lang_de = $this->request("lang_de",array());
		$lang_fr = $this->request("lang_fr",array());
		$lang_it = $this->request("lang_it",array());
		$lang_gr = $this->request("lang_gr",array());
		$lang_no = $this->request("lang_no",array());
		$lang_da = $this->request("lang_da",array());
		$lang_es = $this->request("lang_es",array());
		
		if (isset($lang_ru) && count($lang_ru)>0){
			foreach ($lang_ru as $id => $ru){
				$en = $lang_en[$id];
				$de = $lang_de[$id];
				$fr = $lang_fr[$id];
				$it = $lang_it[$id];
				$gr = $lang_gr[$id];
				$no = $lang_no[$id];
				$da = $lang_da[$id];
				$es = $lang_es[$id];
				
				if (!empty($en)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',en='".mysql_real_escape_string($en)."' WHERE id='".(int)$id."';");
				if (!empty($de)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',de='".mysql_real_escape_string($de)."' WHERE id='".(int)$id."';");
				if (!empty($fr)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',fr='".mysql_real_escape_string($fr)."' WHERE id='".(int)$id."';");
				if (!empty($it)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',it='".mysql_real_escape_string($it)."' WHERE id='".(int)$id."';");
				if (!empty($gr)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',gr='".mysql_real_escape_string($gr)."' WHERE id='".(int)$id."';");
				if (!empty($no)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',no='".mysql_real_escape_string($no)."' WHERE id='".(int)$id."';");
				if (!empty($da)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',da='".mysql_real_escape_string($da)."' WHERE id='".(int)$id."';");
				if (!empty($es)) $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',es='".mysql_real_escape_string($es)."' WHERE id='".(int)$id."';");
				
				// $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',en='".mysql_real_escape_string($en)."' WHERE id='".(int)$id."';");
				// $db->post("UPDATE ".DB_PREFIX."langs SET ru='".mysql_real_escape_string($ru)."',en='".mysql_real_escape_string($en)."', de='".mysql_real_escape_string($de)."', fr='".mysql_real_escape_string($fr)."', it='".mysql_real_escape_string($it)."', gr='".mysql_real_escape_string($gr)."', no='".mysql_real_escape_string($no)."', da='".mysql_real_escape_string($da)."', es='".mysql_real_escape_string($es)."' WHERE id='".(int)$id."';");
				// die ($en . " - " .$de. " - " .$fr. " - " .$it. " - " .$gr. " - " .$no. " - " .$da. " - " .$es);
			}
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование названий в переводе сайта',URL_NOW);
		}
		
		$this->redirectUrl('/staffcp/langs/view/?side=front&lang=de');
	}

	public function add() {
		$this->prepareAddData();
	}

	public function edit() {
		$this->prepareEditData();
	}

	function beforeAction(){
		parent::beforeAction();
		$this->view->side_title = ($this->setLangSide)?$this->translates['admin.langs.backend']:$this->translates['admin.langs.frontend'];
	}
	function beforeRender(){
		parent::beforeRender();
	}
}