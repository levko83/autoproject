<?php

class SearchnotepadModel extends Orm {
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'search_notepad');
	}
	
	public static function getById($id) {
		$model = new SearchnotepadModel();
		return $model->select()->where("id=?",(int)($id))->fetchOne();
	}
	
	public static function getSearchNotes($id) {
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."search_notepad WHERE account_id = '".(int)$id."' ORDER BY brand,article;";
		$query = $db->query($sql);
		return $query;
	}
	
	public static function getSearchNotesSimple($id) {
		$db = Register::get('db');
		$sql = "SELECT id,article,brand FROM ".DB_PREFIX."search_notepad WHERE account_id = '".(int)$id."' ORDER BY brand,article;";
		$query = $db->query($sql);
		$convert = array();
		if (isset($query) && count($query)>0){
			foreach ($query as $dd){
				$convert [FuncModel::stringfilter($dd['article'])][FuncModel::stringfilter($dd['brand'])]= $dd['id'];
			}
		}
		return $convert;
	}
}
?>