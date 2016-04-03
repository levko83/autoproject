<?php

class ImageResizeWatermarkType extends Type {
	
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
				
				$db = Register::get('db');
				$sql = "SELECT value FROM ".DB_PREFIX."settings WHERE code = 'logo';";
				$img_res = $db->get($sql);
				
				$watermark = new watermark();
				$img = imagecreatefromjpeg($fileName);
				$water = imagecreatefrompng(Application::getUploadDir(true)."/settings/".$img_res['value']);
				$im=$watermark->create_watermark($img,$water,80);
				imagejpeg($im,$fileName,100);
				
				$aThumbs = array();
				
				if (is_array(@$this->fieldInfo['images'])) {
					$aThumbs = $this->fieldInfo['images'];
				}
				foreach ($aThumbs as $thumb_sufix=>$thumb_options) {
					list($aOptions['width'],$aOptions['height']) = explode("x",$thumb_options);
					
					if (empty($aOptions['width'])||$aOptions['width']==0) $aOptions['width']=100;
					if (empty($aOptions['height'])||$aOptions['height']==0) $aOptions['height']=100;
					
					$thumbPath = $uploadPath . $thumb_sufix . '-' . basename($fileName);
					
					$this->img_resize($fileName,$thumbPath,$aOptions['width'],$aOptions['height']);
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
	
	public function img_resize($src, $dest, $width, $height, $rgb=0xffffff, $quality=100) {
		
		if (!file_exists($src)) return false;
		
		$size = getimagesize($src);
		
		if ($size === false) return false;
		
		$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		$icfunc = "imagecreatefrom" . $format;
		if (!function_exists($icfunc)) return false;
		
		$x_ratio = $width / $size[0];
		$y_ratio = $height / $size[1];
		
		$ratio = min($x_ratio, $y_ratio);
		$use_x_ratio = ($x_ratio == $ratio);
		
		$new_width = $use_x_ratio ? $width : floor($size[0] * $ratio);
		$new_height = !$use_x_ratio ? $height : floor($size[1] * $ratio);
		$new_left = $use_x_ratio ? 0 : floor(($width - $new_width) / 2);
		$new_top = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
		
		//var_dump($icfunc);
		$isrc = $icfunc($src);
		$idest = imagecreatetruecolor($width, $height);
		
		imagefill($idest, 0, 0, $rgb);
		imagecopyresampled($idest,$isrc,$new_left,$new_top,0,0, $new_width,$new_height, $size[0],$size[1] );
		imagejpeg($idest, $dest);
		
		imagedestroy($isrc);
		imagedestroy($idest);
		
//		$watermark = new watermark();
//		$img = imagecreatefromjpeg($dest);
//		$water = imagecreatefrompng("http://v3.ryli.by/media/files/settings/inside-placeholder-logo.png");
//		$im=$watermark->create_watermark($img,$water,10);
//		imagejpeg($im,$dest);
		
		return $dest;
	}
}

class watermark {
 
	# given two images, return a blended watermarked image
	function create_watermark( $main_img_obj, $watermark_img_obj, $alpha_level = 100 ) {
		$alpha_level	/= 100;	# convert 0-100 (%) alpha to decimal

		# calculate our images dimensions
		$main_img_obj_w	= imagesx( $main_img_obj );
		$main_img_obj_h	= imagesy( $main_img_obj );
		$watermark_img_obj_w	= imagesx( $watermark_img_obj );
		$watermark_img_obj_h	= imagesy( $watermark_img_obj );
 
		$set = 1;
		# determine center position coordinates
		$main_img_obj_min_x	= floor( ( $main_img_obj_w / $set ) - ( $watermark_img_obj_w / $set ) );
		$main_img_obj_max_x	= ceil( ( $main_img_obj_w / $set ) + ( $watermark_img_obj_w / $set ) );
		$main_img_obj_min_y	= floor( ( $main_img_obj_h / $set ) - ( $watermark_img_obj_h / $set ) );
		$main_img_obj_max_y	= ceil( ( $main_img_obj_h / $set ) + ( $watermark_img_obj_h / $set ) ); 
 
		# create new image to hold merged changes
		$return_img	= imagecreatetruecolor( $main_img_obj_w, $main_img_obj_h );
 
		# walk through main image
		for( $y = 0; $y < $main_img_obj_h; $y++ ) {
			for( $x = 0; $x < $main_img_obj_w; $x++ ) {
				$return_color	= NULL;
 
				# determine the correct pixel location within our watermark
				$watermark_x	= $x - $main_img_obj_min_x;
				$watermark_y	= $y - $main_img_obj_min_y;
 
				# fetch color information for both of our images
				$main_rgb = imagecolorsforindex( $main_img_obj, imagecolorat( $main_img_obj, $x, $y ) );
 
				# if our watermark has a non-transparent value at this pixel intersection
				# and we're still within the bounds of the watermark image
				if (	$watermark_x >= 0 && $watermark_x < $watermark_img_obj_w &&
							$watermark_y >= 0 && $watermark_y < $watermark_img_obj_h ) {
					$watermark_rbg = imagecolorsforindex( $watermark_img_obj, imagecolorat( $watermark_img_obj, $watermark_x, $watermark_y ) );
 
					# using image alpha, and user specified alpha, calculate average
					$watermark_alpha	= round( ( ( 127 - $watermark_rbg['alpha'] ) / 127 ), 2 );
					$watermark_alpha	= $watermark_alpha * $alpha_level;
 
					# calculate the color 'average' between the two - taking into account the specified alpha level
					$avg_red		= $this->_get_ave_color( $main_rgb['red'],		$watermark_rbg['red'],		$watermark_alpha );
					$avg_green	= $this->_get_ave_color( $main_rgb['green'],	$watermark_rbg['green'],	$watermark_alpha );
					$avg_blue		= $this->_get_ave_color( $main_rgb['blue'],	$watermark_rbg['blue'],		$watermark_alpha );
 
					# calculate a color index value using the average RGB values we've determined
					$return_color	= $this->_get_image_color( $return_img, $avg_red, $avg_green, $avg_blue );
 
				# if we're not dealing with an average color here, then let's just copy over the main color
				} else {
					$return_color	= imagecolorat( $main_img_obj, $x, $y );
 
				} # END if watermark

				# draw the appropriate color onto the return image
				imagesetpixel( $return_img, $x, $y, $return_color );
 
			} # END for each X pixel
		} # END for each Y pixel

		# return the resulting, watermarked image for display
		return $return_img;
 
	} # END create_watermark()

	# average two colors given an alpha
	function _get_ave_color( $color_a, $color_b, $alpha_level ) {
		return round( ( ( $color_a * ( 1 - $alpha_level ) ) + ( $color_b	* $alpha_level ) ) );
	} # END _get_ave_color()

	# return closest pallette-color match for RGB values
	function _get_image_color($im, $r, $g, $b) {
		$c=imagecolorexact($im, $r, $g, $b);
		if ($c!=-1) return $c;
		$c=imagecolorallocate($im, $r, $g, $b);
		if ($c!=-1) return $c;
		return imagecolorclosest($im, $r, $g, $b);
	} # EBD _get_image_color()

}

?>