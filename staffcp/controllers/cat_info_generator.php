<?php
class Cat_info_generatorController  extends CmsGenerator {
	
	public $layout = 'global';
	private $db;
	
	function __construct(){
		$this->db = $db = Register::get('db');
	}
	
	public function index(){
		
		unset($_SESSION['_pgenerator']);
		$this->view->firstlevel = CatModel::getSecondLevel(0);
		
		$title = 'Генератор мета данных для каталога товаров';
		$this->view->title = $title;
		$this->addBreadCrumb($title,'/staffcp/cat_info_generator/');
	}
	
	public function generate(){
		
		$per_page = 1000;
		$this->view->per_page = $per_page;
		
		$page = $this->request("page",1);
		$this->view->page = $page;
		
		$category_id = $this->request("category_id",((isset($_SESSION['_pgenerator']['category_id']) && $_SESSION['_pgenerator']['category_id'])?$_SESSION['_pgenerator']['category_id']:false));
		
// 		var_dump($category_id);
// 		exit();
		
		if ($category_id){
				
			$title = $this->request("title",((isset($_SESSION['_pgenerator']['title']) && $_SESSION['_pgenerator']['title'])?$_SESSION['_pgenerator']['title']:false));
			$kwords = $this->request("kwords",((isset($_SESSION['_pgenerator']['kwords']) && $_SESSION['_pgenerator']['kwords'])?$_SESSION['_pgenerator']['kwords']:false));
			$descr = $this->request("descr",((isset($_SESSION['_pgenerator']['descr']) && $_SESSION['_pgenerator']['descr'])?$_SESSION['_pgenerator']['descr']:false));
			$use_category_names = $this->request("use_category_names",((isset($_SESSION['_pgenerator']['use_category_names']) && $_SESSION['_pgenerator']['use_category_names'])?$_SESSION['_pgenerator']['use_category_names']:false));
				
			$_SESSION['_pgenerator']=array(
					'category_id' => $category_id,
					'title' => $title,
					'kwords' => $kwords,
					'descr' => $descr,
					'use_category_names' => $use_category_names,
			);
				
			$catModel = new CatModel();
			$catModel->getLevelsNext($category_id);
			$allIds = CatModel::$next;
				
			$direct = 0; if (isset($allIds) && count($allIds)>0){
		
				/* ТОВАРЫ */
				$sql = "SELECT COUNT(*) CC FROM ".DB_PREFIX."products WHERE fk IN (".join(",", $allIds).");";
				$count = $this->db->get($sql);
				$pages_num = (int)(($count['CC'] - 1) / $per_page) + 1;
				$this->view->pages_num = $pages_num;
				$this->view->totalitems = $count['CC'];
				
				if ($pages_num == $page){
					unset($_SESSION['_pgenerator']);
				}
		
				$page = ($page - 1)*$per_page;
				$sql = "SELECT id,fk,name,article FROM ".DB_PREFIX."products WHERE fk IN (".join(",", $allIds).") LIMIT $page,$per_page;";
				$res = $this->db->query($sql);
		
				if (isset($res) && count($res)>0){
						
					$sql = "";
					$i = 0; foreach ($res as $product){ $i++; $direct++;
		
						$newName = '';
						if ($use_category_names){
							
							CatModel::$titles = array();
							$catModel->getLevelsBack($product['fk']);
							$road = CatModel::$titles;
							if (count($road)>0)
								arsort($road);
							
							$newName .= join(" ", $road).' ';
						}
						$newName .= $product['name'];
			
						$titleNew = (str_replace("{name}", $newName, $title));
						$kwordsNew = (str_replace("{name}", $newName, $kwords));
						$descrNew = (str_replace("{name}", $newName, $descr));
			
						$sql = "UPDATE ".DB_PREFIX."products SET title='".mysql_real_escape_string($titleNew)."', kwords='".mysql_real_escape_string($kwordsNew)."', descr='".mysql_real_escape_string($descrNew)."' WHERE id='".(int)$product['id']."'; ";
						$this->db->post($sql);
					}
				}
		
				/* КАТЕГОРИИ */
				/* надо доделать */
			}
		}
		else 
			$this->redirectUrl("/staffcp/cat_info_generator/");
	}
	
	public function cancel(){
		unset($_SESSION['_pgenerator']);
		$this->redirectUrl("/staffcp/cat_info_generator/");
	}
}
?>