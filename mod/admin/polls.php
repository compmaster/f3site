<?php
if(iCMSa!=1 || !admit('Q')) exit;
require LANG_DIR.'admAll.php';

#Operacje
if($_POST)
{
	include './mod/polls/poll.php';
	isset($_POST['del']) AND DeletePoll();
	isset($_POST['reset']) AND ResetPoll();
}

#Odczyt
$res = $db->query('SELECT ID,name,num,access FROM '.PRE.'polls ORDER BY ID DESC');
$res->setFetchMode(3); //Num

#Lista
$total = 0;
$polls = array();

foreach($res as $x)
{
	$polls[] = array(
		'num'  => ++$total,
		'url'  => url('poll/'.$x[0]),
		'edit' => url('editPoll/'.$x[0], '', 'admin'),
		'id'   => $x[0],
		'title'  => $x[1],
		'votes'  => $x[2],
		'access' => $x[3]
	);
}

#Przekieruj, gdy nie ma nic
if(empty($polls))
{
	header('Location: '.URL.url('editPoll','','admin'));
	exit;
}

$res = null;
$view->add('polls', array('polls' => $polls));
