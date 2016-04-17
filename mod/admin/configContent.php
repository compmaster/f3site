<?php
if(iCMSa!=1 || !admit('CFG')) exit;

#Action: save
if($_POST)
{
	$opt =& $_POST;
	require './lib/config.php';
	try
	{
		#Tags
		if(isset($_POST['tags']))
		{
			$cfg['tags'] = 1;
			$f = new Config('main');
			$f->var = 'cfg';
			$f->save($cfg);
			unset($opt['tags']);
		}
		elseif(isset($cfg['tags']))
		{
			unset($cfg['tags']);
			$f = new Config('main');
			$f->var = 'cfg';
			$f->save($cfg);
		}
		$f = new Config('content');
		$f->save($opt);
		$view->info($lang['saved']);
		event('CONFIG');
		include './mod/admin/config.php';
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($lang['error'].$e);
	}
	unset($f);
}
else
{
	$opt =& $cfg;
}

require LANG_DIR.'admCfg.php';
require './cfg/content.php';

#Variable containing <select> options
$out = '<optgroup label="'.$lang['cats'].'">';

#Get categories
$res = $db->query('SELECT ID,name FROM '.PRE.'cats WHERE sc=0 AND access!=3 ORDER BY name');
$res ->setFetchMode(3); //NUM

foreach($res as $cat)
{
	$out.='<option value="'.$cat[0].'">'.$cat[1].'</option>'; //Bez 1-
}
$res=null;

#For each language
$i = 0;
$cats = $js = array();

foreach(scandir('./lang') as $dir)
{
	if(strpos($dir,'.')===false && is_dir('./lang/'.$dir))
	{
		if(isset($cfg['start'][$dir]))
		{
			$js[$dir] = $cfg['start'][$dir];
		}
		$cats[strtoupper($dir)] = '<select name="start['.$dir.']">'.$out.'</select>';
	}
}

#Page title
$view->title = $lang['content'];

#Template
$view->add('configContent', array(
	'cfg' => &$opt,
	'cats'=> &$cats,
	'def' => json_encode($js)
));
