<?php //User functions

#Level list
function LevelList($sel=null,$owner=0,$locked=0)
{
	global $lang;
	return (($locked==1)?'<option value="0">'.$lang['locked'].'</option>':'').'
	<option value="1"'.(($sel==1 || $sel=='all')?' selected="selected"':'').'>'.$lang['user'].'</option>
	<option value="2"'.(($sel==2 || $sel=='all')?' selected="selected"':'').'>'.$lang['editor'].'</option>
	<option value="3"'.(($sel==3 || $sel=='all')?' selected="selected"':'').'>'.$lang['admin'].'</option>'.
	(($owner==1)?
	'<option value="4"'.(($sel==4 || $sel=='all')?' selected="selected"':'').'>'.$lang['owner'].'</option>':'');
}

#Groups
function GroupList($sel=null)
{
	global $db;
	$res = $db->query('SELECT ID,name FROM '.PRE.'groups');
	$res->setFetchMode(3); //NUM
	$out = '';
	foreach($res as $g)
	{
		$out.='<option value="'.$g[0].'"'.(($g[0]==$sel || $sel=='all')?' selected="selected"':'').'>'.$g[1].'</option>';
	}
	return $out;
}