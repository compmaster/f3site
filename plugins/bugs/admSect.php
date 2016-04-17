<?php
if(iCMSa!=1) exit;

#Action: save
if($_POST)
{
	$all = array();
	$fix = array();
	$add = array();
	foreach($_POST['sect'] as $seq=>$title)
	{
		if(isset($_POST['id'][$seq]))
		{
			$fix[] = array($seq, clean($title), $_POST['id'][$seq]);
			$all[] = (int)$_POST['id'][$seq];
		}
		else
		{
			$add[] = array($seq, clean($title));
		}
	}
	#Delete old sections
	try
	{
		$db->beginTransaction();
		if($all)
		{
			$db->exec('DELETE FROM '.PRE.'bugsect WHERE ID NOT IN('.join(',', $all).')');
		}
		else
		{
			$db->exec('DELETE FROM '.PRE.'bugsect');
		}

		#Edit existing
		if($fix)
		{
			$q = $db->prepare('UPDATE '.PRE.'bugsect SET seq=?, title=? WHERE ID=?');
			foreach($fix as &$x) $q -> execute($x);
		}

		#New records
		if($add)
		{
			$q = $db->prepare('INSERT INTO '.PRE.'bugsect (seq,title) VALUES (?,?)');
			foreach($add as $x) $q -> execute($x);
		}
		$db->commit();
		header('Location: '.URL.url('bugs','','admin'));
		return 1;
	}
	catch(PDOException $e)
	{
		$view->info($e);
	}
}

#Get sections - FETCH_ASSOC
$all = $db->query('SELECT * FROM '.PRE.'bugsect ORDER BY seq') -> fetchAll(2);

#Template
$view->script('lib/forms.js');
$view->add('adminSect', array('section' => &$all));
