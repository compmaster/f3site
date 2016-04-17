<?php /* Lista pozycji */
if(iCMS!=1 OR !IS_EDITOR) return;
require LANG_DIR.'content.php';
require './lib/categories.php';

#Action, category ID
$act = isset($URL[1]) ? (int)$URL[1] : 5;
$id  = isset($URL[2]) ? (int)$URL[2] : 0;

#Action
switch($act)
{
	case 5:
		$type = $lang['news'];
		$table = 'news';
		$table2 = 'newstxt';
		$href = url('news/');
		break;
	case 4:
		$type = $lang['links'];
		$href = url('link/');
		$table = 'links';
		$table2 = false;
		break;
	case 3:
		$type = $lang['images'];
		$href = url('img/');
		$table = 'imgs';
		$table2 = false;
		break;
	case 2:
		$type = $lang['files'];
		$href = url('file/');
		$table = 'files';
		$table2 = 'false';
		break;
	case 1:
		$type = $lang['arts'];
		$href = url('art/');
		$table = 'arts';
		$table2 = 'artstxt';
		break;
	default:
		if(!$data = parse_ini_file('./cfg/types.ini',1) OR !isset($data[$act])) return;
		$type = $data[$act][LANG];
		$table = $data[$act]['table'];
		$table2 = isset($data[$act]['table2']) ? $data[$act]['table2'] : false;
		$href = isset($data[$act]['name']) ? url($data[$act]['name'].'/') : '';
		unset($data);
}

#Page title
$view->title = $type;

#Mass change
if(isset($_POST['x']) && count($_POST['x'])>0)
{
	try
	{
		$q = admit('+') ? '' : ' AND cat IN (SELECT CatID FROM '.PRE.'acl WHERE type="CAT" AND UID='.UID.')';
		$ids = array();
		$db->beginTransaction();

		foreach($_POST['x'] as $x=>$n) $ids[] = (int)$x;
		$ids = join(',', $ids);

		if(isset($_POST['del']))
		{
			$db->exec('DELETE FROM '.PRE.$table.' WHERE ID IN ('.$ids.')'.$q);
			if($table2) $db->exec('DELETE FROM '.PRE.$table2.' WHERE ID IN ('.$ids.')'.$q);
			foreach($_POST['x'] as $x=>$n) UpdateTags((int)$x, $act, array());

			#Delete old comments
			$db->exec('DELETE FROM '.PRE.'comms WHERE TYPE='.$act.' AND CID NOT IN (
				SELECT ID FROM '.PRE.$table.')');
		}
		else
		{
			$ch = array();
			if($_POST['cat'] != 'N') $ch[] = 'cat='.(int)$_POST['cat'];
			if($_POST['pub'] != 'N') $ch[] = 'access='.(int)$_POST['pub'];

			if($ch = join(',', $ch))
			$db->exec('UPDATE '.PRE.$table.' SET '.$ch.' WHERE ID IN ('.$ids.')'.$q);
		}
		CountItems();
		Latest();
		$db->commit();
	}
	catch(PDOException $e)
	{
		$view->info($e->getMessage());
	}
	unset($q,$ids,$ch,$x);
}

#Param: category ID
if($id)
{
	$param = array('cat='.$id);
}
else
{
	$param = array();
}

#Rights
if(admit('+'))
{
	$join = '';
}
else
{
	$join = ' c LEFT JOIN '.PRE.'acl a ON c.cat=a.CatID';
	$param[] = 'a.UID='.UID;
}

#Page
if(isset($_GET['page']) && $_GET['page']>1)
{
	$page = $_GET['page'];
	$st = ($page-1)*30;
}
else
{
	$page = 1;
	$st = 0;
}

#Find
$find = empty($_GET['find']) ? '' : clean($_GET['find'],30);
if($find) $param[] = 'name LIKE '.$db->quote($find.'%');

#Params -> string
$param = $join . ($param ? ' WHERE '.join(' AND ',$param) : '');

#Count items
$total = dbCount($table.$param);

#Zero
if($total == 0 && !$find)
{
	header('Location: '.URL.url('edit/'.$act, $id ? 'catid='.$id : null));
	$view->info($lang['noc']);
	return 1;
}

#Prepare URL
$url = url('list/'.$act.'/'.$id);

#Get items
$res = $db->query('SELECT ID,name,access FROM '.PRE.$table.$param.
	' ORDER BY ID DESC LIMIT '.$st.',30');

$res -> setFetchMode(3);
$items = array(); 

#Prepare item
foreach($res as $i)
{
	switch($i[2])
	{
		case '1': $a = $lang['yes']; break;
		default: $a = $lang['no'];
	}
	$items[] = array(
		'num'  => ++$st,
		'title'=> $i[1],
		'id'   => $i[0],
		'on'   => $a,
		'url'  => $href.$i[0],
		'editURL' => url('edit/'.$act.'/'.$i[0])
	);
}

#Template
$view->add('list', array(
	'item'  => $items,
	'act'   => $act,
	'url'   => $url,
	'intro' => $lang['i'.$act],
	'type'  => $type,
	'cats'  => Slaves($act),
	'pages' => pages($page,$total,30,$url.'&find='.$find,1),
	'addURL' => url('edit/'.$act, $id ? 'catid='.$id : null),
	'catsURL'=> admit('C') ? url('cats/'.$act, null, 'admin') : false,
));
