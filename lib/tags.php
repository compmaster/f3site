<?php
function tags($id, $type, $mayTag=false)
{
	global $db,$cfg,$view;

	$may = admit('TAG');
	$url = url('tags/');
	$tag = array();

	$res = $db->prepare('SELECT tag,num FROM '.PRE.'tags WHERE ID=? AND TYPE=? GROUP BY tag ORDER BY tag');
	$res -> execute(array($id, $type));

	foreach($res as $x)
	{
		$tag[] = array(
			'tag' => $x['tag'],
			'url' => $url.$x['tag'],
			'num' => $x['num']
		);
	}

	if($tag || $may):

	$view->add('tag', array(
		'tag'      => $tag,
		'editTags' => $may,
		'urls'     => "['$url','request.php?go=tags&type=$type&id=$id']"
	));

	endif;
}
