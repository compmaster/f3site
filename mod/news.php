<?php
if(iCMS!=1) exit;
require './cfg/content.php';

#Get news from database if enabled
if(!$news = $db->query('SELECT n.*,c.opt as catOpt FROM '.PRE.'news n LEFT JOIN '.
PRE.'cats c ON n.cat=c.ID WHERE c.access!=3 AND n.ID='.$id) -> fetch(2)) return;

#Disabled
if(!$news['access'])
{
	if(!admit($news['cat'],'CAT')) return;
	$view->info(sprintf($lang['NVAL'], $news['name']), null, 'warning');
}

#Full content
if($news['opt']&4)
{
	$full = $db->query('SELECT text FROM '.PRE.'newstxt WHERE ID='.$id)->fetchColumn();
}
else
{
	$full = '';
}

#Page title
$view->title = $news['name'];

#Emoticons
if($news['opt']&2)
{
	$news['txt'] = emots($news['txt']);
	if($full) $full = emots($full);
}

#Line breaks
if($news['opt']&1)
{
	$news['txt'] = nl2br($news['txt']);
	if($full) $full = nl2br($full);
}

#Date, author
$news['date']  = formatDate($news['date'], true);
$news['wrote'] = autor($news['author']);

#Assign to template
$view->add('news', array(
	'news' => &$news,
	'full' => &$full,
	'path' => catPath($news['cat']),
	'edit' => admit($news['cat'],'CAT') ? url('edit/5/'.$id,'ref') : false,
	'root' => isset($cfg['allCat']) ? $lang['cats'] : $lang['news'],
	'cats' => url(isset($cfg['allCat']) ? 'cats' : 'cats/news')
));

#Tags
if(isset($cfg['tags']))
{
	include './lib/tags.php';
	tags($id, 5);
}

#Comments
if(isset($cfg['ncomm']) && $news['catOpt']&2)
{
	require './lib/comm.php';
	comments($id, 5);
}
