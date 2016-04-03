<?php
/**
 * Base controller for cms controllers.
 * This controller implemers base actions with models described in cmsGenerator
 * entities.
 */
class CmsGenerator extends Controller {

	public $listLangs = null;
	public $setLang = LANG;
	public $setLangSide = 1;
	public $translates = null;
	
	public $layout = 'global';
	
	public $modelName = null;
	protected $dataModel = null;
	protected $model = null;
	protected $breadCrumbs;

	public $isManager = false;
	public $isLevel = false;
	
	/* Global functions */
	function getManagerId(){
		$userId = Acl::getAuthedUserId();
		return $userId;
	}
	function getDataAboutOffice(){
		$userId = $this->getManagerId();
		$db = Register::get('db');
		$sql = "SELECT 
					U.id user_id,
					U.name user_name,
					O.id office_id,
					O.name office_name,
					O.info office_info,
					C.name city_name
				FROM ".DB_PREFIX."_user U
				LEFT JOIN ".DB_PREFIX."offices O ON U.office_id=O.id
				LEFT JOIN ".DB_PREFIX."dic_cities C ON O.city_id=C.id
				WHERE U.id='".(int)$userId."';";
		$res = $db->get($sql);
		$this->view->office = $res;
		$this->view->officeData = $res;
	}
	
    /**
     * Create a controller
     *
     * @todo I think modelName should be supplied already prepared
     * @param string $modelName
     */
	public function __construct($modelName = null){
		$this->modelName = str_replace('-','_',$modelName);
	}
	
    /**
     * List action by default
     */
	public function index() {
		
		$this->prepareIndexData();
		$this->render('generator/list');
	}
	
	function online(){
		$db = Register::get('db');
		$count = "select count(*) as cc from ".DB_PREFIX."session;";
		$res = $db->get($count);
		$this->view->online = $res['cc'];
	}
	
	public function prepareIndexData()
	{
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
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - '.$this->dataModel->getListTitle().' '.$this->controller.'/'.$this->action,URL_NOW);
		
		/* ACL */
		if ($listIds == 'all')
		{
			
			$search = $this->request("search",false);
			$this->view->search = $search;
			if (trim($search)) {
				
				$this->view->tableajax = true;
				$db = Register::get('db');
				$sql = "SHOW COLUMNS FROM `".$this->dataModel->getTable()."`;";
				$colums = $db->query($sql);
				$dataColums = array();
				if (isset($colums) && count($colums)>0) {
					foreach ($colums as $dd){
						$dataColums []= '`'.$dd['Field'].'`';
					}
					$searchString = join(" LIKE '%".mysql_real_escape_string(trim($search))."%' OR ", $dataColums).
					" LIKE '%".mysql_real_escape_string(trim($search))."%'";
					$this->view->data = 
					$this->model->select()->where($searchString)->fetchAll();
				}
			}
			else {
				/* if the rows more then 100 items, switch on ajax search template, because a lot of items stopping view page faster */
				$per_page = 100;
				$page = (int)$this->request("page",1);
				$this->view->currentPage = $page;
					
				$numRows = $this->model->select()->fields("COUNT(*) cc")->fetchOne();
				$Rows = isset($numRows['cc'])?$numRows['cc']:0;
				$this->view->totalPage = (int)(($Rows - 1) / $per_page) + 1;
				$this->view->total = $Rows;
					
				if ($Rows > $per_page) {
				
					$this->view->tableajax = true;
					$dataModelPage = ($page - 1)*$per_page;
					$this->view->data = $this->model->select()->limit($dataModelPage,$per_page)->order($this->dataModel->getIndexField()." DESC")->fetchAll();
				
				}
				else {
					$this->view->data = $this->model->select()->fetchAll();
				}
			}
		} 
		elseif ($listIds == 'none')
		{
			$this->view->data = array();
		}
		elseif ($listIds == 'category')
		{
			if ($categoryListIds == 'all')
				$this->view->data = $this->model->select()->fetchAll();
			elseif ($categoryListIds == 'none')
				$this->view->data = array();
			else
				$this->view->data = $this->model
								->select()
								->where($this->dataModel->getTable().'.category_id IN ?',explode(',',$categoryListIds))
								->fetchAll();
		}
		else
		{
			$this->view->data = $this->model
								->select()
								->where($this->dataModel->getIndexField().' IN ?',explode(',',$listIds))
								->fetchAll();
		}
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
	}

    /**
     * Add action
     */
	public function add()
	{
		$this->prepareAddData();
		$this->render('generator/add');
	}

	public function prepareAddData()
	{
		$this->view->tabs = $this->dataModel->getAddTabs();
		$this->view->tabFields = array();
		$tabFields = array();
		foreach ($this->view->tabs as $tabName)
		{
			$tabFields[$tabName] = $this->dataModel->getAddTabFields($tabName);
		}
		$this->view->tabFields = $tabFields;
		$this->view->dataModel = $this->dataModel;

		$this->view->listTitle = $this->dataModel->getListTitle();
		$this->view->listUrl = '/staffcp/'.$this->dataModel->getModelName().'/';
		$this->view->title = $this->dataModel->getAddTitle();
		$this->view->submit = $this->dataModel->getAddSubmit();

		$this->addBreadCrumb($this->dataModel->getAddTitle(),'#');
	}

	/**
     * Edit action
     */
	public function edit() {
		$this->prepareEditData();
		$this->render('generator/edit');
	}

	public function prepareEditData() {
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$id = mysql_real_escape_string($id);
		$data = $this->model->select()->where($indexField." = '$id'")->fetchOne();
	
		if (empty($data[$indexField]))
			$this->error404();
			
		$this->dataModel->setValues($data);
		$this->view->tabs = $this->dataModel->getEditTabs();
		$this->view->tabFields = array();
		$tabFields = array();
		foreach ($this->view->tabs as $tabName)
		{
			$tabFields[$tabName] = $this->dataModel->getEditTabFields($tabName);
		}
		$this->view->tabFields = $tabFields;
		$this->view->dataModel = $this->dataModel;

		$this->view->listTitle = $this->dataModel->getListTitle();
		$this->view->listUrl = '/staffcp/'.$this->dataModel->getModelName().'/';
		$this->view->title = $this->dataModel->getEditTitle();
		$this->view->submit = $this->dataModel->getEditSubmit();

		$this->view->indexField = $indexField;
		$this->view->indexValue = $id;

		$this->addBreadCrumb($this->dataModel->getEditTitle(),'#');
	}

    /**
     * Save action
     */
	public function save($params='') {
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
			Logs::addLog(Acl::getAuthedUserId(),'Сохранение данных в разделе '.$this->dataModel->getAddTitle().' '.$this->controller.'/'.$this->action,URL_NOW);
			
			$this->redirect('index',$this->dataModel->getModelName(),$params);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование данных в разделе '.$this->dataModel->getEditTitle().' '.$this->controller.'/'.$this->action.' id:'.$id,URL_NOW);
			
			//$this->redirectUrl($_SERVER['HTTP_REFERER']);
			$this->redirect('index',$this->dataModel->getModelName(),$params);
		}
	}
	
	public function translitIt($str){
	    $tr = array("А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y","ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", " "=> "_", "."=> "", "/"=> "_");
	    return strtr($str,$tr);
	}
	
	public function doTraslit($urlstr){
		if (preg_match('/[^A-Za-z0-9_\-]/', $urlstr)) {
		    $urlstr = $this->translitIt($urlstr);
		    $urlstr = preg_replace('/[^A-Za-z0-9_\-]/', '', $urlstr);
		}
		return $urlstr;
	}
	
	public function trimA($str, $set=null){
	    if(is_Array($str) || is_Object($str))
	        foreach($str as &$s)
	            $s=$this->trimA($s,$set);
	    elseif($set===null)$str=trim($str);
	    else $str=trim($str,$set);
	    return $str;
	}

    /**
     * Delete action
     */
	public function delete(){
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		if (!empty($id)){
			$this->model->delete(array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Удаление данных в разделе '.$this->controller.'/'.$this->action.' id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	
	/**
     * Delete list action
     */
	public function delete_list(){
		$indexField = $this->dataModel->getIndexField();
		$ids = $this->request("delete_list",0);
		if (!empty($ids)) {
			foreach ($ids as $id) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
				}
			}
			Logs::addLog(Acl::getAuthedUserId(),'Удаление списка данных в разделе '.$this->controller.'/'.$this->action.' id:'.join(",", $ids),URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}

    /**
     * Initialize action
     *
     * @todo refactor code, maybe should create some private method for handling data
     */
	public function beforeAction(){
		
		$this->installLang();
		
		$this->view->_controller = $this->controller;
		$this->view->_action = $this->action;
		$this->modelName = str_replace('-','_',$this->controller);

		if (!Acl::isAuthed())
			$this->redirect('login','security');
		
		$userId = Acl::getAuthedUserId();
		$acl = new Acl($userId);
		$this->view->acl = $acl;
		
		$this->isManager = $acl->isManager;
		$this->view->isManager = $this->isManager;
		
		if (!$acl->canViewMenuItem(str_replace('/staffcp/','',$_SERVER['REQUEST_URI']),$userId))
			$this->redirect('denied','security');
		
		$this->view->setLayout('global');
		$this->dataModel = new CmsGeneratorConfig($this->modelName);
		$this->model = new CmsGeneratorModel($this->dataModel);
		
		if ($this->controller != 'index') {
			$parentTitle = $this->dataModel->getParentTitle();
			if (!empty($parentTitle)){
				$this->addBreadCrumb($parentTitle,'/staffcp/'.$this->dataModel->getParentUrl());
			}
			if ($this->dataModel->getTitle())
				$this->addBreadCrumb($this->dataModel->getTitle(),'/staffcp/'.$this->dataModel->getModelName().'/');
		}
		$this->view->descrhtml = $this->dataModel->getDescr();
		
		$this->view->currency = SettingsModel::get('currency');
		$this->view->currency_eur = SettingsModel::get('currency_eur');
		$this->view->currency_usd_eur = SettingsModel::get('currency_usd_eur');
		$this->view->currency_rur = SettingsModel::get('currency_rur');
		$this->view->favicon = SettingsModel::get('favicon');
		
		$this->getDataAboutOffice();
		
		$db = Register::get('db');
		if ($this->isManager){
			$res = $db->get("SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills WHERE manager_id = '".(int)$manager_id."' AND status = 0;");
			$this->view->fullOrderCCNew = isset($res['cc'])?$res['cc']:0;
			$res = $db->get("SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills WHERE manager_id = '".(int)$manager_id."';");
			$this->view->fullOrderCCOther = isset($res['cc'])?$res['cc']:0;;
		}
		else {
			$res = $db->get("SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills WHERE status = 0;");
			$this->view->fullOrderCCNew = isset($res['cc'])?$res['cc']:0;
			$res = $db->get("SELECT COUNT(*) cc FROM ".DB_PREFIX."cart_bills;");
			$this->view->fullOrderCCOther = isset($res['cc'])?$res['cc']:0;
		}

		$layoutReinstall = $this->dataModel->getLayout();
		if ($layoutReinstall){
			$this->layout = $layoutReinstall;
		}
	}

    /**
     * Prepare data for rendering
     */
	public function beforeRender(){
		global $menu,$menu_settings,$menu_manager;
		
		$userId = Acl::getAuthedUserId();
		$acl = new Acl($userId);
		
		$this->checkMenu();
		//if ($this->isManager) {
		//	$this->checkManagerMenu();
		//	$this->view->menu = $menu_manager;
		//} else {
			$this->view->menu = $menu;
		//}
		$this->view->breadCrumbs = $this->breadCrumbs;
	}

    /**
     * Add item to breadrumbs
     * @param string $title
     * @param string $url
     */
	public function addBreadCrumb($title, $url = '#'){
		$this->breadCrumbs[$title] = $url; 
	}
	
	public function checkManagerMenu(){
		$newVals = array();
		if (isset($GLOBALS['menu_manager']) && count($GLOBALS['menu_manager'])>0){
			foreach ($this->isLevel as $thisL){
				foreach ($GLOBALS['menu_manager'] as $key=>$val){
					if (strpos($val,$thisL)){
						$newVals [$key]= $val;
					}
				}
			}
			$GLOBALS['menu_manager'] = $newVals;
		}
	}
	
	public function checkMenu(){
		
		foreach ($GLOBALS['menu'] as $key=>$val){
			
			if (is_array($val)) {
				foreach ($val as $skey=>$sval){
					if (is_array($sval)){
						if (@$sval[1] == 'add'){
							if (!$this->view->acl->canViewMenuItem($sval[0])){
								unset($GLOBALS['menu'][$key][$skey]);
							}
						} else {
							foreach ($sval as $sskey=>$ssval){
								if (is_array($ssval)){
									if (!$this->view->acl->canViewMenuItem($ssval[0])){
										unset($GLOBALS['menu'][$key][$skey][$sskey]);
									}
								} else {
									if (!$this->view->acl->canViewMenuItem($ssval)){
										unset($GLOBALS['menu'][$key][$skey][$sskey]);
									}
								}
							}
							if (!count($GLOBALS['menu'][$key][$skey]))
								unset($GLOBALS['menu'][$key][$skey]);
						}
					} else {
						if (is_array($sval)){
							if (!$this->view->acl->canViewMenuItem($sval[0])){
								unset($GLOBALS['menu'][$key][$skey]);
							}
						} else {
							if (!$this->view->acl->canViewMenuItem($sval)){
								unset($GLOBALS['menu'][$key][$skey]);
							}
						}
					}
				}
				if (!count($GLOBALS['menu'][$key]))
					unset($GLOBALS['menu'][$key]);
			}
			else {
				
				//$expVal = explode("/", $val);
				//unset($expVal[0]);
				//unset($expVal[count($expVal)]);
				//$val = array_pop($expVal);

				if (!$this->view->acl->canViewMenuItem($val,Acl::getAuthedUserId())){
					unset($GLOBALS['menu'][$key]);
				}
			}
			
		} /* foreach menu */
	}
		
	function error404($exception = null) {
		if (debug) {
			echo '
			<html>
			<head>
			<style>
			* { font-family: Verdana, Tahoma, Arial; font-size: 13px; }
			body {}
			.error {width: 860px;text-align: left;}
			.error h1 {font-size: 18px;margin-left: 36px;}
			.error-num {float: left;width: 30px;background-color: #fff;padding: 3px;text-align: left;}
			.error-descr {float: left;background-color: #f5f5f5;margin-bottom: 10px;padding: 3px;width: 800px;text-align: left;}
			.error-descr span {font-weight: bold;}
			</style>
			</head>
			</body>
			';
			echo '<center><div class="error"><h1>'.$exception->getMessage().'</h1>';
			foreach ($exception->getTrace() as $key=>$item) {
				echo '<div class="error-item"><div class="error-num">'.($key + 1).'.</div>';
				echo '<div class="error-descr">';
				echo '<span>'.$item['class'].' '.$item['type'].' '.$item['function'].' '.'( '.join(', ',$item['args']).' )'.'</span><br>';
				echo $item['file'].' (Line: '.$item['line'].')<br>';
				echo '</div><br clear="all" /></div>';
			}
			echo '</div></center>';
			echo '</body></html>';
		}
		else {
			
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			$controller = new Dispatcher();
			$controller->process('/error404');
			exit();
			
		}
	}
	
	/* *************************************** LANG *************************************** */
	function setLangSide(){
		$side = addslashes($this->request('side',false));
		if ($side == 'front') {
			$_SESSION['side']=0;
		}
		if ($side == 'back') {
			$_SESSION['side']=1;
		}
		$this->setLangSide = (isset($_SESSION['side']) && $_SESSION['side']==1)?1:0;
	}
	function setLangLang(){
		$side = addslashes($this->request('lang',false));
		$_SESSION['sidelang']=$side;
		$this->setLangSide = (isset($_SESSION['side']) && $_SESSION['side']==1)?1:0;
	}
	
	function setLang() {
		$lang = addslashes($this->request("lang"));
		if ($lang) {
			$_SESSION['setLang']=$lang;
		}
		if (!isset($_SESSION['setLang']) || empty($_SESSION['setLang'])) {
			$_SESSION['setLang']=LANG;
		}
		#$this->setLang = isset($_SESSION['setLang'])?$_SESSION['setLang']:LANG;
		$this->setLang = 'ru';
	}
	function installLang(){
		# 1 - backend
		# 0 - frontend
		$this->setLang(); # set lang
		$this->setLangSide(); # set side of lang
		
		$this->listLangs = Register::get('langs');
		$this->translates = Register::get('translates');
		
		$this->view->translates = $this->translates;
		$this->view->lang = $this->setLang;
		$this->view->langs = $this->listLangs;
		$this->view->langSide = $this->setLangSide;
	}

	public function unset_simpleview_shopping_system(){
		if (isset($_SESSION['simpleview']))
			unset($_SESSION['simpleview']);
		if (isset($_SESSION['simpleview_shopping_active']))
			unset($_SESSION['simpleview_shopping_active']);
		if (isset($_SESSION['simpleview_shopping_account_id']))
			unset($_SESSION['simpleview_shopping_account_id']);
		if (isset($_SESSION['simpleview_shopping_car_id']))
			unset($_SESSION['simpleview_shopping_car_id']);
		if (isset($_SESSION['simple_scsid']))
			unset($_SESSION['simple_scsid']);
	}
	
	public function setAccountAlert($account_id=0,$type=false){
		if ($account_id && $type) {
			$db = Register::get('db');
			$sql = "
				INSERT INTO ".DB_PREFIX."accounts_alerts 
					(`account_id`,`type`) 
				VALUES 
					('".(int)$account_id."','".mysql_real_escape_string($type)."');";
			$db->post($sql);
		}
	}
}

?>