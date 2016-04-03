<?php

class PchartController  extends BaseController {
	
	public $layout = 'ajax';
	
	public function index() {
		
		$id = (int)$this->request("id",0);
		if (!$id)
			exit();
			
		$importer = ImportersModel::getById($id);
		
		require("../core/services/pChart/pChart/pData.class");
		require("../core/services/pChart/pChart/pChart.class");
		
		// Dataset definition 
		$DataSet = new pData;
		$DataSet->AddPoint(array(1,0.009),"Serie1");
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
		$pC->Render('cache/'.$pic);
		echo '<img src="/cache/'.$pic.'"/>';
		exit();
	}
	
	function beforeAction(){
		parent::beforeAction();
	}
	function beforeRender() {
		parent::beforeRender();
	}
}