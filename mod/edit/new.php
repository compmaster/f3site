<?php
if(EC!=1) exit;

#Action: save as new
if(isset($_POST['asNew'])) $id = 0;

#Page title
$view->title = $id ? $lang['edit5'] : $lang['add5'];

#Action: save
if($_POST)
{
	$news = array(
	'opt'  => isset($_POST['br']) + (isset($_POST['emo']) ? 2:0) + (isset($_POST['fn']) ? 4:0),
	'name' => clean($_POST['name']),
	'img'  => clean($_POST['img']),
	'txt'  => &$_POST['txt'],
	'cat'	 => (int)$_POST['cat'],
	//'pin'  => isset($_POST['pos']),
	'access' => isset($_POST['access']));

	#Full text
	$full = &$_POST['text'];

	try
	{
		$e = new Saver($news,$id,'news');

		#Query
		if($id)
		{
			$news['ID'] = $id;
			$q = $db->prepare('UPDATE '.PRE.'news SET cat=:cat, access=:access,
			name=:name, txt=:txt, img=:img, opt=:opt WHERE ID=:ID');
		}
		else
		{
			$news['author'] = UID;
			$news['date'] = gmdate('Y-m-d H:i:s');
			$q = $db->prepare('INSERT INTO '.PRE.'news (cat,access,name,txt,date,author,img,opt)
				VALUES (:cat,:access,:name,:txt,:date,:author,:img,:opt)');
		}
		$q->execute($news);

		#Get new ID
		if(!$id) $id = $db->lastInsertId();

		$q = $db->prepare('REPLACE INTO '.PRE.'newstxt (id,cat,text) VALUES (?,?,?)');
		$q->bindValue(1, $id, 1);
		$q->bindValue(2, $news['cat'], 1); //INT
		$q->bindParam(3, $full);
		$q->execute();

		#Update RSS channels
		RSS();

		#Finish job
		$e->apply();

		#Redirect to news
		if(isset($_GET['ref']))
		{
			if(empty($_GET['ref']))
			{
				header('Location: '.URL.url('news/'.$id));
			}
			elseif(is_numeric($_GET['ref']))
			{
				header('Location: '.URL.($news['cat']==$cfg['start'][LANG] ? '' : url($_GET['ref'])));
			}
		}

		#Info + links
		$view->info($lang['saved'], array(
			url('news/'.$id)  => sprintf($lang['see'], $news['name']),
			url($news['cat']) => $lang['goCat'],
			url('edit/5')     => $lang['add5'],
			url('list/5')     => $lang['news'],
			url('list/5/'.$news['cat']) => $lang['doCat']));
		unset($e,$news);
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($e->getMessage());
	}
}

#Form
else
{
	if($id)
	{
		$news = $db->query('SELECT n.*,f.text FROM '.PRE.'news n LEFT JOIN '.
			PRE.'newstxt f ON n.ID=f.ID WHERE n.ID='.$id) -> fetch(2);
		$full = &$news['text'];

		#Verify privileges
		if(!$news || !admit($news['cat'],'CAT',$news['author'])) return;
	}
	else
	{
		$news = array('cat'=>$lastCat,'name'=>'','txt'=>'','access'=>1,'img'=>'',/*'pin'=>0,*/'opt'=>3);
		$full = '';
	}
}

#Checkbox fields
$news['br']  = $news['opt'] & 1;
$news['emo'] = $news['opt'] & 2;
$news['fn']  = $news['opt'] & 4;

#JavaScript editor
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
$view->add('edit_news', array(
	'news' => &$news,
	'full' => &$full,
	'id'   => $id,
	'cats' => Slaves(5,$news['cat']),
	'fileman' => admit('FM')
));
