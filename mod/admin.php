<?php if(iCMS!=1) exit;
const iCMSa = 1;
require LANG_DIR.'adm.php';

#Not logged in
if(!UID)
{
	header('Location: '.BASE_URL.url('login','from=admin'));
	exit;
}
elseif(!IS_ADMIN)
{
	header('Location: '.BASE_URL); //Redirect to homepage
	exit;
}

#Reauthenticate to admin
if(isset($cfg['auth2']))
{
	
}

#Templates folder
$view->dir = './style/admin/';
$view->cache = './cache/admin/';

#Function: Get selected ID
function GetID($toStr=false, $array=null)
{
	$x = array();
	if(!$array && isset($_POST['x'])) $array = $_POST['x']; //Domyślny klucz: x
	if(!$array) return false;

	foreach($array as $key=>$val)
	{
		if(is_numeric($key)) $x[] = $key;
	}
	if(!$x) return false;

	#Return array or string
	return $toStr ? join(',', $x) : $x;
}

#Maintenance mode info
if(isset($cfg['MA']))
{
	$view->info($lang['siteOff'], null, 'warning');
}

#Module and ID based on URL
$mod = isset($URL[1]) && ctype_alnum($URL[1]) ? $URL[1] : 'summary';
$id = isset($URL[2]) && is_numeric($URL[2]) ? $URL[2] : 0;

#Load module
if(file_exists('./mod/admin/'.$mod.'.php'))
{
	include './mod/admin/'.$mod.'.php';
}
elseif(file_exists('./plugins/'.$mod.'/admin.php'))
{
	include './plugins/'.$mod.'/admin.php';
}
elseif(file_exists('./style/admin/'.$mod.'.html'))
{
	$view->add($mod);
}

#Build module list
$modules = array(
	array($lang['cats'], 'cats', 'C'),
	array($lang['polls'], 'polls', 'Q'),
	array($lang['ipages'], 'pages', 'P'),
	//array($lang['rss'], 'rss', 'R'),
	array($lang['users'], 'users', 'U'),
	array($lang['groups'], 'groups', 'G'),
	array($lang['log'], 'log', 'L'),
	array($lang['mailing'], 'mailing', 'M'),
	array($lang['config'], 'config', 'CFG'),
	array($lang['nav'], 'menu', 'N'),
	array($lang['dbcopy'], 'db', 'DB'),
	array($lang['plugs'], 'plugins', 'E')
);

#Addons
$res = $db->query('SELECT ID,text,file FROM '.PRE.'admmenu WHERE menu=1');
foreach($res as $x)
{
	$modules[] = array($x['text'],$x['file'],$x['ID']);
}

#Build menu for admin
$menu = array();
foreach($modules as $x)
{
	if(admit($x[2])) $menu[] = array('text'=>$x[0],'url'=>url('admin/'.$x[1],null,'admin'),'class'=>$x[1]);
}

#Default title
if(!$view->title && isset($lang[$mod])) $view->title = $lang[$mod];

#Set admin layout
$view->setLayout('admin', ['menu'=>$menu]);