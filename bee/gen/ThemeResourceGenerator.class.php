<?php

class ThemeResourceGenerator extends GeneratorBase
{
	var $filename;
	
	function __construct( $filename )
	{
		$this->filename = $filename;
	}
	
	
	function generate()
	{
		// Read a static file from theme (Images, CSS or JS)
		HttpUtil::lastModified(filemtime(THEME_DIR.SLASH.THEME.SLASH.$this->filename));
		readfile(THEME_DIR.SLASH.THEME.SLASH.$this->filename);
	}
}


?>