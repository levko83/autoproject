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
					$contentBlock[] = '<b>'.$i.'</b>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<a href="'.$uri.'">'.$i.'</a>';
					}
					else {
						$contentBlock[] = '<a href="'.$link.'">'.$i.'</a>';
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
					$contentBlock[] = '<b>'.$i.'</b>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<a href="'.$uri.'">'.$i.'</a>';
					}
					else {
						$contentBlock[] = '<a href="'.$link.'">'.$i.'</a>';
					}
				}
			}
			$link = str_replace(':page:', $current + 1, $url);
			$contentBlock[] = '<a href="'.$link.'">'.$translates['next'].' &raquo;</a>';
			$content = join('', $contentBlock);
			return $content;
		}
		
		
		
		
		if ($total - $current == 0)
		{
			$contentBlock = array();
			$link = str_replace(':page:', $current - 1, $url);
			$contentBlock[] = '<a href="'.$link.'">&laquo; '.$translates['back'].'</a>';
			for ($i = $total - 4; $i <= $total; $i++)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<b>'.$i.'</b>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<a href="'.$uri.'">'.$i.'</a>';
					}
					else {
						$contentBlock[] = '<a href="'.$link.'">'.$i.'</a>';
					}
				}
			}
			$content = join('', $contentBlock);
			return $content;
		}
		
		
		
		$contentBlock = array();
		$link = str_replace(':page:', $current - 1, $url);
		$contentBlock[] = '<a href="'.$link.'">&laquo; '.$translates['back'].'</a>';
		for ($i = $current - 2; $i <= $current + 2; $i++)
		{
			if ($i <= $total && $i > 0)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<b>'.$i.'</b>';
				} else {
					if ($i == 1){
						$uri = preg_replace("|&?page=[0-9]+&?|", "", $link);
						$uri = preg_replace("|&?limit=[0-9]+&?|", "", $uri);
						$uri = preg_replace("|\?$|", "", $uri);
						$contentBlock[] = '<a href="'.$uri.'">'.$i.'</a>';
					}
					else {
						$contentBlock[] = '<a href="'.$link.'">'.$i.'</a>';
					}
				}
			}
		}
		$link = str_replace(':page:', $current + 1, $url);
		$contentBlock[] = '<a href="'.$link.'">'.$translates['next'].' &raquo;</a>';
		$content = join('', $contentBlock);
		return $content;
	}
}