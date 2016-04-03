<?php

set_time_limit(1800);
// error_reporting(E_ALL | E_STRICT);
// ini_set('display_errors', 'On');

class Harvester_emailController  extends CmsGenerator {
	
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
		
		try{
		
			$orm = new Orm(DB_PREFIX.'harverster_email__params');
			$ormTasks = new Orm(DB_PREFIX.'harverster_email__tasks');
			
			$this->view->list = 
				$orm->
					select()->
						fields(DB_PREFIX.'harverster_email__params.*,'.DB_PREFIX.'importers.name importer')->
						join(DB_PREFIX.'importers',DB_PREFIX.'harverster_email__params.importer_id='.DB_PREFIX.'importers.id')->
						fetchAll();
			
			$this->view->tasks = 
				$ormTasks->
					select()->
						fields(
							DB_PREFIX.'harverster_email__params.*,'.
							DB_PREFIX.'harverster_email__tasks.id task_id,'.
							DB_PREFIX.'harverster_email__tasks.filename task_filename,'.
							DB_PREFIX.'harverster_email__tasks.parent_id task_parent_id,'.
							DB_PREFIX.'harverster_email__tasks.dt task_dt,'.
							DB_PREFIX.'harverster_email__tasks.err task_err,'.
							DB_PREFIX.'harverster_email__tasks.status task_status'
						)->
						join(DB_PREFIX.'harverster_email__params',DB_PREFIX.'harverster_email__params.id='.DB_PREFIX.'harverster_email__tasks.parent_id')->
						order('dt DESC')->
						fetchAll();
			
			$form = $this->request("form",false);
			if ($form){
				$orm->insert($form);
				$this->redirectUrl("/staffcp/harvester_email/");
			}
			
			$do = $this->request("do",false);
			if ($do){
				$params = $orm->select()->where("id=?",(int)$do)->fetchOne();
				$msg = new Phpmailreader($params['host'], $params['hlogin'], $params['hpass'], $params['hsearch']);
				if ($msg->mail){
					foreach ($msg->mail as $id=>$dd){
						
						if (isset($dd['attach']) && count($dd['attach'])>0){
							foreach ($dd['attach'] as $filename=>$data){
								
								$filename = iconv_mime_decode($filename,0,"UTF-8");
								$filename = AliasViewHelper::doTraslitFile($filename);
								
								if (file_put_contents($this->base_dir.$filename,$data)){
									$ormTasks->insert(array('filename'=>$filename,'parent_id'=>$do,'dt'=>time(),'status'=>'Файл записан, ожидает обработки / Время '.ceil($this->totaltime()).'s. / '.$dd['from'].' '.$dd['name'].' '.$dd['subject']));
								} else {
									$ormTasks->insert(array('filename'=>$filename,'parent_id'=>$do,'dt'=>time(),'err'=>'Ошибка записи файла'.ceil($this->totaltime()).'s. / '.$dd['from'].' '.$dd['name'].' '.$dd['subject']));
								}
							}
							//end files
						}
					}
				}
				else {
					$ormTasks->insert(array('filename'=>'','parent_id'=>$do,'dt'=>time(),'err'=>'Почтовые сообщения не найдены'));
				}
				
				$this->redirectUrl("/staffcp/harvester_email/");
			}
			//$do
			
			$delete_task_id = $this->request("delete_task_id",false);
			if ($delete_task_id){
				
				$filename = $ormTasks->select()->fields("filename")->where("id=?",(int)$delete_task_id)->fetchOne();
				@unlink($this->base_dir.$filename['filename']);
				
				$ormTasks->delete(array("id"=>(int)$delete_task_id));
				$this->redirectUrl("/staffcp/harvester_email/");
			}
			
			$delete_param_id = $this->request("delete_param_id",false);
			if ($delete_param_id){
				$orm->delete(array("id"=>(int)$delete_param_id));
				$this->redirectUrl("/staffcp/harvester_email/");
			}
		}
		catch (Exception $exception){
			$this->exc($exception);
		}
		
		Logs::addLog(Acl::getAuthedUserId(),'Просмотр раздела обработки прайсов с почты',URL_NOW);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	
	function process(){

		try {
			
			$ftp = $this->request("ftp",false);
			$redirect_action = ($ftp)?'harvester_ftp':'harvester_email';
			$table_task = ($ftp)?'harverster_ftp__tasks':'harverster_email__tasks';
			$table_param = ($ftp)?'harverster_ftp__params':'harverster_email__params';
			
			$importDB = Register::get('db');
			$task_id = $this->request("task_id",false);
			
			if ($task_id){
				
				//Берем задачу на обработку
				$ormTasks = new Orm(DB_PREFIX.$table_task);
				$getTask = $ormTasks->
						select()->
							fields(
								DB_PREFIX.$table_param.'.*,'.
								DB_PREFIX.$table_task.'.id task_id,'.
								DB_PREFIX.$table_task.'.filename task_filename,'.
								DB_PREFIX.$table_task.'.parent_id task_parent_id,'.
								DB_PREFIX.$table_task.'.dt task_dt,'.
								DB_PREFIX.$table_task.'.err task_err,'.
								DB_PREFIX.$table_task.'.status task_status'
							)->
							join(DB_PREFIX.$table_param,DB_PREFIX.$table_param.'.id='.DB_PREFIX.$table_task.'.parent_id')->
							where(DB_PREFIX.$table_task.'.id=?',(int)$task_id)->
							fetchOne();
				
				if (file_exists($this->base_dir.$getTask['task_filename'])){
				
					$this->delimeter = $getTask['split'];
					if ($this->delimeter == '[tab]'){
						$this->delimeter = '	';
					}
					
					//Если файл в архиве
					$is_archive = true; 
					$file_to_process = null;
					
					$expT = explode(".", basename($getTask['task_filename']));
					$extT = array_pop($expT);
					$ext = strtolower($extT);
					switch ($ext){
						case 'zip':
							
							require_once '../extensions/pclzip.lib.php';
							$archive = new PclZip($this->base_dir.$getTask['task_filename']);
							$extract = $archive->extract(PCLZIP_OPT_PATH, $this->base_dir, PCLZIP_OPT_REMOVE_PATH, 'install/release');
							if ($extract == 0) {
								die("Error: ".$archive->errorInfo(true)." / Некорректный архив");
							}
							foreach ($extract as $file){
								$expT = explode(".", basename($file['filename']));
								$extT = array_pop($expT);
								$fileExt = strtolower($extT);
								if ($fileExt == $getTask['format']){
									$file_to_process = $file['filename'];
									break;
								}
							}
							$is_archive = false;
							
							break;
						case 'rar':
							
							$filename = $this->base_dir.$getTask['task_filename'];
							$rar_file = rar_open($filename,$getTask['rar_password']);
							if ($rar_file === FALSE)
								die("Failed opening file");
							$entries = rar_list($rar_file);
							if ($entries === FALSE)
								die("Failed fetching entries");
							if (empty($entries))
								die("No valid entries found.");
							foreach($entries as $entry){
								
								$fileName = $entry->getName();
								$expT = explode(".", basename($fileName));
								$extT = array_pop($expT);
								$fileExt = strtolower($extT);
								if ($fileExt == $getTask['format']){
									$entry->extract($this->base_dir); // extract to the current dir
									$file_to_process = $fileName;
									break;
								}
							}
							$rar_file->close();
							$is_archive = false;
							break;
						default: 
							$file_to_process = $getTask['task_filename'];
							$is_archive = false;
							break;
					}
	
					//Берем файл в работу
					if (!$is_archive && $file_to_process){
						
						$file_to_process = $this->base_dir.str_replace($this->base_dir, "", $file_to_process);
						$expT = explode(".", basename($file_to_process));
						$extT = array_pop($expT);
						$file_to_process_ext = strtolower($extT);
						
						$details = new Orm(DB_PREFIX.'details');
						$dump_name = date("d_m_Y_h_i").'-'.$getTask['importer_id'].'.sql';
						
						if ($file_to_process_ext == 'txt'){
							$file_to_process_ext = 'csv';
						}
						switch ($file_to_process_ext){
							case 'csv':
								
								$details->delete(array("IMPORT_ID"=>(int)$getTask['importer_id']));
								
								/* НАЧАЛО TXT * * * * * * * * * * * * * * * * * * * * * * * * */
								require_once '../xreaders/readers/ycsvparser.class.php';
								
								$contentClear = file_get_contents($file_to_process);
								$contentClear = str_replace(array('"',','),'',$contentClear);
								file_put_contents($file_to_process,$contentClear);
								unset($contentClear);
								
								$ycsv = new ycsvParser($file_to_process,false);
								$ycsv->delim = $this->delimeter;
								
								$line=0; 
								$db = array();
								while ($record = $ycsv->getRecord()) {
									$line++;
									$res = $ycsv->parseRecord($record);
									
									$colum_article = $this->convert($res[$getTask['colum_article']-1]);//Артикул
									$colum_brand = $this->convert($res[$getTask['colum_brand']-1]);//Бренд
									$colum_name = $this->convert($res[$getTask['colum_name']-1]);//Название
									$colum_box = $this->convert($res[$getTask['colum_box']-1]);//Колво
									$colum_price = $this->convert($res[$getTask['colum_price']-1]);//Цена
									$db []= "('".$getTask['importer_id']."','".$this->findBrand($colum_brand)."','".$colum_brand."','".FuncModel::stringfilter($colum_article)."','".$colum_price."','".$colum_name."','".$colum_box."','".$colum_article."')";
									
									if ($line == 10000){
										$dump = "INSERT INTO ".DB_PREFIX."details (IMPORT_ID,BRAND_ID,BRAND_NAME,ARTICLE,PRICE,DESCR,BOX,ARTICLE_DEFAULT) VALUES ".join(",", $db).";";
										$importDB->post($dump);
										unset($db);
										$line = 0;
									}
								}
								if (count($db)>0){
									$dump = "INSERT INTO ".DB_PREFIX."details (IMPORT_ID,BRAND_ID,BRAND_NAME,ARTICLE,PRICE,DESCR,BOX,ARTICLE_DEFAULT) VALUES ".join(",", $db).";";
									$importDB->post($dump);
									unset($db);
								}
								
								//@unlink($this->base_dir.$getTask['task_filename']);
								//@unlink($file_to_process);
								
								$ormTasks->update(array('status'=>'Данные успешно загружены! / Время выполнения: '.ceil($this->totaltime()).'s. / '.date("d.m.Y H:i:s")),array('id'=>$getTask['task_id']));
								$this->redirectUrl("/staffcp/".$redirect_action."/");
								/* КОНЕЦ TXT * * * * * * * * * * * * * * * * * * * * * * * * */
								
								break;
							case 'xlsx':
								
								$details->delete(array("IMPORT_ID"=>(int)$getTask['importer_id']));
								
								/* НАЧАЛО XLSX * * * * * * * * * * * * * * * * * * * * * * * * */
								require_once '../xreaders/readers/simplexlsx.class.php';
								$xlsx = new SimpleXLSX($file_to_process);
									
								$line=0;
								$db = array();
								foreach($xlsx->rows() as $res){
									$line++;
								
									$colum_article = $this->convert($res[$getTask['colum_article']-1],false);//Артикул
									$colum_brand = $this->convert($res[$getTask['colum_brand']-1],false);//Бренд
									$colum_name = $this->convert($res[$getTask['colum_name']-1],false);//Название
									$colum_box = $this->convert($res[$getTask['colum_box']-1],false);//Колво
									$colum_price = $this->convert($res[$getTask['colum_price']-1],false);//Цена
									$db []= "('".$getTask['importer_id']."','".$this->findBrand($colum_brand)."','".$colum_brand."','".FuncModel::stringfilter($colum_article)."','".$colum_price."','".$colum_name."','".$colum_box."','".$colum_article."')";
									
									if ($line == 10000){
										$dump = "INSERT INTO ".DB_PREFIX."details (IMPORT_ID,BRAND_ID,BRAND_NAME,ARTICLE,PRICE,DESCR,BOX,ARTICLE_DEFAULT) VALUES ".join(",", $db).";";
										$importDB->post($dump);
										unset($db);
										$line = 0;
									}
								}
								if (count($db)>0){
									$dump = "INSERT INTO ".DB_PREFIX."details (IMPORT_ID,BRAND_ID,BRAND_NAME,ARTICLE,PRICE,DESCR,BOX,ARTICLE_DEFAULT) VALUES ".join(",", $db).";";
									$importDB->post($dump);
									unset($db);
								}
									
								//@unlink($this->base_dir.$getTask['task_filename']);
								//@unlink($file_to_process);
								
								$ormTasks->update(array('status'=>'Данные успешно загружены! / Время выполнения: '.ceil($this->totaltime()).'s. / '.date("d.m.Y H:i:s")),array('id'=>$getTask['task_id']));
								$this->redirectUrl("/staffcp/".$redirect_action."/");
								/* КОНЕЦ XLSX * * * * * * * * * * * * * * * * * * * * * * * * */
								
								break;
							default:
								
								die('Ошибка файла. Неверный формат файла. Отмена обработки.');
								break;
						}
					} else {
						die('Ошибка файла. Неверный формат файла. Отмена обработки.');
					}
				}
				else {
					die('Ошибка файла. Файл отсутствует!');
				}
			}
			
		}catch (Exception $exception){
			$this->exc($exception);
		}
		
		$this->redirectUrl("/staffcp/".$redirect_action."/");
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	
	private function findBrand($BRAND){
		if (isset($this->brands[$BRAND]) && $this->brands[$BRAND])
			return $this->brands[$BRAND];
		$orm = new Orm(DB_PREFIX."brands");
		$brand = 
			$orm->
			select()->
				fields('BRA_ID, BRA_ID_GET, BRA_BRAND')->
					where("BRA_BRAND LIKE '".mysql_real_escape_string(trim($BRAND))."'")->
						fetchOne();
		$res = ($brand['BRA_ID_GET'])?$brand['BRA_ID_GET']:$brand['BRA_ID'];
		$this->brand [$BRAND]= $res;
		return $res;
	}
	
	private function convert($str, $useiconv=true, $in_charset='windows-1251', $out_charset='utf8'){
		if ($useiconv)
			return mysql_real_escape_string(iconv($in_charset, $out_charset, $str));
		else
			return mysql_real_escape_string($str);
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
	
	private function exc($exception){
		echo '<html><head><style> * { font-family: Verdana, Tahoma, Arial; font-size: 13px; } body {} .error {width: 860px;text-align: left;} .error h1 {font-size: 18px;margin-left: 36px;} .error-num {float: left;width: 30px;background-color: #fff;padding: 3px;text-align: left;} .error-descr {float: left;background-color: #f5f5f5;margin-bottom: 10px;padding: 3px;width: 800px;text-align: left;} .error-descr span {font-weight: bold;}</style></head></body>';
		echo '<center><div class="error"><h1>'.$exception->getMessage().'</h1>';
		foreach ($exception->getTrace() as $key=>$item) {
			echo '<div class="error-item"><div class="error-num">'.($key + 1).'.</div>';
			echo '<div class="error-descr">';
			echo '<span>'.$item['class'].' '.$item['type'].' '.$item['function'].' '.'( '.join(', ',$item['args']).' )'.'</span><br>';
			echo $item['file'].' (Line: '.$item['line'].')<br>';
			echo '</div><br clear="all" /></div>';
		}
		echo '</div></center>';
		echo '</body></html>';
		exit();
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