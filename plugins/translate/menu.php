<?php
if(iCMSa!=1) exit;

$view->file = 'menu';
$all = array();

foreach(scandir('./lang') as $x)
{
	$files = [];
	$all[] = array(
		'id' => $x
	);
}
