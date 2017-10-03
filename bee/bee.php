<?php
error_reporting( E_ALL );

// Include external dependencies.
require('./parsedown/ParseDown.php'); // Markdown
require('./spyc/Spyc.php'); // YAML

require('./gen/GeneratorBase.class.php');
require('./gen/DateGenerator.class.php');
require('./gen/CategoryGenerator.class.php');
require('./gen/HtmlGenerator.class.php');
require('./gen/IndexGenerator.class.php');
require('./gen/KeywordGenerator.class.php');
require('./gen/MarkdownPageGenerator.class.php');
require('./gen/StaticFileGenerator.class.php');
require('./gen/ThemeResourceGenerator.class.php');
require('./util/HttpUtil.class.php');
require('./SiteReader.class.php');


function init()
{
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
	
	define('SCRIPT',basename($_SERVER['SCRIPT_FILENAME']));
	
	define('SITES_DIR','../site');
	define('THEME_DIR','../theme');
	define('SLASH'     ,'/');
	
	global $querypath;
	$querypath = explode('/',$_SERVER['PATH_INFO']);
	
	define('ROOT_UP',str_repeat('../',substr_count($_SERVER['PATH_INFO'],'/')));
	define('SITE_UP',str_repeat('../',substr_count($_SERVER['PATH_INFO'],'/')-3));
	
	               array_shift($querypath); // Remove first slash
	define('CMD'  ,array_shift($querypath));
	define('SITE' ,array_shift($querypath));
}

init();



if (is_null(CMD))
{
	$options = array('Preview'=>ROOT_UP.SCRIPT.'/preview/');
	output( 'Select an option',$options);
	
}
elseif (CMD=='preview')
{
	if	( is_null(SITE) || SITE=='')
	{
		$sitelinks = array();
		if ( $handle = opendir(SITES_DIR.SLASH.SITE.SLASH.implode('/',PATH)) )
		{
			while (false !== ($entry = readdir($handle))) {
				if  ( $entry[0] != '.' )
				{
					$sitelinks[$entry] =  ROOT_UP.SCRIPT.'/preview/'.$entry.'/';
				}
			}
			closedir($handle);
		}
		output( 'Select a site for previewing',$sitelinks);
	}
	else
	{
		if	(!is_dir(SITES_DIR.SLASH.SITE))
		{
			HttpUtil::sendStatus(404,"Site ".SITE." not found"); // Site does not exist
		}

		if	( is_file(SITES_DIR.SLASH.SITE.SLASH.'site-config.ini'))
		{
			$siteconfig = parse_ini_file(SITES_DIR.SLASH.SITE.SLASH.'site-config.ini');
			if	( isset($siteconfig['site_dir']))
				define('SITE_DIR',$siteconfig['site_dir']);
			else
				define('SITE_DIR',SITES_DIR.SLASH.SITE);
			
			if	( isset($siteconfig['theme']))
				define('THEME',$siteconfig['theme']);
			else
				define('THEME','default');

			if	( isset($siteconfig['locale']))
				setlocale(LC_ALL,$siteconfig['locale']);
		}
		else 
		{
			define('THEME','default');
			define('SITE_DIR',SITES_DIR.SLASH.SITE);
		}
		
		if	( !is_file(THEME_DIR.SLASH.THEME.'.php') )
			HttpUtil::sendStatus(501,'Internal Server Error: Theme '.THEME.' not found'); // Theme does not exist
		
		// global used arrays.
		$PAGES_BY_URL      = array();
		$PAGES_BY_DATE     = array();
		$PAGES_BY_KEYWORD  = array();
		$PAGES_BY_CATEGORY = array();
		$PAGES_RELATED     = array();
		$CATEGORY_NAMES    = array();
		$KEYWORD_NAMES     = array();
		$PAGE              = null;
		
		// read all pages from file system.
		
		readSite();
		$filename = implode(SLASH,$querypath);
		
		define('PATH',$querypath);
		$generator = null;

		if  (count($querypath)==2 && $querypath[0]=='tag' )
		{
			if	( !isset($querypath[1]))
				HttpUtil::sendStatus(410,'Missing keyword name'); // Pfad '/tag' ohne weiteres
			
			
			$generator = new KeywordGenerator( $querypath[1] );
		}
		
		elseif  (count($querypath)==2 && $querypath[0]=='category' )
		{
			if	( !isset($querypath[1]))
				HttpUtil::sendStatus(410,'Missing category name'); // Pfad '/category' ohne weiteres
			
			$generator = new CategoryGenerator( $querypath[1] );
		}

		elseif  (count($querypath)>=1 && strlen($querypath[0])==4 && is_numeric(($querypath[0])) )
		{
			// By date
			$year = intval($querypath[0]);
			if	( isset($querypath[1]) )
			{
				$month  = intval($querypath[1]);
				$toYear = $year;
				
				if	( isset($querypath[2]) )
				{
					// by day
					$day = intval($querypath[2]);
					$toMonth = $month;
					$toDay   = $day+1;
				}
				else
				{
					// by month
					$day = 1;
					$toDay = 1;
					$toMonth = $month+1;
				}
				
			}
			else
			{
				// By year
				$day   = 1;
				$toDay = 1;
				$month = 1;
				$toMonth = 1;
				$toYear = $year+1;
			}

			$from = mktime(0,0,0,$month,$day,$year);
			$to   = mktime(0,0,0,$toMonth,$toDay,$toYear);
			$generator = new DateGenerator( $from,$to-1 );
			
		}
		
		
		// Directory-Listing
		elseif	( empty($filename))
		{
			$generator = new IndexGenerator();
		}
		
		// Static file from theme
		elseif	(is_file(THEME_DIR.SLASH.THEME.SLASH.$filename))
		{
			$generator = new ThemeResourceGenerator($filename);
		}
		
		// Static file from site
		elseif	(is_file(SITE_DIR.SLASH.$filename))
		{
			$generator = new StaticFileGenerator($filename);
		}
		elseif	(isset($PAGES_BY_URL[$filename]))
		{
			$generator = new MarkdownPageGenerator( $PAGES_BY_URL[$filename] );
		}
		elseif	(is_file(SITES_DIR.SLASH.SITE.SLASH.$filename.'.html'))
		{
			$generator = new HtmlGenerator( $filename );
		}
		else {
			HttpUtil::sendStatus(404,'Resource not Found');
		}
		
		$generator->generate();
		
	}
}
else {
	HttpUtil::sendStatus(400,'Unknown command');
}










function readSite()
{
	global $PAGES_BY_URL;
	global $PAGES_BY_DATE;
	global $PAGES_BY_KEYWORD;
	global $PAGES_BY_CATEGORY;
	global $KEYWORD_NAMES;
	global $CATEGORY_NAMES;
	
	$reader = new SiteReader();
	$reader->readSite();

	$PAGES_BY_URL      = $reader->pagesByUrl;
	$PAGES_BY_DATE     = $reader->pagesByDate;
	$PAGES_BY_KEYWORD  = $reader->pagesByKeyword;
	$PAGES_BY_CATEGORY = $reader->pagesByCategory;
	
	$KEYWORD_NAMES     = $reader->keywordNames;
	$CATEGORY_NAMES    = $reader->categoryNames;
	
	
}


function output( $text, $options ) 
{
	echo '<html><head><meta charset="utf-8"><title>'.$text.' - Bee Static Site Generator</title>';
    echo '<style>';
    readfile('bee.css');
    echo '</style>';
	echo '</head>';
	echo '<body>';
    echo '<h1>'.$text.'</h1><ul>';
    foreach( $options as $option=>$url )
    {
	    echo '<li><a href="'.$url.'">'.$option.'</a></li>';
    }
  	echo '</ul></body>';
	echo '</html>';
}

?>