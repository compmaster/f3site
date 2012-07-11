<?php /* Show link details */
if(iCMS!=1) exit;
require './cfg/content.php';

#Full view is disabled
if(!isset($cfg['linkFull']) && !IS_EDITOR) return;

#Get record
if(!$link = $db->query('SELECT l.*,c.opt FROM '.PRE.'links l INNER JOIN '.
PRE.'cats c	ON l.cat=c.ID WHERE c.access!=3 AND l.ID='.$id)->fetch(2)) return;

#Disabled
if(!$link['access'])
{
	if(!admit($link['cat'],'CAT')) return;
	$view->info(sprintf($lang['NVAL'], $link['name']), null, 'warning');
}

#Tag title and meta description
$view->title = $link['name'];
$view->desc  = $link['dsc'] ? $link['dsc'] : $cfg['metaDesc'];

#Ocena
if(isset($cfg['lrate']) AND $link['opt'] & 4)
{
	$view->css(SKIN_DIR.'rate.css');
	$rate = 'vote.php?type=4&amp;id='.$id;
}
else
{
	$rate = 0;
}

#Template
$view->add('link', array(
	'link'  => &$link,
	'rates' => &$rate,
	'count' => isset($cfg['lcnt']),
	'href'  => isset($cfg['lcnt']) ? url('go/'.$id) : $link['adr'],
	'edit'  => admit($link['cat'],'CAT') ? url('edit/4/'.$id,'ref') : false,
	'path'  => catPath($link['cat']),
	'root'  => isset($cfg['allCat']) ? $lang['cats'] : $lang['links'],
	'cats'  => url(isset($cfg['allCat']) ? 'cats' : 'cats/links')
));

#Tags
if(isset($cfg['tags']))
{
	include './lib/tags.php';
	tags($id, 4);
}

#Comments
if($link['opt'] & 2)
{
	require './lib/comm.php';
	comments($id, 4);
}
