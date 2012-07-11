<?php //Poll results
if(iCMS!=1) exit;

#Get a poll from database or cache
if($id)
{
	if(!$poll = $db->query('SELECT * FROM '.PRE.'polls WHERE ID='.$id) -> fetch(2)) return;
}
elseif(file_exists('./cache/poll_'.LANG))
{
	require('./cache/poll_'.LANG);
}
else return;

#Page title and description
$view->title = $poll['name'];
$view->desc  = $poll['q'];
$id = $poll['ID'];

#No votes
if($poll['num'] == 0)
{
	$view->info($lang['novotes'], array(url('polls') => $lang['archive']));
	return 1;
}

#Get answers
if($id)
{
	$option = $db->query('SELECT ID,a,color,num FROM '.PRE.'answers WHERE IDP='.$id.
	' ORDER BY '.(isset($cfg['pollSort']) ? 'num DESC,' : '').'seq')->fetchAll(3);
}

#How much answers?
$num = count($option);

#Creation date
$poll['date'] = formatDate($poll['date'], true);

# %
$item = array();
$file = 'poll';

#Pie chart
if(true)
{
	$file = 'poll-svg';
	$x0 = 50;
	$y0 = $a = $sum = 0;
	foreach($option as &$o) $sum += $o[3];
	$half = $sum / 2;
	foreach($option as &$o)
	{
		if($o[3] === '0') continue;
		if($o[3] == $sum)
		{
			$x = 49.9;	// 100% fix
			$y = 0;
		}
		else
		{
			$a += $o[3] * M_PI / $half;
			$x = 50 + sin($a) * 50;
			$y = 50 - cos($a) * 50;
		}
		$item[] = array(
			'num' => $o[3],
			'label' => $o[1],
			'color' => $o[2],
			'angle' => $a,
			'x1' => $x0,
			'y1' => $y0,
			'x2' => $x0 = $x,
			'y2' => $y0 = $y,
			'large' => $o[3] > $half ? '1' : '0',
			'percent' => round($o[3] / $poll['num'] * 100 ,$cfg['pollRound'])
		);
	}
}
else
{
	foreach($option as &$o)
	{
		$item[] = array(
			'num'  => $o[3],
			'label' => $o[1],
			'color' => $o[2],
			'percent' => round($o[3] / $poll['num'] * 100 ,$cfg['pollRound'])
		);
	}
}

#Template
$view->add($file, array(
	'poll' => &$poll,
	'item' => &$item,
	'archive' => url('polls')
));

#Comments
if(isset($cfg['pollComm']))
{
	include './lib/comm.php';
	comments($id, 103);
}
