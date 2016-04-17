<?php if(!JS) exit;
require './cfg/chat.php';

#Ostatni pobrany ID
if(isset($_SESSION['chatLast']))
{
	$id = $_SESSION['chatLast'];
}
elseif(isset($_GET['last']) && is_numeric($_GET['last']))
{
	$id = $_SESSION['chatLast'] = $_GET['last'];
}
else
{
	$id = time();
}

#Pobierz ostatnie rekordy
$num = -1;
$msg = array();
$res = $db->prepare('SELECT * FROM '.PRE.'chat WHERE ID>? ORDER BY ID DESC LIMIT 20');
$res -> bindValue(1, $id, 1);
$res -> execute();

foreach($res as $x)
{
	array_unshift($msg, array(
		'id'   => $x['ID'],
		'nick' => $x['nick'],
		'uid'  => $x['uid'],
		'msg'  => $x['msg'],
		'time' => $x['time']
	)); ++$num;
}

#Return messages as JSON
echo json_encode($msg);

#Dodaj wys³any rekord
if($_POST)
{
	if($_POST['msg'][0] == '/')
	{
		/*coming soon */
	}
	else
	{
		$msg = array(
			0 => $_SERVER['REQUEST_TIME'],
			1 => UID,
			2 => UID ? $user['login'] : clean('Guest', $cfg['chatNickLen']),
			3 => clean($_POST['msg'], $cfg['chatMsgLen'], 1)
		);

		#Czy mo¿e wstawiaæ linki
		if(!UID && !isset($cfg['URLs']))
		{
			if(strpos($msg[3],'://') OR strpos($msg[3],'www.')!==false)
			{
				return;
			}
		}

		try
		{
			$q = $db->prepare('INSERT INTO '.PRE.'chat (time,uid,nick,msg) VALUES (?,?,?,?)');
			$q -> execute($msg);
			$_SESSION['chatLast'] = $db->lastInsertId();
		}
		catch(PDOException $e) {echo $e;}
	}
}
elseif($msg)
{
	$_SESSION['chatLast'] = $msg[$num]['id'];
}