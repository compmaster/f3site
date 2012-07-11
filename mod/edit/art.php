<?php
if(EC!=1) exit;

#Action: save as new
if(isset($_POST['asNew'])) $id = 0;

#Page title
$view->title = $id ? $lang['edit1'] : $lang['add1'];

#Action: save
if($_POST)
{
	$num = count($_POST['txt']);
	$full = array();

	#Pages, pre, code
	for($i=0; $i<$num; ++$i)
	{
		if(empty($_POST['txt'][$i])) continue;
		if(isset($_POST['code'][$i]))
		{
			$_POST['txt'][$i] = preg_replace_callback(array(
			'#<(pre)([^>]*)>(.*?)</pre>#si',
			'#<(code)([^>]*)>(.*?)</code>#si'), create_function('$x',
			'return "<$x[1]$x[2]>".htmlspecialchars($x[3],0)."</$x[1]>";'), $_POST['txt'][$i]);
		}
		$full[] = array($i+1, &$_POST['txt'][$i], isset($_POST['br'][$i]) +
			(isset($_POST['emo'][$i]) ? 2 : 0) + (isset($_POST['code'][$i]) ? 4 : 0));
	}

	#Prepare data
	$art = array(
	'pages' => count($full),
	'cat'   => (int)$_POST['cat'],
	'dsc'   => clean($_POST['dsc']),
	'name'  => clean($_POST['name']),
	'author' => clean($_POST['author']),
	'access'  => isset($_POST['access']),
	'priority'=> (int)$_POST['priority']);

	try
	{
		$e = new Saver($art,$id,'arts');

		#DB query
		if($id)
		{
			$art['ID'] = $id;
			$q = $db->prepare('UPDATE '.PRE.'arts SET cat=:cat, access=:access, name=:name,
			dsc=:dsc, author=:author, priority=:priority, pages=:pages WHERE ID=:ID');
		}
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'arts (cat,name,dsc,date,author,access,priority,pages)
			VALUES (:cat,:name,:dsc,"'.gmdate('Y-m-d H:i:s').'",:author,:access,:priority,:pages)');
		}
		$q->execute($art);

		#Get new ID
		if(!$id) $id = $db->lastInsertId();

		#Prepare query for pages
		$q = $db->prepare('REPLACE INTO '.PRE.'artstxt (id,page,cat,text,opt)
			VALUES ('.$id.',?,'.$art['cat'].',?,?)');

		#Modify article pages
		foreach($full as &$x) $q->execute($x);

		#Delete other pages
		$db->exec('DELETE FROM '.PRE.'artstxt WHERE ID='.$id.' AND page>'.count($full));

		#Apply changes
		$e->apply();

		#Redirect to article
		if(isset($_GET['ref']) && is_numeric($_GET['ref']))
		{
			$page = $_GET['ref']>1 && isset($full[$_GET['ref']-1]) ? '/'.$_GET['ref'] : '';
			header('Location: '.URL.url('art/'.$id.$page));
		}

		#Info + links
		$view->info($lang['saved'], array(
			url('art/'.$id)  => sprintf($lang['see'], $art['name']),
			url($art['cat']) => $lang['goCat'],
			url('edit/1')    => $lang['add1'],
			url('list/1')    => $lang['arts'],
			url('list/1/'.$art['cat']) => $lang['doCat']));
		unset($e,$q,$art,$full);
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($e->getMessage());
	}
}
else
{
	if($id)
	{
		$res = $db->query('SELECT * FROM '.PRE.'arts WHERE ID='.$id);
		$art = $res->fetch(2); //ASSOC
		$res = null;

		#Privileges
		if(!$art || !admit($art['cat'],'CAT',$art['author'])) return;

		#Get text
		$res = $db->query('SELECT page,text,opt FROM '.PRE.'artstxt WHERE ID='.$id.' ORDER BY page');
		$full = $res->fetchAll(3);
		$res = null;
		if(!$full) $full = array(array(1,'',1));
	}
	else
	{
		$art = array(
			'pages' => 1, 'name' => '', 'access' => 1, 'priority' => 2, 'dsc' => '',
			'author'=> $user['login'], 'cat' => $lastCat);
		$full = array(array(1,'',1));
	}
}

#Checkbox
foreach($full as $key=>&$val)
{
	$full[$key] = array('page'=>$val[0], 'txt'=>$val[1], 'br'=>$val[2]&1, 'emo'=>$val[2]&2, 'code'=>$val[2]&4);
	if($full[$key]['code'])
	{
		$full[$key]['txt'] = preg_replace_callback(array(
			'#<(pre)([^>]*)>(.*?)</pre>#si',
			'#<(code)([^>]*)>(.*?)</code>#si'), create_function('$x',
			'return "<$x[1]$x[2]>".htmlspecialchars_decode($x[3],0)."</$x[1]>";'), $full[$key]['txt']);
	}
}

#Editor JS
if(isset($cfg['editor']) && is_dir('plugins/'.$cfg['editor']))
{
	$view->script('plugins/'.$cfg['editor'].'/editor.js');
}
else
{
	$view->script(LANG_DIR.'edit.js');
	$view->script('cache/emots.js');
	$view->script('lib/editor.js');
}

#Template
$view->add('edit_art', array(
	'art' => &$art,
	'id'  => $id,
	'full' => &$full,
	'cats' => Slaves(1,$art['cat']),
	'author' => authors()
));
