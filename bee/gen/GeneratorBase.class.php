<?php

class GeneratorBase
{
	function generate()
	{
		
	}
	
	
	function outputTheme()
	{
		global $PAGES_BY_CATEGORY;
		global $PAGES_BY_KEYWORD;
		global $PAGES_BY_DATE;
		global $PAGES_RELATED;
		global $PAGE;
		global $KEYWORD_NAMES;
		global $CATEGORY_NAMES;
		
		global $siteconfig;
		extract($siteconfig,EXTR_PREFIX_ALL,'site');
		
		require( THEME_DIR.SLASH.THEME.'.php');
		exit;
	}
	
}


?>