<?php
function Install()
{
	global $db;

	if(!file_exists('./cfg/chat.php'))
	{
		if(!copy('./plugins/chat/cfg.php', './cfg/chat.php'))
		{
			throw new Exception('Cannot create configuration file! CHMOD <b>cfg</b> catalog to 777!');
		}
		if(!is_dir('./cache/chat') && !mkdir('./cache/chat'))
		{
			throw new Exception('ERROR: You must CHMOD <b>cache</b> catalog to 777!');
		}
	}

	$db->exec('CREATE TABLE IF NOT EXISTS '.PRE.'chat (
		ID '.AUTONUM.',
		time int,
		uid  int,
		nick varchar(30),
		msg  text)');
}
function Uninstall()
{
	global $db;
	$db->exec('DROP TABLE IF EXISTS '.PRE.'chat');
}