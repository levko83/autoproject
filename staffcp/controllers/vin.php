<?php

class vinController  extends CmsGenerator {

	public function edit() {
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела - запрос по VIN',URL_NOW);
		
		$this->prepareEditData();
		$this->render('vin/edit');
	}

	public function prepareEditData() {
		
		$db = Register::get('db');
		
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		$id = mysql_real_escape_string($id);
		$data = $this->model->select()->where($indexField." = '$id'")->fetchOne();
		
		$sql = "SELECT * FROM ".DB_PREFIX."vin_details WHERE vin_id = '".(int)$id."';";
		$this->view->view_details = $db->query($sql);
	
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
			Logs::addLog(Acl::getAuthedUserId(),'Создание нового запроса по VIN',URL_NOW);
		} else {
			
			$send_mail = $form['send_mail'];
			unset($form['send_mail']);
			
			$this->model->update($form,array($indexField => $id));
			$this->saveddetails($id);
			$this->setAccountAlert($form['account_id'],'vin');
			
			if ($send_mail) {
				
				$MsgHTML ="
					<p>Здравствуйте, ".$form['name']."! Ответ на запрос по Vin от ".$form['dt'].".</p>
					<p><b>Автомобиль:</b></p>
					<p>".$form['mark']."</p>
					<p><b>Запрос:</b></p>
					<p>".$form['message']."</p>
					<p><b>Ответ:</b></p>
					".$form['answer']."
				";
				
				$db = Register::get('db');
				$sql = "SELECT * FROM ".DB_PREFIX."vin_details WHERE vin_id = '".(int)$id."';";
				$vinsdets = $db->query($sql);
				if (isset($vinsdets) && count($vinsdets)>0){
					$MsgHTML .= '<table>';
					$MsgHTML .= '<tr>';
						$MsgHTML .= '<th>Артикул</th>';
						$MsgHTML .= '<th>Бренд</th>';
						$MsgHTML .= '<th>Название</th>';
						$MsgHTML .= '<th>Кол-во</th>';
						$MsgHTML .= '<th>Комментарий</th>';
					$MsgHTML .= '</tr>';
					foreach ($vinsdets as $dds){
					$MsgHTML .= '<tr>';
						$MsgHTML .= '<td>'.$dds['article'].'</td>';
						$MsgHTML .= '<td>'.$dds['brand'].'</td>';
						$MsgHTML .= '<td>'.$dds['name'].'</td>';
						$MsgHTML .= '<td>'.$dds['box'].'</td>';
						$MsgHTML .= '<td>'.$dds['comment'].'</td>';
					$MsgHTML .= '</tr>';
					}
					$MsgHTML .= '</table>';
				}
				
				$from = SettingsModel::get('contact_email');
				$mail = new Phpmailer();
				$mail->From     = $from;
				$mail->FromName = 'Администратор сайта '.$_SERVER['HTTP_HOST'];
				$mail->Subject  = 'Запрос по Vin -> Ответ '.$_SERVER['HTTP_HOST'];
				$mail->MsgHTML($MsgHTML);
				$mail->AddAddress($form['email']);
				$mail->Send();
				$mail->ClearAddresses();
			}
			
			Logs::addLog(Acl::getAuthedUserId(),'Редактирование запроса по VIN id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName(),$params);
	}
	
	function saveddetails($sid=0){
		$db = Register::get('db');
		
		$detailssaved = $this->request("detailssaved",false);
		if (isset($detailssaved) && count($detailssaved)>0){
			foreach ($detailssaved as $key=>$dd){
				$db->post("
				UPDATE ".DB_PREFIX."vin_details 
				SET 
					article='".mysql_real_escape_string($dd['article'])."',
					brand='".mysql_real_escape_string($dd['brand'])."',
					name='".mysql_real_escape_string($dd['name'])."',
					box='".mysql_real_escape_string($dd['box'])."',
					comment='".mysql_real_escape_string($dd['comment'])."'
				WHERE id='".(int)$key."';");
				
				if (isset($dd['delete']) && $dd['delete']){
					$db->post("DELETE FROM ".DB_PREFIX."vin_details WHERE id = '".(int)$dd['delete']."';");
				}
			}
		}
		
		$new_detailssaved = $this->request("new_detailssaved",array());
		if (isset($new_detailssaved['article']) && count($new_detailssaved['article'])>0){
			foreach ($new_detailssaved['article'] as $k=>$a){
				
				$b = $new_detailssaved['brand'][$k];
				$n = $new_detailssaved['name'][$k];
				$b2 = $new_detailssaved['box'][$k];
				$c = $new_detailssaved['comment'][$k];
				
				$db->post("
				INSERT INTO ".DB_PREFIX."vin_details 
					(`vin_id`,`article`,`brand`,`name`,`box`,`comment`)
				VALUES
					('".(int)$sid."','".mysql_real_escape_string($a)."','".mysql_real_escape_string($b)."','".mysql_real_escape_string($n)."','".mysql_real_escape_string($b2)."','".mysql_real_escape_string($c)."');
				");
			}
		}
	}
}

?>