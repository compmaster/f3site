<?php /* Lista kategorii */
if(iCMS!=1) exit;

#Tytu³ strony
$view->title = $lang['cats'];

#Typ kategorii - domyœlnie: news
if(isset($URL[1]))
{
	switch($URL[1])
	{
		case 'articles': $id = 1; break;
		case 'files': $id = 2; break;
		case 'images': $id = 3; break;
		case 'links': $id = 4; break;
		case 'news': $id = 5; break;
		default: if(is_numeric($URL[1])) $id = $URL[1]; else return; break;
	}
}
else $id = 0;

#Odczyt
$res = $db->query('SELECT ID,name,dsc,nums FROM '.PRE.'cats WHERE sc=0'.
	($id ? ' AND type='.$id : '').' AND (access=1 OR access="'.LANG.'") ORDER BY name');

$res->setFetchMode(3);
$total = 0;
$cat = array();

#Do szablonu
foreach($res as $x)
{
	$cat[] = array(
		'title'=> $x[1],
		'url'  => url($x[0]),
		'desc' => $x[2],
		'num'  => $x[3],
	);
	++$total;
}

#Brak kategorii?
if($total === 0)
{
	$view -> info($lang['nocats']); return 1;
}
#Tylko 1 - przekierowaæ?
elseif($total === 1)
{
	require './cfg/content.php';
	if(isset($cfg['goCat']))
	{
		$URL = array($x[0]);
		unset($cat,$x,$total,$res);
		require './lib/category.php';
	}
}
else
{
	$view->add('cats', array('cat' => &$cat));
}
