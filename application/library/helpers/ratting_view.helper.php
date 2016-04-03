<?php
/**
 * Pager
 *
 */
class RattingViewHelper {

	public static function ratting($ratting)
	{
		$content = '';
		for($i = 0.5; $i <= 4.5; $i = $i + 1)
		{
			if ($ratting >= $i)
			{
				$content .='<span class="inline"><a class="star-lit"></a></span>';
			} else {
				$content .='<span class="inline"><a class="star"></a></span>';
			}
		}
		$content .= '';
		return $content;
	}
}