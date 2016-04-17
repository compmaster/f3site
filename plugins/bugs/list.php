<?php
if(iCMS!=1) exit;

#Get category
if(isset($URL[2]) && is_numeric($URL[2]))
{
	$id = $URL[2];
	$q = $db->prepare('SELECT name,post,num,text FROM '.PRE.'bugcats WHERE (see=1 OR see=?) AND ID=?');
	$q->execute(array(LANG, $URL[2]));
	if(!$cat = $q->fetch(2)) return;
}
else return;

#Page title
$view->title = $cat['name'];

#Category text
if($cat['text'] && isset($cfg['bugsUp'])) $view->info(nl2br($cat['text']));

#Page number
if(isset($URL[3]) && is_numeric($URL[3]) && $URL[3]>1)
{
	$page = $URL[3];
	$st = ($page-1)*$cfg['bugsNum'];
}
else
{
	$page = 1;
	$st = 0;
}

#Get issues
$res = $db->prepare('SELECT ID,name,num,date,status,level FROM '.PRE.'bugs WHERE cat=?'.
	(admit('BUGS') ? '' : ' AND status!=5').' ORDER BY ID DESC LIMIT ?,?');
$res -> bindValue(1, $id, 1);
$res -> bindValue(2, $st, 1);
$res -> bindValue(3, $cfg['bugsNum'], 1);
$res -> execute();

$all = array();
$num = 0;

foreach($res as $x)
{
	$all[] = array(
		'id'     => $x['ID'],
		'title'  => $x['name'],
		'status' => $x['status'],
		'lv'     => $x['level'],
		'num'    => $x['num'],
		'url'    => url('bugs/'.$x['ID']),
		'date'   => formatDate($x['date'], 1),
		'class'  => BugIsNew('', $x['date']) ? 'New' : 'Old',
		'level'  => $lang['L'.$x['level']]
	);
	++$num;
}

#Pages
if(!$num)
{
	$view->info($lang['noc']);
}
elseif($cat['num'] > $num)
{
	$pages = pages($page, $cat['num'], $cfg['bugsNum'], url('bugs/list/'.$id), 0, '/');
}
else
{
	$pages = '';
}

#template
$view->add('bugs', array(
	'issue'   => &$all,
	'postURL' => BugRights($cat['post']) ? url('bugs/post', 'f='.$id) : false
));
