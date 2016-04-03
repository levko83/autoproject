<?php

class CrossesController  extends CmsGenerator {
	
	public function index(){
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр базы кроссов',URL_NOW);
		$this->prepareIndexData();
		$this->render('crosses/list');
	}
	
	public function prepareIndexData(){
		$db = Register::get('db');
		
		$delete = $this->request("delete",false);
		if (isset($delete['brand']) && $delete['brand']){
			$db->post("DELETE FROM ".DB_PREFIX."details_db__crosses WHERE BRAND LIKE '".mysql_real_escape_string($delete['brand'])."';");
			$this->redirectUrl('/staffcp/crosses/');
		}
		if (isset($delete['time']) && $delete['time']){
			$db->post("DELETE FROM ".DB_PREFIX."details_db__crosses WHERE DT_LOAD = '".mysql_real_escape_string($delete['time'])."';");
			$this->redirectUrl('/staffcp/crosses/');
		}
		
		$clear = $this->request("clear",false);
		if (isset($clear) && $clear == 'all'){
			$db->post("DELETE FROM ".DB_PREFIX."details_db__crosses;");
			$this->redirectUrl('/staffcp/crosses/');
		}
		
		$search = $this->request("search");
		$this->view->_search = $search;
		
		$per_page = 100;
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
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
		
		$iSQL = '';
		if (isset($search['article']) && $search['article']){
			$iSQL .= " AND ARTICLE LIKE '".mysql_real_escape_string($search['article'])."' ";
		}
		if (isset($search['brand']) && $search['brand']){
			$iSQL .= " AND BRAND LIKE '".mysql_real_escape_string($search['brand'])."' ";
		}
		if (isset($search['time']) && $search['time']){
			$iSQL .= " AND DT_LOAD = '".mysql_real_escape_string($search['time'])."' ";
		}
		
		$page = ($page-1)*$per_page;
		$sql = "SELECT * FROM ".DB_PREFIX."details_db__crosses WHERE 1 $iSQL LIMIT $page,$per_page;";
		$this->view->data = $db->query($sql);
		
		$sql = "SELECT COUNT(*) cc FROM ".DB_PREFIX."details_db__crosses WHERE 1 $iSQL;";	
		$data = $db->get($sql);
		$this->view->cc = $data['cc'];
		$this->view->pages_num = (int)(($data['cc']-1)/$per_page)+1;
		$this->view->per_page = $per_page;
		
		/* ********************** */
		
		$sql = "SELECT DISTINCT BRAND FROM ".DB_PREFIX."details_db__crosses ORDER BY BRAND;";
		$this->view->listBrandsDB = $db->query($sql);
		
		$sql = "SELECT DISTINCT DT_LOAD FROM ".DB_PREFIX."details_db__crosses ORDER BY DT_LOAD;";
		$this->view->listDT_LOAD = $db->query($sql);
		
		/* ********************** */
	}
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
		
		$form['SEARCH_NUMBER'] = $form['ARTICLE'].'_'.$form['BRAND'];
		
		if (empty($id)){
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Добавление кросса',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			Logs::addLog(Acl::getAuthedUserId(),'Релактирование кросса id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),$params);
	}
	
	function parse(){
    	
		Logs::addLog(Acl::getAuthedUserId(),'Загрузка списка кроссов из файла xlsx',URL_NOW);
		
    	$db = Register::get('db');
    	if ($_FILES['file']['name']) {
    		
    		$ext = array_pop(explode(".", basename($_FILES['file']['name'])));
    		if ($ext == 'xlsx'){
				if (move_uploaded_file($_FILES['file']['tmp_name'], '../cache/'.$_FILES['file']['name'])) {
					
					$DT_LOAD = time();
					
					$file = '../cache/'.$_FILES['file']['name'];
				
					require_once '../xreaders/readers/simplexlsx.class.php';
					$xlsx = new SimpleXLSX($file);
					
					$i = $cc = $cc2 = 0;
					$numROWS = count($xlsx->rows());
					foreach( $xlsx->rows() as $r ) { $i++;
												
						$ARTICLE = str_replace(array("'","/","\""),"",stripslashes(mysql_real_escape_string($r[0])));
						$BRAND = str_replace(array("'","/","\""),"",stripslashes(mysql_real_escape_string($r[1])));
						$DESCR = str_replace(array("'","/","\""),"",stripslashes(mysql_real_escape_string($r[2])));
						$CROSS_BRAND = str_replace(array("'","/","\""),"",stripslashes(mysql_real_escape_string($r[4])));
						$CROSS_ARTICLE = str_replace(array("'","/","\""),"",stripslashes(mysql_real_escape_string($r[3])));
						
						/* QUERY */
						$cc++;$cc2++;
						if ($cc == 1){
							
							$q = "
							INSERT INTO ".DB_PREFIX."details_db__crosses 
							(`SEARCH_ARTICLE`,`ARTICLE`,`BRAND`,`DESCR`,`CROSS_BRAND`,`CROSS_ARTICLE`,`DT_LOAD`) 
							VALUES 
							";
						}
						
						$q .= " ('".FuncModel::stringfilter($ARTICLE)."','".$ARTICLE."','".$BRAND."','".$DESCR."','".$CROSS_BRAND."','".FuncModel::stringfilter($CROSS_ARTICLE)."','".mysql_real_escape_string($DT_LOAD)."') ".( ($cc==100 || $cc2 == $numROWS)?';':',' );
			
						if ($cc == 100 || $cc2 == $numROWS){
							$db->post(stripslashes($q));
							$cc=0;
						}
					}
				}
				echo "<h1>Operation done. Thanks. Imported rows: ".($i)."</h1>";
			}
			else {
				echo "<h1>Error. Incorrect file type.</h1>";
				exit();
			}
		}
		else {
			echo "<h1>Error. Internal error.</h1>";
			exit();
		}
		exit();
	}
}
?>