<?php
if(iCMSa!=1 || !admit('B')) exit;

#Action: save
if($_POST)
{
	$ad = array(
		'name' => clean($_POST['name']),
		'code' => $_POST['code'],
		'ison' => (int)$_POST['ison'],
		'gen'  => (int)$_POST['gen']
	);
	try
	{
		#Invalid key
		if(empty($_SESSION['key']) || $_POST['key'] != $_SESSION['key'])
		{
			$view->info($lang['again']);
		}
		#Update
		elseif($id)
		{
			$q = $db->prepare('UPDATE '.PRE.'banners SET gen=:gen, name=:name,
			ison=:ison, code=:code WHERE ID='.$id);
		}
		#New
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'banners (gen,name,ison,code)
			VALUES (:gen,:name,:ison,:code)');
		}
		#Apply changes
		$q->execute($ad);
		$view->info($lang['saved']);
		header('Location: '.URL.url('ads','','admin'));
		return 1;
	}
	catch(PDOException $e)
	{
		$view->info($lang['error'].$e->getMessage(), null, 'error');
	}
}
elseif($id)
{
	if(!$ad = $db->query('SELECT * FROM '.PRE.'banners WHERE ID='.$id)->fetch(2))
	return;
}
else
{
	$ad = array('gen'=>1,'name'=>'','ison'=>1,'code'=>'');
}

#Language file
require LANG_DIR.'admAll.php';

#Editor
$view->script(LANG_DIR.'edit.js');
$view->script('lib/editor.js');

#Page title, template
$view->title = $id ? $lang['editAd'] : $lang['addAd'];
$view->add('editAd', array(
	'ad' => &$ad,
	'key' => uniqid()
));
