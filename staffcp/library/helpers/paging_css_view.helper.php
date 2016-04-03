<?php
/**
 * Pager
 *
 */
class Paging_cssViewHelper {
	public function paging($url, $total, $current){
		$content = '<script type="text/javascript">';
		$content .= '$(function() {';
			$content .= '$(".slider-paging").pagination({value:'.$current.',total:'.$total.',';
				$content .= "onChange:function(value) {location = '".$url."'+value;}";
			$content .= '});';
		$content .= '});';
		$content .= '</script>';
		$content .= '<div class="slider-paging sp-slider-wrapper">';
		$content .= '<nav>';
			$content .= '<a href="#" class="sp-prev">Предыдущий</a>';
			$content .= '<a href="#" class="sp-next">Дальше</a>';
		$content .= '</nav>';
		$content .= '</div>';
		return $content;
	}
}