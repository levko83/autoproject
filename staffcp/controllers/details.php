<?php

class DetailsController  extends CmsGenerator {
	
	public function index(){
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр списка цен',URL_NOW);
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."importers ORDER BY id;";
		$this->view->imports = $db->query($sql);
		$this->prepareIndexData();
		$this->render('details/list');
	}
	
	public function diagnostic(){
		$id = $this->request("id");
		$db = Register::get('db');
		
		Logs::addLog(Acl::getAuthedUserId(),'Диагностика цены id:'.$id,URL_NOW);
		
		$sql = "SELECT * FROM ".DB_PREFIX."details WHERE ID='".(int)$id."';";
		$res = $db->get($sql);
		$this->view->detail = $res;
		
		$sql = "SELECT * FROM ".DB_PREFIX."brands WHERE BRA_ID='".(int)$res['BRAND_ID']."';";
		$this->view->brand = $db->get($sql);
		
		$sql = "SELECT * FROM ".DB_PREFIX."importers WHERE id='".(int)$res['IMPORT_ID']."';";
		$this->view->importer = $db->get($sql);
	}
	
	public function prepareIndexData(){
		$db = Register::get('db');
		
		$select_imports = $this->request("select_imports",false);
		$this->view->select_imports = $select_imports;
		
		$search = $this->request("search");
		$ARTID = $this->request("ARTID","");
		
		$clear_imports = $this->request("clear_imports",false);
		if ($clear_imports) {
			if ($clear_imports=='clear') {
				$db->query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='0';");
			}
			else {
				$db->query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='".(int)$clear_imports."';");	
			}
			Logs::addLog(Acl::getAuthedUserId(),'Очистка базы цен поставщика id:'.$clear_imports,URL_NOW);
			$this->redirectUrl('/staffcp/details/');
		}
		
		$per_page = 50;
		$page = $this->request("page",1);
		$this->view->page = $page;
		$this->view->paginations = true;
		
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
		
		$page = ($page-1)*$per_page;
		if ($select_imports) {
			
				$this->view->data = $this->model->select()->where("`IMPORT_ID`='".(int)$select_imports."'")->limit($page,$per_page)->fetchAll();
				
		} elseif ($ARTID) {
			
			$this->view->data = $this->model->select()->where("`ARTICLE` LIKE '".addslashes(FuncModel::stringfilter($ARTID))."'")->limit($page,$per_page)->fetchAll();
			$this->view->paginations = false;
			
		} elseif (isset($search) && isset($search['brand']) && $search['brand']) {
			
			$brandSearch = urldecode($search['brand']);
			$this->view->data = $this->model->select()->where("BRAND_NAME LIKE '".mysql_real_escape_string($brandSearch)."%'")->limit($page,$per_page)->fetchAll();
			$this->view->paginations = false;
		}
		else {
			$this->view->data = $this->model->select()->limit($page,$per_page)->fetchAll();
		}
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
		
		if ($select_imports) {
			if ($select_imports == 'none') {
				$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."details WHERE `IMPORT_ID`='0';";
			} else {
				$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."details WHERE `IMPORT_ID`='".(int)$select_imports."';";
			}
		}
		else {
			$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."details;";	
		}
		$data = $db->get($sql);
		$this->view->cc = $data['cc'];
		$this->view->pages_num = (int)(($data['cc']-1)/$per_page)+1;
		
		$res = $db->get("SELECT COUNT(*) cc FROM ".DB_PREFIX."details WHERE `IMPORT_ID`='0'");
		$this->view->noImps = $res['cc'];
	}
}

?>