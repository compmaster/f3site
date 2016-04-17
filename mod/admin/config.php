<?php
if(iCMSa!=1 || !admit('CFG')) exit;

#Page title
$view->title = $lang['config'];

#Option categories language file
require LANG_DIR.'admCfg.php';

#Addons options
$items = array();
$res = $db->query('SELECT ID,name,img FROM '.PRE.'confmenu WHERE lang=1 OR lang="'.LANG.'"');
foreach($res as $x)
{
	$items[] = array(
		'name' => $x['name'],
		'img'  => $x['img'],
		'url'  => url($x['ID'], '', 'admin')
	);
}

#Template
$view->css('style/admin/config.css');
$view->add('config', array(
	'plugins' => &$items,
));