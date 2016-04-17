<?php
if(iCMSa!=1 || !admit('U')) exit;
require LANG_DIR.'profile.php';
require './cfg/account.php';

#Page title
$view->title = $lang['account'];

#No one can edit first user except himself
if($id==1 && UID!=1) return;

#Errors
$error = array();

#Edit user
if($_POST)
{
	$u = array(
		'login' => clean($_POST['login']),
		'about' => clean($_POST['about']),
		'skype' => clean($_POST['skype'],40),
		'jabber'=> clean($_POST['jabber'],60),
		'photo' => clean($_POST['photo']),
		'mail'  => clean($_POST['mail']),
		'city' => clean($_POST['city']),
		'tlen' => clean($_POST['tlen'],30),
		'www'  => clean($_POST['www']),
		'sex'  => (int)$_POST['sex'],
		'icq' => (is_numeric($_POST['icq'])) ? $_POST['icq'] : null,
		'gg'  => (is_numeric($_POST['gg'])) ? $_POST['gg'] : null);

	#Login
	if(isset($u['login'][21]) || !isset($u['login'][2]))
	{
		$error[] = $lang['badLogin'];
	}
	if(dbCount('users WHERE login="'.$u['login'].'" AND ID!='.$id)!==0)
	{
		$error[] = $lang['loginEx'];
	}
	switch($cfg['logins'])
	{
		case 1: $re = '/^[A-Za-z0-9 _-]*$/'; break;
		case 2: $re = '/^[0-9\pL _.-]*$/u'; break;
		default: $re = '/^[^&/?#=]$/'; break;
	}
	if(!preg_match($re, $u['login']))
	{
		$error[] = $lang['loginChar'];
	}

	#E-mail
	if(isset($u['mail'][0]) && !filter_var($u['mail'], FILTER_VALIDATE_EMAIL))
	{
		$error[] = $lang['badMail'];
	}

	#Password
	if(!$id && empty($_POST['pass']))
	{
		$error[] = $lang['badPass'];
	}

	#WWW
	$u['www'] = str_replace('javascript:', 'java_script', $u['www']);
	$u['www'] = str_replace('vbscript:', 'vb_script', $u['www']);

	#Errors
	if($error)
	{
		$view->info('<ul><li>'.join('</li><li>',$error).'</li></ul>');
	}
	else
	{
		try
		{
			if($id)
			{
				$q = $db->prepare('UPDATE '.PRE.'users SET login=:login, mail=:mail,
				sex=:sex, about=:about, www=:www, city=:city, icq=:icq, skype=:skype,
				tlen=:tlen, jabber=:jabber, gg=:gg, photo=:photo WHERE ID='.$id);
			}
			else
			{
				$u['pass'] = md5($_POST['pass']);
				$u['regt'] = $_SERVER['REQUEST_TIME'];
				$q = $db->prepare('INSERT INTO '.PRE.'users
				(login,pass,mail,sex,regt,about,www,city,icq,skype,tlen,jabber,gg,photo) VALUES
				(:login,:pass,:mail,:sex,:regt,:about,:www,:city,:icq,:skype,:tlen,:jabber,:gg,:photo)');
			}
			$q->execute($u);
			$view->info($lang['upd'], array(url('user/'.urlencode($u['login'])) => $u['login']));
			return 1;
		}
		catch(PDOException $e)
		{
			$view->info($lang['error'].$e);
		}
	}
}

#Get user
elseif($id)
{
	if(!$u = $db->query('SELECT * FROM '.PRE.'users WHERE ID='.$id)->fetch(2)) return;
}

#New user
else
{
	$u = array(
		'login' => '',
		'mail'  => '',
		'sex'   => 1,
		'about' => '',
		'www'   => 'http://',
		'city'  => '',
		'icq'   => '',
		'skype' => '',
		'tlen'  => '',
		'jabber'=> '',
		'gg'    => '',
		'photo' => ''
	);
}

#Prepare template
$view->add('editUser', array(
	'u' => &$u,
	'url' => url('editUser/'.$id, '', 'admin'),
	'pass' => !$id,
	'bbcode' => isset($cfg['bbcode']),
	'fileman'=> admit('FM')
));
