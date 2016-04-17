<?php if(iCMS!=1) exit;

$res = $db->query('SELECT ID,name FROM '.PRE.'pages WHERE access=1'.(UID ? ' OR access=3' : '').' ORDER BY name');
$url = url('page/');
$res->setFetchMode(3);

echo '<ul style="white-space: no-wrap">';

foreach($res as $x)
{
	echo '<li><a href="'.$url.$x[0].'">'.$x[1].'</a></li>';
}

if(admit('P'))
{
	echo '<li><a href="'.url('editPage', '', 'admin').'">'.$lang['add'].'...</a></li>';
}

?></ul>