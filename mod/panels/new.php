<?php
if(iCMS!=1) exit;

#Istnieje?
if(file_exists('cache/new-'.LANG.'.php') && $x = file_get_contents('cache/new-'.LANG.'.php'))
{
	echo $x; unset($x);
}
else
{
	echo '<div style="text-align: center; margin: 5px 0">'.$lang['lack'].'</div>';
}