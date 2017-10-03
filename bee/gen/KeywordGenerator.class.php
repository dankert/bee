<?php

class KeywordGenerator extends GeneratorBase
{

	var $keyword;
	
	function __construct( $keyword )
	{
		$this->keyword = $keyword; 
	}
	
	
	function generate()
	{
		global $PAGES_BY_KEYWORD;
		
		if	( ! isset($PAGES_BY_KEYWORD[$this->keyword]) )
			HttpUtil::sendStatus(404,"Category not found");
		
		$html = '<ul>';
		
		foreach( $PAGES_BY_KEYWORD[$this->keyword] as $page )
		{
			$html.='<li><a href="'.SITE_UP.$page['url'].'">'.$page['config']['title'].'</a></li>';
		}
		$html .= '</ul>';
		define('CONTENT',$html);
		define('TITLE'  ,'Keyword '.$this->keyword);
		
		$this->outputTheme();
		exit;
	}
	
}


?>