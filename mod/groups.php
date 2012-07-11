<?php /* Grupy u¿ytkowników */
if(iCMS!=1) exit;
require LANG_DIR.'profile.php';

#Tytu³ strony
$view->title = $lang['groups'];

#Grupy u¿ytkownika
$member = UID ? $db->query('SELECT g FROM '.PRE.'groupuser WHERE u='.UID)->fetchAll(7) : array();

#Pobierz
$res = $db->query('SELECT ID,name,dsc,num FROM '.PRE.'groups WHERE access="1" OR access="'.
	LANG.'" ORDER BY num DESC, name');
$res -> setFetchMode(3);
$gro = array();

foreach($res as $x)
{
	$gro[] = array(
		'title' => $x[1],
		'num'   => $x[3],
		'desc'  => nl2br($x[2]),
		'member'=> in_array($x[0],$member),
		'url'   => url('group/'.$x[0])
	);
}

#Brak
if(empty($gro))
{
	if(admit('G'))
	{
		$view->info($lang['noGroup'], array(
			url('editGroup', '', 'admin') => $lang['addGroup'],
			url('groups', '', 'admin')    => $lang['groups']));
	}
	else
	{
		$view->info($lang['noGroup']);
	}
	return 1;
}

#Szablon
$view->add('groups', array('groups' => &$gro));
