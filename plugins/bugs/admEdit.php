<?php
if(iCMSa!=1) exit;
require './lib/forms.php';

#Category ID
$id = isset($URL[2]) ? (int)$URL[2] : 0;

#Action: save
if($_POST)
{
	$cat = array(
		'sect' => (int)$_POST['sect'],
		'name' => clean($_POST['name']),
		'dsc'  => clean($_POST['dsc']),
		'see'  => clean($_POST['see']),
		'post' => clean($_POST['post']),
		'rate' => (int)$_POST['rate'],
		'text' => &$_POST['text']
	);

	#Change record or add new
	try
	{
		if($id)
		{
			$cat['id'] = $id;
			$q = $db->prepare('UPDATE '.PRE.'bugcats SET sect=:sect, name=:name, dsc=:dsc, see=:see, post=:post, rate=:rate, text=:text WHERE ID=:id');
		}
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'bugcats (sect,name,dsc,see,post,rate,text) VALUES (:sect,:name,:dsc,:see,:post,:rate,:text)');
		}
		$q->execute($cat);

		#Redirect to category list
		header('Location: '.URL.url('bugs','','admin'));
		$view->message($lang['saved'], url('bugs', '', 'admin'));
	}
	catch(PDOException $e)
	{
		$view->info($e);
	}
}
elseif($id)
{
	if(!$cat = $db->query('SELECT * FROM '.PRE.'bugcats WHERE ID='.$id)->fetch(2)) return;
}
else
{
	$cat = array(
		'sect' => 1,
		'name' => '',
		'dsc'  => '',
		'see'  => LANG,
		'post' => 'ALL',
		'rate' => 1,
		'text' => '',
	);
}

#Sections
$sect = array();
$res = $db->query('SELECT ID,title FROM '.PRE.'bugsect ORDER BY seq');
foreach($res as $x)
{
	$sect[] = array(
		'id'    => $x['ID'],
		'title' => $x['title'],
		'this'  => $x['ID'] == $cat['sect']
	);
}

#Form template
$view->add('adminEdit', array(
	'cat'   => &$cat,
	'sect'  => &$sect,
	'langs' => filelist('lang', 1, $cat['see']),
	'title' => $id ? $lang['editCat'] : $lang['addCat']
));