<?php
if(iCMSa!=1 || !admit('U')) exit;
require LANG_DIR.'rights.php';

#Get all privileged users - FETCH_NUM
$res = $db->query('SELECT ID,login,lv,adm FROM '.PRE.'users WHERE lv>1 OR adm!=""');
$res->setFetchMode(3);

#Info, links
$view->info($lang['iadms'], array(url('editUser','','admin')=>$lang['addUser']));

#Page title
$view->title = $lang['admins'];

#Init variables
$num  = 0;
$adms = array();

foreach($res as $adm)
{
	switch($adm[2])
	{
		case '0': $lv = $lang['locked']; break;
		case '1': $lv = $lang['user']; break;
		case '2': $lv = $lang['editor']; break;
		case '3': $lv = $lang['admin']; break;
		case '4': $lv = $lang['owner']; break;
		default: $lv = '!?';
	}
	$adms[] = array(
		'url'   => url('user/'.urlencode($adm[1])),
		'rights'=> str_replace('|',' ',$adm[3]),
		'level' => $lv,
		'login' => $adm[1],
		'url1'  => $adm[2] < LEVEL || LEVEL == 4 ? url('editAdmin/'.$adm[0], '', 'admin') : false,
		'url2'  => $adm[2] < LEVEL || LEVEL == 4 ? url('editUser/'.$adm[0], '', 'admin') : false,
	);
}

#Template
$view->add('admins', array('admin' => &$adms));
