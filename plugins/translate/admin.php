<?php
if(iCMSa!=1 || !admit('LNGTOOL')) exit;

#Jêzyk
if(file_exists('./plugins/translate/lang/'.LANG.'.php'))
{
	require './plugins/translate/lang/'.LANG.'.php';
}
else
{
	require './plugins/translate/lang/en.php';
}

#Katalog szablonów
$view->dir = './plugins/translate/';
$view->cache = './cache/translate/';
$view->title = $lang['translate'];

if(isset($_GET['act']))
{
	switch($_GET['act'])
	{
		case 's': require 'plugins/bugs/admSect.php'; break;
		case 'o': require 'plugins/bugs/admCfg.php'; break;
		case 'e': require 'plugins/bugs/admEdit.php'; break;
		default: return;
	}
}
else
{
	require 'plugins/translate/menu.php';
}
