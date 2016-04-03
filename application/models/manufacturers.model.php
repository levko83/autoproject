<?php

class ManufacturersModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'manufacturers');
	}
	
	public static function getByID($id) {
		$model = new ManufacturersModel();
		return $model->select()->where("MFA_ID=?",(int)$id)->fetchOne();
	}
	
	public static function All($filter=false,$body=false) {
		
		$iBody = '';
		switch ($body){
			case 'passenger': $iBody =" AND MFA_CV_MFC IN (0,1)"; break;
			case 'truck': $iBody =" AND MFA_CV_MFC = 1"; break;
		}
		
		$model = new ManufacturersModel();
		if ($filter){
			return $model->select()->where("MY_ACTIVE=1 AND LEFT(MFA_BRAND,1) LIKE '".mysql_real_escape_string($filter)."' ".$iBody."")->order("MY_SORT,MFA_BRAND")->fetchAll();
		}
		else
			return $model->select()->where("MY_ACTIVE=1 ".$iBody."")->order("MY_SORT,MFA_BRAND")->fetchAll();
	}
	
	public static function AllCountries($filter=false,$body=false) {
		
		$iBody = '';
		switch ($body){
			case 'passenger': $iBody =" AND MFA_CV_MFC IN (0,1)"; break;
			case 'truck': $iBody =" AND MFA_CV_MFC = 1"; break;
		}
		
		$db = Register::get('db');
		if ($filter){
			$sql = "SELECT DISTINCT country FROM ".DB_PREFIX."manufacturers WHERE country !='' AND MY_ACTIVE=1 AND 
				LEFT(MFA_BRAND,1) LIKE '".mysql_real_escape_string($filter)."' ".$iBody." ORDER BY country;";
		}
		else {
			$sql = "SELECT DISTINCT country FROM ".DB_PREFIX."manufacturers WHERE country !='' AND MY_ACTIVE=1 ".$iBody." ORDER BY country;";
		}
		return $db->query($sql);
	}
	
	public static function getAll() {
		$model = new ManufacturersModel();
		return $model->select()->where("MY_ACTIVE=1")->order("MY_SORT,MFA_BRAND")->fetchAll();
	}
	
	public static function getAllPaging($page=1,$per_page=10) {
		$model = new ManufacturersModel();
		$page = ($page-1)*$per_page;
		return $model->select()->where("MY_ACTIVE=1")->order("MY_SORT,MFA_BRAND")->limit($page,$per_page)->fetchAll();
	}
	
	public static function getPaging() {
		$db = Register::get('db');
		$sql = "select count(*) cc from ".DB_PREFIX."manufacturers where MY_ACTIVE=1;";
		$result = $db->get($sql);
		return (isset($result['cc']))?$result['cc']:0;
	}
	
	public static function getAllPagingParams($params) {
		$iWhere='';
		if (isset($params)&&is_array($params)>0){
			foreach ($params as $param=>$value){
				$iWhere .= " AND `".$param."`='".mysql_real_escape_string($value)."'";
			}
		}
		$model = new ManufacturersModel();
		return $model->select()->where("1=1 ".$iWhere)->order("MY_SORT,MFA_BRAND")->fetchAll();
	}
	
	/* ************ */
	
	public static function getMarksLetters(){
		$db = Register::get('db');
		$sql = "
			SELECT 
				DISTINCT(SUBSTR(MFA_BRAND, 1, 1)) LETTER,
				COUNT(SUBSTR(MFA_BRAND, 1, 1)) CC 
			FROM `".DB_PREFIX."manufacturers` 
			WHERE MY_ACTIVE=1 AND MY_DEFAULT=1
			GROUP BY LETTER;";
		return $db->query($sql);
	}
	
	public static function getMarksByLetter($letter=''){
		$db = Register::get('db');
		$sql = "
			SELECT
				marks.*
			FROM `".DB_PREFIX."manufacturers` marks
			WHERE
				marks.MY_ACTIVE=1 AND marks.MY_DEFAULT=1 AND 
				LEFT(marks.MFA_BRAND,1) LIKE '%".mysql_real_escape_string($letter)."%' 
			ORDER BY marks.MFA_BRAND;";
		return $db->query($sql);
	}
	public static function getMarksByLetterLogo($letter=''){
		$db = Register::get('db');
		$sql = "
			SELECT
				marks.* 
			FROM `".DB_PREFIX."manufacturers` marks
			WHERE
				marks.MY_ACTIVE=1 AND withlogo=1 AND marks.MY_DEFAULT=1 AND 
				LEFT(marks.MFA_BRAND,1) LIKE '%".mysql_real_escape_string($letter)."%' 
			ORDER BY marks.MFA_BRAND;";
		return $db->query($sql);
		
	}
	public static function getMarksByLetterLkw($letter=''){
		$db = Register::get('db');
		$sql = "
			SELECT
				marks.*
			FROM `".DB_PREFIX."manufacturers` marks
			WHERE
				marks.MY_ACTIVE=1 AND marks.MY_DEFAULT=1 AND marks.inindex=1 AND marks.lkw=1 AND 
				LEFT(marks.MFA_BRAND,1) LIKE '%".mysql_real_escape_string($letter)."%' 
			ORDER BY marks.MFA_BRAND;";
		return $db->query($sql);
	}
	public static function getMarksByLetterLkwLogo($letter=''){
		$db = Register::get('db');
		$sql = "
			SELECT
				marks.* 
			FROM `".DB_PREFIX."manufacturers` marks
			WHERE
				marks.MY_ACTIVE=1 AND withlogo=1 AND marks.MY_DEFAULT=1 AND marks.inindex=1 AND marks.lkw=1 AND 
				LEFT(marks.MFA_BRAND,1) LIKE '%".mysql_real_escape_string($letter)."%' 
			ORDER BY marks.MFA_BRAND;";
		return $db->query($sql);
	}
	
	public static function getMarksLettersAll($body=false){
		
		$iBody = '';
		switch ($body){
			case 'passenger': $iBody =" AND MFA_CV_MFC IN (0,1)"; break;
			case 'truck': $iBody =" AND MFA_CV_MFC = 1"; break;
		}
		
		$db = Register::get('db');
		$sql = "
			SELECT 
				DISTINCT(SUBSTR(MFA_BRAND, 1, 1)) LETTER,
				COUNT(SUBSTR(MFA_BRAND, 1, 1)) CC 
			FROM `".DB_PREFIX."manufacturers` 
			WHERE MY_ACTIVE=1 ".$iBody."
			GROUP BY LETTER;";
		return $db->query($sql);
	}
}
?>