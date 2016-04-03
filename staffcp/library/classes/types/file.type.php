<?php

class FileType extends Type {
	
	public function getPath() {
		$uploadPath = Application::getUploadDir(true).'/'.$this->fieldInfo['base_dir'];
		$currentFile = $uploadPath . $this->getValue();
		return $currentFile;
	}
	
	public function getSaveValue($value) {
		$uploadPath = Application::getUploadDir(true).'/'.$this->fieldInfo['base_dir'];
		$currentFile = $uploadPath . $value;
		
		/* modify */
		if (isset($_REQUEST[$this->fieldName.'_delete'])){
			if (is_file(iconv('utf-8','windows-1251',$currentFile))) 
				unlink(iconv('utf-8','windows-1251',$currentFile));
			return '';
		}
		/* modify end */
		
		if (!empty($_FILES[$this->fieldName]['name'])) {
			if (isset($this->fieldInfo['name']) && $this->fieldInfo['name'] == 'date'){
				$prfx = date("d.m.Y_H:i:s_");
				if (move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $uploadPath . $prfx . iconv('utf-8','windows-1251',$_FILES[$this->fieldName]['name']))) {
					return $prfx . $_FILES[$this->fieldName]['name'];
				}
			}
			else{ 
				if (move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $uploadPath . 'inside-placeholder-' . iconv('utf-8','windows-1251',$_FILES[$this->fieldName]['name']))) {
					return 'inside-placeholder-' . $_FILES[$this->fieldName]['name'];
				}
			}
		}
		
		return self::NOT_SET;
	}

	public function getFormValue($val='') {
		
		$translates = Register::get('translates');
		
		if (empty($val[$this->fieldName]))
			$szFileName = $this->getPath();
		elseif (!empty($val[$this->fieldName])) {
			$szFileName = $this->getPath().$val[$this->fieldName];
			$this->setValue($val[$this->fieldName]);
		}
		
		if (file_exists($szFileName)) 
			$nFileSize = $this->size2string(filesize(iconv('utf-8','windows-1251',$szFileName)));
		else {
			$szFileName = '';
			$nFileSize = 0;
		}
		
		$link = '<a href="/media/files/'.$this->fieldInfo['base_dir'].$this->getValue().'">'.($this->getValue()).'</a>';
				
		$result =<<<EOD
			<table width="100%" cellpadding="0" cellspacing="0" border="1">
			<tr>
			        <td colspan="1" class="td_main">
			                <input type="file" name="{$this->fieldName}" class=""> {$translates['letter.name']}
			                <input type="hidden" name="form[{$this->fieldName}]" value="{$this->getValue()}">
			
			        </td>
			        <td>
			                <input type="checkbox" name="{$this->fieldName}_delete" value="1"> {$translates['admin.main.delete']}
			        </td>
			</tr>
			<tr>
			        <td width="30%" class="td_main">{$translates['admin.main.size']} : {$nFileSize}
			        </td>
			        <td class="td_main">
			                {$translates['admin.main.path']} : {$link}<br>
							{$translates['admin.main.filename']} : {$this->value}
			        </td>
			
			</tr>
			</table>		
EOD;
		return $result;
	}
	
	public function size2string($bytes){
		$aVal = array('b','Kb','Mb','Gb','Tb');
		$pow = intval(log($bytes,1024));
		return round($bytes/pow(1024,$pow),2).' '.$aVal[$pow];
	}
	
	public function getViewValue(){
		$fir = $this->fieldInfo['base_dir'];
		return '<a href="/media/files/'.$this->fieldInfo['base_dir'].''.$this->getValue().'">'.$this->getValue().'</a>';
	}
}