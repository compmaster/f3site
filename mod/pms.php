<?php /* Prywatne wiadomo¶ci */
if(iCMS!=1) exit;

#Jêzyk
require LANG_DIR.'pms.php';

#Tytu³ strony
$view->title = $lang['pm3'];

#Jest dostêp?
if(isset($cfg['pmOn']) && UID)
{
	#Razem PM
	$size = $db->query('SELECT COUNT(*) FROM '.PRE.'pms WHERE owner='.UID)->fetch(7);

	#Dane do szablonu
	$view->add('pms', array(
		'new'   => $user['pms'],
		'limit' => $cfg['pmLimit'],
		'size'  => $size,
		'quota' => $cfg['pmLimit']>0 && $size>0 ? $size / $cfg['pmLimit'] * 100 : 0
	));

	#Akcja
	if(isset($URL[1]))
	{
		switch($URL[1]) {
			case 'view': require './mod/pms/message.php'; break;
			case 'edit': require './mod/pms/posting.php'; break;
			case 'del':  require './mod/pms/action.php'; break;
			default: require './mod/pms/list.php';
		}
	}
	else require './mod/pms/list.php';
}
elseif(!UID)
{
	$view->info($lang['pm2']);
}
else
{
	$view->info($lang['pm1']);
}
