<?php

class CatalogController  extends BaseController {
	
	public $layout = 'home';
	
	public function index() {
		
		$filter = $this->request("filter",false);
		$this->view->filter = $filter;
		
		$body = $this->request("body",false);
		$this->view->body = $body;
		
		$cList = array();
		$countries = ManufacturersModel::AllCountries($filter,$body);
		if (isset($countries) && count($countries)>0){
			$i=0; $j=10; foreach ($countries as $country){
				if ($country['country'] == 'Европа'){ $i++;
					$cList [$i]= $country;
				}
				else { $j++;
					$cList [$j]= $country;
				}
			}
			ksort($cList);
		}
		$this->view->allcountries = $cList;
		
		$allcountries = array();
		$mfa_index = ManufacturersModel::All($filter,$body);
		if (isset($mfa_index) && count($mfa_index)>0){
			foreach ($mfa_index as $dd){
				$allcountries [$dd['country']][]= $dd;
			}
		}
		$this->view->marks = $allcountries;
		$this->view->page_info = SettingsModel::get('page_catalogauto');
		
		$this->view->searchbyletters = ManufacturersModel::getMarksLettersAll($body);
		
		$this->breadcrumbs ['Catalog']= '#';
	}
	function beforeAction(){
		parent::beforeAction();
	}
	function beforeRender(){
		parent::beforeRender();
	}
}
?>