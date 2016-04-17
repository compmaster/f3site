<?php if(iCMS!=1) exit;

$url = $db->query('SELECT l.adr FROM '.PRE.'links l LEFT JOIN '.PRE.'cats c ON l.cat=c.ID WHERE l.access=1 AND c.access!=3 AND l.ID='.$id) -> fetchColumn();

if($url)
{
	#Increase visits counter
	if(isset($cfg['lcnt'])) $db->exec('UPDATE '.PRE.'links SET count=count+1 WHERE ID='.$id);

	#Redirect to URL
	header('Location: '.str_replace('&amp;','&',$url));
	exit('<script>location="'.$url.'"</script>');
}