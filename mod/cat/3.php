<?php
if(iCMS!=1) exit;

#Sortowanie
switch($cat['sort'])
{
	case '1': $sort = 'ID'; break;
	case '3': $sort = 'name'; break;
	case '5': $sort = 'rate DESC, ID DESC'; break;
	default: $sort = 'ID DESC';
}

#Zacznij od...
if($st != 0) $st = ($page-1) * $cfg['inp'];

#Odczyt
$res = $db->query('SELECT ID,name,date,th FROM '.PRE.'imgs WHERE '.$cats.
	' AND access=1 ORDER BY priority,'.$sort.' LIMIT '.$st.','.$cfg['inp']);

$res->setFetchMode(3);
$total = 0;
$url = url('img/');
$img = array();

#Lista
foreach($res as $x)
{
	$img[] = array(
		'num'   => ++$total,
		'title' => $x[1],
		'src'   => $x[3],
		'url'   => $url.$x[0],
		'date'  => formatDate($x[2])
	);
}

#Strony
if($cat['num'] > $total)
{
	$pages = pages($page, $cat['num'], $cfg['inp'], url($d), 0, '/');
}
else
{
	$pages = null;
}

#Do szablonu
$view->add('cat_images', array(
	'pages' => &$pages,
	'image' => &$img,
	'add'   => admit($d,'CAT') ? url('edit/3') : null,
	'cats'  => url(isset($cfg['allCat']) ? 'cats' : 'cats/images'),
	'type'  => isset($cfg['allCat']) ? $lang['cats'] : $lang['imgs']
));
unset($res,$total,$x);
