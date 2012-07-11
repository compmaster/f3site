<?php
if(iCMSa!=1 || !admit('GB')) exit;

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
$view->title = $lang['gbAdmin'];

#Delete old
if(isset($_POST['prune']) && strlen($_POST['prune'])===20)
{
	$db->prepare('DELETE FROM '.PRE.'guestbook WHERE date<?')->execute(array(strtotime($_POST['prune'])));
	$view->info($lang['gbPruned']);
}

$styles = '';
$opt = null;

#Edit settings
if(admit('CFG'))
{
	#Save settings
	if(isset($_POST['gbSkin']))
	{
		$opt =& $_POST;
		$opt['gbSkin'] = str_replace(array('.', '/', '\\'), '', $opt['gbSkin']);
		require './lib/config.php';
		$f = new Config('gb');
		try
		{
			$f->save($opt);
			$view->info($lang['saved']);
		}
		catch(Exception $e)
		{
			$view->info($lang['error'].$e);
		}
	}
	else
	{
		$opt =& $cfg;
	}
	#Styles
	foreach(scandir('./plugins/guestbook/style') as $x)
	{
		if(strpos($x,'.html') && $x != 'admin.html' && $x != 'index.html' && $x != 'posting.html')
		{
			$styles .= '<option>'.substr($x, 0, -5).'</option>';
		}
	}
}

#Template
$view->add('admin', array(
	'cfg'    => &$opt,
	'styles' => $styles,
));
