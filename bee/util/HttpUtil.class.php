<?php

class HttpUtil
{
	
	/**
	 * Schickt einen HTTP-Status zum Client und beendet das Skript.
	 *
	 * @param Integer $status HTTP-Status (ganzzahlig) (Default: 501)
	 * @param String $text HTTP-Meldung (Default: 'Internal Server Error')
	 */
	static function sendStatus( $status=501,$text='Internal Server Error' )
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
	
	
	static function lastModified( $time )
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
}





?>