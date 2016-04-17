<?php /* Wy¶wietl profil u¿ytkownika */
if(iCMS!=1) exit;
require LANG_DIR.'profile.php';

#Tylko dla zalogowanych
if(isset($cfg['hideUser']) && !UID)
{
	$view->info($lang['mustLogin'], null, 'error');
	return 1;
}

#User ID or login
if(isset($URL[1]))
{
	$login = $URL[1];
}
elseif(UID)
{
	$login = $user['login'];
}
else return;

#Query
$q = $db->prepare('SELECT * FROM '.PRE.'users WHERE login=?');
$q->execute(array($login));

#If does not exist
if(!$u = $q->fetch(2)) return;

#N/A
define('NA',$lang['na']);

#O sobie
$u['about'] = nl2br(emots($u['about']));

#BBCode
if(isset($cfg['bbcode']) && $u['about'])
{
	include_once './lib/bbcode.php';
	$u['about'] = BBCode($u['about']);
}

#WWW
$u['www'] = ($u['www'] && $u['www']!='http://') ? $u['www'] : null;

#E-mail
if($u['opt'] & 1 && (UID || empty($cfg['hideMail'])))
{
	$u['mail'] = str_replace('@', '&#64;', $u['mail']);
	$u['mail'] = str_replace('.', '&#46;', $u['mail']);
}
else
{
	$u['mail'] = null;
}

#P³eæ
switch($u['sex'])
{
	case 1: $u['sex'] = $lang['male']; break;
	case 2: $u['sex'] = $lang['female']; break;
	default: $u['sex'] = false;
}

#URL linku EDYTUJ
if(UID)
{
	if($u['ID'] == UID)
	{
		$may = url('account');
	}
	elseif(IS_ADMIN && admit('U'))
	{
		$may = url('editUser/'.$u['ID'], '', 'admin');
	}
	else
	{
		$may = false;
	}
}
else
{
	$may = false;
}

#Grupy
$g = array();
$r = $db->query('SELECT g.ID,g.name,g.num FROM '.PRE.'groups g INNER JOIN '.
	PRE.'groupuser u ON g.ID=u.g WHERE u.u='.$u['ID'].' ORDER BY g.num DESC');

foreach($r as $x)
{
	$g[] = array(
		'title' => $x['name'],
		'num'   => $x['num'],
		'url'   => url('group/'.$x['ID'])
	);
}

#Szablon
$view->title = $u['login'];
$view->add('user', array(
	'u'  => &$u,
	'pm' => isset($cfg['pmOn']) && UID ? url('pms/edit', 'to='.$u['ID']) : false,
	'edit' => $may,
	'group' => $g,
	'users'  => url('users'),
	'join_date' => formatDate($u['regt'],1), //Data rejestracji
	'last_visit'=> $u['lvis'] ? formatDate($u['lvis'],1) : NA
));

if(isset($cfg['userComm']) && $u['opt'] & 2)
{
	include './lib/comm.php';
	comments($u['ID'], 100);
}
