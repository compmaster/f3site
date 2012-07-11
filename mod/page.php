<?php
if(iCMS!=1) exit;

#Get record
if(!$page = $db->query('SELECT * FROM '.PRE.'pages WHERE ID='.$id)->fetch(2)) return;

#Rights
$edit = admit('P');

#Unavailable (0) or for logged in (3)
if($page['access'] != 1)
{
	if(!$page['access'])
	{
		if(!$edit) return;
		$view->info(sprintf($lang['NVAL'], $page['name']), null, 'warning');
	}
	elseif(!UID) return;
}

#Evaluate PHP first
if($page['opt'] & 16)
{
	ob_start();
	eval('?>'.$page['text']);
	$page['text'] = ob_get_clean();
}

#Emoticons
if($page['opt'] & 2)
{
	$page['text'] = emots($page['text']);
}

#Line breaks
if($page['opt'] & 1)
{
	$page['text'] = nl2br($page['text']);
}

#Page title, template
$view->title = $page['name'];
$view->add('page', array(
	'page' => &$page,
	'box'  => $page['opt'] & 4,
	'all'  => $edit ? url('pages','','admin') : false,
	'edit' => $edit ? url('editPage/'.$id, 'ref', 'admin') : false
));

#Keywords
if(isset($cfg['tags']))
{
	include './lib/tags.php';
	tags($id, 102);
}

#Comments
if($page['opt'] & 8)
{
	require './lib/comm.php';
	comments($id, 102);
}
