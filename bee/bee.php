<?php

define('DEVELOPMENT',true);
define('HTML_EXTENSION','.html');

$site = array(
	'title'=>'My new Blog',
	'keywords_dir'=>'tags',
	'category_dir'=>'category',
	'sites_dir'=>'site',
	'theme_dir'=>'theme',
	'html_extension'=>false,
	'http_caching'=>true,
	'theme'=>'default'
);
require('./parsedown/ParseDown.php'); // Markdown
require('./spyc/Spyc.php'); // YAML

define('SCRIPT',basename($_SERVER['SCRIPT_FILENAME']));

define('SITES_DIR','../site');
define('THEME_DIR','../theme');
define('SLASH'     ,'/');

$querypath = explode('/',$_SERVER['PATH_INFO']);

define('ROOT_UP',str_repeat('../',substr_count($_SERVER['PATH_INFO'],'/')));
define('SITE_UP',str_repeat('../',substr_count($_SERVER['PATH_INFO'],'/')-3));

               array_shift($querypath); // Remove first slash
define('CMD'  ,array_shift($querypath));
define('SITE' ,array_shift($querypath));

if (is_null(CMD))
{
	$sitelinks = '';
	if ( $handle = opendir(SITES_DIR.SLASH.SITE.SLASH.implode('/',PATH)) )
	{
		while (false !== ($entry = readdir($handle))) {
			if  ( $entry[0] != '.' )
			{
				$sitelinks.= '<a href="'.ROOT_UP.SCRIPT.'/preview/'.$entry.'">'.$entry.'</a>';
			}
		}
		closedir($handle);
	}
	$preview_link = '<a href="'.ROOT_UP.SCRIPT.'/preview'.'">Preview</a>';
	output( 'Menu',$sitelinks);
	
}
elseif (CMD=='preview')
{
	if	( is_null(SITE) || SITE=='')
	{
		$sitelinks = '';
		if ( $handle = opendir(SITES_DIR.SLASH.SITE.SLASH.implode('/',PATH)) )
		{
			while (false !== ($entry = readdir($handle))) {
				if  ( $entry[0] != '.' )
				{
					$sitelinks.= '<a href="'.ROOT_UP.SCRIPT.'/preview/'.$entry.'/">'.$entry.'</a>';
				}
			}
			closedir($handle);
		}
		output( 'Sites',$sitelinks);
	}
	else
	{
		if	(!is_dir(SITES_DIR.SLASH.SITE))
		{
			http(404,"Not found"); // Site does not exist
		}

		if	( is_file(SITES_DIR.SLASH.'site-config.ini'))
		{
			$siteconfig = parse_ini_file(SITES_DIR.SLASH.'site-config.ini');
			if	( isset($siteconfig['site_dir']))
				define('SITE_DIR',$siteconfig['site_dir']);
			else
				define('SITE_DIR',SITES_DIR.SLASH.SITE);
			if	( isset($siteconfig['theme']))
				define('THEME',$siteconfig['theme']);
			else
				define('THEME','default');
		}
		else 
		{
			define('THEME','default');
			define('SITE_DIR',SITES_DIR.SLASH.SITE);
		}
		
		if	( !is_file(THEME_DIR.SLASH.THEME.'.php') )
			http(501,'Internal Server Error: Theme '.THEME.' not found'); // Theme does not exist
		
		
		$pages = read_all_pages('');
		$pages_by_url     = array();
		$pages_by_date    = array();
		$pages_by_keyword = array();
		
		foreach( $pages as $page )
		{
			$url = $page['url'];
			$i=1;

			// Prüfung auf doppelte Dateinamen. Jeder Dateiname muss eindeutig sein. Ggf. wird ein Suffix ergänzt.
			while( isset($pages_by_url[$url]) )
			{
				$url = $page['url'].'-'.++$i;
			}
			$pages_by_url[$url] = $page;
			$page['url'] = $url;
			
			$date = $page['date'];
			if	( !isset($pages_by_date[$date]))
				$pages_by_date[$date] = array();
			$pages_by_date[$date][] = $page;
			
			$keywords = $page['keywords'];
			foreach( $keywords as $keyword ) {
				if	( !isset($pages_by_keyword[$keyword]))
					$pages_by_keyword[$keyword] = array();
				$pages_by_keyword[$keyword][] = $page;
			}
			
		}
		
		
		ksort($pages_by_date   );
		ksort($pages_by_keyword);
		ksort($pages_by_url    );
		
		define('PAGES_BY_DATE'   ,$pages_by_date   );
		define('PAGES_BY_KEYWORD',$pages_by_keyword);
		define('PAGES_BY_URL'    ,$pages_by_url    );
		define('KEYWORDS'        ,array_keys($pages_by_keyword) );
		
		$filename = implode(SLASH,$querypath);
		
		define('PATH',$querypath);

		// Pfad /tag für die Keywords
		if  (count($querypath)==2 && $querypath[0]=='tag' )
		{
			if	( !isset($querypath[1]))
				http(410,'Bad request'); // Pfad '/tag' ohne weiteres
			
			generate_keyword($querypath[1]);
		}
		
		// Directory-Listing
		if	(is_dir(SITES_DIR.SLASH.SITE.SLASH.$filename))
		{
			generate_directory();
		}
		// Static file from theme
		elseif	(is_file(THEME_DIR.SLASH.THEME.SLASH.$filename))
		{
			// Read a static file from theme (Images, CSS or JS)
			lastModified(filemtime(THEME_DIR.SLASH.THEME.SLASH.$filename));
			readfile(THEME_DIR.SLASH.THEME.SLASH.$filename);
		}
		// Static file from site
		elseif	(is_file(SITE_DIR.SLASH.$filename))
		{
			// Read a static file from site (Images, CSS or JS)
			lastModified(filemtime(SITE_DIR.SLASH.$filename));
			readfile(SITE_DIR.SLASH.$filename);
		}
		elseif	(isset($pages_by_url[$filename]))
		{
			generate_markdown_page($pages_by_url[$filename]);
		}
		elseif	(is_file(SITES_DIR.SLASH.SITE.SLASH.$filename.'.html'))
		{
			generate_html_page();
		}
		else {
			http(404,'Not Found');
		}
		
	}
}
else {
	http(400,'Unknown command');
}


function generate_directory()
{
	define('INDEX'  ,true);
	$article = '<div class="directory"';
	

	
	foreach( array_reverse(PAGES_BY_DATE) as $pages )
	{
		foreach( $pages as $page)
		{
			if	( substr($page['filename'],0,strlen(implode('/',PATH))) == implode('/',PATH) )
				$article .= '<div><a href="'.SITE_UP.$page['url'].'">'.$page['title'].'</a></div>';
		}
	}
	$article .= '</div>';
	
	
	define('ARTICLE',$article);
	define('TITLE',implode('/',PATH));
	define('UP'  ,'../');
	require( THEME_DIR.SLASH.THEME.'.php');
	exit;
}

function generate_markdown_page($page)
{
	lastModified( $page['date'] );
	
	$parsedown = new Parsedown();
	
	$html = $parsedown->text( $page['value'] );
	
	define('ARTICLE',$html);
	define('INDEX'  ,false);
	define('TITLE'  ,$page['title']);
	define('UP'  ,SITE_UP);
	define('PAGE'  ,$page);
	
	$relatedPages = array();
	foreach( $page['keywords'] as $keyword )
	{
		foreach( PAGES_BY_KEYWORD[$keyword] as $pagesPerKeyword)
		{
			foreach($pagesPerKeyword as $page)
			{
				if	( !isset($relatedPages[$page['url']]))
					$relatedPages[$page['url']] = 1;
				else
					$relatedPages[$page['url']] = $relatedPages[$page['url']] + 1;
			}
		}
	}
	arsort($relatedPages);
	$pages = array();
	foreach( $relatedPages as $url)
	{
		$pages[] = PAGES_BY_URL[$url];
	}
	define('RELATED_PAGES',$pages);

	if	( is_file(SITES_DIR.SLASH.SITE.'/site-config.ini'))
		extract(parse_ini_file( SITES_DIR.SLASH.SITE.'/site-config.ini'),EXTR_PREFIX_ALL,'site');
	
	require( THEME_DIR.SLASH.THEME.'.php');
	
	exit;
}

function generate_html_page()
{
	$file = file(SITES_DIR.SLASH.SITE.SLASH.implode('/',PATH).'.html');

	lastModified( filemtime(SITES_DIR.SLASH.SITE.SLASH.implode('/',PATH).'.html') );
	$html = implode("\n",$file);
	define('ARTICLE',$html);
	define('INDEX'  ,false);
	define('TITLE'  ,implode('/',PATH));
	define('UP'  ,SITE_UP);
	require( THEME_DIR.SLASH.THEME.'.php');
	exit;
}


function generate_keyword( $keyword )
{
	$html = '<ul>';
	foreach( PAGES_BY_KEYWORD[$keyword] as $page )
	{
		$html.='<li><a href="'.SITE_UP.$page['url'].'">'.$page['config']['title'].'</a></li>';
	}
	$html .= '</ul>';
	define('ARTICLE',$html);
	define('INDEX'  ,false);
	define('TITLE'  ,$keyword);
	define('UP'  ,'../');
	require( THEME_DIR.SLASH.THEME.'.php');
	exit;
}



function output( $title, $body ) 
{
	header('Content-Type: text/html');
	?>	
<html>
<head>
<title><?php echo $title ?></title>
<link rel="stylesheet" href="<?php echo ROOT_UP ?>bee.css">
</head>
<body>
<h1>Bee Site Generator - <?php echo $title ?></h1>
<?php echo $body ?>
</body></html>
<?php 
	exit;
}




/**
 * Schickt einen HTTP-Status zum Client und beendet das Skript.
 *
 * @param Integer $status HTTP-Status (ganzzahlig) (Default: 501)
 * @param String $text HTTP-Meldung (Default: 'Internal Server Error')
 */
function http( $status=501,$text='Internal Server Error' )
{
	if	( headers_sent() )
	{
		echo "$status $text\n$message";
		exit;
	}

	header('HTTP/1.0 '.intval($status).' '.$text);
	header('Content-Type: text/html');
	echo <<<HTML
<html>
<head><title>$status $text</title></head>
<body>
<h1>$text</h1>
<hr>
<address>Bee</adddress>
</body>
</html>
HTML;
	exit;
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


function read_page($filename)
{
	$file = file(SITE_DIR.SLASH.$filename);
	$page = array();
	$fileParts = explode_array('---',$file);
	if	(isset($fileParts[1]))
	{
		$pageYaml = spyc_load( implode("\n",$fileParts[1]));
		$page['config'] = array_change_key_case($pageYaml);
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
	
	if	( isset( $page['config']['tags'] ))
		if  ( is_array($page['config']['tags']) )
			$page['keywords'] = urlify_array($page['config']['tags']);
		else
			$page['keywords'] = urlify_array(explode(',',$page['config']['tags']));
	else
		$page['keywords'] = array();
	
	
		
	if (!empty($page['config']['url']))
		if	( substr($page['config']['url'],0,1)=='/')
			$page['url'] = urlify(substr($page['config']['url'],1));
		else
			$page['url'] = dirname($page['filename']).SLASH.urlify($page['config']['url']);
	elseif (!empty($page['config']['title']))
	{
		if (!empty($page['config']['category']))
			$page['url'] = urlify($page['config']['category']).SLASH.urlify($page['config']['title']);
		else
			$page['url'] = urlify($page['config']['title']);
	}
	else
		$page['url'] = dirname($page['filename']).SLASH.urlify(substr(basename($page['filename']),0,-3));
	
	if (isset($page['config']['title']))
		$page['title'] = $page['config']['title'];
	else
		$page['title'] = basename($page['filename']);
	
	if (isset($page['config']['author']))
		$page['author'] = $page['config']['author'];
	else
		$page['author'] = posix_getpwuid(fileowner(SITE_DIR.SLASH.$filename))['name'];
	
	return $page;
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
				$pages = array_merge($pages,read_all_pages($dir.(!empty($dir)?SLASH:'').$entry));
			}
			if	( is_file( SITE_DIR.SLASH.$dir.(!empty($dir)?SLASH:'').$entry ) && substr($entry,-3)=='.md')
			{
				$page = read_page($dir.(!empty($dir)?SLASH:'').$entry);
				
				if	( $page['date'] <= time() )
					$pages[] = $page;
				else
					; // pages in the future are not being published yet
			}
		}
		closedir($handle);
	}
	
	return $pages;
}





function lastModified( $time )
{

	if ( DEVELOPMENT ) return;
	
	// Conditional-Get eingeschaltet?
	$lastModified = substr(date('r',$time -date('Z')),0,-5).'GMT';
	$etag         = '"'.md5($lastModified).'"';

	// Header senden
	header('Last-Modified: '.$lastModified );
	header('ETag: '         .$etag         );

	// Die vom Interpreter sonst automatisch gesetzten
	// Header uebersteuern
	header('Cache-Control: must-revalidate');
	header('Pragma:');

	// See if the client has provided the required headers
	$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
	$if_none_match     = isset($_SERVER['HTTP_IF_NONE_MATCH']    ) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']    ) :	false;

	if	( !$if_modified_since && !$if_none_match )
		return;

	// At least one of the headers is there - check them
	if	( $if_none_match && $if_none_match != $etag )
		return; // etag is there but doesn't match

	if	( $if_modified_since && $if_modified_since != $lastModified )
		return; // if-modified-since is there but doesn't match

	// Der entfernte Browser bzw. Proxy holt die Seite nun aus seinem Cache
	header('HTTP/1.0 304 Not Modified');
	exit;  // Sofortiges Skript-Ende
}



/**
 * Macht aus einem Namen eine korrekte, lesebare URL.
 * @param $filename Name
 * @return string
 */
function urlify( $filename )
{
	// thx https://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
	$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $filename)));
	return $slug;
}


function urlify_array( $nameArray )
{
	$newNames = array();
	foreach( $nameArray as $name)
	{
		$newNames[] = urlify($name);
	}
	return $newNames;
	
}


?>