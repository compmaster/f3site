<?php
if(!$_POST) exit;

#J±dro
define('iCMS',1);
require './kernel.php';

#Adres IP
$ip = $db->quote($_SERVER['REMOTE_ADDR'].
	(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? ' '.$_SERVER['HTTP_X_FORWARDED_FOR'] : ''));
	
#Oceny
if(isset($_POST['v']) && isset($_GET['type']) && $id && $_POST['v'] > 0 && $_POST['v'] < 6)
{
	#Typy kategorii i ocena
	$data = parse_ini_file('cfg/types.ini', 1);
	$v = (int)$_POST['v'];
	$t = (int)$_GET['type'];

	#Referer
	$ref = isset($_SERVER['HTTP_REFERER']) ? clean($_SERVER['HTTP_REFERER']) : '';

	#AJAX
	if(JS) require LANG_DIR.'special.php';

	#Zalogowany?
	if(!UID && !isset($cfg['grate']))
	{
		if(JS) exit($lang[9]); else $view->message(9, $ref);
	}

	#Czy oceniany ID istnieje i jest w³±czony
	if(!isset($data[$t]['rate']) OR !dbCount($data[$t]['table'].' i INNER JOIN '.PRE.'cats c ON i.cat=c.ID WHERE i.access=1 AND c.access!="3" AND c.opt&4 AND i.ID='.$id))
	{
		if(JS) exit($lang[7]); else $view->message(7, $ref);
	}

	#Co ocenia³?
	$rated = isset($_COOKIE['rated']) ? explode('o',$_COOKIE['rated']) : array();

	#Gdy ocenia³ - zakoñcz
	if(in_array($t.'.'.$id, $rated))
	{
		if(JS) exit($lang[6]); else $view->message(6, $ref);
	}

	#Je¿eli brak wpisu w bazie, ¿e ocenia³...
	if(dbCount('rates WHERE type='.$t.' AND ID='.$id.' AND IP='.$ip) === 0)
	{
		$db->beginTransaction();
		$db->exec('INSERT INTO '.PRE.'rates (type,ID,mark,IP) VALUES ('.$t.','.$id.','.$v.','.$ip.')');

		#Aktualizuj ocenê
		$num = $db->query('SELECT count(*),SUM(mark) FROM '.PRE.'rates WHERE type='.$t.' AND ID='.$id)->fetch(3);
		$avg = $num[0] > 0 ? round($num[1] / $num[0]) : 0;

		$db->exec('UPDATE '.PRE.$data[$t]['table'].' SET rate='.$avg.' WHERE ID='.$id);
		$db->commit();
	}
	else
	{
		$avg = $v;
	}

	#Zapisz cookie
	$rated[] = $t.'.'.$id;
	setcookie('rated', join('o',$rated), time()+7776000, $_SERVER['PHP_SELF']);

	#OK
	if(JS) echo $lang[5]; else $view->message(5, $ref);
}

#Ankieta
if(isset($_POST['poll']))
{
	#Dane
	$poll = $db->query('SELECT * FROM '.PRE.'polls WHERE access="'
		.LANG.'" ORDER BY ID DESC LIMIT 1') -> fetch(2);

	#Istnieje? + ID
	if($poll) { $id = $poll['ID']; } else { $view->message(22); exit; }

	#G³osowa³ na...
	$voted = isset($_COOKIE['voted']) ? explode('o', $_COOKIE['voted']) : array();

	#ID u¿ytkownika lub adres IP
	$u = ($poll['ison']==3 && UID) ? UID : $ip;

	#Mo¿e g³osowaæ?
	if(!in_array($poll['ID'],$voted) && $poll['ison']!=2 && (UID || $poll['ison']==1))
	{
		#Je¿eli brak wpisu w bazie, ¿e g³osowa³...
		if(dbCount('pollvotes WHERE ID='.$id.' AND user='.$u)==0)
		{
			if($poll['type']==1)
			{
				$q = (int)$_POST['vote']; //1 odp.
			}
			else
			{
				$correct = array();
				foreach(array_keys($_POST['vote']) as $key)
				{
					if(is_numeric($key)) $correct[] = $key; //Wiele odp.
				}
				$q = $correct ? join(',',$correct) : 0;
			}
			#Aktualizuj
			try
			{
				$db->beginTransaction();
				$db->exec('UPDATE '.PRE.'polls SET num=num+1 WHERE ID='.$id);
				$db->exec('UPDATE '.PRE.'answers SET num=num+1 WHERE IDP='.$id.' AND ID IN ('.$q.')');
				$db->exec('INSERT INTO '.PRE.'pollvotes (user,ID) VALUES ('.$u.','.$id.')');
				$db->commit();

				#Pobierz odpowiedzi
				$o = $db->query('SELECT ID,a,num,color FROM '.PRE.'answers WHERE IDP='.$id.' ORDER BY seq')->fetchAll(3);
				++$poll['num'];

				#Zapisz nowe dane do pliku
				require './lib/config.php';
				$file = new Config('./cache/poll_'.LANG.'.php');
				$file->add('poll',$poll);
				$file->add('option',$o);
				$file->save();
			}
			catch(Exception $e)
			{
				$view->message(22); exit;
			}
		}
	}
	#Cookie
	$voted[] = $id;
	setcookie('voted', join('o',$voted), time()+7776000);
	
	#JS?
	if(JS)
	{
		$_GET['id'] = $id; include 'mod/panels/poll.php'; //Wy¶wietl ma³e wyniki
	}
	else
	{
		$view->message(5, url('poll/'.$id));
	}
}
