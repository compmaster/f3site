<?php if(iCMS!=1) exit;
require './lib/categories.php';
require LANG_DIR.'content.php';

#Template
$view->title = $lang['batch'];
$view->dir = './plugins/upload/';
$view->cache = './cache/upload/';
$view->add('upload', array(
	'cat' => Slaves(1, 0)
));

#Supported archives
$zip = extension_loaded('zip');
$rar = extension_loaded('rar');

#Supported miniatures
$gd = extension_loaded('gd');

#Supported EXIF
$exif = extension_loaded('exif');

#Archives


#Stage 1: select archive
if(isset($_POST['next']))
{
	$cat = is_numeric($_POST['cat']) ? $_POST['cat'] : 0;
	$title = empty($_POST['title']) ? $lang['noname'] : clean($_POST['title']); //todo:translate
	$autor = clean($_POST['author']);
	$dsc = $_POST['dsc'];

	#Choice: Folder on server
	if(empty($_POST['up']))
	{
		$dir = new DirectoryIterator($_POST['folder']);
		foreach($dir as $x)
		{
			//$x->
		}
	}
}
