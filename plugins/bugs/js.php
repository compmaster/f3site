<?php //AJAX requests
require './plugins/bugs/lang/'.LANG.'.php';

#Your rights
$right = admit('BUGS');

#Bug ID
if(isset($_POST['id']) && is_numeric($_POST['id']))
{
	$id = $_POST['id'];
}
else return;

#Vote
if($URL[1] == 'vote' && is_numeric($_POST['v']))
{
	#Mark and IP
	$vote = $_POST['v'];
	$ip = $_SERVER['REMOTE_ADDR'].(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? 
		' '.$_SERVER['HTTP_X_FORWARDED_FOR'] : '');

	#Guests
	if(!UID && !isset($cfg['bugsVote']))
	{
		exit($lang['logtov']);
	}

	#Get data
	$q = $db->prepare('SELECT c.rate FROM '.PRE.'bugs b INNER JOIN '.PRE.'bugcats c ON b.cat=c.ID WHERE b.ID=?');
	$q->bindValue(1, $id, 1);
	$q->execute();

	#Does not exist
	if(!$bug = $q->fetch(2))
	{
		exit('Issue not found!');
	}

	#Hands
	try
	{
		if(dbCount('rates WHERE type=66 AND ID='.$id.' AND IP='.$db->quote($ip)))
		{
			exit($lang['voted']);
		}
		$db->beginTransaction();
		if($bug['rate']==1)
		{
			$field = ($vote == 5) ? 'pos' : 'neg';
			$db->exec(sprintf('UPDATE %sbugs SET %s=%s+1 WHERE ID=%d',PRE,$field,$field,$id));
			$info = 'OK';
		}
		elseif($vote > 0 && $vote < 6)
		{
			$all = $db->query('SELECT count(*),SUM(mark) FROM '.PRE.'rates WHERE type=66 AND ID='.$id)->fetch(3);
			$all[1] += $vote;
			$all[0] += 1;
			$avg = $all[0] > 1 ? count($all[1] / $all[0]) : $vote;
			$db->exec('UPDATE '.PRE.'bugs SET pos='.$avg.', neg='.$all[0].' WHERE ID='.$id);
			$info = json_encode(array($avg,$all[0]));
		}
		else
		{
			exit('Wrong parameters!');
		}
		$q = $db->prepare('INSERT INTO '.PRE.'rates (type,ID,mark,IP) VALUES (?,?,?,?)');
		$q->execute(array(66, $id, $vote, $ip));
		$db->commit();
		echo $info; exit;
	}
	catch(PDOException $e)
	{
		exit($lang['error'].$e->getMessage());
	}
}

#Change status
if($right && $URL[1] == 'status' && is_numeric($_POST['s']) && $_POST['s']>0 && $_POST['s']<6)
{
	try
	{
		$db->beginTransaction();
		$q = $db->prepare('UPDATE '.PRE.'bugs SET status=? WHERE ID=?');
		$q->execute(array($_POST['s'], $id));
		$db->commit();
		echo $lang['S'.$_POST['s']];
	}
	catch(Exception $e)
	{
		echo $lang['error'].': '.$e;
	}
}