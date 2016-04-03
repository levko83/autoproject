<?php

class ImportersController  extends CmsGenerator {
	
	public $layout = 'global';
	
	public function index(){
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела списка поставщиков',URL_NOW);
		
		$db = Register::get('db');
		$sql = "
			SELECT DISTINCT 
				country,COUNT(id) C
			FROM ".DB_PREFIX."importers 
			GROUP BY
				country
			ORDER BY country;";
		$this->view->countries = $db->query($sql);
		
		$this->prepareIndexData();
		$this->render('importers/list');
	}
	public function delete(){
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		if (!empty($id)){
			$this->model->delete(array($indexField => $id));
			$this->deleter($id);
			Logs::addLog(Acl::getAuthedUserId(),'Удаление поставщика id:'.$id,URL_NOW);
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	public function delete_list(){
		$indexField = $this->dataModel->getIndexField();
		$ids = $this->request("delete_list",0);
		if (!empty($ids)) {
			foreach ($ids as $id) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
					$this->deleter($id);
				}
				Logs::addLog(Acl::getAuthedUserId(),'Удаление поставщика id:'.$id,URL_NOW);
			}	
		}
		$this->redirect('index',$this->dataModel->getModelName());
	}
	private function deleter($id){
		$db = Register::get('db');
		$db->query("DELETE FROM ".DB_PREFIX."details WHERE IMPORT_ID='".(int)$id."';");	
		$db->query("DELETE FROM ".DB_PREFIX."details_cross WHERE IMPORT_ID='".(int)$id."';");
	}
	
	public function pchart() {
		
		Logs::addLog(Acl::getAuthedUserId(),'Формирование статистики заказов по поставщикам',URL_NOW);
		
		$db = Register::get('db');
		$sql = "
		SELECT 
			IMP.id,
			IMP.name_price,
			IMP.delivery,
			(SELECT COUNT(*) FROM ".DB_PREFIX."cart CART WHERE CART.import_id=IMP.id) allorders, 
			(SELECT COUNT(*) FROM ".DB_PREFIX."cart CART LEFT JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=CART.status WHERE CART.import_id=IMP.id AND DS.type=2) doneorders,
			(SELECT COUNT(*) FROM ".DB_PREFIX."cart CART LEFT JOIN ".DB_PREFIX."dic_statuses DS ON DS.id=CART.status WHERE CART.import_id=IMP.id AND DS.type=3) denyorders
		FROM ".DB_PREFIX."importers IMP;";
		$result = $db->query($sql);
		
		
		require("../core/services/pChart/pChart/pData.class");
		require("../core/services/pChart/pChart/pChart.class");
		
		foreach ($result as $importer) {
			
			#allorders
			#doneorders
			#denyorders
			
			$win_deals = ($importer['doneorders']?$importer['doneorders']*100/$importer['allorders']:1);
			$lost_deals = ($importer['denyorders']?$importer['denyorders']*100/$importer['allorders']:0);
			
			// Dataset definition 
			$DataSet = new pData;
			$DataSet->AddPoint(array($win_deals,$lost_deals),"Serie1");
			$DataSet->AddPoint(array("Выдано","Отказано"),"Serie2");
			$DataSet->AddAllSeries();
			$DataSet->SetAbsciseLabelSerie("Serie2");
			
			// Initialise the graph
			$pC = new pChart(420,250);
			$pC->drawFilledRoundedRectangle(7,7,413,243,5,240,240,240);
			$pC->drawRoundedRectangle(5,5,415,245,5,230,230,230);
			$pC->createColorGradientPalette(195,204,56,223,110,41,5);
			
			// Draw the pie chart
			$pC->setFontProperties("../core/services/pChart/Fonts/tahoma.ttf",8);
			$pC->AntialiasQuality = 0;
			$pC->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),200,130,110,PIE_PERCENTAGE_LABEL,FALSE,50,20,5);
			$pC->drawPieLegend(330,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);
		
			// Write the title
			$pC->setFontProperties("../core/services/pChart/Fonts/tahoma.ttf",10);
			$pC->drawTitle(10,20,"Качество выдачи данным поставщиком.",100,100,100);
			$pC->drawTitle(10,35,"Код: ".$importer['name_price'],100,100,100);
			$pC->drawTitle(10,50,"Ожидаемый срок, дн.: ". $importer['delivery'],100,100,100);
		
			$pic = md5($importer['id']).'.png';
			$pC->Render('../cache/'.$pic);
		}
		
		$this->view->result = $result;
	}
}
?>