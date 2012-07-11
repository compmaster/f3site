<?php
if(iCMSa!=1 || !admit('BUGADM')) exit;

#Language
if(file_exists('./plugins/bugs/lang/adm'.LANG.'.php'))
{
	require './plugins/bugs/lang/adm'.LANG.'.php';
}
else
{
	require './plugins/bugs/lang/en.php';
}

#Template folders
$view->dir = './plugins/bugs/style/';
$view->cache = './cache/bugs/';
$view->title = $lang['tracker'];

if(isset($URL[1]))
{
	switch($URL[1])
	{
		case 'sections': require 'plugins/bugs/admSect.php'; break;
		case 'config': require 'plugins/bugs/admCfg.php'; break;
		case 'edit': require 'plugins/bugs/admEdit.php'; break;
		default: return;
	}
}
else
{
	require 'plugins/bugs/admCats.php';
}
