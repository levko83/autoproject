<?php
/**
 * Pager
 *
 */
class DateViewHelper {

	public static function date($value, $type = 'full')
	{
		$translates = Register::get('translates');
		
		$aMonth = array(
			1	=> $translates['front.january'],
			2	=> $translates['front.february'],
			3	=> $translates['front.march'],
			4	=> $translates['front.april'],
			5	=> $translates['front.may'],
			6	=> $translates['front.june'],
			7	=> $translates['front.july'],
			8	=> $translates['августа'],
			9	=> $translates['front.september'],
			10	=> $translates['front.october'],
			11	=> $translates['front.november'],
			12	=> $translates['front.december'],
		);
		$month = intval(date('m',$value));
		switch ($type)
		{
			case 'number': return date('d.m.y',$value);
			case 'day': return mktime(0,0,0,date('m',$value),date('d',$value),date('Y',$value));
			case 'only_day' : return date('d',$value);
			case 'only_month' : return $aMonth[date('m',$value)];
			case 'only_year' : return date('Y',$value);
			case 'full': return date('d',$value).' '.$aMonth[$month].' '.date('Y');
			default: return  date('d',$value).' '.$aMonth[$month];
		}
	}
}