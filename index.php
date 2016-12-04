<?php //F3Site 2016 (C) 2005-2016 COMPMaster
const iCMS = 1;
require './lib/core.php';

#First line of defense against CSRF
if($_POST && isset($_SERVER['HTTP_REFERER']))
{
	$pos = strpos($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME']);
	if($pos < 3 OR $pos > 8) exit;
}

#Main arrays
$lang = array();
$cfg  = array();
$user = null;

#Settings TODO:join
require './cfg/main.php';
require './cfg/db.php';

#AJAX request, protocol
define('JS', isset($_SERVER['HTTP_X_REQUESTED_WITH']));
define('PROTO', isset($_SERVER['HTTPS']) ? 'https://' : 'http://');
define('NICEURL', $cfg['niceURL']);

#Path to module based on PATH_INFO or GET param
if(isset($_SERVER['PATH_INFO'][1]))
{
	$URL = explode('/', substr($_SERVER['PATH_INFO'],1));
	define('PATH', substr(dirname($_SERVER['PHP_SELF']),0,-9));
}
else
{
	$URL = isset($_GET['go']) ? explode('/', $_GET['go']) : array();
	define('PATH', str_replace('//','/',dirname($_SERVER['PHP_SELF']).'/'));
}

#Detect full URL
define('URL','http://'.$_SERVER['SERVER_NAME'].PATH);
define('ID', $id = isset($URL[1]) ? (int)$URL[1] : 0);

#Skin path
define('SKIN_DIR', 'style/'.$cfg['skin'].'/');

session_start();

#Default language
$nlang = $cfg['lang'];

#Language: load from URL, session, cookies or Accept-Language header
if(isset($URL[0][1]) && empty($URL[0][2]) && file_exists('lang/'.$URL[0].'/main.php'))
{
	$nlang = $_SESSION['LANG'] = array_shift($URL);
	setcookie('lang', $nlang, PHP_INT_MAX);
}
elseif(isset($_SESSION['LANG']))
{
	$nlang = $_SESSION['LANG'];
}
elseif(isset($_COOKIE['lang']) && ctype_alnum($_COOKIE['lang']) && is_dir('lang/'.$_COOKIE['lang']))
{
	$nlang = $_SESSION['LANG'] = $_COOKIE['lang'];
}
elseif(isset($cfg['detectLang']))
{
	foreach(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $x)
	{
		if(isset($x[2]))
		{
			$x = $x[0].$x[1];
		}
		if(ctype_alnum($x) && file_exists('lang/'.$x.'/main.php'))
		{
			$nlang = $_SESSION['LANG'] = $x; break;
		}
	}
	unset($x);
}

#Lang: paths
define('LANG', $nlang);
define('LANG_DIR', './lang/'.LANG.'/');

#Include main lang file
require LANG_DIR.'main.php';

#Include skin class and create object
require './lib/view.php';
$view = new View;

#Cache, charset
header('Cache-Control: public');
header('Content-Type: text/html; charset=utf-8');

#Connect to database
try
{
	if($db_db==='sqlite')
	{
		$db = new PDO('sqlite:'.$db_d);
	}
	else
	{
		$db = new PDO('mysql:host='.$db_h.';dbname='.$db_d,$db_u,$db_p);
		$db->exec('SET NAMES utf8');
	}
	$db->setAttribute(3,2); #throw exceptions
	$db->setAttribute(19,2); #fetch ASSOC by default
}
catch(PDOException $e)
{
	$view->message(1);
}
//TODO: cache nie sesje i inwalidować po przyjściu PW
if(isset($_SESSION['userdata']))
{
	if(isset($_SESSION['IP']) && $_SERVER['REMOTE_ADDR']===$_SESSION['IP'])
	{
		$user =& $_SESSION['userdata'];
	}
	else
	{
		session_regenerate_id(1);
		unset($_SESSION['userdata']);
	}
}
elseif(isset($_COOKIE['authid']) && isset($_COOKIE['authkey']))
{
	$q = $db->prepare('SELECT s.key,u.ID,login,pass,lv,adm,lvis,pms FROM '.PRE.
		'sessions s INNER JOIN '.PRE.'users u ON s.UID=u.ID WHERE u.lv>0 AND s.ID=:sid');
	$q->execute(array('sid'=>(int)$_COOKIE['authid']));
	if($tmp = $q->fetch(2) && password_verify($_COOKIE['authkey'], $tmp['key']))
	{
		//TODO: osobna tabela z logowaniami
		$db->exec('UPDATE '.PRE.'sessions SET `last`='.time().' WHERE ID='.$tmp['SID']);
		$user = $_SESSION['userdata'] = $tmp;
	}
	unset($q,$tmp);
}

if(isset($user))
{
	define('UID', $user['ID']);
	define('LEVEL', $user['lv']);
	define('IS_EDITOR', LEVEL > 1);
	define('IS_ADMIN', LEVEL > 2);
	define('IS_OWNER', LEVEL > 3);
	if(!isset($_SESSION['recent']))
	{
		$db->exec('UPDATE '.PRE.'users SET lvis='.$_SERVER['REQUEST_TIME'].' WHERE ID='.UID);
		$_SESSION['recent'] = (int)$user['lvis'];
	}
}
else
{
	define('UID', 0);
	define('LEVEL', 1);
	define('IS_ADMIN', 0);
	define('IS_EDITOR', 0);
	define('IS_OWNER', 0);
}

#Default META description
$view->desc = $cfg['metaDesc'];

#Load module: built-in module, extension, category
try
{
	if(isset($URL[0]) && !is_numeric($URL[0]) && !isset($URL[0][30]))
	{
		if(file_exists('./mod/'.$URL[0].'.php'))
		{
			include './mod/'.$URL[0].'.php';
		}
		elseif(file_exists('./plugins/'.$URL[0].'/index.php'))
		{
			$view->dir = 'plugins/'.$URL[0].'/';
			include './plugins/'.$URL[0].'/index.php';
		}
		else
		{
			include './lib/category.php';
		}
	}
	else
	{
		include './lib/category.php';
	}
}
catch(Exception $e)
{
	error_log($e);
	$view->message(2);
}
if(JS)
{
	$view->loop();
}
else
{
	#Channels for language
	#TODO: RSS as extension
	if(!empty($cfg['RSS'][LANG]))
	{
		$view->rss($cfg['RSS'][LANG]);
	}
	$view->display();
}