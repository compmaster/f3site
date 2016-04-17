<?php
if(iCMS!=1) exit;

#Page title
$view->title = $lang['archive'];

#Get polls from database
$res = $db->query('SELECT ID,name,num,date FROM '.PRE.'polls WHERE access="'.LANG.'" ORDER BY ID DESC');
$res->setFetchMode(3);

#Initialize
$poll = array();
$num = 0;

foreach($res as $p)
{
	$poll[] = array(
		'title' => $p[1],
		'url'   => url('poll/'.$p[0]),
		'date'  => formatDate($p[3]),
		'votes' => $p[2],
		'num'   => ++$num
	);
}

#Template
if($num > 0)
	$view->add('polls', array('poll' => &$poll));
else
	$view->info($lang['noc']);

unset($res,$poll,$lp);
