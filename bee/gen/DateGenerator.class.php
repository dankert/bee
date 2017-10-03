<?php

class DateGenerator extends GeneratorBase
{
	var $category;
	
	function __construct( $from,$to )
	{
		$this->from = $from;
		$this->to   = $to;
	}
	
	function generate()
	{
		global $PAGES_BY_DATE;
	
		$html = '<ul>';
		foreach( $PAGES_BY_DATE as $pageOfDate )
			foreach( $pageOfDate as $page )
				if	( $page['date'] >= $this->from && $page['date'] <= $this->to )
					$html.='<li><a href="'.SITE_UP.$page['url'].'">'.$page['config']['title'].'</a></li>';
		$html .= '</ul>';
		define('CONTENT',$html);
		define('TITLE'  ,'from '.date('r',$this->from).' to '.date('r',$this->to));
		
		$this->outputTheme();
	}
}


?>