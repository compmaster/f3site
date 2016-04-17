<?php
if(iCMSa!=1 || !admit('U')) return;
require LANG_DIR.'rights.php';
require LANG_DIR.'profile.php';

#Cannot edit own privileges
if(!$id || $id < 1 || ($id == UID && !IS_OWNER)) return;

#Privileges
$set = array(
	'C',  //Categories
	'P',  //Free pages
	'Q',  //Polls
	'R',  //RSS
	'U',  //Users
	'G',  //Groups
	'L',  //Event log
	'M',  //Mail merge
	'CFG',//Options
	'DB', //Database copy
	'N',  //Menu
	'B',  //Banners
	'E',  //Addons
	'CM', //Comments
	'TAG',//Keywords
	'FM', //File manager
	'UP', //Uploading files
	'$',  //WYSIWYG
	'+'   //Global editor
);

#Get user - FETCH_NUM
$adm = $db->query('SELECT login,lv,adm FROM '.PRE.'users WHERE ID='.$id.
	(!IS_OWNER ? ' && lv!=4' : '')) -> fetch(3);

#Cannot edit higher or equal level
if(!$adm OR (!IS_OWNER && $adm[1] >= LEVEL)) return;

#Get addons - FETCH_NUM
$plug1 = $db->query('SELECT ID,text FROM '.PRE.'admmenu WHERE rights=1')->fetchAll(3);

#Get categories
$cats1 = $db->query('SELECT ID,name,c.type,CatID FROM '.PRE.'cats c LEFT JOIN '.PRE.'acl a
	ON c.ID=a.CatID AND a.type="CAT" AND a.UID='.$id.' ORDER BY c.type') -> fetchAll(3);

#Page title
$view->title = sprintf('%s - %s', $lang['editAdm'], $adm[0]);

#Action: save
if($_POST)
{
	#Level
	$lv = (int)$_POST['lv'];

	#Cannot edit owner
	if(!IS_OWNER && ($lv>3 OR $lv<0)) return;

	#Global and new privileges
	$glo = array();
	$new = array();
	foreach($set as $x)
	{
		if(isset($_POST[$x])) $glo[] = $x;
	}
	foreach($plug1 as &$x)
	{
		if(isset($_POST[$x[0]])) $glo[] = $x[0];
	}
	$checked = isset($_POST['c']) ? join(',', array_map('intval',$_POST['c'])) : ''; //Selected
	try
	{
		$db->beginTransaction();
		if($checked)
		$db->exec('DELETE FROM '.PRE.'acl WHERE UID='.$id.' AND type="CAT" AND CatID NOT IN('.$checked.')');

		#Update ACL privileges
		$q = $db->prepare('REPLACE INTO '.PRE.'acl (UID,CatID,type) VALUES (?,?,"CAT")');
		foreach($cats1 as $x)
		{
			if(isset($_POST['c'][$x[0]])) $q->execute(array($id, $x[0]));
		}
		$q = null;

		#Update global rights and level
		$db->exec('UPDATE '.PRE.'users SET adm="'.join('|',$glo).'", lv='.$lv.' WHERE ID='.$id);

		#Apply changes
		$db->commit();
		$view->info($lang['saved']);
		header('Location: '.URL.url('admins','','admin'));
	}
	catch(PDOException $e)
	{
		$view->info($e->getMessage());
	}
	return 1;
}

/* FORM */

#Functions
require './lib/user.php';
require './lib/categories.php';

$prv = explode('|', $adm[2]); //Rights
$lv  = $adm[1]; //Level

#Rights
$rights = array();
foreach($set as $x)
{
	if(in_array($x,$prv)) $rights[$x] = true;
}

#Addons
$plugins = '';
foreach($plug1 as &$x)
{
	$plugins .= '<label><input type="checkbox" name="'.$x[0].'"'.((in_array($x[0],$prv)) ?
		' checked="checked"' : '').' /> '.$x[1].'</label><br />';
}

#Categories
$cats = '';
$type = 0;
foreach($cats1 as &$x)
{
	if($x[2] > $type) //Type change
	{
		if($type!=0) $cats .= '</fieldset>';
		$cats .= '<fieldset><legend>'.$lang['cats'].': '.$lang[ typeOf($x[2]) ].'</legend>';
		$type = $x[2];
	}
	$cats .= '<label><input type="checkbox" name="c['.$x[0].']"'.(($x[3]) ?
		' checked="checked"' : '').' /> '.$x[1].'</label><br />';
}
if($cats!='') $cats.='</fieldset>';

#Template
$view->add('editAdmin', array(
	'owner'   => IS_OWNER,
	'lv'      => $lv,
	'cats'    => &$cats,
	'plugins' => &$plugins,
	'rights'  => &$rights
));
