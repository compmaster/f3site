<?php
if(iCMS!=1 OR !admit('CM')) exit;
require LANG_DIR.'comm.php';

#Page title
$view->title = $lang['comms'];

#Commit action
if(isset($_POST['a']))
{
	$del = $ok = $no = array();
	foreach($_POST['a'] as $id=>$x)
	{
		switch($x)
		{
			case 1: $ok[] = (int)$id; break;
			case 0: $no[] = (int)$id; break;
			case 2: $del[] = (int)$id;
		}
		try
		{
			if($del) $db->exec('DELETE FROM '.PRE.'comms WHERE ID IN('.join(',',$del).')');
			if($no) $db->exec('UPDATE '.PRE.'comms SET access=0 WHERE ID IN('.join(',',$no).')');
			if($ok) $db->exec('UPDATE '.PRE.'comms SET access=1 WHERE ID IN('.join(',',$ok).')');
		}
		catch(PDOException $e)
		{
			$view->info('ERROR: '.$e);
		}
	}
}

#Page number
if(isset($_GET['page']) && $_GET['page']>1)
{
	$page = (int)$_GET['page'];
	$st = ($page-1)*20;
}
else
{
	$page = 1;
	$st = 0;
}

#Filter: IP / hidden
if(isset($URL[1]))
{
	if($URL[1] == 'hidden')
	{
		$q = ' WHERE access!=1';
	}
	else
	{
		$q = ' WHERE ip='.$db->quote($URL[1]);
	}
}
else
{
	$q = '';
}

#Count all comments
$total = dbCount('comms'.$q);
$com   = array();

#Get comments from database
$res = $db->query('SELECT c.*,u.login FROM '.PRE.'comms c LEFT JOIN '.PRE.
	'users u ON c.UID!=0 AND c.UID=u.ID '.$q.' ORDER BY c.ID DESC LIMIT '.$st.',20');

#BBCode support
if(isset($cfg['bbcode'])) include_once('./lib/bbcode.php');

#Get category types
$type = parse_ini_file('cfg/types.ini',1);

foreach($res as $x)
{
	switch($x['TYPE'])
	{
		case '10': $co = 'user'; break;
		case '59': $co = 'page'; break;
		case '15': $co = 'poll'; break;
		case '11': $co = 'group'; break;
		default: $co = isset($type[$x['TYPE']]) ? $type[$x['TYPE']]['name'] : null;
	}
	$com[] = array(
		'text'  => nl2br(emots(isset($cfg['bbcode']) ? BBCode($x['text']) : $x['text'])),
		'date'  => formatDate($x['date'],1),
		'url'   => url('comment/'.$x['ID']),
		'findIP'=> url('moderate/'.$x['IP']),
		'item'  => $co ? url($co.'/'.$x['CID']) : null,
		'id'    => $x['ID'],
		'title' => $x['name'],
		'user'  => $x['login'] ? $x['login'] : $x['author'],
		'ip'    => $x['IP'],
		'access' => $x['access'],
		'profile' => $x['login'] ? url('user/'.urlencode($x['login'])) : null
	);
}

#Prepare template
$view->add('moderate', array(
	'comment' => $com,
	'total'   => $total,
	'url'     => url('moderate'),
	'nourl'   => url('moderate/hidden'),
	'color'   => isset($cfg['colorCode']),
	'pages'   => pages($page,$total,20,url('moderate'),1)
));
