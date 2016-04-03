<?php

//set_time_limit(1800);
// error_reporting(E_ALL | E_STRICT);
// ini_set('display_errors', 'On');

class Harvester_ftpController  extends CmsGenerator {
	
	public $delimeter = null;
	public $mtime = '';
	public $layout = 'global';
	public $brands = array();
	
	protected $base_dir = '../xreaders/cache_emails/';
	protected $dataModel = null;
	protected $model = null;
	
	function __construct(){
		$this->mtime = microtime();
	}
	
	function index() {
		
		$orm = new Orm(DB_PREFIX.'harverster_ftp__params');
		$ormTasks = new Orm(DB_PREFIX.'harverster_ftp__tasks');
			
		$this->view->list =
			$orm->
				select()->
					fields(DB_PREFIX.'harverster_ftp__params.*,'.DB_PREFIX.'importers.name importer')->
						join(DB_PREFIX.'importers',DB_PREFIX.'harverster_ftp__params.importer_id='.DB_PREFIX.'importers.id')->
							fetchAll();
			
		$this->view->tasks =
			$ormTasks->
				select()->
					fields(
							DB_PREFIX.'harverster_ftp__params.*,'.
							DB_PREFIX.'harverster_ftp__tasks.id task_id,'.
							DB_PREFIX.'harverster_ftp__tasks.filename task_filename,'.
							DB_PREFIX.'harverster_ftp__tasks.parent_id task_parent_id,'.
							DB_PREFIX.'harverster_ftp__tasks.dt task_dt,'.
							DB_PREFIX.'harverster_ftp__tasks.err task_err,'.
							DB_PREFIX.'harverster_ftp__tasks.status task_status'
					)->
						join(DB_PREFIX.'harverster_ftp__params',DB_PREFIX.'harverster_ftp__params.id='.DB_PREFIX.'harverster_ftp__tasks.parent_id')->
							order('dt DESC')->
								fetchAll();
		
		// НАЧАЛО ДОБАВИТЬ ПОДКЛЮЧЕНИЕ FTP
		$form = $this->request("form",false);
		if ($form){
			$orm->insert($form);
			$this->redirectUrl("/staffcp/harvester_ftp/");
		}
		// КОНЕЦ
		
		// НАЧАЛО УДАЛИТЬ ЗАДАЧУ
		$delete_task_id = $this->request("delete_task_id",false);
		if ($delete_task_id){
		
			$filename = $ormTasks->select()->fields("filename")->where("id=?",(int)$delete_task_id)->fetchOne();
			@unlink($this->base_dir.$filename['filename']);
		
			$ormTasks->delete(array("id"=>(int)$delete_task_id));
			$this->redirectUrl("/staffcp/harvester_ftp/");
		}
		// КОНЕЦ
		
		// НАЧАЛО УДАЛИТЬ ПОДКЛЮЧЕНИЕ FTP
		$delete_param_id = $this->request("delete_param_id",false);
		if ($delete_param_id){
			$orm->delete(array("id"=>(int)$delete_param_id));
			$this->redirectUrl("/staffcp/harvester_ftp/");
		}
		// КОНЕЦ
		
		
		// НАЧАЛО ОБРАБОТКА ПОДКЛЮЧЕНИЕ ПО FTP, ЧТЕНИЕ И ЗАПИСЬ ФАЙЛА
		$do = $this->request("do",false);
		if ($do){
			$params = $orm->select()->where("id=?",(int)$do)->fetchOne();
			$this->view->params = $params;
			
			$ftpcrawler = new Ftpcrawler();
			$ftpcrawler->server = "ftp://".$params['hlogin'].":".$params['hpass']."@".$params['host'].":21".$params['hsearch'];
			
			$save_file = $this->request("save_file",false);
			if ($save_file){
				$save_file_rename = date("d.m.Y").'_id'.$do.'_'.str_replace("//", "", $save_file);
				$file_saved = $ftpcrawler->savefile($this->base_dir.$save_file_rename, $save_file);
				if ($file_saved){
					$ormTasks->insert(array('filename'=>$file_saved,'parent_id'=>$do,'dt'=>time(),'status'=>'Файл записан, ожидает обработки / Время '.ceil($this->totaltime()).'s.'));
				}else{
					$ormTasks->insert(array('filename'=>$file_saved,'parent_id'=>$do,'dt'=>time(),'err'=>'Ошибка записи файла'));
				}
				$this->redirectUrl("/staffcp/harvester_ftp/");
			}
			
			$this->view->ftp_result = $ftpcrawler->crawl("html");
		}
		// КОНЕЦ
	    
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела обработки прайсов с FTP',URL_NOW);
	}
	
	private function totaltime() {
		$mtime = explode(" ",$this->mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tstart = $mtime;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tend = $mtime;
		$totaltime = ($tend - $tstart);
		return $totaltime;
	}
	
	function beforeAction(){
		parent::beforeAction();
		$this->view->importers = ImportersModel::getAll();
	}
	function beforeRender(){
		parent::beforeRender();
	}
}

?>