<?php
if(iCMSa!=1 || !admit('P')) exit;
require LANG_DIR.'admAll.php';

#Delete / enable / disable
if($_POST && $x = GetID(true))
{
	if(isset($_POST['del']))
	{
		$db->beginTransaction();
		$db->exec('DELETE FROM '.PRE.'pages WHERE ID IN('.$x.')');
		$db->exec('DELETE FROM '.PRE.'comms WHERE TYPE=102 AND CID IN('.$x.')');
		$db->commit();
	}
	else
	{
		$db->exec('UPDATE '.PRE.'pages SET access='.(isset($_POST['show']) ? 1 : 0).' WHERE ID IN('.$x.')');
	}
}

#Get pages from database - FETCH_NUM
$res = $db->query('SELECT ID,name,access FROM '.PRE.'pages ORDER BY ID DESC');
$res->setFetchMode(3);
$total = 0;
$pages = array();

foreach($res as $page)
{
	$pages[] = array(
		'id'    => $page[0],
		'num'   => ++$total,
		'url'   => url('page/'.$page[0]),
		'edit'  => url('editPage/'.$page[0], '', 'admin'),
		'title' => $page[1],
		'access'=> $page[2]=='1' ? $lang['on2'] : ($page[2]=='3' ? sprintf('%s *',$lang['on2']) : $lang['off2'])
	);
}

#No pages - add new page
if(empty($pages) && !JS)
{
	header('Location: '.URL.url('editPage','','admin'));
	exit;
}

$res = null;
$view->add('pages', array('pages' => $pages));
$view->title = $lang['ipages'];
