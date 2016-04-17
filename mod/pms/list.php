<?php /* PM - lista wiadomoœci */
if(iCMS!=1) exit;

#Strona
if(isset($URL[2]) && $URL[2] > 1 && is_numeric($URL[2]))
{
	$page = $URL[2];
	$st = ($page-1)*30;
}
else
{
	$page = 1;
	$st = 0;
}

#Nazwa folderu
$id = isset($URL[1]) && ctype_alnum($URL[1]) ? $URL[1] : 'inbox';

#Tytu³ strony + warunek do zapytania SQL
switch($id)
{
	case 'sent':
		$q = 'p.st<3 AND p.usr='.UID; #Wys³ane
		$view->title = $lang['sent'];
		break;
	case 'topics':
		$q = 'p.th=0 AND st<3 AND (p.owner='.UID.' OR p.usr='.UID.')'; #W¹tki
		$view->title = $lang['topics'];
		break;
	case 'drafts':
		$q = 'p.st=3 AND p.owner='.UID; #Kopie robocze
		$view->title = $lang['drafts'];
		break;
	default:
		$q = 'p.st<3 AND p.owner='.UID; #Odebrane
		$view->title = $user['pms'] ? sprintf('%s (%d)',$lang['inbox'],$user['pms']) : $lang['inbox'];
		$id = 'inbox';
}

#Licz
$total = dbCount('pms p WHERE p.del!='.UID.' AND '.$q);

#Brak?
if($total < 1)
{
	$view->info($view->title.'<br /><br />'.$lang['pm11']);
	return 1;
}

#Pobierz
$res = $db->query('SELECT p.ID, p.th, p.topic, p.st, u.ID as uid, u.login FROM '.
	PRE.'pms p LEFT JOIN '.PRE.'users u ON p.usr=u.ID WHERE p.del!='.
	UID.' AND '.$q.' ORDER BY p.st,p.ID DESC LIMIT '.$st.',20');

#Adresy
$userURL = url('user/');
$yourURL = $userURL . urlencode($user['login']);
$pms = array();
$url = url('pms/view/');

#Lista
foreach($res as $x)
{
	$pms[] = array(
		'id'    => $x['ID'],
		'topic' => $x['topic'],
		'new'   => $x['st']=='1',
		'url'   => $url.($x['th'] ? $x['th'] : $x['ID']),
		'login' => $x['login'],
		'userURL' => $userURL.urlencode($x['login']),
	);
}
$res=null;

#Szablon
$view->add('pms_list', array(
	'pm'  => $pms,
	'who' => $id=='drafts' ? $lang['pm13'] : $lang['pm12'],
	'url' => url('pms/del/'.$id),
	'total' => $total,
	'pages' => pages($page, $total, 30, url('pms/'.$id), 1, '/')
));
