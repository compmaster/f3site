<?php
if(iCMS!=1) exit;

#Rights
if(admit('BUGS'))
{
	$rights = 1;
}
else
{
	$rights = 0;
}

#Get issue - FETCH_ASSOC
$bug = $db->query('SELECT b.*, c.name as catName, c.rate FROM '.PRE.'bugs b INNER JOIN '.PRE.'bugcats c ON b.cat = c.ID WHERE b.ID='.$id.' AND (c.see=1 OR c.see="'.LANG.'")') -> fetch(2);

#Page title
$view->title = $bug['name'];

#Does not exist
if(!$bug)
{
	$view->set404();
	return;
}

#Not approved
if($bug['status']==5 && $bug['who']!=UID && !$rights)
{
	header('Location: '.URL.url('bugs'));
	return;
}

#BBCode
if(isset($cfg['bbcode']))
{
	require 'lib/bbcode.php';
	$bug['text'] = BBCode($bug['text']);
}

#Date, author
$bug['date'] = formatDate($bug['date'],1);
$bug['who']  = $bug['UID'] ? autor($bug['UID']) : $bug['who'];
$bug['text'] = nl2br(emots($bug['text']));
$bug['level'] = $lang['L'.$bug['level']];
$bug['status'] = $lang['S'.$bug['status']];

#Rate
if($bug['rate'] == 2)
{
	$bug['mark']  = $bug['pos'] ? $bug['pos'] : $lang['lack'];
	$bug['marks'] = $bug['neg'] ? $bug['neg'] : 0;
	$view->css(SKIN_DIR.'rate.css');
}

#Template
$view->add('view', array(
	'bug'   => &$bug,
	'edit'  => $rights || ($bug['poster']==UID && isset($cfg['bugsEdit'])) ? url('bugs/post/'.$id) : false,
	'hands' => $bug['rate'] == 1,
	'stars' => $bug['rate'] == 2,
	'catURL' => url('bugs/list/'.$bug['cat']),
	'mainURL' => url('bugs'),
	'canVote' => $bug['rate'] && (UID || isset($cfg['bugsVote'])),
	'editStatus' => $rights
));
