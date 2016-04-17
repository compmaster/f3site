<?php if(iCMSa!=1) exit;

$styles = array();
foreach(scandir('style') as $dir)
{
	if($dir[0] !== '.' && file_exists('style/'.$dir.'/body.html'))
	{
		$styles[] = array(
			'dir' => $dir,
			
		);
	}
}

$view->add('styles', array(

));