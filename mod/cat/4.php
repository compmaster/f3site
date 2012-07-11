<?php
if(iCMS!=1) exit;

#Sortowanie
switch($cat['sort'])
{
	case '1': $sort = 'ID'; break;
	case '3': $sort = 'name'; break;
	case '4': $sort = 'count DESC, ID DESC'; break;
	case '5': $sort = 'rate DESC, ID DESC'; break;
	default: $sort = 'ID DESC';
}

#Odczyt
$res = $db->query('SELECT ID,name,dsc,adr,count,nw FROM '.PRE.'links WHERE '.$cats.
	' AND access=1 ORDER BY priority,'.$sort.' LIMIT '.$st.','.$cfg['np']);

$res->setFetchMode(3);
$total = 0;
$links = array();
$count = isset($cfg['lcnt']) ? 1 : 0;
$url   = isset($cfg['linkFull']) ? url('link/') : false;

#Lista
foreach($res as $link)
{
	$links[] = array(
		'title' => $link[1],
		'url'   => $url ? $url.$link[0] : ($count ? url('go/'.$link[0]) : $link[3]),
		'site'  => $link[3],
		'views' => $count ? $link[4] : null,
		'nw'    => $link[5],
		'desc'  => $link[2],
		'num'   => ++$st
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
$view->add('cat_links', array(
	'pages' => &$pages,
	'links' => &$links,
	'count' => $count,
	'add'   => admit($d,'CAT') ? url('edit/4') : null,
	'cats'  => url(isset($cfg['allCat']) ? 'cats' : 'cats/links'),
	'type'  => isset($cfg['allCat']) ? $lang['cats'] : $lang['links']
));
unset($res,$link,$total);
