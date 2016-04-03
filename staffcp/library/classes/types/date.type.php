<?php

class DateType extends Type  {
	var $aMonth;
	var $bEmpty = false;
	
	public function getSaveValue($date) {
		if (is_numeric($date))
			return $date;
		if (preg_match('|(\d{1,2})\.(\d{1,2})\.(\d{1,4}) (\d{1,2})\:(\d{1,2})|',$date,$aD)) {
			$d = $aD[1];
			$m = $aD[2];
			$y = $aD[3];
			$h = $aD[4];
			$min = $aD[5];
			if (strlen($y)==2)
				$y = '20'.$y;
			return mktime($h,$min,0,$m,$d,$y);
		}
		
		if (preg_match('|(\d{1,2})\.(\d{1,2})\.(\d{1,4})|',$date,$aD)) {
			$d = $aD[1];
			$m = $aD[2];
			$y = $aD[3];
			if (strlen($y)==2)
				$y = '20'.$y;
			return mktime(0,0,0,$m,$d,$y);
		}
		
		
	}
	
	public function getFormValue($val='') {
		
		$translates = Register::get('translates');
		
		if (!empty($val[$this->fieldName])) {
			$this->value = strtotime($val[$this->fieldName]);
		}
		elseif (empty($this->value)) {
			$this->value = time();
		}
		$showTime = !empty($this->fieldInfo['show_time']);
		
		if (!$showTime)
			$value = date('d.m.Y',$this->value);
		else
			$value = date('d.m.Y H:i',$this->value);
			
		$result = '<input type="text" name="form['.$this->getFieldName().']" value="'.$value.'" id="'.$this->getFieldName().'">';
		$result .= '<input type="button" id="'.$this->getFieldName().'_picker" value="'.$translates['select.date'].'">';
		$showTimeJs = $showTime?'showsTime: true, ifFormat:"%d.%m.%Y %H:%M", daFormat:"%d.%m.%Y %H:%M",':'';
		$result .=<<<EOD
		<script>
			Calendar.setup(
				{
					{$showTimeJs}
					inputField: '{$this->getFieldName()}',
					button: '{$this->getFieldName()}_picker',
					date: '{$value}'
				}
			);
			Calendar.setup(
				{
					{$showTimeJs}
					inputField: '{$this->getFieldName()}',
					button: '{$this->getFieldName()}',
					date: '{$value}',
					eventName: 'focus'
				}
			);

		</script>
EOD;
		
		return $result;
	}
	

	public function getValueArray() {
		$aDate = explode('-',$this->value);
		return array(
			'year'  => $aDate[0],
			'month' => $aDate[1],
			'day'   => intval($aDate[2]),
			);
	}
	
	public function getViewValue() {
		if ($this->value){
			if (empty($this->fieldInfo['show_time']))
				return date('d.m.Y',$this->value);
			else
				return date('d.m.Y H:i',$this->value);
		}
		else {
			return '-';
		}
	}
}