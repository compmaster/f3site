<?php /* Plik dla ¿±dañ JavaScript - XMLHTTPRequest */
define('iCMS',1);
require 'kernel.php';

#Gdy to nie jest ¿±danie JS
if(!JS) header('Location: '.URL);

#Gdy ID modu³u zawiera niedozwolone znaki
if(!isset($URL[0]) || strpos($URL[0], '/') !== false || isset($URL[0][30]))
{
	exit('Wrong URL params!');
}
switch($URL[0])
{
	#Podgl±d
	case 'preview':
		include './lib/preview.php';
		break;

	#Pliki CSS
	case 'css':
		$view->file = 'css';
		break;

	#Edytuj tagi
	case 'tags':
		include './lib/categories.php';
		ajaxTags($id, (int)$_GET['type']);
		break;

	#Rozszerzenia
	default:
		if(file_exists('./plugins/'.$URL[0].'/js.php'))
			(include './plugins/'.$URL[0].'/js.php') or $view->set404();
}

#Wy¶wietl szablon
#if($view->file) $view->display();
