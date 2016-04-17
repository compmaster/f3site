<?php
if(iCMSa!=1 || !admit('R')) exit;
require LANG_DIR.'admAll.php';

#Aktualizuj lub usun
if($_POST && isset($_POST['del']) && $x = GetID(true))
{
	$db->exec('DELETE FROM '.PRE.'rss WHERE ID IN ('.$x.')');
}

#Pobierz kanaly RSS
$res = $db->query('SELECT ID,auto,name,lang FROM '.PRE.'rss ORDER BY lang,name');
$all = array();

foreach($res as $x)
{
	$all[] = array(
		'id'    => $x['ID'],
		'title' => $x['name'],
		'land'  => $x['lang'],
		'auto'  => $x['auto'] ? $lang['yes'] : $lang['no'],
		'edit'  => url('editRss/'.$x['ID'], '', 'admin'),
		'file'  => file_exists('rss/'.$x['ID'].'.xml') ? 'rss/'.$x['ID'].'.xml' : null,
	);
}

#Szablon
$view->add('rss', array('channel' => &$all));

#Zapisz tytuly w opcjach
if($_POST || isset($URL[1]))
{
	$cfg['RSS'] = array();
	foreach($all as $x)
	{
		if($x['auto']) $cfg['RSS'][$x['land']][$x['id']] = $x['title'];
	}
	include_once './lib/config.php';
	$o = new Config('main');
	$o->add('cfg', $cfg);
	$o->save();
}
