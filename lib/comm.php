<?php #Show comments
function comments($id, $type=5, $mayPost=true, $url='')
{
	global $db,$cfg,$view,$URL;

	#Page division
	if($cfg['commNum'])
	{
		#Select page
		if(isset($_GET['page']) && $_GET['page']>1)
		{
			$page = $_GET['page'];
			$st = ($page-1)*$cfg['commNum'];
		}
		else
		{
			$page = 1;
			$st = 0;
		}
		if(!$url)
		{
			$url = url($URL[0].'/'.$id);
		}
		$total = dbCount('comms WHERE TYPE='.$type.' AND CID='.$id);
		$CP = ($total > $cfg['commNum']) ? pages($page,$total,$cfg['commNum'],$url) : null;
	}
	else
	{
		$total = null;
		$CP = null;
	}

	$comm = array();

	#May post, edit or delete
	$mayPost &= UID || isset($cfg['commGuest']);
	$mayEdit = admit('CM');
	$mayDel  = $mayEdit || $type == 100 && $id == UID;
	$comURL  = url('comment/');
	$modURL  = url('moderate/');
	$userURL = url('user/');

	#Get from database
	if($total !== 0)
	{
		$res = $db->query('SELECT c.ID,c.access,c.name,c.author,c.ip,c.date,c.UA,c.text,u.login,u.photo,u.mail
			FROM '.PRE.'comms c LEFT JOIN '.PRE.'users u ON c.UID!=0 AND c.UID=u.ID
			WHERE c.TYPE='.$type.' AND c.CID='.$id.
			(($mayEdit) ? '' : ' AND c.access=1').
			(($cfg['commSort']==2) ? '' : ' ORDER BY c.ID DESC').
			(($total) ? ' LIMIT '.$st.','.$cfg['commNum'] : ''));

		$res->setFetchMode(3);

		#BBCode
		if(isset($cfg['bbcode'])) include_once './lib/bbcode.php';

		foreach($res as $x)
		{
			$comm[] = array(
				'text' => nl2br(emots( isset($cfg['bbcode']) ? BBCode($x[7]) : $x[7] )),
				'date' => formatDate($x[5],1),
				'title'=> $x[2],
				'nick' => $x[8] ? $x[8] : $x[3],
				'ip'   => $mayEdit ? $x[4] : null,
				'edit' => $mayEdit ? $comURL.$x[0] : false,
				'del'  => $mayDel ? $comURL.$x[0] : false,
				'agent' => $x[6],
				'accept' => $mayEdit && $x[1]!=1 ? $comURL.$x[0] : false,
				'findIP' => $mayEdit ? $modURL.$x[4] : false,
				'profile' => $x[8] ? $userURL.urlencode($x[8]) : false,
				'photo' => empty($cfg['commPhoto']) ? false : (
					$x[9] ? $x[9] : ($cfg['commPhoto']==2 ?
					PROTO.'www.gravatar.com/avatar/'.md5(strtolower($x[10]?$x[10]:$x[4])).'?d='.$cfg['gdef'] : false
				))
			);
		}
		$res = null;
	}

	#Prepare template
	$data['comment'] =& $comm;
	$data['parts'] =& $CP;

	#Highlight code
	$data['color'] = isset($cfg['colorCode']);

	#May comment
	if($mayPost)
	{
		$data['quickreply'] = true;
		$data['form'] = true;
		$data['url'] = $comURL.$id.'/'.$type;
		$data['mustLogin'] = false;
		$_SESSION['CV'][$type][$id] = true;
	}
	else
	{
		$data['quickreply'] = false;
		$data['mustLogin'] = true;
	}

	#Assign to template
	$view->add('comments', $data);
}
