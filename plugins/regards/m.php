<?php
if(iCMS!=1) exit;

#Pobierz 1 pozdrowienie
if($regard = $db->query('SELECT who,rcpt FROM '.PRE.'regards ORDER BY RANDOM() LIMIT 1') -> fetch(3))
{
	echo '<b>Od: </b>'.$regard[0].'<br /><b>Dla: </b>'.$regard[1];
}
else
{
	echo '<div style="text-align: center">'.$lang['lack'].'</div>';
}
?><br />
<a href="javascript:Okno('?mode=pozdrowka&amp;co=d',400,300,100,200)">Dodaj pozdrowienie &raquo;</a>