<?php
if(iCMSa!=1 || !admit('L')) exit;
require LANG_DIR.'events.php';

#Delete events
if($_POST && $x = GetID(true))
{
	$db->exec('DELETE FROM '.PRE.'log WHERE ID IN ('.$x.')');
	event('ERASE');
}

#Page number
if(isset($_GET['page']) && $_GET['page']>1)
{
	$page = $_GET['page'];
	$st = ($page-1)*30;
}
else
{
	$page = 1;
	$st = 0;
}

#Total events
$total = dbCount('log');
$event = array();

#Get events - FETCH_ASSOC
$res = $db->query('SELECT l.*,u.login FROM '.PRE.'log l LEFT JOIN '.PRE.'users u
	ON l.user=u.ID AND l.user!=0 ORDER BY date DESC LIMIT '.$st.',30');
$res->setFetchMode(3);

#List events
foreach($res as $i)
{
	$event[] = array(
		'id'   => $i[0],
		'text' => isset($events[$i[1]]) ? $events[$i[1]] : $i[1],
		'date' => formatDate($i[2], true),
		'login'=> $i[5],
		'ip'   => $i[3],
		'user' => $i[4] ? url('user/'.urlencode($i[5])) : false
	);
}

#Prepare template
$view->add('log', array(
	'event' => &$event,
	'pages' => pages($page, $total, 30, url('log', '', 'admin'), 1),
	'url'   => url('log', 'page='.$page, 'admin')
));
