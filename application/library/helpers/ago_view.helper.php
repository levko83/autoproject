<?php
/**
 * Pager
 *
 */
class AgoViewHelper {

	// since Calculates how many time past since given time
	public static function since($time) {

		$nowtime = date("Y-m-d H:i:s");
	
		$nowyear = AgoViewHelper::extrdate($nowtime,"Y");
		$nowmonth = AgoViewHelper::extrdate($nowtime,"m");
		$nowday = AgoViewHelper::extrdate($nowtime,"d");
		$nowhour = AgoViewHelper::extrdate($nowtime,"H");
		$nowminut = AgoViewHelper::extrdate($nowtime,"i");
		$nowsecond = AgoViewHelper::extrdate($nowtime,"s");
				
		$timeyear = AgoViewHelper::extrdate(date("Y-m-d H:i:s",$time),"Y");
		$timemonth = AgoViewHelper::extrdate(date("Y-m-d H:i:s",$time),"m");
		$timeday = AgoViewHelper::extrdate(date("Y-m-d H:i:s",$time),"d");
		$timehour = AgoViewHelper::extrdate(date("Y-m-d H:i:s",$time),"H");
		$timeminut = AgoViewHelper::extrdate(date("Y-m-d H:i:s",$time),"i");
		$timesecond = AgoViewHelper::extrdate(date("Y-m-d H:i:s",$time),"s");
		
		$yearsince = $nowyear - $timeyear;
		$monthsince = $nowmonth - $timemonth;
		$daysince = $nowday - $timeday;
		$hoursince = $nowhour - $timehour;
		$minutsince = $nowminut - $timeminut;
		
		
		$secondsince = $nowsecond - $timesecond;
		
		/// Seconds Conf ///
		if ($nowsecond < $timesecond) {
			$secondsince = 60 + $nowsecond - $timesecond;
			$minusminut = TRUE;
		}
		/// Minuts Conf ///
		if (@$minusminut == TRUE) {
			$nowminut = $nowminut - 1;
			
			if ($nowminut == $timeminut) {
				$minutsince =  $nowminut - $timeminut;
			}
		}
		if ($nowminut < $timeminut) {
			$minutsince =  60 + $nowminut - $timeminut;
			$minushour = TRUE;
		}	
		/// Hours Conf ///	
		if (@$minushour == TRUE) {
			$nowhour = $nowhour - 1;
			
			if ($nowhour == $timehour) {
				$hoursince =  $nowhour - $timehour;
			}
		}
		if ($nowhour < $timehour) {
			$hoursince =  24 + $nowhour - $timehour;
			$minusday = TRUE;
		}	
		/// Days Conf ///	
		if (@$minusday == TRUE) {
			$nowday = $nowday - 1;
			
			if ($nowday == $timeday) {
				$daysince =  $nowday - $timeday;
			}
		}
		if ($nowday < $timeday) {
			$daysince =  30 + $nowday - $timeday;
			$minusmonth = TRUE;
		}	
		/// Months Conf ///
		if (@$minusmonth == TRUE) {
			$nowmonth = $nowmonth - 1;
			
			if ($nowmonth == $timemonth) {
				$monthsince =  $nowmonth - $timemonth;
			}
		}
		if ($nowmonth < $timemonth) {
			$monthsince =  12 + $nowmonth - $timemonth;
			$minusyear = TRUE;
		}	
		/// Years Conf ///
		if (@$minusyear == TRUE) {
			$nowyear = $nowyear - 1;
			
			if ($nowyear == $timeyear) {
				$yearsince =  $nowyear - $timeyear;
			}
		}
		if ($nowyear < $timeyear) {
			$error = "The time you giving seems to be older then todays time";
		}	
		/////////////// end /////////////	
		if (!@$error){
			if (!empty($yearsince)) {
			$time = "$yearsince years $monthsince months";	
			}
			if (empty($yearsince)) {
			$time = "$monthsince months $daysince days ago";
			}
			if (empty($yearsince) && empty($monthsince)) {
			$time = "$daysince days $hoursince hours ago";
			}
			if (empty($yearsince) && empty($monthsince) && empty($daysince)) {
			$time = "$hoursince hours $minutsince minutes ago";	
			}
			if (empty($yearsince) && empty($monthsince) && empty($daysince) && empty($hoursince)) {
			$time = "$minutsince minutes $secondsince seconds ago";	
			}
		return $time;
		}
		else echo $error;
	}
	
	// extrdate Extracts mySQL timestamp into any date() parameter given
	function extrdate($date,$parameters) {
		$strtotime = strtotime($date);
		$newdate = date($parameters,$strtotime);	
		return $newdate;
	}
}