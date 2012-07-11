<?php if(iCMS!=1) exit;

#Action: show tagged items
if(isset($URL[1]) && !isset($URL[1][31]))
{
	$q = $db->prepare('SELECT TYPE,ID FROM '.PRE.'tags WHERE tag=?');
	$q->bindValue(1, $URL[1]);
	$q->execute();

	$ini = parse_ini_file('cfg/types.ini', true);
	$all = $in = array();

	# PDO::FETCH_GROUP | PDO::FETCH_COLUMN
	foreach($q->fetchAll(65543) as $id=>$x)
	{
		switch($id)
		{
			case 4:
				$col = '"link",ID,name,dsc,' . (empty($cfg['linkFull']) ? ',url' : '');
				$tab = 'links';
				break;
			case 5:
				$col = '"news",ID,name,txt';
				$tab = 'news';
				break;
			case 102:
				$col = '"page",ID,name,SUBSTR(text,255)';
				$tab = 'pages';
				break;
			default:
				if(empty($ini[$id])) continue;
				$col = sprintf('\'%s\',ID,name,dsc', $ini[$id]['name']);
				$tab = $ini[$id]['table'];
		}
		$in[] = 'SELECT '.$col.' FROM '.PRE.$tab.' WHERE access=1 AND ID IN('.join(',',$x).')';
	}

	#Sort items of all genre by name
	$q = $db->query(join(' UNION ALL ', $in).' ORDER BY name');
	$q->setFetchMode(3);

	#Prepare item description - strip tags, shorten, etc.
	foreach($q as $x)
	{
		if($x[3])
		{
			$x[3] = strip_tags($x[3]);
			if(isset($x[3][200]) && ($pos = strpos($x[3], ' ', 180)))
			{
				$x[3] = substr($x[3], 0, $pos). '...';
			}
		}
		$all[] = array(
			'title' => $x[2],
			'desc'  => empty($x[3]) ? '' : emots($x[3]),
			'url'   => isset($x[4]) ? $x[4] : url($x[0].'/'.$x[1])
		);
	}

	#Prepare template
	$view->title = clean($URL[1]);
	$view->add('tags', array('item'=>&$all, 'tag'=>false, 'tags'=>url('tags')));
}
else
{
	$view->title = $lang['tags'];

	#Action: show tag cloud
	$res = $db->query('SELECT tag, num FROM '.PRE.'tags GROUP BY tag ORDER BY tag LIMIT 30');
	$tag = $res->fetchAll(12); //PDO::FETCH_KEY_PAIR

	#32 - max font size [px]
	#12 - min font size [px]
	if(!$tag) return;

	#Array edges
	$all = array_values($tag);
	$max = max($all);
	$min = min($all);
	$all = array();

	#Font size amplitude
	$spread = $max - $min;
	if($spread == 0) $spread = 1;

	#Font size step
	$step = 20 / $spread;

	#Build tag cloud
	foreach($tag as $key => $num)
	{
		$all[] = array(
			'title' => $key,
			'num'   => $num,
			'url'   => url('tags/'.$key),
			'size'  => round(($num - $min) * $step) + 13
		);
	}
	$view->add('tags', array('tag' => &$all, 'item' => false));
}
