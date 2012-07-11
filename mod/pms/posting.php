<?php /* Wysy³anie PW */
if(iCMS!=1) exit;

#Tytu³ strony
$view->title = $lang['write'];
$preview = null;

#ID i w±tek
$id = isset($URL[2]) && is_numeric($URL[2]) ? $URL[2] : 0;
$th = isset($_GET['th']) && is_numeric($_GET['th']) ? $_GET['th'] : 0;

#Wys³ane dane
if($_POST)
{
	#Dane
	$pm = array(
		'to'  => clean($_POST['to']),
		'txt' => clean($_POST['txt'],0,1),
		'topic' => empty($_POST['topic']) ? $lang['notopic'] : clean($_POST['topic'],50,1)
	);

	#Wy¶lij lub edytuj
	if(isset($_POST['send']) OR isset($_POST['save']))
	{
		try
		{
			$db->beginTransaction();
			include './mod/pms/api.php';

			#Obiekt API
			$o = new PM;
			$o -> exceptions = true;
			$o -> topic = $pm['topic'];
			$o -> text = $pm['txt'];
			$o -> to = $pm['to'];
			$o -> thread = $th;

			#Wy¶lij - je¶li do siebie, zapisz jako kopiê robocz±
			if(isset($_POST['send']) && $o->to != $user['login'])
			{
				if($id && !isset($_POST['keep']))
				{
					$o -> status = 1;
					$o -> update($id); //Wy¶lij kopiê - zmiana w³a¶ciciela
				}
				else
				{
					$o -> send(); //Wy¶lij now± wiadomo¶æ
				}
			}
			else
			{
				if($id)
				{
					$o -> status = 3;
					$o -> update($id); //Aktualizuj kopiê robocz±
				}
				else
				{
					$o -> status = 3;
					$o -> send(); //Nowa kopia robocza
				}
			}
			$db -> commit();

			#Przekieruj do w±tku
			header('Location: '.URL.url('pms/view/'.($th?$th:$db->lastInsertId())));
			return 1;
		}
		catch(Exception $e)
		{
			$view->info($e->getMessage());
		}
	}
	#Podgl±d
	else
	{
		#BBCode
		if(isset($cfg['bbcode']))
		{
			require './lib/bbcode.php';
			$preview = emots(BBCode($pm['txt']));
		}
		else
		{
			$preview = emots($pm['txt']);
		}
	}
	$url = url('pms/edit/'.$id, 'th='.$th);
}

#Pobierz wiadomo¶æ
elseif($id)
{
	$pm = $db -> query('SELECT p.*,u.login as `to` FROM '.PRE.'pms p LEFT JOIN '
		.PRE.'users u ON p.usr=u.ID WHERE p.ID='.$id.' AND p.owner='.UID) -> fetch(2);

	#Nie istnieje?
	if(!$pm OR !is_numeric($pm['usr'])) return;

	#Dodaj Re: lub Fwd: do tytu³u
	if(isset($_GET['fwd']))
	{
		if(strpos($pm['topic'], 'Fwd:') === false)
		{
			$pm['topic'] = 'Fwd: '.$pm['topic'];
		}
		$url = url('pms/edit');
	}
	elseif($pm['st'] == 2)
	{
		if(strpos($pm['topic'], $lang['re']) === false)
		{
			$pm['topic'] = $lang['re'].$pm['topic'];
			$pm['txt'] = isset($cfg['bbcode']) ? '[quote]'.$pm['txt']."[/quote]\n" : '"'.$pm['txt']."\"\n";
		}
		$url = url('pms/edit', 'th='.$th);
	}
	else
	{
		$url = url('pms/edit/'.$id);
	}
}
else
{
	if(isset($_GET['to']) && is_numeric($_GET['to']))
	{
		$to = $db->query('SELECT login FROM '.PRE.'users WHERE ID='.$_GET['to']) -> fetchColumn();
	}
	else
	{
		$to = '';
	}
	$pm = array(
		'to'  => $to,
		'txt' => '',
		'topic' => '',
	);
	$url = url('pms/edit');
}

#Edytor JS
if(isset($cfg['bbcode']))
{
	$view->script(LANG_DIR.'edit.js');
	$view->script('lib/editor.js');
	$view->script('cache/emots.js');
}

#Szablon formularza
$view->add('pms_posting', array(
	'pm' => &$pm,
	'url' => $url,
	'color' => $preview && isset($cfg['colorCode']),
	'bbcode' => isset($cfg['bbcode']),
	'preview' => isset($preview) ? $preview : null
));
