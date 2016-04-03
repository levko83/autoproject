<?php
/**
 * Price
 *
 */
class PriceHelper {
	
	/*public static function number($number,$exhange=false,$round=false){
		
		$curExchangeType = Register::get('curExchangeType');
		$nf = Register::get('nf');
		
		if ($round) {
			$setRound = Register::get('roundDefault');
		}
		else {
			$Rounds = Register::get('rounds');
			$setRound = (isset($Rounds[$curExchangeType]) && $Rounds[$curExchangeType])?$Rounds[$curExchangeType]:false;
		}
		if (isset($setRound) && $setRound) {
			$number = ceil($number/$setRound)*$setRound;
		}
		
		if ($exhange) {
			return number_format($number, $nf[$curExchangeType], ".", " ");
		}
		
		if (!$number)
			return 0;
		
		return number_format($number, $nf[$curExchangeType], ".", " ");
	}*/
	
	public static function number($number,$exhange=false,$round=false){
		
		$setRound = Register::get('roundDefault');
		
		if (isset($setRound) && $setRound) {
			$number = ceil($number/$setRound)*$setRound;
		}
		
		if ($exhange) {
			return number_format($number, $nf[$curExchangeType], ".", " ");
		}
		
		if (!$number)
			return 0;
		
		//return number_format($number, '2', ",", "");
		return round($number, '2');
	}
	
	public static function percent($num_amount, $num_total) {
		$count1 = $num_amount / $num_total;
		$count2 = $count1 * 100;
		$count = number_format($count2, 2);
		return $count;
	}
	
	public static function inverse_percent($num_amount, $num_total) {
		$num_rest = $num_total - $num_amount;
		if ($num_rest > 0) {
			$count1 = $num_rest / $num_total;
			$count2 = $count1 * 100;
			$count = number_format($count2, 2);
			return $count;
		} else {
			return 0;
		}
	}
	
	
	public static function myceil($number){
		
		$curExchangeType = Register::get('curExchangeType');
		
		$setRound = Register::get('roundDefault');
		if (isset($setRound) && $setRound) {
			$number = ceil($number/$setRound)*$setRound;
		}
		
		if (!$number)
			return 0;
		
		return $number;
	}
	public static function universal($number){
		
		if (!$number)
			return (int)$number;
			
		return number_format($number, 2, ".", " ");
	}
	public static function rate($summary=0){
		$accountRate = Register::get('accountRate');
		if ($accountRate){
			return $summary/$accountRate;
		}
		return $summary;
	}
}
?>