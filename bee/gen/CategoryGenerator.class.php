<?php

class CategoryGenerator extends GeneratorBase
{
	var $category;
	
	function __construct( $category )
	{
		$this->category = $category;
	}
	function generate()
	{
		global $PAGES_BY_CATEGORY;
	
		if	( ! isset($PAGES_BY_CATEGORY[$this->category]) ) 
			HttpUtil::sendStatus(404,"Category not found");
				
		$html = '<ul>';
		foreach( $PAGES_BY_CATEGORY[$this->category] as $page )
			$html.='<li><a href="'.SITE_UP.$page['url'].'">'.$page['config']['title'].'</a></li>';
		$html .= '</ul>';
		define('CONTENT',$html);
		define('TITLE'  ,$keyword);
		$this->outputTheme();
	}
}


?>