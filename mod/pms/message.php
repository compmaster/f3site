<?php /* PM - wyœwietl wiadomoœci */
if(iCMS!=1) exit;

#Odczyt
if(isset($URL[2]) && is_numeric($URL[2]))
{
	$q = $db->prepare('SELECT p.*,u.login,u.photo FROM '.PRE.'pms p LEFT JOIN '.PRE.
	'users u ON p.usr=u.ID WHERE (p.owner=? OR p.usr=?) AND (p.ID=? OR p.th=?) ORDER BY p.date');
	$q->execute(array(UID, UID, $URL[2], $URL[2]));
	$pm = array();
}
else
{
	$view->set404();
	return;
}

#BBCode
if(isset($cfg['bbcode']))
{
	include './lib/bbcode.php';
}

#Oznaczymy jako przeczytane
$read = array();
$th = 0;

#Przygotuj posty
foreach($q as $x)
{
	if($x['th']=='0')
	{
		$th = $x['ID'];
	}

	$pm[] = array(
		'topic' => $x['topic'],
		'date'  => formatDate($x['date'], true),
		'txt'   => nl2br(emots(isset($cfg['bbcode']) ? BBCode($x['txt']) : $x['txt'])),
		'fwd'   => url('pms/edit/'.$x['ID'], 'fwd'),
		'edit'  => $x['st'] == 3 ? url('pms/edit/'.$x['ID']) : false,
		'reply' => $x['st'] < 3 ? url('pms/edit/'.$x['ID'], 'th='.$th) : false,
		'read'  => $x['st'] == 2,
		'photo' => $x['photo'],
		'id'    => $x['ID'],
		'who'   => $x['login'],
		'url'   => $x['login'] ? url('user/'.urlencode($x['login'])) : ''
	);

	#Dodaj do oznaczenia jako przeczytane
	if($x['st'] == 1 && $x['owner'] == UID)
	{
		$read[] = $x['ID'];
	}

	#Tytu³ strony
	if($x['ID'] == $URL[2])
	{
		$view->title = $x['topic'];
	}
}

#Brak?
if(!$pm)
{
	$view->info($lang['noex']);
	return 1;
}

#Przeczytana?
if($read)
{
	$num = count($read);
	$db->beginTransaction();
	$db->exec('UPDATE '.PRE.'users SET pms=pms-'.$num.' WHERE ID='.UID);
	$db->exec('UPDATE '.PRE.'pms SET st=2 WHERE ID IN('.join(',', $read).')');
	$db->commit();
	$user['pms'] -= $num;
}

#Szablon i dane
$view->add('pms_view', array(
	'pm' => &$pm,
	'form' => $x['st'] < 3 ? url('pms/edit','th='.$th) : false,
	'color' => isset($cfg['colorCode']),
	'bbcode' => isset($cfg['bbcode'])
));
