<?php

#Get table name
function typeOf($x)
{
	static $data;
	switch($x)
	{
		case 1: return 'arts'; break;
		case 2: return 'files'; break;
		case 3: return 'imgs'; break;
		case 4: return 'links'; break;
		case 5: return 'news'; break;
		default: if(!$data) $data = parse_ini_file('cfg/types.ini',1); return $data[$x]['table'];
	}
}

#Get all users - FETCH_COLUMN
function authors(PDO $db=null)
{
	if(!$db) global $db;
	return $db->query('SELECT login FROM '.PRE.'users')->fetchAll(7);
}

#Refresh latest items cache
function Latest($lang=null)
{
	global $db,$cfg;
	include './cfg/latest.php';
	if(empty($cfg['newOn']) OR empty($cfg['newTypes'])) return;

	#Detect languages
	if($lang)
	{
		$lang = array($lang);
	} 
	else
	{
		foreach(scandir('./lang') as $x)
		{
			if($x[0] != '.' && is_dir('./lang/'.$x)) $lang[] = $x;
		}
	}

	#Parse cat types
	$data = parse_ini_file('cfg/types.ini',1);
	
	#Build cache for each language
	foreach($lang as $l)
	{
		$out = '';
		foreach($cfg['newTypes'] as $t=>$x)
		{
			$res = $db->query('SELECT i.'.(isset($data[$t]['get']) ? $data[$t]['get'] : 'ID').',
				i.name, c.name as cat, c.type FROM '.PRE.$data[$t]['table'].' i
				INNER JOIN '.PRE.'cats c ON i.cat=c.ID WHERE i.access=1 AND
				(c.access=1 OR c.access="'.$l.'") ORDER BY i.ID DESC LIMIT '.(int)$cfg['newNum']);
			$res -> setFetchMode(3);

			$got = '';
			$cur = &$data[$t];
			$url = isset($cur['name']) ? url($cur['name'].'/') : '';

			foreach($res as $x)
			{
				$got .= '<li><a href="'.$url.$x[0].'" title="'.$x[2].'">'.$x[1].'</a></li>';
			}
			if($got) $out .= '<h3>'.(isset($cur[$l]) ? $cur[$l] : $cur['en']).'</h3><ul class="latest">'.$got.'</ul>';
		}
		file_put_contents('cache/new-'.$l.'.php', $out, 2);
	}
}

#Refresh RSS cache
function RSS($id = null, PDO $db = null)
{
	if(!$db) global $db;
	$q = is_numeric($id) ? 'ID='.$id : 'auto=1';
	$all = $db->query('SELECT ID,name,dsc,url,lang,num FROM '.PRE.'rss WHERE '.$q)->fetchAll(3);
	foreach($all as $x)
	{
		require_once './lib/rss.php';
		$rss = new RSS;
		$rss -> title = $x[1];
		$rss -> desc  = $x[2];
		$rss -> link  = $x[3];
		$rss -> base  = URL;

		$q = $db->query('SELECT i.ID,i.name,i.date,i.txt,i.opt,c.name as cat FROM '.
		PRE.'news i JOIN '.PRE.'cats c ON i.cat=c.ID WHERE i.access=1 AND
		(c.access=1 OR c.access="'.$x[4].'") ORDER BY i.ID DESC LIMIT '.$x[5]);

		foreach($q as $item)
		{
			$rss->add( array(
				'ID'    => $item['ID'],
				'title' => $item['name'],
				'text'  => $item['opt'] & 1 ? nl2br($item['txt']) : $item['txt'],
				'cat'   => $item['cat'],
				'date'  => date('r', strtotime($item['date'].' UTC')),
				'url'   => URL . url('news/' . $item['ID'])
			));
		}
		$rss->save('rss/'.$x[0].'.xml');
	}
}

#Update item tags
function UpdateTags($id, $type, $in=null, $t=null)
{
	global $db,$cfg;
	if(empty($cfg['tags']) || !admit('TAG')) return;

	#Get item tags
	$res = $db->prepare('SELECT tag,num FROM '.PRE.'tags WHERE ID=? AND TYPE=? GROUP BY tag ORDER BY tag');
	$res -> execute(array($id, $type));
	$num = $res->fetchAll(12);
	$tag = array_keys($num);
	$new = array();

	#Get all tags
	$res = $db->query('SELECT tag,num FROM '.PRE.'tags GROUP BY tag ORDER BY num DESC,tag');
	$all = $res->fetchAll(12);

	#Update existing tags
	if(isset($in))
	{
		if($t) $db->beginTransaction();
		foreach($in as $x)
		{
			if(preg_match('/^[0-9\pL _.-]{1,50}$/u', $x))
			{
				$new[] = trim($x);
			}
		}
		$d = $db->prepare('DELETE FROM '.PRE.'tags WHERE ID=? AND TYPE=? AND tag=?');
		$i = $db->prepare('REPLACE INTO '.PRE.'tags (tag,ID,TYPE,num) VALUES (?,?,?,?)');
		$u = $db->prepare('UPDATE '.PRE.'tags SET num=? WHERE tag=?');

		#Delete old tags
		foreach(array_diff($tag,$new) as $x)
		{
			$d->execute(array($id, $type, $x));
			if($num[$x]>1) $u->execute(array($num[$x]-1, $x));
		}

		#Add new tags
		foreach(array_diff($new,$tag) as $x)
		{
			$i->execute(array($x, $id, $type, isset($all[$x]) ? $all[$x] : 1));
			if(isset($all[$x])) $u->execute(array($all[$x]+1, $x));
		}
		if($t) $db->commit();
		return $new;
	}
	foreach($all as $x=>$y)
	{
		$new[] = array($x, isset($num[$x]), $y);
	}
	return $new;
}

#Get or update tags with AJAX
function ajaxTags($id, $type)
{
	echo json_encode(UpdateTags($id, $type, $_SERVER['REQUEST_METHOD']=='POST' ? $_POST : null, 1));
}

#Refresh subcategories and path cache
function UpdateCatPath($cat)
{
	global $db;
	if(is_numeric($cat))
	{
		$cat = $db->query('SELECT ID,name,sc,lft,rgt FROM '.PRE.'cats WHERE ID='.$cat)->fetch(2);
	}
	$out = '';
	if($cat['sc'] != 0)
	{
		$res = $db->query('SELECT ID,name FROM '.PRE.'cats WHERE lft<'.$cat['lft'].
		' AND rgt>'.$cat['rgt'].' AND (access!=2 OR access!=3) ORDER BY lft');
		$res->setFetchMode(3);
		foreach($res as $c)
		{
			$out.= '<a href="'.url($c[0]).'">'.$c[1].'</a> &raquo; ';
		}
	}
	$out.= '<a href="'.url($cat['ID']).'">'.$cat['name'].'</a>';
	file_put_contents('cache/cat'.$cat['ID'].'.php', $out, 2);
	return $out;
}

#Update item count in category
function SetItems($id,$ile)
{
	global $db;
	static $new;
	$id  = (int)$id;
	$ile = (int)$ile;
	$ile = ($ile>0) ? '+'.$ile : '-'.$ile;

	#Get LFT, RGT, parent category
	$res = $db->query('SELECT sc,lft,rgt FROM '.PRE.'cats WHERE access!=2 AND access!=3 AND ID='.$id);
	if(!$cat = $res->fetch(3)) return;

	#Update local and total item count
	$db->exec('UPDATE '.PRE.'cats SET num=num'.$ile.', nums=nums'.$ile.' WHERE ID='.$id);

	#Update total item count in parent categories
	if($cat[0]) $db->exec('UPDATE '.PRE.'cats SET nums=nums'.$ile.
		' WHERE access!=2 AND access!=3 AND lft<'.$cat[1].' AND rgt>'.$cat[2]);
}

#List subcategories where user may manage content
function Slaves($type=0,$id=0,$o=null)
{
	global $db;
	$where = array();
	if(is_numeric($o)) $where[]='ID!='.$o;
	if(!IS_OWNER && !$where && !admit('+'))
	{
		$where[] = 'ID IN (SELECT CatID FROM '.PRE.'acl WHERE UID='.UID.')';
	}
	if($type!=0)
	{
		$where[] = 'type='.(int)$type;
	}
	$res = $db->query('SELECT ID,name,access,lft,rgt FROM '.PRE.'cats'.
		(($where)?' WHERE '.join(' AND ',$where):'').' ORDER BY lft');
	$depth = 0;
	$last = 1;
	$o = '';

	foreach($res as $cat)
	{
		if($last > $cat['rgt'])
			++$depth;

		elseif($depth>0 && $last+2!=$cat['rgt'] && $last+1!=$cat['lft'])
			$depth -= floor(($cat['lft']-$last)/2);

		if($depth < 0) $depth = 0;

		$last = $cat['rgt'];

		$o.='<option value="'.$cat['ID'].'"'.($id==$cat['ID'] ? ' selected="selected"' : '').
			' style="padding-left: '.$depth.'em' . ($cat['access']=='3' ?
			'; color: gray':'').'">'.$cat['name'].'</option>';
	}
	return $o;
}

#Count items for all categories
function CountItems()
{
	global $db;
	$cat = $db->query('SELECT ID,type,access,sc FROM '.PRE.'cats') -> fetchAll(3);
	$ile = count($cat);
	if($ile > 0)
	{
		for($i=0; $i<$ile; ++$i)
		{
			$id = $cat[$i][0];
			$num[$id] = dbCount(typeOf($cat[$i][1]).' WHERE cat='.$id.' AND access=1');
			$sub[$id] = $cat[$i][3];
			$total[$id] = $num[$id];
		}
		for($i=0; $i<$ile; $i++)
		{
			if($cat[$i][2] != 2 && $cat[$i][2] != 3)
			{
				$x = $cat[$i][3];
				while($x!=0 && is_numeric($x))
				{
					$total[$x] += $total[$cat[$i][0]];
					$x = $sub[$x];
				}
			}
		}
		$q = $db->prepare('UPDATE '.PRE.'cats SET num=?, nums=? WHERE ID=?');
		foreach($total as $k=>$x)
		{
			if(is_numeric($x) && is_numeric($num[$k]))
			{
				$q->execute(array($num[$k], $x, $k));
			}
		}
	}
}

#Rebuild category tree
function RTR($parent,$left,$db)
{
	$right = $left+1;
	$all = $db->query('SELECT ID FROM '.PRE.'cats WHERE sc='.$parent)->fetchAll(3);
	foreach($all as $row)
	{
		$right = RTR($row[0], $right, $db);
	}
	$db->exec('UPDATE '.PRE.'cats SET lft='.$left.', rgt='.$right.' WHERE ID='.$parent);
	return $right+1;
}
function RebuildTree(PDO $db=null)
{
	$left = 1;
	if(!$db) global $db;
	foreach($db->query('SELECT ID FROM '.PRE.'cats WHERE sc=0 ORDER BY type,name') as $x)
	{
		$left = RTR($x['ID'],$left,$db);
	}
}