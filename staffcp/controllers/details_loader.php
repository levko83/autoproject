<?php

set_time_limit(600);

ini_set ('display_errors', 1); // при тестировани - 1, при работе - 
ini_set ('display_startup_errors', 1); // при тестировани - 1, при работе - 
ini_set ('log_errors', 1); // всегда 1. Читайте логи Апача, там будут копиться ПХП-ошибки!!!
ini_set ('error_reporting', 2047); // при тестировании - 2047, при работе - 2039
ini_set ('track_errors', 0); // Сохранять ли последнее сообщение об ошибке или предупреждение в переменной $php_errormsg

class Details_loaderController  extends CmsGenerator {
	
	function index() {

		if (isset($_SESSION['parser'])) {
			if ($_SESSION['parser']==1) {
				$this->view->send = 1;
			}
			unset($_SESSION['parser']);
		} else {
		  $this->view->send = 0;
		}
		
		$this->view->importers = ImportersModel::getAll();
	}
	
	function parse() {
		
		header("Content-Type: text/html; charset=windows-1251");
		
		$db = Register::get('db');
		
		$IMPORT_ID = $this->request("importer",0);
		
		define ('Path', realpath (dirname (__FILE__).'/').'/');
		
		#var_dump($_FILES['csv_file']['type']);
		
		if (isset($_FILES['csv_file'])) {
			
			if ($_FILES['csv_file']['type'] != "text/csv") {
				
				echo "File type must be <b>text/csv</b> but ".$_FILES['csv_file']['type']." given";
				
			} elseif($_FILES['csv_file']['error'] > 0) {
				
				echo "Error. Code: ".$_FILES['csv_file']['error'];
				
			}else {
				
				$db->query("DELETE FROM ".DB_PREFIX."details where IMPORT_ID='".(int)$IMPORT_ID."';");
				
				require_once('ycsvparser.class.php');
				$ycsv = new ycsvParser($_FILES['csv_file']['tmp_name'],true);
				
				if (!$ycsv)
					die("Cannot start a parser");
					
				echo "<table border=1 width=100%>";
				
				echo ("<tr><td colspan=\"".(count($ycsv->config)+2)."\" align=\"center\"><a href=\"/staffcp/details_loader/\"><b>OK</b></a></td></tr>");
				
				if (count($ycsv->config)) {
					echo "<tr>";
						echo "<td>#</td>";
					foreach($ycsv->config as $title) {
						echo "<td>$title</td>";
					}
						echo "<td></td>";
					echo "</tr>";
				}
				
				$i=0;
				while ($record = $ycsv->getRecord()) {
				$i++;
				
					$res = $ycsv->parseRecord($record);
					if (count($ycsv->config) && count($res) != count($ycsv->config)) {
						echo ("<tr><td colspan=\"".(count($ycsv->config)+2)."\">Error! Must be <b>".count($ycsv->config)."</b> feilds, string has <b>".count($res)."</b> fields!</td></tr>");
					} else {
						if (count($res)) {
							
							$ART_ID = addslashes($res[0]);
							$BRAND = addslashes($res[1]);
							$PRICE = $res[2];
														
							$ART_ID = FuncModel::stringfilter($ART_ID);
							$FOUND_BRA = BrandsModel::find($BRAND);
							
							$db->query("INSERT INTO ".DB_PREFIX."details (`IMPORT_ID`,`BRAND_ID`,`BRAND_NAME`,`ARTICLE`,`PRICE`) VALUES ('".$IMPORT_ID."','".$FOUND_BRA['BRA_ID']."','".$BRAND."','".$ART_ID."','".$PRICE."');");
							
							if (count($FOUND_BRA)>0)
								$check = true;
							else
								$check = false;
							
							echo "<tr>";
								echo '<td>'.$i.'</td>';
							foreach($res as $title) {
								echo "<td>$title</td>";
							}
							
							if ($check) {
								$descr = iconv("UTF-8","WINDOWS-1251","Done!");
								echo '<td style="color:green;">'.$descr.'</td>';
							}
							else {
								$descr = iconv("UTF-8","WINDOWS-1251","Done!, but the brand is not found or an error number of columns.");
								echo '<td style="color:red;">'.$descr.'</td>';
							}
							
							echo "</tr>";
						}
					}		
				}
				echo "</table>";
				$ycsv->close();
				
				$_SESSION['parser'] = 1;
			}
		}
		exit();
	}
}

?>