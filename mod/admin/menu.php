<?php
if(iCMSa!=1 || !admit('N')) exit;
require LANG_DIR.'admAll.php';
require './lib/forms.php';

#Page title
$view->title = $lang['nav'];

#Save and delete menu blocks
if($_POST)
{
	try
	{
		$db->beginTransaction();
		$q = $db->prepare('UPDATE '.PRE.'menu SET seq=?, disp=?, menu=? WHERE ID=?');
		$del = array();

		foreach($_POST['seq'] as $id => $seq)
		{
			if(isset($_POST['x'][$id]))
			{
				$del[] = $id;
			}
			else
			{
				$q->execute(array( (int)$seq, clean($_POST['vis'][$id]), (int)$_POST['page'][$id], $id));
			}
		}

		#Delete menu block and unlinked items
		if($del)
		{
			$db->exec('DELETE FROM '.PRE.'menu WHERE ID IN ('.join(',', $del).')');
			$db->exec('DELETE FROM '.PRE.'mitems WHERE menu NOT IN (SELECT ID FROM '.PRE.'menu)');
		}
		$db->commit();
		$view->info($lang['saved']);
		unset($q,$seq,$_POST);

		#Rebuild menu cache
		require './lib/mcache.php';
		RenderMenu();
	}
	catch(PDOException $e)
	{
		$view->info($lang['error'].$e); return 1;
	}
}

#Get menu blocks
$res = $db->query('SELECT ID,seq,text,disp,menu,type FROM '.PRE.'menu ORDER BY disp,menu,seq');
$res->setFetchMode(3); //Num
$num = 0;
$lng = '1';
$prev = '1';
$blocks = array();

foreach($res as $m)
{
	if($m[3] != $prev && $m[3] != '3' && $m[3] != '2')
	{
		$lng = $prev = $m[3];
	}
	else
	{
		$lng  = false;
	}
	$blocks[] = array(
		'id' => $m[0],
		'seq' => $m[1],
		'url'  => url('editMenu/'.$m[0], '', 'admin'),
		'langs' => filelist('lang',1,$m[3]),
		'disp'  => $m[3],
		'title' => $m[2],
		'page'  => $m[4],
		'group' => $lng
	);
}

#Do szablonu
$view->add('menu', array(
	'blocks' => $blocks,
	'newURL' => url('editMenu', '', 'admin')
));