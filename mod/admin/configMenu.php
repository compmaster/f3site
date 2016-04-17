<?php
if(iCMSa!=1 || !admit('CFG')) exit;

if($_POST)
{
	$opt =& $_POST;

	require './lib/config.php';
	try
	{
		$f = new Config('main');
		$f->add('cfg', $opt);
		$f->save();
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

#Prepare template
$view->title = $lang['main'];
$view->script('lib/forms.js');
$view->add('configMenu', array(
	'cfg' => &$opt,
));
