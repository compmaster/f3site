<?php
if(iCMS!=1) exit;

#Sortowanie
switch($cat['sort'])
{
	case '1': $sort = 'ID'; break;
	case '3': $sort = 'name'; break;
	case '4': $sort = 'ent DESC, ID DESC'; break;
	case '5': $sort = 'rate DESC, ID DESC'; break;
	default: $sort = 'ID DESC';
}

#Odczyt
$res = $db->query('SELECT ID,name,dsc,date FROM '.PRE.'arts WHERE '.$cats.
	' AND access=1 ORDER BY priority,'.$sort.' LIMIT '.$st.','.$cfg['np']);

$res->setFetchMode(3);
$arts = array();
$url = url('art/');
$total = 0;

#Lista
foreach($res as $art)
{
	$arts[] = array(
		'title' => $art[1],
		'desc'  => $art[2],
		'num'   => ++$st,
		'url'   => $url.$art[0],
		'date'  => $art[3]
	);
	++$total;
}

#Strony
if($cat['num'] > $total)
{
	$pages = pages($page, $cat['num'], $cfg['np'], url($d), 0, '/');
}
else
{
	$pages = null;
}

#Do szablonu
$view->add('cat_arts', array(
	'pages' => &$pages,
	'arts'  => &$arts,
	'add'   => admit($d,'CAT') ? url('edit/1') : null,
	'cats'  => url(isset($cfg['allCat']) ? 'cats' : 'cats/articles'),
	'type'  => isset($cfg['allCat']) ? $lang['cats'] : $lang['arts']
));
unset($res,$total,$art);
