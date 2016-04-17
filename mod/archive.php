<?php /* Archiwum wszystkich nowo¶ci */
if(iCMS!=1) exit;
include './cfg/content.php';

#Tytu³
$view->title = $lang['archive'];

#Lista nowo¶ci
if(isset($URL[1]))
{
	#Ca³y rok / 1 miesi±c
	if(isset($cfg['archYear']) && is_numeric($URL[1]))
	{
		$q = 'date BETWEEN \''.$URL[1].'-01-01\' AND \''.$URL[1].'-12-31\'';
	}
	elseif(is_numeric($URL[1]) && is_numeric($URL[2]))
	{
		if(!isset($URL[2][1])) $URL[2] = '0'.$URL[2];
		$q = 'date BETWEEN \''.$URL[1].'-'.$URL[2].'-01\' AND \''.$URL[1].'-'.$URL[2].'-31\'';
	}
	else return;
	
	#Pobierz newsy
	$res = $db->query('SELECT ID,name,date FROM '.PRE.'news WHERE '.$q.' AND access=1 ORDER BY ID DESC');

	$res->setFetchMode(3);
	$news = array();
	$num  = 0;

	#Przygotuj dane
	foreach($res as $n)
	{
		$news[] = array(
			'num'  => ++$num,
			'date' => formatDate($n[2], true),
			'title'=> $n[1],
			'url'  => url('news/'.$n[0])
		);
	}
	$res=null;

	#Do szablonu
	$view->add('archive', array('news' => &$news, 'newslist' => true));
	return 1;
}

#Lista lat i miesiêcy
$date = $db->query('SELECT date FROM '.PRE.'news LIMIT 1') -> fetchColumn();

#Brak nowo¶ci?
if(!$date[0]) return;

#Data 1. newsa
$year = (int)($date[0].$date[1].$date[2].$date[3]);
$mon  = (int)($date[5].$date[6]);

#Bie¿±cy miesi±c i rok
$m = (int)date('n');
$y = (int)date('Y');

$dates = array();

#Lata
if(isset($cfg['archYear']))
{
	do {
		$dates[] = array(
			'url'   => url('archive/'.$y),
			'title' => $y--
		);
	}
	while( $y>$year && $y>1981 );
}

#Miesi±ce
elseif($mon)
{
	include LANG_DIR.'date.php';
	do {
		$dates[] = array(
			'url'   => url('archive/'.$y.'/'.$m),
			'title' => $months[$m].' '.$y
		);
		if($m==1)
		{
			$m=12; --$y; $dates[] = array('url' => null);
		}
		else --$m;
	}
	while( $y>$year || ($y==$year && $m>=$mon) );
}
unset($y,$m,$date);

#Do szablonu
$view->add('archive', array('dates' => &$dates, 'newslist' => false));
