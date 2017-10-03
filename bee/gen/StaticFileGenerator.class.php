<?php

class StaticFileGenerator extends GeneratorBase
{
	var $filename;
	
	function __construct( $filename )
	{
		$this->filename = $filename;
	}
	
	function generate()
	{
		// Read a static file from site (Images, CSS or JS)
		header('Content-Type: application/octet-stream');
		HttpUtil::lastModified(filemtime(SITE_DIR.SLASH.$this->filename));
		readfile(SITE_DIR.SLASH.$this->filename);	
	}
}