<?php
if(iCMSa!=1 || !admit('U')) exit;
require LANG_DIR.'admAll.php';
require LANG_DIR.'profile.php';

#Delete + 2 triggers
if(isset($_POST['del']) && !isset($_POST['x'][1]) && $x = GetID(true))
{
	$res = $db->query('SELECT ID FROM '.PRE.'users WHERE ID IN('.$x.')'.(UID!=1 ? ' AND lv<'.LEVEL : ''));
	if($all = join(',', $res->fetchAll(7))) //FETCH_COLUMN
	{
		$db->beginTransaction();
		$db->exec('DELETE FROM '.PRE.'users WHERE ID IN ('.$all.')');
		$db->exec('DELETE FROM '.PRE.'pollvotes WHERE user IN ('.$all.')');
		$db->exec('DELETE FROM '.PRE.'groupuser WHERE u IN ('.$all.')');
		$db->exec('UPDATE '.PRE.'groups SET num=(SELECT COUNT(*) FROM '.PRE.'groupuser WHERE g=ID)');
		$db->exec('DELETE FROM '.PRE.'comms WHERE (guest!=1 AND author IN('.$all.'))
		OR (type=100 AND CID IN('.$all.'))');
		$db->commit();
	}
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

#Search users
if(isset($_REQUEST['s']) && $_REQUEST['s'])
{
	$s = str_replace(array('"','\'','%'), '', clean($_REQUEST['s'],30));
	$w = ' WHERE login LIKE "'.str_replace('*', '%', $s).'%"';
}
else
{
	$s = $w = '';
}

#User number
$total = dbCount('users'.$w);
$users = array();

#Get users
$res = $db->query('SELECT ID,login,lv,adm FROM '.PRE.'users'.$w.' ORDER BY lv DESC, adm DESC, ID DESC LIMIT '.$st.',30');
$res ->setFetchMode(3); //NUM

foreach($res as $u)
{
	#User level
	switch($u[2])
	{
		case '0': $lv = $lang['locked']; break;
		case '1': $lv = $lang['user']; break;
		case '2': $lv = $lang['editor']; break;
		case '3': $lv = $lang['admin']; break;
		case '4': $lv = $lang['owner']; break;
		default: $lv = '!?';
	}

	$users[] = array(
		'id'    => $u[0],
		'login' => $u[1],
		'num'   => ++$st,
		'privs' => str_replace('|',' ',$u[3]),
		'url'   => url('user/'.urlencode($u[1])),
		'level' => $lv,
		'priv'  => $u[2]<LEVEL || LEVEL==4 ? url('editAdmin/'.$u[0], '', 'admin') : false,
		'edit'  => $u[2]<LEVEL || LEVEL==4 ? url('editUser/'.$u[0], '', 'admin') : false
	);
}

#Template
$view->add('users', array(
	'users' => &$users,
	'search'=> $s,
	'pages' => pages($page,$total,30,url('users','s='.$s,'admin'),1)
));
