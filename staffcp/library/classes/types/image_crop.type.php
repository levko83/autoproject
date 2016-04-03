<?php

class ImageCropType extends Type {
	
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

			$fileName = $uploadPath . 'inside-placeholder-' . time() . '-' . $_FILES[$this->fieldName]['name'];
			if (move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $fileName)) {
				$aThumbs = array();
				
				if (is_array(@$this->fieldInfo['images'])) {
					$aThumbs = $this->fieldInfo['images'];
				}
				foreach ($aThumbs as $thumb_sufix=>$thumb_options) {
					list($aOptions['width'],$aOptions['height']) = explode("x",$thumb_options);
					
					if (empty($aOptions['width'])||$aOptions['width']==0) $aOptions['width']=100;
					if (empty($aOptions['height'])||$aOptions['height']==0) $aOptions['height']=100;
					
					$thumbPath = $uploadPath . $thumb_sufix . '-' . basename($fileName);
					
					$this->createImageThumbs($fileName,$thumbPath,$aOptions['width'],$aOptions['height']);
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
	
	function createImageThumbs( $img_dir, $pathToThumbs, $thumbWidth, $thumbHeight )
	{
		// parse path for the extension
		$info = pathinfo($img_dir);
		// continue only if this is a JPEG image
		if (  isset($info['extension']) )
		{
			//echo "Creating thumbnail for {$fname} <br />";
			// load image and get image size
			$img_str = file_get_contents ("{$img_dir}");
			$isrc = imagecreatefromstring( $img_str );
			
			$width = $thumbWidth;
			$height = $thumbHeight;
			$width = $width ? $thumbWidth:320;
			$height = $height ? $thumbHeight:180;
			//pr($width);
			// copy and resize old image into new image
			//imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
			$size[0] = imagesx($isrc);
			$size[1] = imagesy($isrc);
			
			if ($size === false) return false;
			
			$x_ratio = $width / $size[0];
			$y_ratio = $height / $size[1];
			
			$ratio       = max($x_ratio, $y_ratio);
			$use_x_ratio = ($x_ratio == $ratio);
			
			if (( $size[0] <= $width ) && ( $size[1] <= $height) )
			{
			$new_width = $size[0];
			$new_height = $size[1];
			}
			else
			{
			$new_width   = floor($size[0] * $ratio);
			$new_height  = floor($size[1] * $ratio);
			}
			
			$new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
			$new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
			
			//$isrc = $icfunc($src);
			$idest = imagecreatetruecolor($width, $height);
			
			$rgb = 0xFFFFFF;
			imagefill($idest, 0, 0, $rgb);
			imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
			$new_width, $new_height, $size[0], $size[1]);
			
			//imagejpeg( $idest,  $dest, $quality );
			imagejpeg( $idest, "{$pathToThumbs}", 85 );
			
			imagedestroy($isrc);
			imagedestroy($idest);
		}
	}
}