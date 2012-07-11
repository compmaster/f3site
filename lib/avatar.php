<?php //Wy¶lij obraz na serwer - zanim wywo³asz funkcjê, do³±cz konfig i jêzyk
function Avatar(&$error=array(), $id=UID)
{
	global $lang,$cfg,$db;
	$ok   = 1;
	$file = &$_FILES['photo'];
	$ext  = strtolower(strrchr($file['name'],'.'));
	$size = getimagesize($file['tmp_name']);

	#Gdy $size = 0, plik nie jest obrazem
	if(empty($size) || $size[0]==0 || ($ext!='.png' && $ext!='.jpg' && $ext!='.gif'))
	{
		$error[] = $lang['photoEx'];
		$ok = 0;
	}

	#Rozmiar pliku
	elseif(filesize($file['tmp_name']) / 1024 > $cfg['maxSize'])
	{
		$error[] = sprintf($lang['photoKB'], $cfg['maxSize']);
		$ok = 0;
	}

	#Sprawdz rozmiar i zmniejsz
	elseif($size[0]>$cfg['maxDim1'] OR $size[1]>$cfg['maxDim2'])
	{
		if(isset($cfg['autoResize']) && extension_loaded('gd') && is_writable($file['tmp_name']))
		{
			switch($size[2])
			{
				case 3: $res = imagecreatefrompng($file['tmp_name']); break;
				case 1: $res = imagecreatefromgif($file['tmp_name']); break;
				case 2: $res = imagecreatefromjpeg($file['tmp_name']); break;
				default: return false;
			}
			$wid = min($size[0],$cfg['maxDim1']);
			$hei = min($size[1],$cfg['maxDim2']);
			$new = imagecreatetruecolor($wid, $hei);
			imagecopyresampled($new, $res, 0, 0, 0, 0, $wid, $hei, $size[0], $size[1]);

			switch($size[2])
			{
				case 3: $ok = imagepng($new, $file['tmp_name'],9); break;
				case 1: $ok = imagegif($new, $file['tmp_name']); break;
				case 2: $ok = imagejpeg($new, $file['tmp_name'],80); break;
			}
		}
		else
		{
			$ok = 0;
			$error[] = $lang['photoBig'];
		}
	}
	try
	{
		#Pobierz aktualny URL
		$old = $db->query('SELECT photo FROM '.PRE.'users WHERE ID='.$id) -> fetchColumn();

		#Przenie¶ plik
		if($ok && move_uploaded_file($file['tmp_name'], 'img/user/'.$id.$ext))
		{
			#Usuñ stary
			if($old != 'img/user/'.$id.$ext && strpos($old,'user/'.$id) && !strpos($old,':'))
			{
				@unlink($old);
			}

			#Aktualizuj w bazie
			$db->exec('UPDATE '.PRE.'users SET photo="img/user/'.$id.$ext.'" WHERE ID='.$id);

			#Zwróæ URL
			return 'img/user/'.$id.$ext;
		}
		else
		{
			$error[] = $lang['photoErr'];
			return $old;
		}
	}
	catch(PDOException $e)
	{
		$error[] = $e;
		return $old;
	}
}

function RemoveAvatar(&$error, $id=UID)
{
	global $db;
	try
	{
		#Pobierz aktualny URL
		$old = $db->query('SELECT photo FROM '.PRE.'users WHERE ID='.$id) -> fetchColumn();

		#Aktualizuj w bazie
		$db->exec('UPDATE '.PRE.'users SET photo="" WHERE ID='.$id);
	}
	catch(PDOException $e)
	{
		$error[] = $e;
		return $old;
	}
	if(strpos($old,'user/'.$id) && strpos($old,':') === false) @unlink($old);
	return '';
}