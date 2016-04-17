<?php
if(iCMS!=1) exit;

#Skin
$view->title = 'Chat';
$view->dir = './plugins/chat/';
$view->cache = './cache/chat/';
$view->css('plugins/chat/chat.css');
$view->script('plugins/chat/chat.js');

#Config
if(file_exists('./cfg/chat.php'))
{
	require './cfg/chat.php';
}
else
{
	$view->message('Chat is NOT installed!');
}

#Messages
$num = -1;
$msg = array();
$res = $db->prepare('SELECT * FROM '.PRE.'chat ORDER BY ID DESC LIMIT ?');
$res -> bindValue(1, $cfg['chatLast'], 1);
$res -> execute();

foreach($res as $x)
{
	array_unshift($msg, array(
		'id'   => $x['ID'],
		'uid'  => $x['uid'],
		'msg'  => $x['msg'],
		'nick' => $x['nick'],
		'time' => date('H:i', $x['time']),
		'user_url' => url('user/'.urlencode($x['nick'])),
	));
	++$num;
}

#Template
$view->add('chat', array(
	'message' => &$msg,
	'lastID'  => $msg ? ($_SESSION['chatLast'] = $msg[$num]['id']) : 0,
	'nick'    => UID ? $user['login'] : 'Guest'
));
