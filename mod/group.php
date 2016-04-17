<?php
if(iCMS!=1) exit;
require './cfg/account.php';
require LANG_DIR.'profile.php';

#Get group
if(!$group = $db->query('SELECT * FROM '.PRE.'groups WHERE access!="0" AND ID='.$id)->fetch(2)) return;

#Page title
$view->title = $group['name'];

#Are you a member
$member = UID ? dbCount('groupuser WHERE g='.$id.' AND u='.UID) : 0;

#Group URL
$url = url('group/'.$id);

#May join
$mayJoin = UID && !$member && $group['opened']==1;
$mayLeave = UID && $member;
$askJoin = sprintf($lang['wantJoin'], $group['name']);
$askLeave = sprintf($lang['wantLeave'], $group['name']);

#Special missions
if(isset($URL[2]))
{
	#Join group
	if($mayJoin && $URL[2] == 'join')
	{
		if(isset($_POST['yes']))
		{
			try
			{
				$db->beginTransaction();
				$q = $db->prepare('INSERT INTO '.PRE.'groupuser (u,g,date) VALUES (?,?,?)');
				$q->execute(array(UID, $id, $_SERVER['REQUEST_TIME']));
				$db->prepare('UPDATE '.PRE.'groups SET num=num+1 WHERE ID=?')->execute(array($id));
				$db->commit();
				$mayJoin = 0;
				$member = $mayLeave = 1;
				$group['num']++;
			}
			catch(Exception $e)
			{
				$view->info($e);
			}
		}
		elseif(!$_POST)
		{
			$view->add('ask', array('url'=>'', 'query'=>$askJoin));
			return 1;
		}
	}

	#Exit the ranks
	if($mayLeave && $URL[2] == 'leave')
	{
		if(isset($_POST['yes']))
		{
			try
			{
				$db->beginTransaction();
				$q = $db->prepare('DELETE FROM '.PRE.'groupuser WHERE u=? AND g=?');
				$q->execute(array(UID, $id));
				$db->prepare('UPDATE '.PRE.'groups SET num=num-1 WHERE ID=?')->execute(array($id));
				$db->commit();
				$group['num']--;
				$mayLeave = $member = 0;
				$mayJoin = 1;
			}
			catch(Exception $e)
			{
				$view->info($e);
			}
		}
		elseif(!$_POST)
		{
			$view->add('ask', array('url'=>'', 'query'=>$askLeave));
			return 1;
		}
	}
}

#Owner, date
$group['who'] = $group['who']>0 ? autor($group['who']) : false;
$group['date'] = formatDate($group['date']);

#Who joined
$new = array();

if(UID || empty($cfg['hideUser'])):
$res = $db->query('SELECT * FROM '.PRE.'users u INNER JOIN '.PRE.'groupuser g ON u.ID=g.u WHERE g.g='.$id);
foreach($res as $x)
{
	$new[] = array(
		'login' => $x['login'],
		'date'  => formatDate($x['date']),
		'url'   => url('user/'.urlencode($x['login']))
	);
}
endif;

#Template
$view->add('group', array(
	'group'  => &$group,
	'user'   => &$new,
	'edit'   => admit('G') ? url('editGroup/'.$id, 'ref', 'admin') : false,
	'groups' => url('groups'),
	'status' => $group['opened'] ? $lang['open'] : $lang['shut'],
	'join'   => $mayJoin ? $url.'/join' : false,
	'leave'  => $mayLeave ? $url.'/leave' : false,
	'query'  => $mayJoin ? $askJoin : ($mayLeave ? $askLeave : false),
	'all'    => $new ? url('users/'.$id) : false
));

#Comments
if(true)
{
	require './lib/comm.php';
	comments($id, 101);
}
