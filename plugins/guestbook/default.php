<?php
if(iCMS!=1) exit;

#Settings
require './cfg/gb.php';

#Language
if(file_exists('./plugins/guestbook/lang/'.LANG.'.php'))
{
	require './plugins/guestbook/lang/'.LANG.'.php';
}
else
{
	require './plugins/guestbook/lang/en.php';
}

#Template path
$view->dir = './plugins/guestbook/style/';
$view->cache = './cache/guestbook/';

#Guestbook disabled
if(!isset($cfg['gbOn']))
{
	$view->info($lang['disabled']);
	return 1;
}

#Action
if(isset($URL[1]))
{
	switch($URL[1])
	{
		case 'post': require './plugins/guestbook/post.php'; break;
		default: return;
	}
}
else
{
	require './plugins/guestbook/list.php';
}
