<?php
/**
 * Checks if user has privileges to module or content
 */
function admit($type, $id=null)
{
	if(!UID) return false;
	global $user, $db;
	static $global, $all;

	#Owner may access everything
	if(IS_OWNER) return true;

	if($id)
	{
		if($type==='CAT')
		{
			if(IS_EDITOR)
			{
				if(admit('+')) return true;
			}
			return false;
		}
		if(!isset($all[$type]))
		{
			$q = $db->prepare('SELECT CatID,1 FROM '.PRE.'acl WHERE type=? AND UID=?');
			$q -> execute(array($type, UID));
			$all[$type] = $q->fetchAll(12); //KEY_PAIR
		}
		return isset($all[$type][$id]);
	}
	else
	{
		if(empty($global)) $global = explode('|',$user['adm']);
		return in_array($type,$global);
	}
}

/**
 * Returns fancy URL based on module path and params
 */
function url($path, $query=null)
{
	if($query && is_array($query)) $query = http_build_query($query);
	switch(NICEURL)
	{
		case 1: return $path . ($query ? '?'.$query : ''); break;
		case 2: return 'index.php/' . $path . ($query ? '?'.$query : ''); break;
		default: return '?go=' . $path . ($query ? '&'.$query : '');
	}
}

/**
 * @deprecated
 */
function catPath($id, &$cat=null)
{
	if(file_exists('cache/cat'.$id.'.php'))
	{
		return file_get_contents('cache/cat'.$id.'.php');
	}
	else
	{
		include_once './lib/categories.php';
		return UpdateCatPath($cat ? $cat : $id);
	}
}

/**
 * Builds pagination HTML code
 */
function pages($page,$ile,$max,$url='',$type=0,$p='')
{
	global $lang;
	$all = ceil($ile / $max);
	$out = '';

	if(!$p) $p = strpos($url, '?')===false ? '?page=' : '&amp;page=';

	#Select
	if($type)
	{
		$out = '<select onchange="location=\''.$url.$p.'\'+(this.selectedIndex+1)">';
		for($i=1; $i<=$all; ++$i)
		{
			$out.='<option'.(($page==$i)?' selected="selected"':'').'>'.$i.'</option>';
		}
		return $out.'</select> '.$lang['of'].$all;
	}
	else
	{
		for($i=1; $i<=$all; ++$i)
		{
			if($all > 9 && $i > 1)
			{
				if($i+2 < $page)
				{
					$i = $page-2;
				}
				elseif($i-2 > $page)
				{
					$i = $all;
				}
			}
			if($page==$i)
			{
				$out.='<a class="active">'.$i.'</a>';
			}
			else
			{
				$out.='<a href="'.$url.($i>1 ? $p.$i : '').'">'.$i.'</a>';
			}
		}
		return $out;
	}
}

/**
 * Converts textual emoticons into HTML images
 */
function emots($txt)
{
	static $emodata;
	include_once './cfg/emots.php';
	foreach($emodata as $x)
	{
		$txt = str_replace($x[2],'<img src="img/emo/'.$x[1].'" title="'.$x[0].'" alt="'.$x[2].'" />',$txt);
	}
	return $txt;
}

/**
 * Formats date
 */
function formatDate($x, $time=false)
{
	static $now,$yda,$tom;
	global $cfg,$lang;

	if(!$x) return $x;
	if($x[4] === '-') $x = strtotime($x.' GMT'); #Convert DATETIME to timestamp

	$diff = $_SERVER['REQUEST_TIME'] - $x;
	if($diff < 5941 && $diff >= 0)
	{
		return sprintf($lang['ago'], ceil($diff/60)); #X min ago (to 99)
	}
	elseif($diff > -5941 && $diff < 0)
	{
		return sprintf($lang['in'], ceil(-$diff/60)); #In X min
	}
	$date = strftime($cfg['date'], $x);

	#Today, yesterday, tomorrow
	if(!$now)
	{
		$now = strftime($cfg['date'], $_SERVER['REQUEST_TIME']);
		$yda = strftime($cfg['date'], $_SERVER['REQUEST_TIME'] - 86400);
		$tom = strftime($cfg['date'], $_SERVER['REQUEST_TIME'] + 86400);
	}
	if($now === $date)
	{
		$date = $lang['today'];
	}
	elseif($yda === $date)
	{
		$date = $lang['YDA'];
	}
	elseif($tom === $date)
	{
		$date = $lang['TOM'];
	}
	if($time)
	{
		return $date . strftime($cfg['time'], $x);
	}
	else
	{
		return $date;
	}
}

/**
 * Returns link to author profile
 * @deprecated
 */
function autor($x)
{
	global $db,$user;
	static $all;
	if(is_numeric($x))
	{
		if($x == UID)
		{
			$login = $user['login'];
		}
		else
		{
			if(!isset($all[$x]))
			{
				$all[$x] = $db->query('SELECT login FROM '.PRE.'users WHERE ID='.$x)->fetchColumn();
			}
			if($all[$x])
			{
				$login = $all[$x];
			}
			else return $x;
		}
		return '<a href="'.url('user/'.urlencode($login)).'">'.$login.'</a>';
	}
	else return $x;
}

/**
 * Protects against XSS, censorship
 */
function clean($val,$max=0,$wr=0)
{
	if($max) $val = substr($val,0,$max);
	if($wr)
	{
		static $words1,$words2;
		include_once './cfg/words.php';
		$val = str_replace($words1,$words2,$val);
	}
	return trim(htmlspecialchars($val, 2, 'UTF-8'));
}

/**
 * Writes log entry
 */
function event($type, $u=UID, $db=null)
{
	if(!$db) global $db;
	try
	{
		$q = $db->prepare('INSERT INTO '.PRE.'log (name,ip,user) VALUES (?,?,?)');
		$q->execute(array($type, $_SERVER['REMOTE_ADDR'], $u));
	}
	catch(Exception $e) {}
}

/**
 * Counts in database
 * @deprecated
 */
function dbCount($table)
{
	global $db;
	return (int)$db->query('SELECT COUNT(*) FROM '.PRE.$table)->fetchColumn();
}

/**
 * Loads panels from database or cache
 */
function panel()
{

}

/** Get choosen theme name for given skin */
function getTheme($skinName)
{
	if(isset($_COOKIE[$skinName]['CSS']) && ctype_alnum($_COOKIE[$skinName]['CSS']))
	{
		return $_COOKIE[$skinName]['CSS'];
	}
	return 'default';
}