<?php // News list
if(iCMS!=1) exit;

#Start from
if($st != 0) $st = ($page-1) * $cfg['newsNum'];

#Load news
$res = $db->query('SELECT n.*,login FROM '.PRE.'news n LEFT JOIN '.PRE.'users u ON
	n.author=u.ID WHERE n.'.$cats.' AND n.access=1 ORDER BY n.ID DESC LIMIT '.$st.','.$cfg['newsNum']);

#Check if user can edit items in category
$rights = admit($d,'CAT') ? true : false;

#Comments
$comm = $cat['opt']&2 && isset($cfg['ncomm']) ? true : false;

#Prepare URL
$userURL = url('user/');
$fullURL = url('news/');
$editURL = url('edit/5/');

$news = array();
$num = 0;

foreach($res as $n)
{
	#Apply emoticons
	if($n['opt']&2) $n['txt'] = emots($n['txt']);

	#Apply nl2br
	if($n['opt']&1) $n['txt'] = nl2br($n['txt']);

	$news[] = array(
		'title' => $n['name'],
		'date'  => formatDate($n['date']),
		'wrote' => $n['login'],
		'comm'  => $n['comm'],
		'img'   => $n['img'],
		'text'  => $n['txt'],

		#News URL
		'url' => $fullURL.$n['ID'],

		#Comments URL
		'comm_url' => $comm ? $fullURL.$n['ID'] : false,

		#Fulltext URL
		'full_url' => $n['opt']&4 ? $fullURL.$n['ID'] : false,

		#Edit URL
		'edit_url' => $rights ? $editURL.$n['ID'] : false,

		#Author URL
		'wrote_url' => $userURL.urlencode($n['login'])
	);
	++$num;
}

#Strony
if(isset($cfg['newsPages']) && $cat['num'] > $num)
{
	$pages = pages($page, $cat['num'], $cfg['newsNum'], url($d), 0, '/');
}
else
{
	$pages = null;
}

#Do szablonu
$view->add('cat_news', array(
	'news'  => &$news,
	'pages' => &$pages,
	'add'   => $rights ? url('edit/5') : null,
	'cats'  => url(isset($cfg['allCat']) ? 'cats' : 'cats/news'),
	'type'  => isset($cfg['allCat']) ? $lang['cats'] : $lang['news']
));
