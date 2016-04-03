<?php
/**
 */
class DateViewHelper {

	public static function date($value, $type = 'full')
	{
		$translates = Register::get('translates');
		
		$aMonth = array(
			1	=> $translates['january'],
			2	=> $translates['february'],
			3	=> $translates['march'],
			4	=> $translates['april'],
			5	=> $translates['may'],
			6	=> $translates['june'],
			7	=> $translates['july'],
			8	=> $translates['august'],
			9	=> $translates['september'],
			10	=> $translates['october'],
			11	=> $translates['november'],
			12	=> $translates['december'],
		);
		$month = intval(date('m',$value));
		switch ($type)
		{
			case 'number': return date('d.m.y H:i',$value);
			case 'day': return mktime(0,0,0,date('m',$value),date('d',$value),date('Y',$value));
			case 'only_day' : return date('d',$value);
			case 'only_month' : return $aMonth[date('m',$value)];
			case 'only_year' : return date('Y',$value);
			case 'full': return date('d',$value).' '.$aMonth[$month].' '.date('Y',$value);
			default: return  date('d',$value).' '.$aMonth[$month];
		}
	}
}