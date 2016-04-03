<?php

class ImageGd {
	
	var $srcWidth = 0;
	var $srcHeight = 0;
	var $descWidth = 0;
	var $descHeight = 0;
	
	var $descBiggestSide = 0;
	
	var $descPath;
	var $srcPath;
	
	var $bLoaded = false;
	
	var $imageType;
	
	
	function ImageGd($path) {
		$this->bLoaded = false;
		
		$this->srcPath = $path;
		
		$aImageInfo = @getimagesize($this->srcPath);
		
		if ($aImageInfo) {
			$this->bLoaded = true;
			$this->imageType = $this->getType($aImageInfo[2]);
		}
	}
	
	function resize($descPath,$width = 0,$height = 0, $biggestSide = 0) {
			$this->descWidth = $width;
			$this->descHeight = $height;
			$this->descBiggestSide = $biggestSide;
			$this->descPath = $descPath;
						
			if ($this->bLoaded) {				
				$aCopyArea = $this->getCopyArea();
				$iSrc = $this->createFromFile();
				$iDesc = imagecreatetruecolor($aCopyArea['width'],$aCopyArea['height']);

				// следующая поебень нужна для прозрачности			
				$transperentColor = imagecolorat($iDesc,1,1);
				if ($this->imageType == 'jpeg') {
					imagecopyresampled($iDesc,$iSrc,0,0,$aCopyArea['x'],$aCopyArea['y'],$aCopyArea['width'],$aCopyArea['height'],$aCopyArea['src_w'],$aCopyArea['src_h']);
				} else {
					imagecopyresized($iDesc,$iSrc,0,0,$aCopyArea['x'],$aCopyArea['y'],$aCopyArea['width'],$aCopyArea['height'],$aCopyArea['src_w'],$aCopyArea['src_h']);
				}
				
				imagecolortransparent($iDesc,$transperentColor);
				
				if ($this->imageType == 'gif') {
					imagetruecolortopalette($iDesc, true, 256);
    				imageinterlace($iDesc);
				}
				
				if ($this->imageType == 'jpeg')
					imagejpeg($iDesc,$this->descPath,99);
				else 
					call_user_func("image".$this->imageType,$iDesc,$this->descPath);
			}
	}
	
	function getSize() {
		$aSize = getimagesize($this->srcPath);
		$this->srcWidth = $aSize[0];
		$this->srcHeight = $aSize[1];
	}
	
	function getCopyArea() {
		$this->getSize();
		
		$aCopyArea = array();
		
		$width2height = $this->srcWidth/$this->srcHeight;
		
		if ($this->descBiggestSide) {
			if ($this->srcWidth > $this->srcHeight) {
				$aCopyArea['width'] = $this->descBiggestSide;
				$aCopyArea['height'] = intval($this->descBiggestSide/$width2height);
			} else {
				$aCopyArea['height'] = $this->descBiggestSide;
				$aCopyArea['width'] = intval($this->descBiggestSide*$width2height);
			}
		} elseif ($this->descWidth!=0 && $this->descHeight!=0) {
			$aCopyArea['width'] = $this->descWidth;
			$aCopyArea['height'] = $this->descHeight;
		} elseif ($this->descWidth == 0) {
			$aCopyArea['height'] = $this->descHeight;
			$aCopyArea['width'] = intval($this->descHeight*$width2height);	
		} elseif ($this->descHeight == 0) {
			$aCopyArea['width'] = $this->descWidth;
			$aCopyArea['height'] = intval($this->descWidth/$width2height);		
		}
		
		if ($this->srcWidth!=0 && $this->srcHeight!=0) {
			$aCopyArea['x'] = 0;
			$aCopyArea['src_w'] = $this->srcWidth;
			$aCopyArea['y'] = 0;		
			$aCopyArea['src_h'] = $this->srcHeight;			
		} elseif ($this->srcWidth < $this->srcHeight) {
			$aCopyArea['x'] = 0;
			$aCopyArea['src_w'] = intval($this->srcWidth);
			$aCopyArea['y'] = intval(($this->srcHeight - (($aCopyArea['width']/$aCopyArea['height'])*$this->srcWidth))/2);		
			$aCopyArea['src_h'] = $this->srcHeight - 2*$aCopyArea['y'];
		} else {
			$aCopyArea['y'] = 0;
			$aCopyArea['src_h'] = intval($this->srcHeight);
			$aCopyArea['x'] = intval(($this->srcWidth - (($aCopyArea['height']/$aCopyArea['width'])*$this->srcHeight))/2);
			$aCopyArea['src_w'] = intval($this->srcWidth - 2*$aCopyArea['x']);
		}
		
		return $aCopyArea;
	}
	
    function getType($imageTypeId) {
        switch ($imageTypeId) {
            case 1: return 'gif';
            case 2: return 'jpeg';
            case 3: return 'png';
            case 4: return 'swf';
            case 5: return 'psd';
            case 6: return 'bmp';
            case 7: return 'tiff';
            case 8: return 'tiff';
            case 9: return 'jpc';
            case 10:return 'jp2';
            case 11:return 'jpx';
            case 12:return 'jb2';
            case 13:return 'swc';
            case 14:return 'iff';
            case 15:return 'wbmp';
            case 16:return 'xbm';
        }
    }
    
    function createFromFile() {
        switch ($this->imageType) {
            case 'gif':return imagecreatefromgif($this->srcPath);
            case 'jpeg':return imagecreatefromjpeg($this->srcPath);
            case 'png':return imagecreatefrompng($this->srcPath);
            case 'wbmp':return imagecreatefromwbmp($this->srcPath);
        }
    }
	
	
}

?>