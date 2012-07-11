<?php
if(iCMS!=1) exit;

#Sortowanie
switch($cat['sort'])
{
	case '1': $sort = 'ID'; break;
	case '3': $sort = 'name'; break;
	case '4': $sort = 'dls DESC, ID DESC'; break;
	case '5': $sort = 'rate DESC, ID DESC'; break;
	default: $sort = 'ID DESC';
}

#Odczyt
$res = $db->query('SELECT ID,name,date,dsc,file,size FROM '.PRE.'files WHERE '.
	$cats.' AND access=1 ORDER BY priority,'.$sort.' LIMIT '.$st.','.$cfg['np']);

$res->setFetchMode(3);
$total = 0;
$files = array();
$url = url('file/');

#Lista
foreach($res as $file)
{
	$files[] = array(
		'title' => $file[1],
		'desc'  => $file[3],
		'size'  => $file[5],
		'url'   => $url.$file[0],
		'num'   => ++$st,
		'date'  => formatDate($file[2]),
		'file_url' => isset($cfg['fcdl']) ? url('get/'.$file[0]) : $file[4]
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
$view->add('cat_files', array(
	'files' => &$files,
	'pages' => &$pages,
	'add'   => admit($d,'CAT') ? url('edit/2') : null,
	'cats'  => url(isset($cfg['allCat']) ? 'cats' : 'cats/files'),
	'type'  => isset($cfg['allCat']) ? $lang['cats'] : $lang['files']
));
unset($res,$total,$file);
