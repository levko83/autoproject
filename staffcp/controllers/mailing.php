<?php

class MailingController  extends CmsGenerator {
	
	public $layout = 'global';
	
	function index() {
		
		$this->prepareIndexDataMailing();
		$this->prepareIndexDataEmails();
		
		$this->view->mailing_accounts = AccountsModel::getAllScribers();
	}
	
	public function prepareIndexDataMailing()
	{
		$this->dataModel = new CmsGeneratorConfig('mailing');
		$this->model = new CmsGeneratorModel($this->dataModel);
		
		$this->view->title = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();

		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles = $fieldTitles;
		
		$this->view->addUrl = '/staffcp/mailing/add/';
		$this->view->addTitle = $this->dataModel->getAddTitle();

		$listIds = $this->view->acl->getListIds($this->controller);
		
		$this->view->data = $this->model->select()->fetchAll();
		
		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/staffcp/'.$this->dataModel->getModelName());
	}
	
	public function prepareIndexDataEmails()
	{
		$this->dataModel = new CmsGeneratorConfig('mailing_emails');
		$this->model = new CmsGeneratorModel($this->dataModel);
		
		$this->view->title_emails = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();

		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles_emails = $fieldTitles;
		
		$this->view->addUrl_emails = '/staffcp/mailing_emails/add/';
		$this->view->addTitle_emails = $this->dataModel->getAddTitle();

		$listIds = $this->view->acl->getListIds($this->controller);
		
		$this->view->data_emails = $this->model->select()->fetchAll();
		
		$this->view->dataModel_emails = $this->dataModel;
		$this->view->indexField_emails = $this->dataModel->getIndexField();
	}
	
	public function save()
	{
		$form = $this->request('form');
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
			
		$form = $this->trimA($form);
		
		$sended = $this->mailing($form);
		$form['sended'] = $sended;
		$form['dt_sended'] = date("Y-m-d H:i:s");
		
		if (empty($id))
		{
			$this->model->insert($form);
		} else {
			$this->model->update($form,array($indexField => $id));
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Создание рассылки',URL_NOW);
		$this->redirect('index',$this->dataModel->getModelName());
	}
	
	function mailing($data) {
		
		$subject = $data['name'];
		$letter = $data['content'];
		
		if (empty($data['file'])) {
			if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name']))
				$file = $_FILES['file']['name'];
		}
		else
			$file = $data['file'];
		
		$mail = new Phpmailer();
		
		$sEmails = array();
		$mailing_list = Mailing_emailsModel::getAll();
		if (isset($mailing_list)&&count($mailing_list)>0){
			foreach ($mailing_list as $m){
				$sEmails []= $m['email'];
			}
		}
		$mailing_accounts = AccountsModel::getAllScribers();
		if (isset($mailing_accounts)&&count($mailing_accounts)>0){
			foreach ($mailing_accounts as $m){
				$sEmails []= $m['email'];
			}
		}
		
		#exit();
		
		if (count($sEmails)>0) {
			foreach ($sEmails as $mailing) {
				
				$deny_data = array();
				$deny_data ['deny']= 'http://'.$_SERVER['SERVER_NAME'].'/mailing/deny/'.base64_encode($mailing).'/';
				$deny = $this->deny($deny_data);
				unset($deny_data);
				
				$messageHTML = $letter.$deny;
				$messageHTML = str_replace(array("../../../"),"http://".$_SERVER['SERVER_NAME']."/",$messageHTML);
				$messageHTML = str_replace(array("/media"),"http://".$_SERVER['SERVER_NAME']."/media",$messageHTML);
		
				$mail->From     = 'no-reply@'.$_SERVER['HTTP_HOST'];
				$mail->FromName = 'Рассылка с сайт '.$_SERVER['HTTP_HOST'];
				$mail->Subject  = $subject;
				if ($file)
					$mail->AddAttachment('../media/files/mailing/'.$file,$file);
				$mail->MsgHTML($messageHTML);
				$mail->AddAddress($mailing);
				$mail->Send();
				$mail->ClearAddresses();
				
				unset($messageHTML);
			}
		}
		
		#exit();
		
		return count($sEmails);
	}
	
	function deny($vars) {
		/* deny scribe */
		$model = new EmailsModel();
		$data = $model->select()->where("code=?",'sribe_deny')->fetchOne();
		$letter = $data['value'];
		foreach ($vars as $kk=>$vv) {
			$str = '{'.$kk.'}';
			$letter = str_replace($str,$vv,$letter);
		}
		return $letter;
	}
	
	/* */
	function import(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Импорт базы подписчиков',URL_NOW);
		
		$db = Register::get('db');
		$file = isset($_FILES['xlsx']['name'])?$_FILES['xlsx']['name']:false;
		if ($file){
			$ext = strtolower(array_pop(explode(".", basename($file))));
			if ($ext == 'xlsx'){
				$saveFile = $_FILES['xlsx']['name'];
				if (move_uploaded_file($_FILES['xlsx']['tmp_name'], '../cache/'.$saveFile)){

					require_once '../xreaders/readers/simplexlsx.class.php';
					$xlsx = new SimpleXLSX('../cache/'.$saveFile);
					
					$i=0;
                    $valuse1 = array();
                    foreach( $xlsx->rows() as $res ) {
                    	
						$email = $res[0];
						$name = $res[1];
						
						if ($this->chk($email)){
							
							if ($email){
								
                                $i++;
								$valuse1[] = "('".mysql_real_escape_string($name)."','".mysql_real_escape_string($email)."','1','".mktime()."')";
							}
						}
					}

                    if(!empty($valuse1)){
                        $db->post("INSERT INTO w_mailing__emails (`name`,`email`,`is_active`,`dt`)
                                    VALUES ".join(",",$valuse1).";");
                    }
                    
					echo("<h1>Finished! (".$i.")</h1>");
					exit();
					
				} else {
					echo("<h1>Upload error!</h1>");
					exit();
				}
				
			} else {
				echo("<h1>File type error!</h1>");
				exit();
			}
			
		} else {
			echo("<h1>File error!</h1>");
			exit();
		}
	}
	
	function chk($email){
		$db = Register::get('db');
		$sql = "SELECT * FROM w_mailing__emails WHERE email LIKE '".mysql_real_escape_string($email)."';";
		$res = $db->get($sql);
		if ($res)
			return false;
		else 
			return true;
	}
	
	function beforeAction() {
		parent::beforeAction();
	}
	
	function beforeRender() {
		parent::beforeRender();
	}
}