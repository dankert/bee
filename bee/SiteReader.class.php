<?php

class SiteReader
{
	var $pages           = array();
	var $pagesByDate     = array();
	var $pagesByKeyword  = array();
	var $pagesByUrl      = array();
	var $pagesByCategory = array();
	var $keywordNames    = array();
	var $categoryNames   = array();
	
	/**
	 * Reads all pages of a site.
	 */
	function readSite()
	{
		$this->pages = $this->read_all_pages('');
		$this->filter_future_pages();
		$this->filter_unique_page_url();
		
		foreach( $this->pages as $page )
		{
			$url = $page['url'];
			$this->pagesByUrl[$url] = $page;
				
			$date = $page['date'];
			if	( !isset($this->pagesByDate[$date]))
				$this->pagesByDate[$date] = array();
			$this->pagesByDate[$date][] = $page;
				
			$keywords = $page['keywords'];
			foreach( $keywords as $keyword ) {
				if	( !isset($this->pagesByKeyword[$keyword]))
					$this->pagesByKeyword[$keyword] = array();
				$this->pagesByKeyword[$keyword][] = $page;
			}
		
			if	( isset( $page['category'] ) )
			{
				$category = $this->urlify($page['category']);
				if	( !isset($this->pagesByCategory[$category]))
					$this->pagesByCategory[$category] = array();
				$this->pagesByCategory[$category][] = $page;
				$this->categoryNames[$category] = $page['category'];			
			}
		}
		
		
		ksort($this->pagesByDate    );
		ksort($this->pagesByKeyword );
		ksort($this->pagesByUrl     );
		ksort($this->pagesByCategory);
		ksort($this->keywordNames   );
		ksort($this->categoryNames  );
	}


	/**
	 * Reads a content file.
	 * 
	 * @param Filename $filename
	 * @return Array of pages
	 */
	function read_page($filename)
	{
		$file = file(SITE_DIR.SLASH.$filename);
		$page = array();
		$fileParts = $this->explode_array('---',$file);
		if	(isset($fileParts[1]))
		{
			$pageYaml = spyc_load( implode("\n",$fileParts[1]));
			$page['config'] = array_change_key_case($pageYaml);  // force lowercase on page attributes
		}
	
		if	(isset($fileParts[2]))
		{
			$page['value'] = implode("\n",$fileParts[2]);
		}
		$page['filename' ] = $filename;
		$page['filemtime'] = filemtime(SITE_DIR.SLASH.$filename);
	
		
		if (isset($page['config']['date']))
			$page['date'] = strtotime($page['config']['date']);
		else
			$page['date'] = $page['filemtime'];
	
		// Category
		if	( isset( $page['config']['category'] ))
			$page['category'] = $page['config']['category'];
		
		
		// keywords is similar to tags
		if	( isset( $page['config']['keywords'] ))
			$page['config']['tags'] = $page['config']['keywords'];
			
		if	( isset( $page['config']['tags'] ))
			if  ( is_array($page['config']['tags']) )
				$page['keywords'] = $this->urlify_array($page['config']['tags']);
			else
				$page['keywords'] = $this->urlify_array(explode(',',$page['config']['tags']));
		else
			$page['keywords'] = array();
		
		
			
		if (!empty($page['config']['url']))
			if	( substr($page['config']['url'],0,1)=='/')
				$page['url'] = $this->urlify(substr($page['config']['url'],1));
			else
				$page['url'] = dirname($page['filename']).SLASH.$this->urlify($page['config']['url']);
		elseif (!empty($page['config']['title']))
		{
			if (!empty($page['config']['category']))
				$page['url'] = $this->urlify($page['config']['category']).SLASH.$this->urlify($page['config']['title']);
			else
				$page['url'] = $this->urlify($page['config']['title']);
		}
		else
			// no title available, so we need to use the filename instead.
			if (!empty($page['config']['category']))
				$page['url'] = $this->urlify($page['config']['category']).SLASH.$this->urlify(substr(basename($page['filename']),0,-3));
			else
				$page['url'] = $this->urlify(substr(basename($page['filename']),0,-3));
		
		if (isset($page['config']['title']))
			$page['title'] = $page['config']['title'];
		else
			$page['title'] = basename($page['filename']);
		
		if (isset($page['config']['author']))
			// Username from page config.
			$page['author'] = $page['config']['author'];
		else
		{
			// Fallback: Detect username from os.
			$uid = posix_getpwuid( fileowner(SITE_DIR.SLASH.$filename) );
			$page['author'] = $uid['name'];
		}
		
		return $page;
	}
	
	
	/**
	 * removes pages which has a future date.
	 * @param unknown_type $pages
	 * @return multitype:unknown
	 */
	function filter_future_pages()
	{
		$validatedPages = array();
		
		foreach( $this->pages as $page )
		{
			if	( $page['date'] <= time() )
				$validatedPages[] = $page;
			else
				; // pages in the future are not being published yet
		}
		
		$this->pages = $validatedPages;
	}
	
	
	/**
	 * forces that every page url is unique.
	 * @param unknown_type $pages
	 * @return multitype:Ambigous <string, unknown>
	 */
	function filter_unique_page_url()
	{
		$urls = array();
		$validatedPages = array();
		
		$i=1;
		
		foreach( $this->pages as $page )
		{
			$url = $page['url'];
			
			// Prüfung auf doppelte Dateinamen. Jeder Dateiname muss eindeutig sein. Ggf. wird ein Suffix ergänzt.
			while( in_array($url, $urls) )
			{
				$url = $page['url'].'-'.++$i;
				$page['url'] = $url;
			}
			$urls[] = $url;
			$validatedPages[] = $page;
		}
		$this->pages = $validatedPages;
	}
	
	
	
	function read_all_pages( $dir ) 
	{
		$pages = array();
		if ( $handle = opendir(SITE_DIR.SLASH.$dir) )
		{
			while (false !== ($entry = readdir($handle)))
			{
				if  ( $entry[0] == '.' )
					continue;
				if	( is_dir( SITE_DIR.SLASH.$dir.(!empty($dir)?SLASH:'').$entry ))
				{
					$pages = array_merge($pages,$this->read_all_pages($dir.(!empty($dir)?SLASH:'').$entry));
				}
				if	( is_file( SITE_DIR.SLASH.$dir.(!empty($dir)?SLASH:'').$entry ) && substr($entry,-3)=='.md')
				{
					$page = $this->read_page($dir.(!empty($dir)?SLASH:'').$entry);
					
					$pages[] = $page;
				}
			}
			closedir($handle);
		}
		
		return $pages;
	}


	/**
	 * Creates a slug url out of the filename.
	 * @param $filename Name
	 * @return string
	 */
	function urlify( $filename )
	{
		$slug = $filename;
		$slug = iconv('utf-8', 'ascii//TRANSLIT', $slug); 
		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $slug);
		$slug = trim($slug, '-');
		$slug = strtolower($slug);
		
		return $slug;
	}
	
	
	function urlify_array( $nameArray )
	{
		$newNames = array();
		foreach( $nameArray as $name)
		{
			$newNames[] = $this->urlify($name);
		}
		return $newNames;
		
	}
	
	
	function explode_array($sep, $array)
	{
		$idx = 0;
		$target = array();
		$target[$idx] = array();
	
		foreach( $array as $line )
		{
			if	( trim($line) == $sep )
			{
				$target[++$idx] = array();
				continue;
			}
			$target[$idx][] = $line;
		}
	
		return $target;
	}
}

?>