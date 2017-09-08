<?php
$lang = array(
	'recent_pages'=>'Recent Pages',
	'related_pages'=>'Related Pages',
	'keywords'=>'Keywords',
	'categories'=>'Categories',
	'date_format'=>'d.m.Y \u\m G:i \U\h\r'
);
extract($lang,EXTR_PREFIX_ALL,'site_lang');
 
?><html lang="de">
<head>
  <title><?php echo TITLE ?> - <?php echo $site_title ?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="<?php echo SITE_UP ?>default.css">
</head>
<body>
<header id="name">
<h1><?php echo TITLE ?></h1>
</header>

<article>

<span class="date"><?php echo date($site_lang_date_format,PAGE['date']) ?></span>
<span class="author"><?php echo PAGE['author'] ?></span>
<span class="category"><?php echo PAGE['author'] ?></span>
<span class="keywords"><?php foreach( PAGE['keywords'] as $keyword ) { ?><a href="<?php echo UP.$keyword ?>"><?php echo $keyword ?></a><?php } ?></span>

<?php echo ARTICLE ?>
</article>

<nav id="related">
<header>
<h1><?php echo $site_lang_related_pages ?></h1>
</header>
<ul>
<?php foreach( PAGES_RELATED as $page ) 
     {?>
     <li><a href="<?php $page['url'] ?>">
     	<span class="date"><?php echo date('r',$page['date']) ?></span>
     	<span class="title"><?php echo $page['title'] ?></span>
     </a></li>
     <?php } ?>
</ul>

</nav>




<nav id="recent">
<header>
<h1><?php $site_lang_recent_pages ?></h1>
</header>
<ul>
<?php $count=0; foreach( PAGES_BY_DATE as $page) 
     { if ($count++ > 10) break; ?>
     <li>
     	<span class="date"><?php echo date('r',$page['date']) ?></span>
     	<span class="title"><?php echo $page['title'] ?></span>
     	</li>
     <?php } ?>
</ul>

</nav>


<nav id="keywords">
<header>
<h1><?php $site_lang_keywords ?></h1>
</header>
<?php $count=0; foreach( KEYWORDS as $keyword) 
     { ?>
     <span style="font-size:1em;"><a href="<?php echo UP.$keyword ?>"><?php echo $keyword ?></a>
     	</span>
     <?php } ?>

</nav>



<nav id="categories">
<header>
<h1><?php $site_lang_categories ?></h1>
</header>
<?php $count=0; foreach( KEYWORDS as $keyword) 
     { ?>
     <span style="font-size:1em;"><a href="<?php echo UP.'tag/'.$keyword ?>"><?php echo $keyword ?></a>
     	</span>
     <?php } ?>

</nav>


<footer>

<ul>
<li><a href="<?php echo UP ?>datenschutz">Datenschutz</a></li>
<li><a href="<?php echo UP ?>impressum">Impressum</a></li>
</ul>

</footer>

</body>