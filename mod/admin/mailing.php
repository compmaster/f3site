<?php
if(iCMSa!=1 || !admit('M')) exit;
require './cfg/mail.php';
require LANG_DIR.'admMail.php';

#Groups and level
function Prepare($x)
{
	if(empty($_POST['lv']))
	{
		return '';
	}
	else
	{
		return join(',',array_map('intval',$x));
	}
}

#Remote emoticons
function RemoteEmots($x)
{
	include './cfg/emots.php';
	foreach($emodata as $e)
	{
		$x = str_replace($e[2], '<img src="'.URL.'img/emo/'.$e[1].'" title="'.$e[0].'" alt="'.$e[2].'" style="border: 0; vertical-align: middle" />', $x);
	}
	return $x;
}

#If E-mail disabled
if(!isset($cfg['mailon']))
{
	$view->info($lang['mailsd']); return 1;
}

#Send E-mail
elseif(isset($_POST['txt']))
{
	#Initialize e-mail library
	require './lib/mail.php';
	$mail = new Mailer();
	$mail->setSender($_POST['from'],$cfg['mail']);
	$mail->topic = clean($_POST['topic']);
	$mail->text  = $_POST['txt']."\r\n\r\n-----\r\n".$lang['candis'];

	#Emoticons
	if(isset($_POST['emot'])) $mail->text = RemoteEmots($mail->text);

	#HTML
	if(!isset($_POST['html'])) $mail->html = 0;

	#User list
	$lv = Prepare(explode(',', $_POST['lv']));
	$gr = Prepare(explode(',', $_POST['gr']));

	#Get recipients
	$res = $db->query('SELECT login,mail FROM '.PRE.'users WHERE mails=1');
	$res ->setFetchMode(3); //NUM
	$log = array();

	#Separate e-mails
	if(isset($_POST['hard']))
	{
		foreach($res as $u)
		{
			if($mail->sendTo($_POST['rcpt'],$u[1])) $log[] = $lang['msent'].$u[0];
			else $log[] = $lang['msent'];
		}
	}
	#BCC
	else
	{
		foreach($res as $u)
		{
			$mail->addBlindCopy($u[0],$u[1]);
		}
		if($mail->sendTo($_POST['rcpt'],$cfg['mail'])) $log[] = $lang['msent'];
		else $log[] = $lang['mnsent'];
	}
	$view->info('<ul><li>'.join('</li><li>',$log).'</li></ul>');
}

#Count number of recipients
elseif(isset($_POST['next']))
{
	$ile = 0;
	$lv = Prepare($_POST['lv']);
	$gr = Prepare($_POST['gr']);
	if($lv && $gr)
	{
		$ile = dbCount('users WHERE mails=1 AND lv IN('.$lv.') AND ID IN (SELECT u FROM '.PRE.'groupuser WHERE g IN('.$gr.'))');
	}
	if($ile==0) $view->info($lang['nousnd']);
}

#Show form
if(isset($_POST['next']) && $ile>0)
{
	$view->script('./lib/editor.js'); //Edytor
	$view->script('./cache/emots.js'); //Emotki
	$view->script(LANG_DIR.'edit.js'); //Jêzyk
	$view->add('mailing', array(
		'start' => false,
		'cfg'   => &$cfg,
		'level' => $lv,
		'group' => $gr,
		'title' => $lang['massl'].$ile
	));
}

#START
if(!$_POST)
{
	include './lib/user.php';
	$view->info($lang['apmm1']);
	$view->add('mailing', array(
		'levels' => LevelList('all',1),
		'groups' => GroupList('all'),
		'start'  => true,
	));
}
