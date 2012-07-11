<ul class="catlist"><?php if(iCMS!=1) exit;

$res = $db->query('SELECT ID,name FROM '.PRE.'cats WHERE sc=0
AND (access=1 OR access="'.LANG.'") ORDER BY name');

$cat = array();
$url = url('');
$res->setFetchMode(3);

foreach($res as $x)
{
	echo '<li><a href="'.$url.$x[0].'">'.$x[1].'</a></li>';
}

if(admit('C'))
{
	echo '<li><a href="'.url('editCat', '', 'admin').'">'.$lang['add'].'...</a></li>';
}

?></ul>