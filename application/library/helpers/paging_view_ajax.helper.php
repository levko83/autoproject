<?php
/**
 * Pager
 *
 */
class PagingViewAjaxHelper {
	
	public static function paging($url, $total, $current, $id='content')
	{
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
					$contentBlock[] = '<a>'.$i.'</a>';
				} else {
					$contentBlock[] = '<a href="" onclick="ajax(\''.$link.'\',\''.$id.'\');return false;">'.$i.'</a>';
				}
			}
			$content = join('&nbsp;&nbsp;', $contentBlock);
			$content = preg_replace('#(</p>&nbsp;&nbsp;&nbsp;\|)#is','</p>',$content);
			$content = preg_replace('#(\|&nbsp;&nbsp;&nbsp;<p)#is','<p',$content);
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
					$contentBlock[] = '<a>'.$i.'</a>';
				} else {
					$contentBlock[] = '<a href="" onclick="ajax(\''.$link.'\',\''.$id.'\');return false;">'.$i.'</a>';
				}
			}
			$link = str_replace(':page:', $current + 1, $url);
			$contentBlock[] = '<a href="" onclick="ajax(\''.$link.'\',\''.$id.'\');return false;">&raquo;</a>';
			$content = join('&nbsp;&nbsp;', $contentBlock);
			$content = preg_replace('#(</p>&nbsp;&nbsp;&nbsp;\|)#is','</p>',$content);
			$content = preg_replace('#(\|&nbsp;&nbsp;&nbsp;<p)#is','<p',$content);
			return $content;
		}
		
		if ($total - $current == 0)
		{
			$contentBlock = array();
			$link = str_replace(':page:', $current - 1, $url);
			$contentBlock[] = '<a href="" onclick="ajax(\''.$link.'\',\''.$id.'\');return false;">&laquo;</a>';
			for ($i = $total - 4; $i <= $total; $i++)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<a>'.$i.'</a>';
				} else {
					$contentBlock[] = '<a href="" onclick="ajax(\''.$link.'\',\''.$id.'\');return false;">'.$i.'</a>';
				}
			}
			$content = join('&nbsp;&nbsp;', $contentBlock);
			$content = preg_replace('#(</p>&nbsp;&nbsp;&nbsp;\|)#is','</p>',$content);
			$content = preg_replace('#(\|&nbsp;&nbsp;&nbsp;<p)#is','<p',$content);
			return $content;
		}
		
		$contentBlock = array();
		$link = str_replace(':page:', $current - 1, $url);
		$contentBlock[] = '<a href="" onclick="ajax(\''.$link.'\',\''.$id.'\');return false;">&laquo;</a>';
		for ($i = $current - 2; $i <= $current + 2; $i++)
		{
			if ($i <= $total && $i > 0)
			{
				$link = str_replace(':page:', $i, $url);
				if ($i == $current)
				{
					$contentBlock[] = '<a>'.$i.'</a>';
				} else {
					$contentBlock[] = '<a href="" onclick="ajax(\''.$link.'\',\''.$id.'\');return false;">'.$i.'</a>';
				}
			}
		}
		$link = str_replace(':page:', $current + 1, $url);
		$contentBlock[] = '<a href="'.$link.'">&raquo;</a>';
		$content = join('&nbsp;&nbsp;', $contentBlock);
		$content = preg_replace('#(</p>&nbsp;&nbsp;&nbsp;\|)#is','</p>',$content);
		$content = preg_replace('#(\|&nbsp;&nbsp;&nbsp;<p)#is','<p',$content);
		return $content;
	}
}