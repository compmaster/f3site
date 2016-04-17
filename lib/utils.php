<?php
#Load SQL file and send queries
#You must use try{} catch{} manually
function execSQL($file, PDO $db=null, $prefix='f3_')
{
	if(!$db) global $db;
	if(!file_exists($file))
	{
		throw new Exception(sprintf('File %s does not exist!', $file));
	}

	#Replace default prefix f3_ into $prefix
	$sql = str_replace('f3_', $prefix, file_get_contents($file));

	#Double line break
	if(strpos($sql, "\r\n"))
	{
		$sql = explode(";\r\n\r\n", $sql); //Win
	}
	elseif(strpos($sql, "\r"))
	{
		$sql = explode(";\r\r", $sql); //MacOS
	}
	else
	{
		$sql = explode(";\n\n", $sql); //Unix
	}

	#Execute queries
	foreach($sql as $q)
	{
		if(substr($q, 0, 2) != '--')
		{
			$db->exec($q);
		}
	}
}

#Create INI file - only values are escaped!
function put_ini_file($file, $data, $append=0)
{
	$out = '';
	foreach($data as $sect=>$body)
	{
		$out .= '['.$sect.']'.PHP_EOL;
		foreach($body as $key=>$val)
		{
			if(is_array($val))
			{
				foreach($val as $i)
				{
					$out .= $key.'[]="'.addslashes($i).'"'.PHP_EOL;
				}
			}
			else
			{
				$out .= $key.'="'.addslashes($val).'"'.PHP_EOL;
			}
		}
		$out .= PHP_EOL;
	}
	return file_put_contents($file, $out, 2|$append*8);
}