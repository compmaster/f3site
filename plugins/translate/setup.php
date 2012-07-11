<?php
function Install()
{
	global $db,$lang;
	$q = $db->prepare('INSERT INTO '.PRE.'admmenu (ID,text,file,menu,rights) VALUES (?,?,?,?,?)');
	$q -> execute(array('LNGTOOL', 'Language tool', 'translate', 1, 1));
}

function Uninstall()
{
	global $db;
	$db->exec('DELETE FROM '.PRE.'admmenu WHERE ID="LNGTOOL"');
}