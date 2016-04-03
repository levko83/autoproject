<?php
class CatModel extends Orm {
	static $parent_set;
	static $tree = array();
	static $next = array();
	static $bread_crumbs = array();
	public function __construct(){
		parent::__construct(DB_PREFIX."cat");
	}
	public static function getHomeView(){
		$model = new CatModel();
		return $model->select()->fields("id,name,home_catname,url")->where("home_view = 1 AND is_active='1'")->order("parent ASC,home_catname")->fetchAll();
	}
	public static function getById($id){
		$model = new CatModel();
		return $model->select()->where("id=?",(int)$id)->fetchOne();
	}
	public static function getBackgroundCategory($id){
		$model = new CatModel();
		return $model->select()->fields('background_image')->where("id=?",(int)$id)->fetchOne();
	}
	public static function getFirst(){
		$model = new CatModel();
		return $model->select()->where("parent='0' and is_active='1' AND is_body_module IN (0,".INSTALL_BODY_MODULE.")")->order("sort,name")->fetchOne();
	}
	public static function getFirstLevel(){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cat C1 WHERE parent='0' and is_active='1' AND is_body_module IN (0,".INSTALL_BODY_MODULE.") ORDER BY sort,name;";
		return $db->query($sql);
	}
	public static function getSecondLevel($id){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."cat C1 WHERE parent='".(int)$id."' and is_active='1' AND is_body_module IN (0,".INSTALL_BODY_MODULE.") ORDER BY sort,name;";
		return $db->query($sql);
	}
	public static function getSecondLevelLimit($id,$limit=5){
		$db = Register::get('db');
		$sql = "SELECT id,name,img FROM ".DB_PREFIX."cat C1 WHERE parent='".(int)$id."' and is_active='1' AND is_body_module IN (0,".INSTALL_BODY_MODULE.") ORDER BY sort,name LIMIT 0,".(int)$limit.";";
		return $db->query($sql);
	}
	public static function getLevelsBack($id) {
		$model = new CatModel();
		$ids = $model->select("id,name,parent")->where("id='".(int)$id."' and is_active='1' AND is_body_module IN (0,".INSTALL_BODY_MODULE.")")->fetchOne();
		CatModel::$tree []= (int)$ids['id'];
		CatModel::$bread_crumbs []= array("id"=>(int)$ids['id'],"name"=>$ids['name']);
		if (!empty($ids['parent'])) {
			CatModel::getLevelsBack($ids['parent']);
		}
	}
	public static function getLevelsNext($id) {
		$db = Register::get('db');
		$sql = "
		SELECT 
			id,parent 
		FROM ".DB_PREFIX."cat 
		WHERE 
			id='".(int)$id."' AND 
			is_active='1' AND 
			is_body_module IN (0,".INSTALL_BODY_MODULE.")
		;";
		$getAll = $db->query($sql);
		if (isset($getAll) && count($getAll)>0){
			foreach ($getAll as $ID) {
				CatModel::$next []= (int)$ID['id'];
				CatModel::GetTreeNext($ID['id']);
			}
		}
	}
	public static function GetTreeNext($id=0){
		$db = Register::get('db');
		$sql = "SELECT id,parent FROM ".DB_PREFIX."cat WHERE parent = '".(int)$id."';";
		$getAll = $db->query($sql);
		if (isset($getAll) && count($getAll)>0){
			foreach ($getAll as $ID) {
				CatModel::$next []= (int)$ID['id'];
				CatModel::GetTreeNext($ID['id']);
			}
		}
	}
}
?>