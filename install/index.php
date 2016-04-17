<?php
header('Cache-Control: private');
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex');

#Full URL
define('PATH', str_replace(array('//','install/'), array('/',''), dirname($_SERVER['PHP_SELF']).'/'));
define('URL', 'http://'.$_SERVER['SERVER_NAME'].PATH);

#Working folder
chdir('../');
define('VIEW_DIR', './cache/install/');
define('SKIN_DIR', './install/HTML/');

#Classes
require './lib/view.php';
require './lib/config.php';
require './lib/utils.php';
require './install/install.php';

#Lang
$setup = new Installer;
$error = array();

#Lang file
require './install/lang/'.$setup->lang.'.php';

#Templates
$view = new View;
$view->title = $lang['installer'];

#Already done
if(file_exists('./cfg/db.php') && filesize('./cfg/db.php')>43) $view->message($lang['ban']);

#PDO drivers
$dr = PDO::getAvailableDrivers();
$my = in_array('mysql', $dr);
$li = in_array('sqlite',$dr);

#No driver
if(!$my && !$li) $view->message($lang['noDB']);

#Only one
$one = ($li xor $my) ? ($my ? 'mysql' : 'sqlite') : false;

#Check CHMOD
if(!$setup->chmods())
{
	$view->add('chmod', array('chmod' => $setup->buildChmodTable()));
}

#Installer level
else switch(isset($_POST['stage']) ? $_POST['stage'] : ($one ? 1 : 0))
{
	#Install
	case 2:

	$type = isset($_POST['file']) ? 'sqlite' : 'mysql';
	$data = array(
		'type' => $type,
		'host' => $type=='mysql' ? htmlspecialchars($_POST['host']) : '',
		'db'   => $type=='mysql' ? htmlspecialchars($_POST['db']) : '',
		'user' => $type=='mysql' ? htmlspecialchars($_POST['user']) : '',
		'pass' => $type=='mysql' ? htmlspecialchars($_POST['pass']) : '',
		'file' => $type=='sqlite' ? htmlspecialchars($_POST['file']) : '',
		'skin' => htmlspecialchars($_POST['skin']),
		'title'=> htmlspecialchars($_POST['title']),
		'pre'  => htmlspecialchars($_POST['pre']),
		'login'=> htmlspecialchars($_POST['login']),
		'urls' => (int)$_POST['urls'],
		'lng'  => (int)$_POST['lng']
	);

	#Prefix
	define('PRE', $data['pre']);
	if(!preg_match('/^[a-zA-Z_]{0,9}$/', PRE))
	{
		$error[] = $lang['e1'];
	}

	#Wrong password
	if(!isset($_POST['uPass'][4]))
	{
		$error[] = $lang['e2'];
	}
	if($_POST['uPass'] != $_POST['uPass2'])
	{
		$error[] = $lang['e3'];
	}

	try
	{
		#Errors
		if($error) throw new Exception('<ul><li>'.join('</li><li>',$error).'</li></ul>');

		#Begin
		$setup->connect($data);
		$setup->sample = true;
		$setup->title = $data['title'];
		$setup->urls = $data['urls'];
		$setup->load('./install/SQL/'.$type.'.sql');

		#Setup languages
		if($_POST['lng']==='1')
		{
			$setup->setupAllLang();
		}
		elseif($_POST['lng']==='3' && !empty($_POST['l']))
		{
			foreach($_POST['l'] as $l)
			{
				$setup->setupLang($l);
			}
		}
		else
		{
			$setup->setupLang();
		}
		$setup->admin($data['login'], $_POST['uPass']);
		$setup->commit($data);
		$view->message($lang['OK'], '..');
	}
	catch(Exception $e)
	{
		$view->info(nl2br($e->getMessage()));
		$view->add('form', array(
			'data'  => &$data,
			'host'  => $_SERVER['HTTP_HOST'],
			'mysql' => $data['type'] == 'mysql',
			'skins' => $setup->getSkins($data['skin'])
		));
	}
	break;

	#Form
	case 1:
	
	$data = array(
		'host'  => 'localhost',
		'title' => $lang['myPage'],
		'urls'  => $setup->urls(),
		'user'  => 'root',
		'pass'  => '',
		'db'    => '',
		'pre'   => 'f3_',
		'login' => 'Admin',
		'skin'  => 'system',
		'file'  => is_writable('..') ? '../db.db' : 'cfg/db.db',
		'lng'   => 2
	);

	$view->add('form', array(
		'host'  => $_SERVER['HTTP_HOST'],
		'mysql' => $one=='mysql' || ($_POST && $_POST['type']=='mysql'),
		'data'  => &$data,
		'langs' => $setup->getLangs(),
		'skins' => $setup->getSkins($data['skin'])
	));

	break;

	#Select database
	default: $view->add('start');
}

#Main template
$view->front('body');
