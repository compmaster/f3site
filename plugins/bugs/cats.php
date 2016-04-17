<?php
if(iCMS!=1) exit;

#Welcome text
if($cfg['bugsIntro']) $view->info($cfg['bugsIntro']);

#Page title
$view->title = $lang['BUGS'];

#Get categories
$res = $db->query('SELECT c.ID,c.name,c.dsc,c.post,c.num,c.last,s.title FROM '.PRE.'bugcats c LEFT JOIN '.PRE.'bugsect s ON c.sect = s.ID WHERE c.see=1 OR c.see="'.LANG.'" ORDER BY s.seq,c.name');

$cat  = array();
$sect = '';
$show = 0;
$num  = 0;

foreach($res as $x)
{
	#Section
	if($x['title'] != $sect)
	{
		$sect = $x['title'];
		$show = 1;
	}
	elseif($show == 1)
	{
		$show = 0;
	}
	$cat[] = array(
		'url'    => url('bugs/list/'.$x['ID']),
		'num'    => $x['num'],
		'title'  => $x['name'],
		'desc'   => $x['dsc'],
		'class'  => BugIsNew('', $x['last']) ? 'bugCatNew' : 'bugCat',
		'section'=> $show ? $sect : false
	);
	++$num;
}

#No category
if(!$num)
{
	$view->info($lang['nocats']);
}
else
{
	$view->css('plugins/bugs/style/bugs.css');
	$view->add('cats', array('cat' => &$cat));
}
