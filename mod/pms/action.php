<?php /* Operacje na PW */
if(iCMS!=1) exit;

#Inicjacja zmiennej
$q = '';

#Usuñ 1
if(isset($_POST['del']) && !is_array($_POST['del']))
{
	$q = 'ID='.(int)$_POST['del'];
}

#Z listy
elseif(isset($_POST['x']) && count($_POST['x'])>0)
{
	$list = array();
	foreach($_POST['x'] as $key=>$val)
	{
		if(is_numeric($key)) $list[] = $key;
	}
	$q = 'ID IN ('.join(',', $list).')';
	unset($list,$key,$val);
}

else return;

#START
$db->beginTransaction();

#Usuñ
if($q)
{
	#Pobierz w³a¶cicieli
	$res = $db->query('SELECT owner FROM '.PRE.'pms WHERE st=1 AND (usr='.UID.' OR owner='.UID.') AND '.$q);
	$res->setFetchMode(7,0); //Column
	$users = array();

	foreach($res as $x)
	{
		if(isset($users[$x])) ++$users[$x]; else $users[$x] = 1;
  }
	$res = null;

	#Zmniejsz ilo¶æ PM
  foreach($users as $u=>$x)
  {
		$db->exec('UPDATE '.PRE.'users SET pms=pms-'.$x.' WHERE ID='.$u);
  }
  unset($u,$x,$users);
	$db->exec('UPDATE '.PRE.'pms SET del='.UID.' WHERE st<3 AND (owner='.UID.' OR usr='.UID.') AND '.$q);
	$db->exec('DELETE FROM '.PRE.'pms WHERE ((owner='.UID.' AND st=3) OR (del NOT IN(0,'.UID.') AND (owner='.UID.' OR usr='.UID.'))) AND '.$q);
}
$db->commit();

unset($q);
$URL[1] = $URL[2];
require './mod/pms/list.php';