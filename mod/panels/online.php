<div id="online"><?php
if(iCMS!=1) exit;
$online = isset($_SESSION['online']) ? $_SESSION['online'] : 0;

#Licznik
if(file_exists('./cfg/visits.txt'))
{
	$licznik = file_get_contents('./cfg/visits.txt');
}
else
{
	$licznik = 0;
}
if(!$online)
{
	file_put_contents('./cfg/visits.txt', ++$licznik, 2); //LOCK_EX
}

#IP
$ip = $_SERVER['REMOTE_ADDR'];

#Google, Bing, user
if(UID)
{
	$name = $user['login'];
}
elseif(strpos($_SERVER['HTTP_USER_AGENT'],'Googlebot')!==false)
{
	$name = 'Google';
}
elseif(strpos($_SERVER['HTTP_USER_AGENT'],'msnbot')!==false)
{
	$name = 'Bing';
}
else
{
	$name = '';
}

#Online (10 minut)
if($online < ($_SERVER['REQUEST_TIME']-600))
{
	$db->exec('DELETE FROM '.PRE.'online WHERE time < CURRENT_TIMESTAMP-600 OR IP="'.$ip.'"');
	$db->prepare('INSERT INTO '.PRE.'online (IP,user,name) VALUES (?,?,?)')->execute(array($ip,UID,$name));
	$_SESSION['online'] = $_SERVER['REQUEST_TIME'];
}

#Lista osób online
$res = $db->query('SELECT user,name FROM '.PRE.'online');
$res->setFetchMode(3);
$list = array();
$num = 0;

foreach($res as $x)
{
	if($x[0])
	{
		$list[] = '<a href="'.url('user/'.urlencode($x[1])).'">'.$x[1].'</a>';
	}
	elseif($x[1])
	{
		$list[] = $x[1];
	}
	++$num;
}
echo
	$lang['visits'].': <b>'.$licznik.'</b><br />'.
	$lang['online'].': <b>'.$num.'</b><br />'.join(', ', $list);
unset($online,$list);
?></div>