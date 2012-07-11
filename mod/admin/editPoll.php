<?php
if(iCMSa!=1) exit;
require LANG_DIR.'admAll.php';
require './lib/forms.php';

#Page title
$view->title = $id ? $lang['editPoll'] : $lang['addPoll'];

#Save poll
if($_POST)
{
	$poll = array(
	'q'      => clean($_POST['q']),
	'name'   => clean($_POST['name']),
	'ison'   => (int)$_POST['ison'],
	'type'   => (int)$_POST['type'],
	'access' => ctype_alpha($_POST['access']) ? $_POST['access'] : LANG
	);

	$num  = count($_POST['an']);
	$keep = array();
	$an = array();

	#Answers
	for($i=0; $i<$num; ++$i)
	{
		if(!$id || empty($_POST['id'][$i]))
		{
			$an[] = array(null, clean($_POST['an'][$i]), clean($_POST['c'][$i]));
		}
		else
		{
			$an[] = array((int)$_POST['id'][$i], clean($_POST['an'][$i]), clean($_POST['c'][$i]));
			$keep[] = (int)$_POST['id'][$i];
		}
	}
	try
	{
		$db->beginTransaction();

		#Edit poll and delete unlinked answers
		if($id)
		{
			$poll['id'] = $id;
			$q = $db->prepare('UPDATE '.PRE.'polls SET name=:name, q=:q, ison=:ison,
				type=:type, access=:access WHERE ID=:id');
			$db->exec('DELETE FROM '.PRE.'answers WHERE ID NOT IN ('.join(',',$keep).') AND IDP='.$id);
		}
		#New poll
		else
		{
			$q = $db->prepare('INSERT INTO '.PRE.'polls (name,q,ison,type,num,access,date)
				VALUES (:name,:q,:ison,:type,0,:access,CURRENT_DATE)');
		}
		$q->execute($poll);

		#Get ID
		if(!$id)
		{
			$id = $db->lastInsertId();
		}

		#Insert answers
		$q1 = $db->prepare('UPDATE '.PRE.'answers SET a=?, seq=?, color=? WHERE ID=? AND IDP=?');
		$q2 = $db->prepare('INSERT INTO '.PRE.'answers (seq,IDP,a,color) VALUES (?,?,?,?)');

		for($i=0; $i<$num; $i++)
		{
			if($an[$i][0])
			{
				$q1->execute(array($an[$i][1], $i, $an[$i][2], $an[$i][0], $id));
			}
			elseif($an[$i][1])
			{
				$q2->execute(array($i, $id, $an[$i][1], $an[$i][2]));
			}
		}

		#Update cache of latest polls
		include './mod/polls/poll.php';
		RebuildPoll();

		#Apply changes
		$db->commit();
		$view->info($lang['saved'], array(
			url('editPoll', '', 'admin') => $lang['addPoll'],
			url('editPoll/'.$id, '', 'admin') => $lang['editPoll'],
			url('poll/'.$id) => $poll['name']));
		return 1;
	}
	catch(Exception $e)
	{
		$view->info($lang['error'].$e);
	}
}

#Edit poll
elseif($id)
{
	if(!$poll = $db->query('SELECT * FROM '.PRE.'polls WHERE ID='.$id)->fetch(2))
	{
		return;
	}
	$an = $db->query('SELECT ID,a,color FROM '.PRE.'answers WHERE IDP='.$id.' ORDER BY seq')->fetchAll(3);
}

#New poll
else
{
	$poll = array('name'=>'', 'q'=>'', 'type'=>1, 'ison'=>1, 'access'=>LANG);
	$an = array(array(0,'','#ed1c24'), array(0,'','#22b14c'), array(0,'','#3f48cc'));
}

#Prepare template
$view->script('lib/forms.js');
$view->add('editPoll', array(
	'langs' => filelist('lang', 1, $id ? $poll['access'] : LANG),
	'poll'  => &$poll,
	'item'  => &$an
));