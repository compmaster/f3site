<?php
if(iCMSa!=1 || !admit('G')) exit;
require LANG_DIR.'admAll.php';
require './lib/forms.php';

#Page title
$view->title = $id ? $lang['editGroup'] : $lang['addGroup'];

#Action: save
if($_POST)
{
	$group = array(
		'name' => clean($_POST['name']),
		'dsc'  => $_POST['dsc'],
		'access' => clean($_POST['access']),
		'opened' => isset($_POST['opened'])
	);

	#Update existing
	if($id)
	{
		$q = $db->prepare('UPDATE '.PRE.'groups SET name=:name, dsc=:dsc,
			access=:access, opened=:opened WHERE ID='.$id);
	}
	#Insert new
	else
	{
		$group['who'] = UID;
		$group['date'] = $_SERVER['REQUEST_TIME'];
		$q = $db->prepare('INSERT INTO '.PRE.'groups (name,dsc,access,opened,who,date)
			VALUES (:name,:dsc,:access,:opened,:who,:date)');
	}
	try
	{
		$q->execute($group);
		if(!$id) $id = $db->lastInsertId();

		#Redirect
		if(isset($_GET['ref'])) header('Location: '.URL.url('group/'.$id));

		$view->info($lang['saved'], array(
			url('group/'.$id) => $group['name'],
			url('editGroup/'.$id, '', 'admin') => $lang['editGroup'],
			url('editGroup', '', 'admin') => $lang['addGroup']));
		return 1;
	}
	catch(PDOException $e)
	{
		$view->info($lang['error'].$e->getMessage());
	}
}

#Action: edit
elseif($id)
{
	if(!$group = $db->query('SELECT * FROM '.PRE.'groups WHERE ID='.$id)->fetch(2))
	return;
}
else
{
	$group = array('name'=>'','access'=>1,'opened'=>1,'dsc'=>'');
}

#Editor JS
if(isset($cfg['wysiwyg']) && is_dir('plugins/editor'))
{
	$view->script('plugins/editor/loader.js');
}
else
{
	$view->script(LANG_DIR.'edit.js');
	$view->script('cache/emots.js');
	$view->script('lib/editor.js');
}

#Template
$view->add('editGroup', array(
	'group' => &$group,
	'langs' => filelist('lang', 1, $id ? $group['access'] : null)
));