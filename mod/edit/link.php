<?php
if(EC!=1) exit;

#Action: save as new
if(isset($_POST['asNew'])) $id = 0;

#Page title
$view->title = $id ? $lang['edit4'] : $lang['add4'];

#Action: save
if($_POST)
{
	$link = array(
	'cat' => (int)$_POST['cat'],
	'dsc' => clean($_POST['dsc']),
	'adr' => clean( str_replace(array('javascript:','vbscript:'),'',$_POST['adr']) ),
	'name'=> clean($_POST['name']),
	'nw'  => isset($_POST['nw']),
	'access'=> isset($_POST['access']),
	'priority'=> (int)$_POST['priority'] );

	try
	{
		$e = new Saver($link, $id, 'links', 'cat,access');

		#Prepare query
		if($id)
		{
			$link['ID'] = $id;
			$q = $db->prepare('UPDATE '.PRE.'links SET cat=:cat, access=:access,
			name=:name, dsc=:dsc,adr=:adr, priority=:priority, nw=:nw WHERE ID=:ID');
		}
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'links (cat,access,name,dsc,adr,priority,nw)
				VALUES (:cat,:access,:name,:dsc,:adr,:priority,:nw)');
		}
		$q->execute($link);
		if(!$id) $id = $db->lastInsertId();

		#Apply changes
		$e->apply();

		#Redirect to link
		if(isset($_GET['ref']) && isset($cfg['linkFull']))
		{
			header('Location: '.URL.url('link/'.$id));
		}

		#Link URL
		$url = isset($cfg['linkFull']) ? url('link/'.$id) : $link['adr'];

		#Info + links
		$view->info($lang['saved'], array(
			$url => sprintf($lang['see'], $link['name']),
			url($link['cat']) => $lang['goCat'],
			url('edit/4') => $lang['add4'],
			url('list/4') => $lang['links'],
			url('list/4/'.$link['cat']) => $lang['doCat']));
		unset($e,$link);
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($e->getMessage());
	}
}

#Action: edit
else
{
	if($id)
	{
		$link = $db->query('SELECT * FROM '.PRE.'links WHERE ID='.$id) -> fetch(2); //ASSOC
		if(!$link || !admit($link['cat'],'CAT'))
		{
			return;
		}
	}
	else
	{
		$link = array('cat'=>$lastCat,'name'=>'','dsc'=>'','access'=>1,'nw'=>0,'priority'=>2,'adr'=>'http://');
	}
}

#Template
$view->add('edit_link', array(
	'link' => &$link,
	'id'   => $id,
	'cats' => Slaves(4,$link['cat'])
));
