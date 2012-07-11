<?php
function Install()
{
	global $db,$lang;

	if(!file_exists('cfg/cards.php'))
	{
		if(!copy('plugins/bugs/cards.php', 'cfg/cards.php'))
		{
			throw new Exception('Cannot create configuration file! CHMOD <b>cfg</b> directory to 777!');
		}
	}

	$db->exec('CREATE TABLE IF NOT EXISTS '.PRE.'cards (
		ID '.AUTONUM.',
		cat int NOT NULL DEFAULT 0,
		access tinyint NOT NULL DEFAULT 1,
		name varchar(50) NOT NULL DEFAULT "",
		sent int NOT NULL DEFAULT 0,
		date datetime,
		th varchar(200)
		img varchar(200))');

	$db->exec('CREATE TABLE IF NOT EXISTS '.PRE.'cardsent (
		KEY '.AUTONUM.',
		ID KEY int NOT NULL DEFAULT 0,
		sender
		senderMail
		senderID
		to
		toMail
		toID
		)');

	$db->exec('CREATE TABLE IF NOT EXISTS '.PRE.'bugs (
		ID ' . AUTONUM.',
		cat    int NOT NULL DEFAULT 0,
		name   varchar(70) NOT NULL DEFAULT "",
		num    int unsigned NOT NULL DEFAULT 0,
		date   int unsigned,
		status tinyint(1) NOT NULL DEFAULT 4,
		level  tinyint(1) NOT NULL DEFAULT 2,
		env    varchar(99) NOT NULL DEFAULT "",
		pos    int unsigned NOT NULL DEFAULT 0,
		neg    int unsigned NOT NULL DEFAULT 0,
		UID    int unsigned NOT NULL DEFAULT 0,
		who    varchar(50) NOT NULL DEFAULT "",
		ip     varchar(40) NOT NULL DEFAULT "",
		text   text)');

	$q = $db->prepare('INSERT INTO '.PRE.'admmenu (ID,text,file,menu,rights) VALUES (?,?,?,?,?)');
	$q -> execute(array('BUGS',   'Issue system - moderator', 'bugs', 0, 1));
	$q -> execute(array('BUGADM', 'Issue system', 'bugs', 1, 1));
}

function Uninstall()
{
	global $db;
	$db->exec('DROP TABLE IF EXISTS '.PRE.'bugs');
	$db->exec('DROP TABLE IF EXISTS '.PRE.'bugsect');
	$db->exec('DROP TABLE IF EXISTS '.PRE.'bugcats');
	$db->exec('DELETE FROM '.PRE.'admmenu WHERE ID="BUGS" OR ID="BUGADM"');
}