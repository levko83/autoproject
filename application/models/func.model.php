<?php

class FuncModel extends Orm {
		
    public static function debug($array,$name='')
	{
	    if (count($array)>0) {
	        
	        echo '<h4>Debug: '.$name.'</h4>';
	        echo '<table border="1" style="font-size:11px;border:solid 1px #dadada;font-family:tahoma;" cellpadding="5px">';
	        echo '<tr>';
	        foreach ($array[0] as $key=>$val)
                echo '<td><b>'.$key.'</b></td>';
	        echo '<tr>';      
	        for($i=0;$i<=count($array)-1;$i++)
	        {
    	        echo '<tr>';
                foreach ($array[$i] as $kk=>$vv) {
                    echo '<td>'.$vv.'</td>';
                }
                echo '<tr>';
	        }
            echo '</table>';
            echo '<p style="font-size:11px;font-family:tahoma;">count: '.count($array).'</p>';
	    }
	    else 
	       echo '<p>empty: '.$name.'</p>';
	       
        $mtime = microtime();
        $mtime = explode(" ",$mtime);
        $mtime = $mtime[1] + $mtime[0];
        $tstart = $mtime;
        $mtime = microtime();
        $mtime = explode(" ",$mtime);
        $mtime = $mtime[1] + $mtime[0];
        $tend = $mtime;
        $totaltime = ($tend - $tstart);
        printf ('<p style="font-size:11px;font-family:tahoma;">Item generated by %f sec</p>', $totaltime);
	}
	
	public static function quicksort_clearing($ret,$field,$field2) {
		if(!count($ret)) return $ret;
		$k = $ret[0];
		$x = $y = array();
		$length = count($ret);
		for($i=1; $i < $length; $i++) {	
			if(($k[$field] != $ret[$i][$field]) || ($k[$field2] != $ret[$i][$field2])) {
				$y[] = $ret[$i];
			}
		}
	return array_merge(FuncModel::quicksort_clearing($x,$field,$field2), array($k), FuncModel::quicksort_clearing($y,$field,$field2));
	}
	//end
	
	public static function quicksort($ret,$field) {
		if(!count($ret)) return $ret;
		$k = $ret[0];
		$x = $y = array();
		$length = count($ret);
		for($i=1; $i < $length; $i++) {	
			if($k[$field] != $ret[$i][$field]) {
				$y[] = $ret[$i];
			} 
			else {
				$x[] = $ret[$i];
			}
		}
	return array_merge(FuncModel::quicksort($x,$field), array($k), FuncModel::quicksort($y,$field));
	}
	//end
	
	public static function stringfilter($str) {
		return str_replace(array("\r\n","\n",chr(194).chr(160),'	','!','@','#','$','%','^','&','*','(',')','_','+','=','-','~','`','"',"'",' ','№','%',';',':','[',']','{','}','*','?','/','\'','|','.',','),'',$str);
	}
}

?>