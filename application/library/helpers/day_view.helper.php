<?php
/**
 * Pager
 *
 */
class DayViewHelper {

	public function day($date)
	{
		$days = array(
			1 => 'Понедельник',
			2 => 'Вторник',
			3 => 'Среда',
			4 => 'Четверг',
			5 => 'Пятница',
			6 => 'Суббота',
			7 => 'Воскресение',
		);
		$result = $days[date('N', $date)];
		return $result;
	}
}