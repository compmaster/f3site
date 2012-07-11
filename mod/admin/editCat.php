<?php
if(iCMSa!=1 || !admit('C')) exit;
require LANG_DIR.'admAll.php';
require './lib/categories.php';
require './lib/forms.php';

#Page title
$view->title = $id ? $lang['editCat'] : $lang['addCat'];

#Action: save
if($_POST)
{
	#Parent category
	$up = (int)$_POST['sc'];

	#Category structure: 1
	$o = isset($_POST['o1']);

	#Comments: 2
	isset($_POST['o2']) AND $o |= 2;

	#Rating: 4
	isset($_POST['o3']) AND $o |= 4;

	#Category list: 8
	isset($_POST['o4']) AND $o |= 8;

	#Content of all subcategories: 16
	isset($_POST['o5']) AND $o |= 16;

	#Data
	$cat = array(
	'sc'    => $up,
	'opt'   => $o,
	'text'  => $_POST['txt'],
	'dsc'   => clean($_POST['dsc']),
	'name'  => clean($_POST['name']),
	'access'=> clean($_POST['vis']),
	'type'  => (int)$_POST['type'],
	'sort'  => (int)$_POST['sort']
	);

	try
	{
		$db->beginTransaction();

		#Update existing
		if($id)
		{
			$q = $db->prepare('UPDATE '.PRE.'cats SET name=:name,dsc=:dsc,access=:access,
				type=:type,sc=:sc,sort=:sort,text=:text,opt=:opt WHERE ID=:id');
			$old = $db->query('SELECT ID,access,sc,lft,rgt FROM '.PRE.'cats WHERE ID='.$id)->fetch(3);
			$cat['id'] = $id;
		}
		#Insert new
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'cats (name,dsc,access,type,sc,sort,text,opt,lft,rgt)
				VALUES (:name,:dsc,:access,:type,:sc,:sort,:text,:opt,:lft,:rgt)');

			#Get right key of last category
			$cat['lft'] = (int) $db->query('SELECT rgt FROM '.PRE.'cats WHERE'.(($up) ?
				' ID='.$up : ' sc=0 ORDER BY lft DESC LIMIT 1')) -> fetchColumn();

			#Shift categories
			if($up)
			{
				$db->exec('UPDATE '.PRE.'cats SET lft=lft+2 WHERE lft>='.$cat['lft']);
				$db->exec('UPDATE '.PRE.'cats SET rgt=rgt+2 WHERE rgt>='.$cat['lft']);
			}
			else
			{
				++$cat['lft'];
			}
			$cat['rgt'] = $cat['lft']+1;
		}
		$q->execute($cat);

		#Get ID or rebuild the whole tree
		if(!$id)
		{
			$id = $db->lastInsertId();
		}
		elseif($up!=$old[2])
		{
			RebuildTree();
		}

		#Apply changes and rebuild category structure cache
		$db->commit();
		UpdateCatPath($id);

		#Redirect
		if(isset($_GET['ref'])) header('Location: '.URL.url($id));

		#Info + links
		$view->info($lang['saved'].' ID: '.$id, array(
			url($id) => $lang['goCat'],
			url('editCat', '', 'admin') => $lang['addCat'],
			url('editCat/'.$id, '', 'admin') => $lang['editCat'],
			url('list/'.$id) => $lang['mantxt'],
			url('edit/'.$cat['type'], 'catid='.$id) => $lang['addItem']
		));
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($e->getMessage()); //Errors
	}
}

#Action: FORM
elseif($id)
{
	if(!$cat = $db->query('SELECT * FROM '.PRE.'cats WHERE ID='.$id) -> fetch(2)) //ASSOC
	return;
}
else
{
	$cat = array(
		'name' => '',
		'dsc'  => '',
		'type' => isset($_GET['type']) ? (int)$_GET['type'] : 5,
		'sc'   => 0,
		'text' => '',
		'sort' => 2,
		'opt'  => 15,
		'access'=> 1
	);
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

#Custom types
$custom = array();
foreach(parse_ini_file('cfg/types.ini',1) as $type=>$data)
{
	if($type > 5)
	{
		if(isset($data[LANG]))
		{
			$custom[] = array('type' => $id, 'name' => $data[LANG]);
		}
		elseif(isset($data['en']))
		{
			$custom[] = array('type' => $id, 'name' => $data['en']);
		}
		else
		{
			$custom[] = array('type' => $id, 'name' => $data['table']);
		}
	}
}

#Prepare template
$view->add('editCat', array(
	'cat'   => &$cat,
	'o1'    => $cat['opt'] & 1,
	'o2'    => $cat['opt'] & 2,
	'o3'    => $cat['opt'] & 4,
	'o4'    => $cat['opt'] & 8,
	'o5'    => $cat['opt'] & 16,
	'custom'=> $custom,
	'cats'  => Slaves(0,$cat['sc'],$id),
	'langs' => filelist('lang',1,$cat['access'])
));
