<?php

class AccountsController extends CmsGenerator {
	
	public function index(){
		$this->prepareIndexData();
		$this->render('accounts/list');
	}
	
	public function search(){
		$this->layout = "ajax";
		
		$q = mysql_real_escape_string($_GET["q"]);
		$items = AccountsModel::globalsearch($q);
		if (isset($items) && count($items)>0){
			foreach ($items as $key=>$value) {
				echo "".$value['name']." (Код: ".$value['account_code'].") ".$value['email']." ".$value['phones']." ".$value['address']." ".$value['firm_name']."|".$value['id']."\n";
			}
		}
		
		exit();
	}
	
	private function getOffices(){
		$db = Register::get('db');
		$sql = "
			SELECT
				AD.*,COUNT(A.id) C
			FROM ".DB_PREFIX."offices AD
			LEFT JOIN  ".DB_PREFIX."accounts A ON A.office_id=AD.id
			GROUP BY
				AD.id
			ORDER BY name;
		";
		return $db->query($sql);
	}
	
	private function getGroups(){
		$db = Register::get('db');
		$sql = "
			SELECT 
				AD.*,COUNT(A.discountname_id) C 
			FROM ".DB_PREFIX."accounts_discountnames AD
			LEFT JOIN  ".DB_PREFIX."accounts A ON A.discountname_id=AD.id
			GROUP BY
				AD.id
			ORDER BY name;
		";
		return $db->query($sql);
	}
	
	public function prepareIndexData(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр списка пользователей',URL_NOW);
		
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
		
		$userId = Acl::getAuthedUserId();
		$userData = UsersModel::getById($userId);
		$office_id = $userData['office_id'];
		$acl_accounts_access = $userData['acl_accounts_access'];
		
		if ($this->isManager && $acl_accounts_access){
			$this->view->data = $this->model->select()->where("office_id=?",(int)$office_id)->fetchAll();
		}
		else {

			//groups
			$this->view->agroups = $this->getGroups();
			//offices
			$this->view->aoffices = $this->getOffices();
			
			$group = (int)$this->request("group",0);
			$this->view->group = $group;
			
			$office = (int)$this->request("office",0);
			$this->view->office = $office;
			
			$search_id = (int)$this->request("search_id",0);
			$this->view->search_id = $search_id;
			
			if ($search_id) {
				$this->view->data = $this->model->select()->where("id=?",(int)$search_id)->fetchAll();
			}
			elseif ($group) {
				$this->view->data = $this->model->select()->where("discountname_id=?",(int)$group)->fetchAll();
			}
			elseif ($office){
				$this->view->data = $this->model->select()->where("office_id=?",(int)$office)->fetchAll();
			}
			else {
				
				/* *** */
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
				/* *** */
			}
		}
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
	}
	
	function updateCashBalance($account_id,$balance,$comment){
		if (isset($balance) && $balance != 0) {
			$db = Register::get('db');
			
			$balance = mysql_real_escape_string($balance);
			
			$db->post("UPDATE ".DB_PREFIX."accounts SET balance=balance+{$balance} WHERE id='".(int)$account_id."';");
			
			$sql = "INSERT INTO ".DB_PREFIX."accounts_history (`account_id`,`sum`,`operation`,`dt`,`comment`) VALUES ('".(int)$account_id."','".mysql_real_escape_string($balance)."','".(($balance>0)?'plus':(($balance<0)?'minus':''))."','".mktime()."','".mysql_real_escape_string($comment)."');";
			$db->post($sql);
			
			Logs::addLog(Acl::getAuthedUserId(),'Обновление баланса пользователя id:'.$account_id,URL_NOW);
		}
	}
	
	public function save($params='') {
		$form = $this->request('form');
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
		$form = $this->trimA($form);
		
		$COMMENT = $form['balance_comment'];
		$PLUS = $form['balance_plus'];
		unset($form['balance_plus']);
		unset($form['balance_comment']);
		
		/* generation alias */
		if (isset($form['code'])&&empty($form['code'])) {
			$form['code'] = strtolower($this->doTraslit($form['name']));
			$form['code'] = substr($form['code'],0,100);
		}
		if (empty($id)){
			$this->model->insert($form);
			Logs::addLog(Acl::getAuthedUserId(),'Добавлен новый пользователь',URL_NOW);
		} else {
			$this->model->update($form,array($indexField => $id));
			if ($PLUS) {
				$this->updateCashBalance($id,$PLUS,$COMMENT);
			}
			$this->setMargins($id);
			$this->setIAccess($id);
			Logs::addLog(Acl::getAuthedUserId(),'Отредактирован пользователь id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	
	public function ajax(){
		$this->layout = "ajax";
		$id = (int)$this->request("id");
		$operations = BillsModel::getOperation($id);
		$this->view->operations = $operations;
	}
	
	#0#
	public function edit() {
		$this->prepareEditData();
		
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$id = mysql_real_escape_string($id);
		
		$this->view->importers = ImportersModel::getAll();
		$this->view->save_margins = $this->getMargins($id);
		$this->view->save_iaccess = $this->getIAccess($id);
		
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."margins ORDER BY name;";
		$this->view->listMargins = $db->query($sql);
		
		Logs::addLog(Acl::getAuthedUserId(),'Редактирование пользователя id:'.$id,URL_NOW);
		
		$this->render('accounts/edit');
	}
	#0#
	
	#1#
	private function setMargins($account_id=0){
		$db = Register::get('db');
		$importers = $this->request("importers");
		$db->post("DELETE FROM ".DB_PREFIX."accounts_margin2account WHERE `account_id`='".(int)$account_id."';");
		if (isset($importers) && count($importers)>0){
			foreach ($importers as $importer_id=>$margin_id){
				if ($importer_id && $margin_id)
				$db->post("INSERT INTO ".DB_PREFIX."accounts_margin2account (`account_id`,`importer_id`,`margin_id`) VALUES ('".(int)$account_id."','".(int)$importer_id."','".(int)$margin_id."');");
			}
			
		}
	}
	private function setIAccess($account_id=0){
		$db = Register::get('db');
		$iaccess = $this->request("iaccess");
		$db->post("DELETE FROM ".DB_PREFIX."accounts_iaccess WHERE `account_id`='".(int)$account_id."';");
		if (isset($iaccess) && count($iaccess)>0){
			foreach ($iaccess as $importer_id){
				if ($importer_id)
				$db->post("INSERT INTO ".DB_PREFIX."accounts_iaccess (`account_id`,`importer_id`) VALUES ('".(int)$account_id."','".(int)$importer_id."');");
			}
		}
	}
	#1#
	
	#2#
	private function getMargins($account_id=0){
		$db = Register::get('db');
		$res = $db->query("SELECT * FROM ".DB_PREFIX."accounts_margin2account WHERE `account_id`='".(int)$account_id."';");
		$margins = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$margins [$dd['importer_id']]= $dd['margin_id'];
			}
		}
		return $margins;
	}
	private function getIAccess($account_id=0){
		$db = Register::get('db');
		$res = $db->query("SELECT importer_id FROM ".DB_PREFIX."accounts_iaccess WHERE `account_id`='".(int)$account_id."';");
		$importer_id = array();
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$importer_id []= $dd['importer_id'];
			}
		}
		return $importer_id;
	}
	#2#
	
	/* EXPORT Clients * ***************************** */
	function export(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Выгрузка базы пользователей в формат csv',URL_NOW);
		
		$db = Register::get('db');
		$sql = "
			SELECT 
				A.id,
				A.email,
				A.name,
				A.phones,
				A.country,
				DC.name city,
				A.address,
				A.info,
				A.discount,
				FROM_UNIXTIME(A.dt) dt,
				A.is_active,
				A.is_firm,
				A.firm_name,
				A.firm_inn,
				A.firm_kpp,
				A.firm_bank,
				A.firm_pc,
				A.firm_kc,
				A.firm_bnk,
				A.firm_ogrn,
				A.firm_okpo,
				A.firm_discount,
				A.balance,
				O.name office,
				U.name manager,
				A.account_code 	 	
			FROM ".DB_PREFIX."accounts A
			LEFT JOIN ".DB_PREFIX."dic_cities DC ON DC.id=A.city
			LEFT JOIN ".DB_PREFIX."offices O ON O.id=A.office_id
			LEFT JOIN ".DB_PREFIX."_user U ON U.id=A.set_manager_id
			ORDER BY `id` DESC;";
		$q = $db->query($sql);
		
		require_once '../xreaders/readers/PHPExcel.php';
		$phpexcel = new PHPExcel();
		$page = $phpexcel->setActiveSheetIndex(0);
		$arr_name = array(
			'ID','E-mail','Имя','Телефон','Страна','Город','Адрес','Инфо','Скидка','Дата регистрации','Активен','Юр.лицо','Название фирмы','ИНН','КПП','Банк','Р/C','К/C','БНК','ОГРН','ОКПО','Скидка юр.лица','Баланс счета','Офис','Менеджер','Код клиента'
		);
		$page->fromArray($arr_name, NULL, 'A1');
		
	    $i=1; foreach ($q as $data){ $i++;
			$item = array(
				$data['id'],
				$data['email'],
				$data['name'],
				$data['phones'],
				$data['country'],
				$data['city'],
				$data['address'],
				$data['info'],
				$data['discount'],
				$data['dt'],
				($data['is_active'])?'да':'нет',
				($data['is_firm'])?'да':'нет',
				$data['firm_name'],
				$data['firm_inn'],
				$data['firm_kpp'],
				$data['firm_bank'],
				$data['firm_pc'],
				$data['firm_kc'],
				$data['firm_bnk'],
				$data['firm_ogrn'],
				$data['firm_okpo'],
				$data['firm_discount'],
				$data['balance'],
				$data['office'],
				$data['manager'],
				$data['account_code'],
			);
			$page->fromArray($item, NULL, 'A'.$i);
		}
		$title = "accounts_".date("d-m-Y_H-i");
		$page->setTitle($title);
		$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$ffExlx = "../cache/".$title.".xlsx";
		$objWriter->save($ffExlx);
		header("location: /cache/".$title.".xlsx");
		exit();
	}
	
	/* */
	function finances(){
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр финансовый раздел базы пользователей',URL_NOW);
		
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
		
			$per_page = 100;
			$page = (int)$this->request("page",1);
			$this->view->currentPage = $page;
				
			$db = Register::get('db');
			$iSQL = "";
			$balance = $this->request("balance",false);
			if ($balance == 'minus'){
				$iSQL .= " AND balance < 0 ";
				$numRows = $this->model->select()->fields("COUNT(*) cc")->where("is_active = 1 ".$iSQL)->fetchOne();
			}
			else {
				$numRows = $this->model->select()->fields("COUNT(*) cc")->where("is_active = 1")->fetchOne();
			}
			
			$Rows = isset($numRows['cc'])?$numRows['cc']:0;
			$this->view->totalPage = (int)(($Rows - 1) / $per_page) + 1;
			$this->view->total = $Rows;
			$dataModelPage = ($page - 1)*$per_page;
			
			$sql = "
				SELECT 
					A.id,A.name,A.phones,A.balance,A.email,A.dt,
					(SELECT COUNT(*) FROM ".DB_PREFIX."accounts_history AH WHERE AH.account_id=A.id) cc
				FROM ".DB_PREFIX."accounts A
				WHERE 
					 is_active = 1
					 $iSQL
				ORDER BY id DESC
				LIMIT ".(int)$dataModelPage.",".(int)$per_page."
			;";
			$this->view->data = $db->query($sql);
		}
		
		$this->view->title = $this->dataModel->getListTitle();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
	}
	
	function operation(){
		$db = Register::get('db');
		
		$balance = $this->request("balance",false);
		$update = $this->request("update",false);
		$id = $this->request("id",false);
		$del = $this->request("del",false);
		
		if ($del){
			$db->post("DELETE FROM ".DB_PREFIX."accounts_history WHERE id = '".(int)$del."';");
			Logs::addLog(Acl::getAuthedUserId(),'Удаление финансовой информации id:'.$del,URL_NOW);
			$this->redirectUrl('/staffcp/accounts/operation/?id='.$id);
		}
		if ($update == 'balance'){
			$balance = str_replace(",",".",$balance);
			$balance = str_replace(" ","",$balance);
			$db->post("UPDATE ".DB_PREFIX."accounts SET balance = '".mysql_real_escape_string($balance)."' WHERE id='".(int)$id."';");
			Logs::addLog(Acl::getAuthedUserId(),'Обновление финансовой информации id:'.$id,URL_NOW);
			$this->redirectUrl('/staffcp/accounts/operation/?id='.$id);
		}
		
		$op = $this->request("op",false);
		if ($op && count($op)>0){
			$operation = ''; $sum = 0;
			if (isset($op['plus']) && $op['plus']){
				$operation = 'plus';
				$sum = $op['plus'];
				$db->post("UPDATE ".DB_PREFIX."accounts SET balance=balance+'".mysql_real_escape_string($sum)."' WHERE id='".(int)$op['account_id']."';");
				Logs::addLog(Acl::getAuthedUserId(),'Пополнение баланса пользователя id:'.$op['account_id'],URL_NOW);
			}
			elseif (isset($op['minus']) && $op['minus']){
				$operation = 'minus';
				$sum = $op['minus'];
				$db->post("UPDATE ".DB_PREFIX."accounts SET balance=balance-'".mysql_real_escape_string($sum)."' WHERE id='".(int)$op['account_id']."';");
				Logs::addLog(Acl::getAuthedUserId(),'Расход баланса пользователя id:'.$op['account_id'],URL_NOW);
			}
			$db->post("
				INSERT INTO ".DB_PREFIX."accounts_history 
				(`account_id`,`sum`,`operation`,`dt`,`comment`) 
				VALUES 
				('".(int)$op['account_id']."','".mysql_real_escape_string($sum)."','".$operation."','".strtotime($op['date'])."','".mysql_real_escape_string($op['comment'])."');
			");
			$this->redirectUrl('/staffcp/accounts/operation/?id='.$op['account_id']);
		}
		
		$sql = "SELECT * FROM ".DB_PREFIX."accounts WHERE id='".(int)$id."';";
		$res = $db->get($sql);
		$this->view->account = $res;
		
		$sql = "SELECT * FROM ".DB_PREFIX."accounts_history WHERE account_id = '".(int)$id."' ORDER BY id DESC;";
		$this->view->operations = $db->query($sql);
	}
	/* */

	public function beforeAction(){
		parent::beforeAction();
	}
	
	public function beforeRender(){
		parent::beforeRender();
	}
}
?>