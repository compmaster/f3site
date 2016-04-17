<?php
if(iCMSa!=1 || !admit('N')) exit;
require LANG_DIR.'admAll.php';
require './lib/forms.php';

#Page title
$view->title = $id ? $lang['editBox'] : $lang['addBox'];

#Action: save
if($_POST)
{
	$m = array(
		'text' => clean($_POST['text']),
		'disp' => clean($_POST['disp']),
		'img'  => clean($_POST['img']),
		'menu' => (int)$_POST['menu'],
		'type' => (int)$_POST['type'],
		'value'=> $_POST['value']
	);

	#Menu options
	$o = array();
	$ile = isset($_POST['adr']) ? count($_POST['adr']) : 0;
	for($i=0;$i<$ile;++$i)
	{
		switch($_POST['t'][$i][0])
		{
			case 'c': $type = 5; $val = (int)substr($_POST['t'][$i], 1); break;
			case 'p': $type = 6; $val = (int)substr($_POST['t'][$i], 1); break;
			case '1': $type = 1; $val = '.'; break;
			default: $type = (int)$_POST['t'][$i]; $val = clean($_POST['adr'][$i]);
		}
		$o[] = array(
			0 => $_POST['txt'][$i],
			1 => $type,
			2 => $val,
			3 => isset($_POST['nw'][$i]),
			4 => $i,
			5 => $id);
	}

	#Start transaction
	try
	{
		$db->beginTransaction();

		#Edit existing
		if($id && !isset($_POST['savenew']))
		{
			$q = $db->prepare('UPDATE '.PRE.'menu SET text=:text, disp=:disp, menu=:menu,
				type=:type, img=:img, value=:value WHERE ID='.$id);
			$db->exec('DELETE FROM '.PRE.'mitems WHERE menu='.$id);
		}
		#New or save as new
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'menu (seq,text,disp,menu,type,img,value)
				VALUES ('.(dbCount('menu')+1).',:text,:disp,:menu,:type,:img,:value)');
		}
		$q->execute($m);

		#Get ID
		if(!$id OR isset($_POST['savenew'])) $id = $db->lastInsertId();

		#Menu items
		if($m['type']==3)
		{
			#Add menu items
			$q = $db->prepare('INSERT INTO '.PRE.'mitems (text,type,url,nw,seq,menu) VALUES (?,?,?,?,?,?)');
			foreach($o as &$i)
			{
				$i[5] = $id;
				$q->execute($i);
			}
		}
		$db->commit();

		#Update menu cache
		include './lib/mcache.php';
		RenderMenu();

		#Redirect
		header('Location: '.URL.url('menu', '', 'admin'));
		$view->message($lang['saved'], url('menu', '', 'admin'));
	}
	catch(PDOException $e)
	{
		$view->info($e->getMessage());
	}
}

#Edit (ASSOC)
elseif($id)
{
	if(!$m = $db->query('SELECT * FROM '.PRE.'menu WHERE ID='.$id) -> fetch(2))
	return;

	if($m['type'] == 3)
	{
		$o = $db->query('SELECT text,type,url,nw FROM '.PRE.'mitems WHERE menu='.$id.' ORDER BY seq')->fetchAll(3);
	}
	else $o = array();
}
else
{
	$m = array('text'=>'', 'disp'=>'', 'img'=>'0', 'menu'=>1, 'type'=>3, 'value'=>'');
	$o = array(array('', 4, '', 0));
}

#Categories and free pages
$cats = $db->query('SELECT ID,name FROM '.PRE.'cats WHERE access!=3 ORDER BY name')->fetchAll(12);
$free = $db->query('SELECT ID,name FROM '.PRE.'pages WHERE access!=0 ORDER BY name')->fetchAll(12);

$view->script('lib/forms.js');
$view->add('editMenu', array(
	'menu' => &$m,
	'item' => &$o,
	'cats' => json_encode($cats),
	'pages' => json_encode($free),
	'fileman' => admit('FM'),
	'langlist' => filelist('lang', 1, $m['disp'])
));