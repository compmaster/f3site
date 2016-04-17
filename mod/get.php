<?php if(iCMS!=1) exit;

$file = $db->query('SELECT f.file FROM '.PRE.'files f LEFT JOIN '.PRE.'cats c ON f.cat=c.ID WHERE f.access=1 AND c.access!=3 AND f.ID='.$id) -> fetchColumn();

if($file)
{
	#Add up downloads counter
	if(isset($cfg['fgets'])) $db->exec('UPDATE '.PRE.'files SET dls=dls+1 WHERE ID='.$id);

	#Download file
	$file = str_replace('&amp;', '&', $file);
	header('Location: '.((strpos($file, ':')) ? $file : URL.$file));
	exit;
}