<?php
class To_info_generatorController  extends CmsGenerator {
	
	public $layout = 'global';
	private $db;
	
	function __construct(){
		$this->db = $db = Register::get('db');
	}
	
	public function index(){
		
		
		$title = 'Генератор мета данных для каталога ТО';
		$this->view->title = $title;
		$this->addBreadCrumb($title,'/staffcp/to_info_generator/');
	}
	
	public function generate(){
		
		$category = $this->request("category",false);
		$title = $this->request("title",false);
		$kwords = $this->request("kwords",false);
		$descr = $this->request("descr",false);
		
		if ($category){
		
			if ($category == 'to_cars'){
				/* 1 ********************************************* */
				$sql = "SELECT id,name FROM ".DB_PREFIX."to_cars;";
				$data = $this->db->query($sql);
				foreach ($data as $dd){
					
					$titleNew = (str_replace("{name}", $dd['name'], $title));
					$kwordsNew = (str_replace("{name}", $dd['name'], $kwords));
					$descrNew = (str_replace("{name}", $dd['name'], $descr));
					
					$this->db->post("
						UPDATE ".DB_PREFIX."to_cars SET 
							title = '".mysql_real_escape_string($titleNew)."',
							kwords = '".mysql_real_escape_string($kwordsNew)."',
							descr = '".mysql_real_escape_string($descrNew)."'
						WHERE id = '".mysql_real_escape_string($dd['id'])."';
					");
				}
			}
			
			if ($category == 'to_models'){
				/* 2 ********************************************* */
				$sql = "
					SELECT 
						M.id id,
						M.name m_alias,
						C.name c_alias 
					FROM ".DB_PREFIX."to_models M
					LEFT JOIN ".DB_PREFIX."to_cars C ON (M.car_id = C.id)
				;";
				$data = $this->db->query($sql);
				foreach ($data as $dd){
					
					$titleNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'], $title));
					$kwordsNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'], $kwords));
					$descrNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'], $descr));
					
					$this->db->post("
						UPDATE ".DB_PREFIX."to_models SET 
							title = '".mysql_real_escape_string($titleNew)."',
							kwords = '".mysql_real_escape_string($kwordsNew)."',
							descr = '".mysql_real_escape_string($descrNew)."'
						WHERE id = '".mysql_real_escape_string($dd['id'])."';
					");
				}
			}
			
			if ($category == 'to_types'){
				/* 3 ********************************************* */
				$sql = "
					SELECT
						T.id id, 
						T.name t_alias, 
						M.name m_alias,
						C.name c_alias 
					FROM ".DB_PREFIX."to_types T
					LEFT JOIN ".DB_PREFIX."to_models M ON (T.model_id = M.id)
					LEFT JOIN ".DB_PREFIX."to_cars C ON (M.car_id = C.id)
				;";
				$data = $this->db->query($sql);
				foreach ($data as $dd){
					
					$titleNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'].' '.$dd['t_alias'], $title));
					$kwordsNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'].' '.$dd['t_alias'], $kwords));
					$descrNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'].' '.$dd['t_alias'], $descr));
					
					$this->db->post("
						UPDATE ".DB_PREFIX."to_types SET 
							title = '".mysql_real_escape_string($titleNew)."',
							kwords = '".mysql_real_escape_string($kwordsNew)."',
							descr = '".mysql_real_escape_string($descrNew)."'
						WHERE id = '".mysql_real_escape_string($dd['id'])."';
					");
				}
			}
			
			if ($category == 'to'){
				/* 4 ********************************************* */
				$sql = "
					SELECT
						TOC.id id, 
						TOC.descr to_alias,
						T.name t_alias,
						M.name m_alias,
						C.name c_alias 
					FROM ".DB_PREFIX."to TOC
					LEFT JOIN ".DB_PREFIX."to_types T ON (TOC.type_id = T.id)
					LEFT JOIN ".DB_PREFIX."to_models M ON (T.model_id = M.id)
					LEFT JOIN ".DB_PREFIX."to_cars C ON (M.car_id = C.id)
				;";
				
				$data = $this->db->query($sql);
				foreach ($data as $dd){
					
					$titleNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'].' '.$dd['t_alias'].' '.$dd['to_alias'], $title));
					$kwordsNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'].' '.$dd['t_alias'].' '.$dd['to_alias'], $kwords));
					$descrNew = (str_replace("{name}", $dd['c_alias'].' '.$dd['m_alias'].' '.$dd['t_alias'].' '.$dd['to_alias'], $descr));
					
					$this->db->post("
						UPDATE ".DB_PREFIX."to SET 
							seo_title = '".mysql_real_escape_string($titleNew)."',
							seo_kwords = '".mysql_real_escape_string($kwordsNew)."',
							seo_descr = '".mysql_real_escape_string($descrNew)."'
						WHERE id = '".mysql_real_escape_string($dd['id'])."';
					");
				}
			}
			
			$this->redirectUrl('/staffcp/to_info_generator/done/');
		}
	}
	
	function done(){
		
	}
}
?>