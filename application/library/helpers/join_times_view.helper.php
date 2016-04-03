<?php
/**
 * Pager
 *
 */
class JoinTimesViewHelper {
	
	public function joinTimes($times, $glue = ' и ')
	{
		if (empty($times))
			return '';
		if (count($times) == 1)
			return end($times);
		if (count($times) == 2)
			return join($glue, $times);
		$times = array_values($times);
		$endTime = end($times);
		unset($times[count($times) - 1]);
		return join(', ', $times).$glue.$endTime;
	}
}