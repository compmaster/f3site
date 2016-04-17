<?php
define('iCMS',1);
require './kernel.php';
require LANG_DIR.'fileman.php';

#Banned extensions and MIME types
$banEx = array('.php'=>1, '.db'=>1, '.ini'=>1);
$banUp = array('.php', '.pl', '.cgi', '.asp', '.shtml', '.phtml', '.phps', '.jsp');
$banMIME = array('php', 'cgi');

#Current folder
if(isset($_GET['dir']) && strpos($_GET['dir'],'.')===false)
{
	$dir = $_GET['dir'];
	if(substr($dir,-1) != '/' && $dir != '') $dir.='/';
}
else
{
	$dir = 'img/';
}

#Check user rights
admit('FM') or exit;
$mayUpload = admit('UP') && is_writable($dir);

#Upload file
if($_FILES && $mayUpload)
{
	foreach($_FILES['file']['name'] as $i=>$x)
	{
		if(!in_array(strrchr($x, '.'), $banUp))
		{
			move_uploaded_file($_FILES['file']['tmp_name'][$i], $dir.$x);
		}
	}
}

#Show file list
$file = $folder = array();
$parent = $dir ? '?dir='.join('/',explode('/',$dir,-2)) : false;

foreach(scandir('./'.$dir) as $x)
{
	if($x[0] === '.') 
	{
		continue;
	}
	if(is_dir('./'.$dir.$x))
	{
		$folder[] = array('name' => $x, 'url' => '?dir='.$dir.$x);
	}
	else
	{
		$e = strrchr($x, '.');
		$file[] = array('name' => $x, 'url' => isset($banEx[$e]) ? '#' : $dir.$x);
	}
}
$_SESSION['FM'] = 1;

#Cut folder path
if(isset($dir[50])) $dir = substr($dir, -40);

#Show template
$view->show('fileman', array(
	'mayUpload' => $mayUpload,
	'parent' => $parent,
	'folder' => &$folder,
	'file' => &$file,
	'dir' => $dir
));