<?php

#Config
include './cfg/content.php';

#Get category ID from URL or load default
if(isset($URL[0]))
{
	$d = (int)$URL[0];
}
elseif(isset($cfg['start'][LANG]))
{
	$d = $cfg['start'][LANG];
}
else
{
	require './mod/cats.php'; return 1;
}

#Load category to ASSOC $cat
if(!$cat = $db->query('SELECT * FROM '.PRE.'cats WHERE access!=3 AND ID='.$d)->fetch(2))
{
	return;
}

#Set title
$view->title = $cat['name'];

#Set description
if($cat['dsc']) $view->desc = $cat['dsc'];

#Page
if(isset($URL[1]) && is_numeric($URL[1]) && $URL[1] > 1)
{
	$page = $URL[1];
	$st = ($page-1) * $cfg['np'];
}
else
{
	$page = 1;
	$st = 0;
}

#Option: items from all subcategories
#TODO: SELECT * FROM items JOIN cats ON items.cat_id = cats.id
if($cat['opt'] & 16)
{
	$cats = 'cat IN (SELECT ID FROM '.PRE.'cats WHERE lft BETWEEN '.$cat['lft'].' AND '.$cat['rgt'].')';
	$cat['num'] = $cat['nums'];
}
else
{
	$cats = 'cat='.$d;
}

#Subcategories
if($cat['opt'] & 8)
{
	$res = $db->query('SELECT ID,name,dsc,nums FROM '.PRE.'cats WHERE sc='.$cat['ID'].
		' AND (access=1 OR access="'.LANG.'") ORDER BY name');
	$res->setFetchMode(3);

	foreach($res as $c)
	{
		$sc[] = array(
			'url'  => url($c[0]),
			'name' => $c[1],
			'desc' => $c[2],
			'num'  => $c[3]
		);
	}
}

#If empty and have privileges
if($cat['num'] == '0' && empty($sc) && admit($d,'CAT'))
{
	header('Location: '.URL.url('edit/'.$cat['type']));
}

#Prepare template
$data = array(
	'cat'  => &$cat,
	'edit' => admit('C') ? url('editCat/'.$d, 'ref', 'admin') : null,
	'add'  => url('edit/'.$cat['type'], 'catid='.$d),
	'list' => url('list/'.$cat['type'].'/'.$d),
	'subcats' => isset($sc) ? $sc : null,
	'options' => admit($d,'CAT')
);

#Category path
if($cat['opt'] & 1 && isset($cfg['catStr']))
{
	$view->path = catPath($d,$cat);
}
else
{
	$view->path = null;
}

#Load item list generator - TODO: improve
if($cat['num'])
{
	$view->add('cat', $data);
	include './mod/cat/'.$cat['type'].'.php';
}
else
{
	$data['type'] = $lang['cats'];
	$data['cats'] = url('cats');
	$view->add('cat', $data);
}
