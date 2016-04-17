<?php if(iCMS!=1) exit;
require LANG_DIR.'profile.php';

if(!function_exists('password_hash'))
{
	require './lib/password.php';
}

#Redirect to...
$from = isset($_GET['from']) && ctype_alnum($_GET['from']) ? $_GET['from'] : '';
$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$to = empty($from) && $ref && strpos($ref,'login')===false ? $ref : URL;

#Bad referer
if($ref && strpos($ref,URL)!==0) { header('Location: '.URL); exit; }

#Logoff
if(UID)
{
	if(isset($URL[1]) && UID && $URL[1] === 'off')
	{
		session_destroy();
		if(isset($_COOKIE['authid']) && isset($_COOKIE['authkey']) && is_numeric($_COOKIE['authid']))
		{
			setcookie('authid','',time()-3600,PATH,null,0,1);
			setcookie('authkey','',time()-3600,PATH,null,0,1);
			$db->exec('DELETE FROM '.PRE.'sessions WHERE ID='.$_COOKIE['authid']);
		}
	}
	header('Location: '.$to);
	exit;
}

#Register
if(isset($_POST['reg']))
{
	header('Location: '.URL.url('account', $_POST['u'] ? 'u='.urlencode($_POST['u']) : ''));
	exit;
}

#You can authenticate over form and HTTP
if(!empty($_POST['u']) && !empty($_POST['p']))
{
	$login = clean($_POST['u'],30);
	$pass  = substr($_POST['p'],0,50);
}
elseif(!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']))
{
	$login = clean($_SERVER['PHP_AUTH_USER'],30);
	$pass  = substr($_SERVER['PHP_AUTH_PW'],0,50);
}
else
{
	$login = $pass = null;
}

#Try to authenticate
if($login && $pass)
{
	$res = $db->query('SELECT ID,login,pass,lv,adm,lvis,pms FROM '.PRE.'users WHERE login='.$db->quote($login));
	$u = $res->fetch(2);
	$res = null;

	#User does not exist
	if($u === null)
	{
		$view->error($lang['noauth']);
	}
	#Inactive account
	elseif($u['lv']=='0')
	{
		$view->error($lang['inactive']);
	}
	#Check password
	elseif(isset($u['pass'][59]) && password_verify($pass, $u['pass']))
	{
		#Regenerate session ID for better security
		session_destroy();
		session_start();
		session_regenerate_id(1);

		#Remember user
		if(isset($_POST['auto']))
		{
			if(function_exists('openssl_random_pseudo_bytes'))
			{
				$token = openssl_random_pseudo_bytes(32);
			}
			elseif(function_exists('random_bytes'))
			{
				$token = random_bytes(32);
			}
			else
			{
				$token = uniqid();
			}
			try
			{
				$hash = password_hash($token, PASSWORD_BCRYPT);
				$base64 = base64_encode($token);
				$ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
				$proxy = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_VALIDATE_IP);
				$q = $db->prepare('INSERT INTO '.PRE.'sessions (`UID`,`key`,`date`,`last`,`IP`,`proxy`)
					VALUES (:UID,:key,:date,:last,:IP,:proxy)');
				$q->execute(array(
					'UID'  => $u['ID'],
					'key'  => $hash,
					'date' => $_SERVER['REQUEST_TIME'],
					'last' => $_SERVER['REQUEST_TIME'],
					'IP'   => inet_pton($ip),
					'proxy'=> $proxy ? inet_pton($proxy) : null
				));
				setcookie('authid', $q->lastInsertId, 2147483647, PATH, null, 0, 1);
				setcookie('authkey', $base64, 2147483647, PATH, null, 0, 1);
			}
			catch(Exception $e)
			{
				$view->error('TODO: Wystąpił błąd podczas logowania. Spróbuj ponownie za chwilę.');
			}
		}
		unset($u['pass']);
		$_SESSION['userdata'] = $u;
		$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
		if(isset($_SESSION['online']))
		{
			unset($_SESSION['online']);
		}
		header('Location: '.$to.$from);
		$view->message(3, $from ? $from : $to, $u['login']);
	}
	else
	{
		$view->error($lang['noauth']);
	}
}
elseif(!UID)
{
	header('WWW-Authenticate: Basic');
	header('HTTP/1.0 401 Unauthorized');
}

require './cfg/account.php';

#Show form
$view->title = $lang['logme'];
$view->add('login', array(
	'register' => isset($cfg['reg']) ? url('account') : null
));