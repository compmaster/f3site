<?php
if(iCMS!=1) exit;

#Opcje
require './cfg/bugs.php';

#Jêzyk
if(file_exists('./plugins/bugs/lang/'.LANG.'.php'))
{
	require './plugins/bugs/lang/'.LANG.'.php';
}
else
{
	require './plugins/bugs/lang/en.php';
}

#Prawa
function BugRights($x)
{
	global $user;
	switch($x)
	{
		case '': case NULL: if(admit('BUGS')) return true; break;
		case 'ALL': return true; break;
		case 'LOGD': if(UID) return true; break;
		default:
		if(UID)
		{
			$r = explode(' ', $x);
			if(in_array('U:'.UID, $r) || in_array('LV:'.LEVEL, $r)) return true;
		}
	}
	return false;
}

#Nowe wpisy?
function BugIsNew($d1,$d2)
{
	if(empty($d1)) $d1 = $_SESSION['recent'];
	if(empty($d2)) return false;
	return (strtotime($d2) > strtotime($d1));
}

#Katalog szablonów
$view->dir = './plugins/bugs/style/';
$view->cache = './cache/bugs/';
$view->css('plugins/bugs/style/bugs.css');

#Modu³
if(!isset($cfg['bugsOn']))
{
	$view->info($lang['bugsOff']);
	return 1;
}

#Akcja
if(isset($URL[1]))
{
	switch($URL[1])
	{
		case 'post': require 'plugins/bugs/edit.php'; break;
		case 'list': require 'plugins/bugs/list.php'; break;
		default: require 'plugins/bugs/view.php'; break;
	}
}
else
{
	require 'plugins/bugs/cats.php';
}
