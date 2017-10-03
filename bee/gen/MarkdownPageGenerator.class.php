<?php

class MarkdownPageGenerator extends GeneratorBase
{
	var $page;
	
	function __construct( $page )
	{
		$this->page = $page;
	}
	
	function generate()
	{
	
		HttpUtil::lastModified( $this->page['date'] );
	
		$parsedown = new Parsedown();
	
		$html = $parsedown->text( $this->page['value'] );
	
		define('CONTENT',$html);
		define('TITLE'  ,$this->page['title']);
		global $PAGE;
		$PAGE = $this->page;
	
		global $PAGES_RELATED;
		$PAGES_RELATED = $this->get_related_pages();
		
	
		if	( is_file(SITES_DIR.SLASH.SITE.'/site-config.ini'))
			extract(parse_ini_file( SITES_DIR.SLASH.SITE.'/site-config.ini'),EXTR_PREFIX_ALL,'site');
	
		$this->outputTheme();
	
		exit;
	}




	/**
	 * Determine related pages.
	 */
	function get_related_pages()
	{
		global $PAGES_BY_KEYWORD;
		global $PAGES_BY_URL;
		
		$relatedPages = array();
		foreach( $this->page['keywords'] as $keyword )
		{
			foreach( $PAGES_BY_KEYWORD[$keyword] as $page)
			{
				if	( $page['url'] == $this->page['url'] )
					continue; // only other sites are related.
				
				if	( !isset($relatedPages[$page['url']]))
					$relatedPages[$page['url']] = 1;
				else
					$relatedPages[$page['url']] = $relatedPages[$page['url']] + 1;
			}
		}
		arsort($relatedPages);
		$pages = array();
		foreach( $relatedPages as $url=>$count)
		{
			$pages[] = $PAGES_BY_URL[$url];
		}
	
		return $pages;
	}



}

?>