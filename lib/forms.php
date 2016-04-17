<?php
#List all files or folders as options
function filelist($dir, $folders=false, $selected=null)
{
	if(!is_dir($dir)) return '';
	$out = '';
	if($folders)
	{
		foreach(scandir($dir) as $x)
		{
			if(is_dir($dir.'/'.$x) && $x[0]!='.')
			{
				$out.= '<option'.(($selected==$x)?' selected="selected"':'').'>'.$x.'</option>';
			}
		}
	}
	else
	{
		foreach(scandir($dir) as $x)
		{
			if(is_file($dir.'/'.$x))
			{
				$x = str_replace('.php', '', $x);
				$out.= '<option'.(($selected==$x)?' selected="selected"':'').'>'.$x.'</option>';
			}
		}
	}
	return $out;
}