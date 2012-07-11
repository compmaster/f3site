<?php
if(iCMSa!=1 || !admit('C')) exit;
require LANG_DIR.'admAll.php';
require './lib/categories.php';
require './cfg/content.php';
try {

#Delete cats
if(isset($_POST['del']) && $x = GetID(1))
{
	$res = $db->query('SELECT ID,name,access,type,lft,rgt FROM '.PRE.'cats WHERE ID IN ('.$x.')');

	#Do the job
	if($_POST['del'] == 'OK')
	{
		$type = parse_ini_file('cfg/types.ini',1);
		$db -> beginTransaction();

		foreach($res as $cat)
		{
			$id  = $cat['ID'];
			$t   = $type[$cat['type']]['table'];
			$t2  = isset($type[$cat['type']]['table2']) ? $type[$cat['type']]['table2'] : false;
			$sub = (int)$_POST['x'][$id];
			$new = (int)$_POST['items'][$id];
			$del = 'ID='.$id;

			#CONTENT
			if($new > 0) //Move
			{
				$db->exec('UPDATE '.PRE.$t.' SET cat='.$new.' WHERE cat='.$id);
			}
			elseif($new < 0) //Delete
			{
				$db->exec('DELETE FROM '.PRE.$t.' WHERE cat='.$id);
				if($t2) $db->exec('DELETE FROM '.PRE.$t.' WHERE cat='.$id);
				$db->exec('DELETE FROM '.PRE.'comms WHERE TYPE='.$cat['type'].
				' AND CID NOT IN (SELECT ID FROM '.PRE.$t.')');
			}

			#SUBCATEGORIES
			if($cat['rgt'] > $cat['lft'] + 1)
			{
				if($sub > 0) //Move
				{
					$db->exec('UPDATE '.PRE.'cats SET sc='.$sub.' WHERE sc='.$id);
				}
				elseif($sub == -1) //Delete
				{
					$del = 'lft BETWEEN '.$cat['lft'].' AND '.$cat['rgt'];
				}
				else //Set as primary
				{
					$db->exec('UPDATE '.PRE.'cats SET sc=0 WHERE sc='.$id);
				}
				UpdateCatPath();
			}
			$db->exec('DELETE FROM '.PRE.'cats WHERE '.$del); //Delete category
		}
		RebuildTree();
		CountItems();
		Latest();

		//Delete links from menu
		if($db->exec('DELETE FROM '.PRE.'mitems WHERE type=5 AND url IN('.$x.')'))
		{
			include './lib/mcache.php';
			RenderMenu();
		}

		//Finish and redirect
		$db->commit();
		header('Location: '.URL.url('cats', '', 'admin'));
	}
	else
	{
		$cat = array();
		foreach($res as $x)
		{
			if(in_array($x['ID'], $cfg['start']))
			{
				$warn = sprintf($lang['warnCat'], strtoupper($x['access']));
			}
			else
			{
				$warn = false;
			}
			$cat[] = array(
				'id'    => $x['ID'],
				'title' => $x['name'],
				'url'   => url($x['ID']),
				'cats'  => Slaves($x['type'],0,$x['ID']),
				'warn'  => $warn,
				'edit'  => $warn ? url('editCat/'.$x['ID'], '', 'admin') : false
			);
		}
		$view->add('editCats', array('cat'=>$cat));
	}
	$view->title = $lang['delCat'];
}

elseif(isset($_POST['count']))
{
	$db->beginTransaction(); CountItems(); $db->commit();
	header('Location: '.URL.url('cats','','admin'));
	exit;
}
else
{
	header('Location: '.URL.url('cats','','admin'));
	$view->info($lang['nocats']);
}

}
catch(PDOException $e) { $view->info($e); }
