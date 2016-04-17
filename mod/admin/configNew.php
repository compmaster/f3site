<?php
if(iCMSa!=1 || !admit('CFG')) exit;

if($_POST) { $opt =& $_POST; } else { $opt =& $cfg; }

#Category types
$type = array();
$data = parse_ini_file('./cfg/types.ini', 1);

#Save
if($_POST)
{
	#Types must be numbers
	if(isset($_POST['newTypes']))
	{
		foreach($_POST['newTypes'] as &$x) $x = (int)$x;
	}
	try
	{
		include './lib/config.php';
		$f = new Config('latest');
		$f -> save($_POST);

		#Update newest
		include './lib/categories.php';
		Latest();
		event('CONFIG');

		#Return to menu
		include './mod/admin/config.php';
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($lang['error'].$e);
	}
}
else
{
	require './cfg/latest.php';
}
require LANG_DIR.'admCfg.php';

foreach($data as $num => &$x)
{
	$type[] = array(
		'id' => $num,
		'on' => isset($opt['newTypes'][$num]),
		'title' => isset($x[LANG]) ? $x[LANG] : (isset($x['en']) ? $x['en'] : $num)
	);
}

#Template
$view->title = $lang['latest'];
$view->add('configNew', array('cfg'=>$opt, 'type'=>$type));
