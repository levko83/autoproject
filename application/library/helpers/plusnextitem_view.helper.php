<?php
/**
 *
 */
class PlusnextitemViewHelper {
	
	static $cc = 0;
	static $cc_car = 0;
	
	public static function try_save($i,$first,$items){
		if (isset($items[$i])){
			$next_un = unserialize($items[$i]);
			if (
				strtoupper(FuncModel::stringfilter($next_un->SUP_BRAND)) == strtoupper(FuncModel::stringfilter($first->SUP_BRAND)) 
				&& 
				strtoupper(FuncModel::stringfilter($next_un->ART_ARTICLE_NR_CLEAR)) == strtoupper(FuncModel::stringfilter($first->ART_ARTICLE_NR_CLEAR))
				&&
				strlen(FuncModel::stringfilter($next_un->ART_ARTICLE_NR_CLEAR)) == strlen(FuncModel::stringfilter($first->ART_ARTICLE_NR_CLEAR))
			){
				$i++;
				PlusnextitemViewHelper::$cc++;
				PlusnextitemViewHelper::try_save($i,$next_un,$items);
			}
		}
	}
	
	public static function try_save_bycar($i,$first,$items){
		if (isset($items[$i])){
			$next_un = unserialize($items[$i]);
			if (
				strtoupper(FuncModel::stringfilter($next_un->DETAIL[0]->SUP_BRAND)) == strtoupper(FuncModel::stringfilter($first->DETAIL[0]->SUP_BRAND))
				&& 
				strtoupper(FuncModel::stringfilter($next_un->DETAIL[0]->ART_ARTICLE_NR)) == strtoupper(FuncModel::stringfilter($first->DETAIL[0]->ART_ARTICLE_NR))
				&&
				strlen(FuncModel::stringfilter($next_un->DETAIL[0]->ART_ARTICLE_NR)) == strlen(FuncModel::stringfilter($first->DETAIL[0]->ART_ARTICLE_NR))
			){
				$i++;
				PlusnextitemViewHelper::$cc_car++;
				PlusnextitemViewHelper::try_save_bycar($i,$next_un,$items);
			}
		}
	}
	
	public static function simple($i,$first,$items){
		
		if (isset($items[$i])){
			$next_un = ($items[$i]);
			if (
				strtoupper(FuncModel::stringfilter($next_un['BRAND_NAME'])) == strtoupper(FuncModel::stringfilter($first['BRAND_NAME'])) && 
				strtoupper(FuncModel::stringfilter($next_un['ARTICLE'])) == strtoupper(FuncModel::stringfilter($first['ARTICLE'])) &&
				strlen(FuncModel::stringfilter($next_un['ARTICLE'])) == strlen(FuncModel::stringfilter($first['ARTICLE']))
			){
				$i++;
				PlusnextitemViewHelper::$cc++;
				PlusnextitemViewHelper::simple($i,$next_un,$items);
			}
		}
	}
}
?>