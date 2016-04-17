<?php /* Lista u¿ytkowników */
if(iCMS!=1) exit;
require(LANG_DIR.'profile.php');

#Tylko dla zalogowanych
if(isset($cfg['hideUser']) && !UID)
{
	$view->info($lang['mustLogin'], null, 'error');
	return 1;
}

#Tytu³ strony
$view->title = $lang['users'];

#Strona
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

#Szukanie
$url = $param = array();
if(isset($cfg['userFind']))
{
	if(!empty($_GET['sl']))
	{
		$sl = clean($_GET['sl'],20);
		$param[] = 'login LIKE "%'.$sl.'%"';  $url[] = 'sl='.$sl; //Login
	}
	if(!empty($_GET['pl']))
	{
		$pl = clean($_GET['pl'],30);
		$param[] = 'city LIKE "%'.$pl.'%"';  $url[] = 'pl='.$pl; //Miasto
	}
	if(!empty($_GET['www']))
	{
		$www = clean($_GET['www'],80);
		$param[] = 'www LIKE "%'.$www.'%"';  $url[] = 'www='.$www; //WWW
	}
	if(!empty($_GET['gg']))
	{
		$gg = (int)$_GET['gg'];
		$param[] = 'gg='.$gg;  $url[] = 'gg='.$gg; //GG
	}
}
#ID Grupy
if(ID)
{
	$param[] = 'ID IN (SELECT u FROM '.PRE.'groupuser WHERE g='.ID.')';
	$toURL = 'users/'.ID;
}
else
{
	$toURL = 'users';
}

#Licz
$total = dbCount('users'.($param ? ' WHERE '.join(' AND ',$param) : ''));

#Brak?
if($total < 1)
{
	$view->info($lang['nousers']);
	return 1;
}

#Sortowanie
if(isset($_GET['sort']) && ctype_alnum($_GET['sort']))
{
	$sortURL = 'sort='.$_GET['sort'];
	switch($_GET['sort'])
	{
		case '1': $sort = 'login'; break;
		case '3': $sort = 'lvis DESC'; break;
		case '4': $sort = 'lv DESC,login'; break;
		default: $sort = 'ID DESC';
	}
}
else
{
	$sortURL = '';
	$sort = 'ID DESC';
}

#Odczyt
$res = $db->query('SELECT ID,login,sex,lv,regt,city,photo FROM '.PRE.'users'.($param ? ' WHERE '.join(' AND ',$param) : '').' ORDER BY '.$sort.' LIMIT '.$st.',30');

$res->setFetchMode(3);
unset($param);

#Users
$users = array();

#Do tablicy!
foreach($res as $u)
{
	#Poziom
	switch($u[3])
	{
		case '2': $lv = $lang['editor']; break;
		case '3': $lv = $lang['admin']; break;
		case '4': $lv = $lang['owner']; break;
		default: $lv = $lang['user'];
	}

	#Plec
	switch($u[2])
	{
		case '1': $sex = 'male'; break;
		case '2': $sex = 'female'; break;
		default: $sex = 'group';
	}

	$users[] = array(
		'login' => $u[1],
		'city'  => $u[5],
		'date'  => formatDate($u[4]),
		'url'   => url('user/'.urlencode($u[1])),
		'photo' => $u[6] ? $u[6] : 'img/user/0.png',
		'level' => $lv,
		'sex'   => $sex,
		'num'   => ++$st
	);
}
$res=null;

#Z³±cz parametry
$url = $url ? '&'.join('&',$url) : '';

#Dane do szablonu
$view->add('users', array(
	'users' => &$users,
	'total' => $total,
	'id'    => ID,
	'find'  => isset($cfg['userFind']),
	'joined_url' => url($toURL, 'sort=2'.$url),
	'login_url'  => url($toURL, 'sort=1'.$url),
	'level_url'  => url($toURL, 'sort=4'.$url),
	'last_url'   => url($toURL, 'sort=3'.$url),
	'find_login' => !empty($sl) ? $sl : '',
	'find_www'   => !empty($www) ? $www : '',
	'find_place' => !empty($pl) ? $pl : '',
	'find_gg'    => !empty($gg) ? $gg : '',
	'pages'      => $total > 30 ? pages($page,$total,30,url($toURL,$sortURL.$url)) : false
));

#Usuñ zbêdne dane
unset($u,$url,$total,$www,$pl,$sl);
