<?php

class IndexGenerator  extends GeneratorBase
{
	var $count = 20;
	
	function generate()
	{
		define('INDEX'  ,true);
		$article = '<ul>';
	
		global $PAGES_BY_DATE;
	
		$nr = 0;
		foreach( array_reverse($PAGES_BY_DATE) as $pagesByDate )
		{
			foreach( $pagesByDate as $page)
			{
				if	( ++$nr > $this->count )
					break;
				
				$article .= '<li><a href="'.SITE_UP.$page['url'].'">'.$page['title'].'</a></li>';
			}
		}
		$article .= '</ul>';
	
	
		define('CONTENT',$article);
		define('TITLE','Index');
		
		$this->outputTheme();
		exit;
	}
}


?>