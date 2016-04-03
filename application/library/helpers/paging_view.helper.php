<?php
/**
 * Pager
 *
 */
class PagingViewHelper {
	
	public static function paging($url, $total, $current)
	{
		$translates = Register::get('translates');
		
		if ($total <= 1)
			return '';
			
			
			
		if ($total <= 5)
		{
			$contentBlock = array();
			for ($i = 1; $i <= $total; $i++)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<li class="active"><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<li><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
					}
					else {
						$contentBlock[] = '<li><a rel="follow" href="'.$link.'">'.$i.'</a></li>';
					}
				}
			}
			$content = join('', $contentBlock);
			return $content;
		}
		
		
		
		
		
		if ($current == 1)
		{
			$contentBlock = array();
			for ($i = 1; $i <= 5; $i++)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<li class="active"><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<li><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
					}
					else {
						$contentBlock[] = '<li><a rel="follow" href="'.$link.'">'.$i.'</a></li>';
					}
				}
			}
			$link = str_replace(':page:', $current + 1, $url);
			// $contentBlock[] = '<li><a rel="follow" href="'.$link.'">'.$translates['front.page.next'].' &raquo;</a></li>';
			$content = join('', $contentBlock);
			return $content;
		}
		
		
		
		
		if ($total - $current == 0)
		{
			$contentBlock = array();
			$link = str_replace(':page:', $current - 1, $url);
			// $contentBlock[] = '<li><a rel="follow" href="'.$link.'">&laquo; '.$translates['front.page.back'].'</a></li>';
			for ($i = $total - 4; $i <= $total; $i++)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<li class="active"><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<li><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
					}
					else {
						$contentBlock[] = '<li><a rel="follow" href="'.$link.'">'.$i.'</a></li>';
					}
				}
			}
			$content = join('', $contentBlock);
			return $content;
		}
		
		
		
		$contentBlock = array();
		$link = str_replace(':page:', $current - 1, $url);
		// $contentBlock[] = '<li><a rel="follow" href="'.$link.'">&laquo; '.$translates['front.page.back'].'</a></li>';
		for ($i = $current - 2; $i <= $current + 2; $i++)
		{
			if ($i <= $total && $i > 0)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<li class="active"><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<li><a rel="follow" href="'.$uri.'">'.$i.'</a></li>';
					}
					else {
						$contentBlock[] = '<li><a rel="follow" href="'.$link.'">'.$i.'</a></li>';
					}
				}
			}
		}
		$link = str_replace(':page:', $current + 1, $url);
		// $contentBlock[] = '<li><a rel="follow" href="'.$link.'">'.$translates['front.page.next'].' &raquo;</a></li>';
		$content = join('', $contentBlock);
		return $content;
	}
}