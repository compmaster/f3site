<?php
if(iCMS!=1) return;
require LANG_DIR.'comm.php';

#No ID
if(!$id) return;

#Error list
$error = array();
$preview = null;

#Blacklist
if(isset($cfg['blacklist']))
{
	require_once './lib/spam.php';
	if(blacklist($_SERVER['REMOTE_ADDR']))
	{
		echo $view->info($lang['c11']);
		return 1;
	}
}

#Accept or delete
if(isset($_POST['act']) && $id)
{
	switch($_POST['act'])
	{
		case 'ok':
		if(admit('CM')) $db->exec('UPDATE '.PRE.'comms SET access=1 WHERE ID='.$id);
		break;
		case 'del':
		if($comm = $db->query('SELECT CID,TYPE FROM '.PRE.'comms WHERE ID='.$id)->fetch(3))
		{
			if(($comm[0] == UID && $comm[1] == '100') OR admit('CM'))
			{
				if($db->exec('DELETE FROM '.PRE.'comms WHERE ID='.$id) && $comm[1] == '5')
				{
					$db->exec('UPDATE '.PRE.'news SET comm=comm-1 WHERE ID='.$comm[0]);
				}
			}
		}
	}
	echo 'OK';
	exit;
}

#If type specified, add new comment
if(isset($URL[2]))
{
	#Guest cannot post if not allowed
	if(!UID && !isset($cfg['commGuest'])) $error[] = $lang['c11'];

	#TYPE MUST BE A NUMBER
	$type = (int)$URL[2];

	#Check if commented object is enabled
	if(!isset($_SESSION['CV'][$type][$id]))
	{
		switch($type)
		{
			case 100: $if = 'users WHERE lv>0 AND ID='.$id; break;
			case 101: $if = 'groups WHERE access!=1 AND ID='.$id; break;
			case 102: $if = 'pages WHERE access=1 AND ID='.$id; break;
			case 103: $if = 'polls WHERE access="'.LANG.'" AND ID='.$id; break;
			default: $data = parse_ini_file('./cfg/types.ini',1);
				include './cfg/content.php';
				$if = isset($data[$type]['comm']) && ($data[$type]['comm']==1 || isset($cfg[$data[$type]['comm']])) ? $data[$type]['table'].' i INNER JOIN '. PRE.'cats c ON i.cat=c.ID WHERE i.access=1 AND c.access!=3 AND c.opt&2 AND i.ID='.$id : '';
		}
		if(!$if OR !dbCount($if))
		{
			$error[] = $lang['c11'];
		}
	}
}
else
{
	if(!admit('CM'))
	{
		$error[] = $lang['c11']; #No right to edit comment
	}
	$type = null;
}

#Page title
$view->title = $type ? $lang['addComm'] : $lang['c1'];

#Init CAPTCHA system
if(UID || empty($cfg['captcha']) || isset($_SESSION['human']))
{
	$noSPAM = false;
}
else
{
	require_once './lib/spam.php';
	$noSPAM = CAPTCHA();
}

if($_POST)
{
	$c = array(
		'name' => empty($cfg['commTitle']) ? '' : clean($_POST['name'], 30, 1),
		'text' => clean($_POST['text'], 0, 1)
	);

	#Max text length
	$max = empty($cfg['maxlen']) ? 1999 : (int)$cfg['maxlen'];

	#Check length
	if(isset($c['text'][$max]))
	{
		$error[] = $lang['c5'];
	}
	if(empty($c['text']))
	{
		$error[] = $lang['c4'];
	}

	#Check author and links in content
	if($type)
	{
		if(UID)
		{
			$c['author'] = $user['login'];
		}
		else
		{
			$c['author'] = empty($_POST['author']) ? $lang['c9'] : clean($_POST['author'],30,1);
			if(!isset($cfg['URLs']))
			{
				if(strpos($c['author'],'://') OR strpos($c['text'],'://') OR strpos($c['name'],'://'))
				{
					$error[] = $lang['c12'];
				}
			}
			if($noSPAM)
			{
				if($noSPAM->verify())
				{
					$noSPAM = false;
				}
				else
				{
					$error[] = $lang[$noSPAM->errorId];
				}
			}
		}
	}

	#Preview
	if(isset($_POST['prev']) && !$error)
	{
		$preview = nl2br(Emots($c['text']));
		if(isset($cfg['bbcode']))
		{
			try
			{
				include './lib/bbcode.php';
				$preview = BBCode($preview, 1);
			}
			catch(Exception $e)
			{
				$error[] = $lang['unclosed'];
			}
		}
	} 

	#Save comment
	elseif(isset($_POST['save']))
	{
		if($type)
		{
			#Anty-flood
			if(isset($_SESSION['post']) && $_SESSION['post']>time()) $error[] = $lang['c3'];

			#Moderować? + IP
			$c['access'] = !isset($cfg['moderate']) || IS_EDITOR || admit('CM') ? 1 : 0;
			$c['IP'] = $_SERVER['REMOTE_ADDR'];
			$c['UA'] = clean($_SERVER['HTTP_USER_AGENT']);
			$c['date'] = $_SERVER['REQUEST_TIME'];
			$c['TYPE'] = $type;
			$c['CID'] = $id;
			$c['UID'] = UID;
		}

		#If no error, save comment
		if(!$error)
		{
			try
			{
				$db->beginTransaction();
				if($type)
				{
					$q = $db->prepare('INSERT INTO '.PRE.'comms (TYPE,CID,name,access,author,UID,IP,UA,date,text)
						VALUES (:TYPE,:CID,:name,:access,:author,:UID,:IP,:UA,:date,:text)');

					#In case of news
					if($type==5) $db->exec('UPDATE '.PRE.'news SET comm=comm+1 WHERE ID='.$id);
				}
				else
				{
					$q = $db->prepare('UPDATE '.PRE.'comms SET name=:name, text=:text WHERE ID='.$id);
				}
				$q->execute($c);
				$db->commit();

				#Set anti-flood
				$_SESSION['post'] = time() + $cfg['antyFlood'];

				#If AJAX, send all comments
				if(JS)
				{
					include './lib/comm.php';
					comments($id, $type);
					return 1;
				}
				else
				{
					$view->message(($type && $c['access']!=1) ? $lang['c6'] : $lang['c7']);
				}
			}
			catch(PDOException $e)
			{
				$view->info($lang['c10'].$e->getMessage());
			}
		}
	}
}
else
{
	if($type)
	{
		$c = array('name'=>'', 'author'=>'', 'text'=>'');
	}
	else
	{
		$c = $db->query('SELECT * FROM '.PRE.'comms WHERE ID='.$id)->fetch(2);
	}
}

#Show errors
if($error) $view->info('<ul><li>'.join('</li><li>',$error).'</li></ul>',null,'error');

#Template
$view->add('comment', array(
	'comment' => $c,
	'code'    => $noSPAM && $cfg['captcha']>1,
	'sblam'   => $noSPAM && $cfg['captcha']===1,
	'author'  => $type && !UID ? true : false,
	'preview' => $preview,
	'title'   => isset($cfg['commTitle']),
	'url'     => url('comment/'.$id.($type ? '/'.$type : ''))
));

#Include JS editor
if(isset($cfg['bbcode']))
{
	$view->script(LANG_DIR.'edit.js');
	$view->script('cache/emots.js');
	$view->script('lib/editor.js');
}
