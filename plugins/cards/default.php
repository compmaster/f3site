<?php if(iCMS!=1) exit;

##Opcje
if(file_exists('cfg/cards.php'))
{
	require 'cfg/cards.php';
}
else
{
	$view->info('Extension not installed');
	//return; /////////////////////
}

##Katalog szablonów
$view->dir = './plugins/cards/style/';
$view->cache = './cache/cards/';
$view->css('plugins/cards/cards.css');

##Akcja
if(isset($URL[1]))
{
	if(isset($URL[1][12]))
	{
		$q = $db->prepare('SELECT * FROM '.PRE.'cardsent INNER JOIN '.PRE.'cards USING ID WHERE KEY=:key');
		$q->bindValue(':key', $URL[1]);
		$q->execute();
	}
	elseif(is_numeric($URL[1]))
	{
		$q = $db->prepare('SELECT * FROM '.PRE.'cards WHERE ID=:id');
		$q->bindValue(':id', $URL[1]);
		$q->execute();
	}
	else
	{
		return;
	}
	if($card = $q->fetch(2))
	{
		
	}
	else
	{
		return; //NOT EXISTS
	}
}
else
{
	$q = $db->prepare('SELECT * FROM '.PRE.'cards WHERE access=1 ORDER BY sent DESC');
	$card = array();
	$num = 0;
	foreach($q->execute() as $x)
	{
		$card[] = array(
			'title' => $x['name'],
			'sent'  => $x['sent'],
			'file'  => $x['th'],
			'num'   => ++$i,
			'date'  => formatDate($x['date']),
			'url'   => url('cards/'.$x['ID']),
		);
	}
	if($num > 0)
	{
		//$view->data = array('card' => $card);
	}
	else
	{
		$view->info('Brak kartek [lang]');
	}
}
