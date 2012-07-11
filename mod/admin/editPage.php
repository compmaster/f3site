<?php
if(iCMSa!=1 || !admit('P')) exit;
require LANG_DIR.'admAll.php';

#Page title
$view->title = $id ? $lang['editPage'] : $lang['addPage'];

#Action: save
if($_POST)
{
	#Break lines
	$o = isset($_POST['o1']);

	#Emoticons
	isset($_POST['o2']) && $o |= 2;

	#On layer
	isset($_POST['o3']) && $o |= 4;

	#Comments
	isset($_POST['o4']) && $o |= 8;

	#PHP
	isset($_POST['o5']) && $o |= 16;

	#Data
	$page = array(
	'text'	=> &$_POST['txt'],
	'access'=> clean($_POST['access']),
	'name'	=> clean($_POST['name']),
	'opt' 	=> $o
	);

	try
	{
		#Update existing
		if($id)
		{
			$q=$db->prepare('UPDATE '.PRE.'pages SET name=:name,access=:access,opt=:opt,text=:text WHERE ID=:id');
			$page['id'] = $id;
		}
		#New page
		else
		{
			$q=$db->prepare('INSERT INTO '.PRE.'pages (name,access,opt,text) VALUES (:name,:access,:opt,:text)');
		}
		$q->execute($page);

		#Get ID
		if(!$id) $id = $db->lastInsertId();

		#Redirect
		if(isset($_GET['ref'])) header('Location: '.URL.url('page/'.$id));

		#Show info
		$view->info($lang['saved'], array(
			url('page/'.$id) => sprintf($lang['goto'], $page['name']),
			url('editPage/'.$id, '', 'admin') => $lang['edit'],
			url('editPage', '', 'admin') => $lang['addPage'] ));
		return 1;
	}
	catch(PDOException $e)
	{
		$view->info($e);
	}
}

#Form
elseif($id)
{
	if(!$page = $db->query('SELECT * FROM '.PRE.'pages WHERE ID='.$id)->fetch(2))
	return;
}
else
{
	$page = array('name'=>'','access'=>1,'text'=>'','opt'=>13);
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
$view->add('editPage', array(
	'page' => &$page,
	'o1'   => $page['opt']&1,
	'o2'   => $page['opt']&2,
	'o3'   => $page['opt']&4,
	'o4'   => $page['opt']&8,
	'o5'   => $page['opt']&16
));
