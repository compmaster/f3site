<?php
if(iCMSa!=1 || !admit('CFG')) exit;

#Update links
if(isset($_SESSION['renew']))
{
	try
	{
		require './lib/mcache.php';
		require './lib/categories.php';
		RenderMenu();
		Latest();
		RSS();
		if(function_exists('glob') && $glob = glob('cache/cat*.php'))
		{
			foreach($glob as $x) unlink($x);
		}
		unset($_SESSION['renew'],$glob,$x);
		include './mod/admin/config.php';
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($lang['saved']);
	}
}

if($_POST)
{
	$opt =& $_POST;
	$opt['title'] = clean($opt['title'],50);
	$opt['metaDesc'] = clean($opt['metaDesc']);
	$opt['antyFlood'] = (int)$opt['antyFlood'];
	$opt['pmLimit'] = (int)$opt['pmLimit'];
	$opt['commNum'] = (int)$opt['commNum'];
	$opt['pollRound'] = (int)$opt['pollRound'];
	$opt['RSS'] = empty($cfg['RSS']) ? array() : $cfg['RSS'];
	if(isset($cfg['tags'])) $opt['tags'] = 1;

	#API keys
	if($opt['captcha'] != 2)
	{
		if(empty($opt['pubKey'])) unset($opt['pubKey']);
		if(empty($opt['prvKey'])) unset($opt['prvKey']);
	}
	if($opt['captcha'] != 1)
	{
		if(empty($opt['sbKey'])) unset($opt['sbKey']);
	}

	require './lib/config.php';
	try
	{
		$f = new Config('main');
		$f->add('cfg', $opt);
		$f->save();
		if($cfg['niceURL'] != $opt['niceURL'])
		{
			$_SESSION['renew'] = 1;
			$view->message(19, URL.'?go=configMain');
		}
		$cfg = &$opt;
		$view->info($lang['saved']);
		event('CONFIG');
		include './mod/admin/config.php';
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($e);
	}
}
else
{
	$opt =& $cfg;
}

include LANG_DIR.'admCfg.php';
include LANG_DIR.'lang.php';

#Style
$skins = array();
foreach(scandir('style') as $x)
{
	if($x[0]!='.' && is_dir('style/'.$x) && file_exists('style/'.$x.'/body.html'))
	{
		$skins[] = $x;
	}
}

#Editor plugins
$editors = array();
foreach(scandir('plugins') as $x)
{
	if($x[0] != '.' && is_dir('plugins/'.$x) && file_exists('plugins/'.$x.'/editor.js'))
	{
		$editors[] = $x;
	}
}

#Languages
$langs = array();
foreach(scandir('lang') as $x)
{
	if($x[0]!='.' && is_dir('lang/'.$x) && file_exists('lang/'.$x.'/main.php'))
	{
		$langs[$x] = isset($lang[$x]) ? $lang[$x] : $x;
	}
}
asort($langs, SORT_STRING);

#API keys
if(empty($cfg['pubKey'])) $cfg['pubKey'] = '';
if(empty($cfg['prvKey'])) $cfg['prvKey'] = '';
if(empty($cfg['sbKey'])) $cfg['sbKey'] = '';

#Page title
$view->title = $lang['main'];

#Template
$view->add('configMain', array(
	'cfg' => &$opt,
	'skins' => &$skins,
	'langs' => &$langs,
	'fileman' => admit('FM'),
	'editors' => &$editors
));
