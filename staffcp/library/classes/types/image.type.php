<?php

class ImageType extends Type {
	
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

			$fileName = $uploadPath . time() . '-' . $_FILES[$this->fieldName]['name'];
			if (move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $fileName)) {
				$aThumbs = array();
				if (is_array(@$this->fieldInfo['images']))
					$aThumbs = $this->fieldInfo['images'];
				$aThumbs['t'] = '100x100';
	
				foreach ($aThumbs as $thumb_sufix=>$thumb_options) {
					$ImageService = new ImageGd($fileName);
					$aOptions = $this->getImageParams($thumb_options);
					$thumbPath = $uploadPath . $thumb_sufix . '-' . basename($fileName);
					$ImageService->resize($thumbPath,$aOptions['width'],$aOptions['height'],$aOptions['biggestSide']);
				}
				return basename($fileName);
			}
			die();
		}
		return self::NOT_SET;
	}

	public function getFormValue($val='') {		
				
		$translates = Register::get('translates');
		
		if (empty($val[$this->fieldName])) {
			if (!empty($_SESSION[$this->fieldName])) {
				$this->fieldInfo['base_dir'] = "temp/";
				$szFileName = $this->getPath().$_SESSION[$this->fieldName];
				$this->setValue($_SESSION[$this->fieldName]);	
			}
			else
				$szFileName = $this->getPath();
		}
		elseif (!empty($val[$this->fieldName])) {
			$szFileName = $this->getPath().$val[$this->fieldName];
			$this->setValue($val[$this->fieldName]);
		}
		
		if (is_file($szFileName)) 
			$nFileSize = $this->size2string(filesize($szFileName));
		else {
			$szFileName = '';
			$nFileSize = 0;
		}
		
		if (empty($szFileName)) {
			$this->setValue('');
		}
		
		$link = '<a href="/media/files/'.$this->fieldInfo['base_dir'].$this->getValue().'">'.($this->getValue()).'</a>';
				
		$result =<<<EOD
			<table width="100%" cellpadding="0" cellspacing="0" border="1">
			<tr>
			        <td colspan="1" class="td_main">
			                <input type="file" name="{$this->fieldName}" class=""> {$translates['letter.name']}
			                <input type="hidden" name="form[{$this->fieldName}]" value="{$this->getValue()}">
			
			        </td>
			        
EOD;
		if (!empty($nFileSize))
		{
			$result .= <<<EOD
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
				
				
						
EOD;
		}
		$result .= '</tr></table>';
		return $result;
	}
	
	public function size2string($bytes)
	{
		$aVal = array('b','Kb','Mb','Gb','Tb');
		$pow = intval(log($bytes,1024));
		return round($bytes/pow(1024,$pow),2).' '.$aVal[$pow];
	}
	
	public function getViewValue()
	{
		return $this->getValue();	
	}
	
	private function getImageParams($aOptions) {
		if (is_array($aOptions)) {
			$szSize = $aOptions['size'];
		} else {
			$szSize = $aOptions;
		}
		
		$aData['width'] = 0;
		$aData['height'] = 0;
		$aData['biggestSide'] = 0;
		
		if (strpos($szSize,'x')) {
			list($width,$height) = explode('x',$szSize);
			$aData['width'] = intval($width);
			$aData['height'] = intval($height);			
		} else {
			$aData['biggestSide'] = intval($szSize);
		}
		
		return $aData;
	}
}