<?php /* Show article */
if(iCMS!=1) exit;
include './cfg/content.php';

#Strona
$page = isset($URL[2]) && is_numeric($URL[2]) ? $URL[2] : 1;

#Get record
$art = $db->query('SELECT t.*,f.text,f.opt,c.opt as catOpt FROM '.PRE.'arts t
INNER JOIN '.PRE.'artstxt f ON t.ID=f.ID INNER JOIN '.PRE.'cats c ON t.cat=c.ID
WHERE t.ID='.$id.' AND c.access!=3 AND f.page='.$page)->fetch(2);

if(!$art) return;

#Disabled
if(!$art['access'])
{
	if(!admit($art['cat'],'CAT')) return;
	$view->info(sprintf($lang['NVAL'], $art['name']), null, 'warning');
}

#Art title
$view->title = $art['name'];

#Art description - clean [temporary]
if($art['dsc']) $view->desc = clean($art['dsc']);

#Emots
if($art['opt']&2)
{
	$art['text'] = emots($art['text']);
}
#BR
if($art['opt']&1)
{
	$art['text'] = nl2br($art['text']);
}

#Date, author
$art['date'] = formatDate($art['date'], true);
$art['author'] = autor($art['author']);

#Ocena
if(isset($cfg['arate']) && $art['catOpt'] & 4)
{
	$view->css(SKIN_DIR.'rate.css');
	$rates = 'vote.php?type=1&amp;id='.$id;
}
else
{
	$rates = 0;
}

#Count popularity
if(isset($cfg['adisp']))
{
	register_shutdown_function(array($db,'exec'),'UPDATE '.PRE.'arts SET views=views+1 WHERE ID='.$id);
	++$art['views'];
}
else
{
	$art['ent'] = 0;
}

#Pages
if($art['pages'] > 1)
{
	$pages = pages($page,$art['pages'],1,url('art/'.$id),0,'/');
}
else
{
	$pages = false;
}

#Template
$view->add('art', array(
	'art'   => &$art,
	'pages' => &$pages,
	'path'  => catPath($art['cat']),
	'edit'  => admit($art['cat'],'CAT') ? url('edit/1/'.$id, 'ref='.$page) : false,
	'color' => $art['opt'] & 4,
	'rates' => $rates,
	'root'  => isset($cfg['allCat']) ? $lang['cats'] : $lang['arts'],
	'cats'  => url(isset($cfg['allCat']) ? 'cats' : 'cats/articles'),
	'lightbox' => isset($cfg['lightbox'])
));

#Tags
if(isset($cfg['tags']))
{
	include './lib/tags.php';
	tags($id, 1);
}

#Comments
if(isset($cfg['acomm']) && $art['catOpt']&2)
{
	require './lib/comm.php';
	comments($id, 1);
}
