<?php
if(EC!=1) exit;

#Action: save as new
if(isset($_POST['asNew'])) $id = 0;

#Page title
$view->title = $id ? $lang['edit2'] : $lang['add2'];

#Action: save
if($_POST)
{
	$file = array(
	'cat'  => (int)$_POST['cat'],
	'dsc'  => clean($_POST['dsc']),
	'name' => clean($_POST['name']),
	'file' => clean($_POST['file']),
	'size' => clean($_POST['size']),
	'fulld' => &$_POST['full'],
	'author' => clean($_POST['author']),
	'access' => isset($_POST['access']),
	'priority' => (int)$_POST['priority']);

	try
	{
		$e = new Saver($file, $id, 'files');

		#Prepare query
		if($id)
		{
			$file['ID'] = $id;
			$q = $db->prepare('UPDATE '.PRE.'files SET cat=:cat, access=:access, name=:name,
			author=:author, dsc=:dsc, file=:file, size=:size, priority=:priority, fulld=:fulld WHERE ID=:ID');
		}
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'files (cat, name, author, date, dsc,
				file, access, size, priority, fulld) VALUES (:cat, :name, :author, "'.
				gmdate('Y-m-d H:i:s').'", :dsc, :file, :access, :size, :priority, :fulld)');
		}
		$q->execute($file);
		if(!$id) $id = $db->lastInsertId();

		#Apply changes
		$e->apply();

		#Redirect to file
		if(isset($_GET['ref'])) header('Location: '.URL.url('file/'.$id));

		#Info + links
		$view->info($lang['saved'], array(
			url('file/'.$id)  => sprintf($lang['see'], $file['name']),
			url($file['cat']) => $lang['goCat'],
			url('edit/2')     => $lang['add2'],
			url('list/2')     => $lang['files'],
			url('list/2/'.$file['cat']) => $lang['doCat']));
		unset($e,$file);
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($e->getMessage());
	}
}

#Action: form
else
{
	if($id)
	{
		$file = $db->query('SELECT * FROM '.PRE.'files WHERE ID='.$id)->fetch(2);

		if(!$file || !admit($file['cat'],'CAT',$file['author']))
		{
			return;
		}
	}
	else
	{
		$file = array(
			'cat' => $lastCat, 'name' => '', 'dsc' => '', 'priority' => 2,
			'file'=> 'files/', 'size' => '', 'author' => $user['login'], 'fulld' => '', 'access' => 1);
	}
}

#Editor JS
if(isset($cfg['wysiwyg']) && is_dir('plugins/editor'))
{
	$view->script('plugins/editor/loader.js');
}
else
{
	$view->script(LANG_DIR.'edit.js');
	$view->script('lib/editor.js');
}

#Template
$view->add('edit_file', array(
	'file'    => &$file,
	'id'      => $id,
	'cats'    => Slaves(2,$file['cat']),
	'fileman' => admit('FM') ? true : false
));
