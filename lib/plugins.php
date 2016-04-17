<?php
const EVENT_BEFORE_SAVE = 0;
const EVENT_AFTER_SAVE = 1;
const EVENT_BEFORE_LOGIN = 2;
const EVENT_AFTER_LOGIN = 3;
const EVENT_BEFORE_REGISTER = 4;
const EVENT_AFTER_REGISTER = 5;

function hook($name, &$data)
{
	foreach(new DirectoryIterator('plugins') as $dir)
	{
		if($dir->isDir() && !$dir->isDot() && file_exists('plugins/'.$dir.'/hook.php'))
		{
			include_once './plugins/'.$dir.'/hook.php';
			if(function_exists($dir.'_'.$name))
			{
				$fn = $dir.'_'.$name;
			}
			elseif(function_exists($dir.'\\'.$name))
			{
				$fn = $dir.'\\'.$name;
			}
			else continue;
			
			$args = array();
			$info = new ReflectionFunction($fn);
			foreach($info->getParameters() as $param)
			{
				global $$param;
				$args[] = isset($$param) ? &$$param : null;
			}
			
			$fn($args);
		}
	}
}

class PluginManager
{
	private static function load()
	{
		foreach(new DirectoryIterator('plugins') as $dir)
		{
			if($dir->isDir() && !$dir->isDot() && file_exists('plugins/'.$dir.'/event.php'))
			{
				include './plugins/'.$dir.'/event.php';
			}
		}
		
	}
	public static function trigger($eventType, &$data)
	{
		
	}
}