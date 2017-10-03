<?php

class HtmlGenerator extends GeneratorBase
{
	function generate()
	{
		$file = file(SITES_DIR.SLASH.SITE.SLASH.implode('/',PATH).'.html');
	
		lastModified( filemtime(SITES_DIR.SLASH.SITE.SLASH.implode('/',PATH).'.html') );
		$html = implode("\n",$file);
		define('ARTICLE',$html);
		define('TITLE'  ,implode('/',PATH));
		$this->outputTheme();
		exit;
	}
	
}