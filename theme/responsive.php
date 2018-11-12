<?php
$lang = array(
	'site_lang_recent_pages' =>'Recent Pages',
	'site_lang_related_pages'=>'Related Pages',
	'site_lang_keywords'     =>'Keywords',
	'site_lang_categories'   =>'Categories',
	'site_lang_calendar'     =>'Calendar',
	'site_lang_from'         =>'from',
	'site_lang_to'           =>'to',
	'site_lang_in'           =>'in',
	'site_date_format'       =>'%x',
	'site_date_format_full'  =>'%x',
	'site_footer_category'   =>'about',
		'site_title'         =>'My Blog'
);
extract($lang,EXTR_SKIP);
 
?><html lang="de">
<head>
  <title><?php echo TITLE ?> - <?php echo $site_title ?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="<?php echo SITE_UP ?>style.css">
</head>
<body>

<header>
	<h1><?php echo $site_title ?></h1>
</header>

<main>

<article>

	<header id="name">
		<h1><?php echo TITLE ?></h1>
		
		<?php if	( is_array($PAGE) ) { ?>
		<span class="date"><?php echo strftime($site_date_format_full,$PAGE['date']) ?></span>
		<span class="author"><?php echo $PAGE['author'] ?></span>
		<span class="category"><a href="<?php echo SITE_UP.'category'.SLASH.$PAGE['category'] ?>"><?php echo $PAGE['category'] ?></a></span>
		<ul class="keywords"><?php foreach( $PAGE['keywords'] as $keyword ) { ?><li><a href="<?php echo SITE_UP.'tag'.SLASH.$keyword ?>"><?php echo $keyword ?></a></li><?php } ?></ul>
		<?php } ?>
	</header>
	
	<?php echo CONTENT ?>
</article>


<?php if	( is_array($PAGE) )  { ?>
<nav id="related">
<header>
<h1><?php echo $site_lang_related_pages ?></h1>
</header>
<ul>
<?php foreach( $PAGES_RELATED as $page ) 
     {?>
     <li>
     <a href="<?php echo SITE_UP.$page['url'] ?>"><?php echo $page['title'] ?></a>
     </li>
     <?php } ?>
</ul>

</nav>
<?php } ?>


<nav id="categories">
<header>
<h1><?php echo $site_lang_categories ?></h1>
</header>

<ul>
<?php $count=0; 	
foreach( $PAGES_BY_CATEGORY as $category=>$pages) 
     { ?>
     <li><a href="<?php echo SITE_UP.'category/'.$category ?>"><?php echo $CATEGORY_NAMES[$category] ?></a></li>
     <?php } ?>
</ul>
</nav>





<nav id="recent">
<header>
<h1><?php echo $site_lang_recent_pages ?></h1>
</header>
<ul>
<?php $count=0; foreach( array_reverse($PAGES_BY_DATE) as $pagesOnDate) 
     { 
     	foreach( $pagesOnDate as $page ) 
     	{
     	
     	if ($count++ > 10) break; ?>
     <li>
     	<a href="<?php echo SITE_UP.$page['url'] ?>"><?php echo $page['title'] ?></a>
     </li>
     <?php }} ?>
</ul>

</nav>


<nav id="calendar">
	<header>
		<h1><?php echo $site_lang_calendar ?></h1>
	</header>

<ul class="year">
<?php  $oldYear = 0; foreach( array_reverse($PAGES_BY_DATE,true) as $date => $pagesOnDate) 
     { 
     	$year = date('Y',$date);
     	if	( $year != $oldYear )
     	{
     		$oldYear = $year;
     		?>
     <li>
     <a href="<?php echo SITE_UP.$year ?>"><?php echo $year ?></a>
     	<ul class="month">
     	<?php
     	$oldMonth = 0;
     	foreach( array_reverse($PAGES_BY_DATE,true) as $date2 => $pagesOnDate)
     	{
     		$y = date('Y',$date2);
     		if ( $y != $year)
     			continue;
     		
     		$month = date('m',$date2);
     		if	( $month != $oldMonth )
     		{
     			$oldMonth = $month;
     			
     		?>
     	<li>
     		<a href="<?php echo SITE_UP.$year.SLASH.$month ?>"><?php echo date('F',$date2) ?></a>
     	</li>
     	<?php }} ?>
     	</ul>
     </li>
     		
     <?php } } ?>
</ul>

</nav>


<nav id="keywords">
<header>
<h1><?php echo $site_lang_keywords ?></h1>
</header>

<ul>
<?php $count=0; foreach( $PAGES_BY_KEYWORD as $keyword=>$pages) 
     {
     	$fontsize = min(3,((count($pages)-1)/5)+1); ?>
     
     <li style="font-size:<?php echo $fontsize ?>em;"><a href="<?php echo SITE_UP.'tag/'.$keyword ?>"><?php echo $keyword ?></a>
     	</li>
     <?php } ?>
</ul>

</nav>


</main>

<footer>

<ul>
<?php foreach( $PAGES_BY_CATEGORY[$site_footer_category] as $page) {?>
<li><a href="<?php echo SITE_UP.$page['url'] ?>"><?php echo $page['title']?></a></li>
<?php } ?>
</ul>

</footer>

</body>
</html>
