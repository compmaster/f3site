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

#Save
if($_POST)
{
	$opt = array(
		'url'  => $_POST['url'],
		'path' => $_POST['path'],
		'nice' => (int)$_POST['nice']
	);
	require './lib/config.php';
	$f = new Config('db');
	try
	{
		$f->add('db_db', $db_db);
		$f->add('db_d', $db_d);

		#Only for MySQL
		if($db_db == 'mysql')
		{
			$f->add('db_h', $db_h);
			$f->add('db_u', $db_u);
			$f->add('db_p', $db_p);
		}

		#Constants
		$f->addConst('PRE', PRE);
		$f->addConst('PATH', $opt['path']);
		$f->addConst('URL', $opt['url']);
		$f->addConst('NICEURL', (int)$opt['nice']);

		#Save file
		$f->save($opt);

		#Log config change
		event('CONFIG');

		#Show update links message
		if(NICEURL != $opt['nice'])
		{
			$_SESSION['renew'] = 1;
			$view->message(19, url('setup','renew','admin'));
		}

		#Otherwise redirect to config menu
		$view->info($lang['saved']);
		include './mod/admin/config.php';
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($lang['error'].$e);
	}
	$f = null;
}
else
{
	$opt = array(
		'url'  => URL,
		'path' => PATH,
		'nice' => NICEURL
	);
}

#Load language file
include LANG_DIR.'admCfg.php';

#Preferred URL and PATH
$prefpath = str_replace(array('//','admin/'), array('/',''), dirname($_SERVER['PHP_SELF']).'/');
$prefurl  = PROTO.$_SERVER['SERVER_NAME'].$prefpath;

#Template
$view->title = $lang['setup'];
$view->add('setup', array(
	'db'  => $db_db=='sqlite' ? 'SQLite' : 'MySQL',
	'cfg' => &$opt,
	'prefurl' => $prefurl,
	'prefpath' => $prefpath
));
