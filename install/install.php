<?php
class Installer
{
	public
		$sample, //Examples
		$title,  //Page title
		$urls,   //URL format
		$lang;   //Installer language
	static
		$urlMode;
	protected
		$catid = array(), //Start category IDs
		$need = array(),  //Wrong CHMOD
		$rss = array(),   //Channels
		$db;

	#Select browser language
	function __construct()
	{
		foreach(explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) as $x)
		{
			if(isset($x[2]))
			{
				$x = $x[0].$x[1];
			}
			if(ctype_alnum($x) && file_exists('./install/lang/'.$x.'.php'))
			{
				$this->lang = $x;
				return true;
			}
		}
		$this->lang = 'en';
	}

	#Start transaction
	function connect(&$data)
	{
		if($data['type'] == 'sqlite')
		{
			@touch($data['file']);
			@chmod($data['file'], 0600);
			$this->db = new PDO('sqlite:'.$data['file']);
		}
		else
		{
			$this->db = new PDO('mysql:host='.$data['host'].';dbname='.$data['db'],$data['user'],$data['pass']);
			$this->db->exec('SET CHARACTER SET "utf8"');
			$this->db->exec('SET NAMES "utf8"');
		}
		$this->db->setAttribute(3,2); //Exceptions
		$this->db->beginTransaction();
	}

	#Load SQL file - create tables
	function load($file)
	{
		try
		{
			execSQL($file,$this->db,PRE);
		}
		catch(PDOException $e)
		{
			throw new Exception($e->getMessage().'<pre>'.$e.'</pre>');
		}
	}

	#Install language $x
	function setupLang($x=null)
	{
		static $c, $m, $n, $i, $r, $lft, $db;
		if(!$x) $x = $this->lang;
		require './install/lang/'.$x.'.php';

		#Prepare queries
		if(!$c)
		{
			$lft = 0;
			$db = $this->db;
			$m = $db->prepare('INSERT INTO '.PRE.'menu (seq,text,disp,menu,type,value) VALUES (?,?,?,?,?,?)');
			$n = $db->prepare('INSERT INTO '.PRE.'news (cat,name,txt,date,author,access) VALUES (?,?,?,?,?,?)');
			$i = $db->prepare('INSERT INTO '.PRE.'mitems (menu,text,type,url,seq) VALUES (?,?,?,?,?)');
			$r = $db->prepare('INSERT INTO '.PRE.'rss (name,url,lang) VALUES (?,?,?)');
			$c = $db->prepare('INSERT INTO '.PRE.'cats (name,access,type,num,nums,opt,lft,rgt)
			VALUES (?,?,?,?,?,?,?,?)');
		}

		#Set up home page
		$c->execute(array($lang['main'], $x, 5, 1, 1, 6, ++$lft, ++$lft));
		$catID = $db->lastInsertId();
		$this->catid[$x] = $catID;

		#Sample categories
		if($this->sample)
		{
			$c->execute(array($lang['arts'], $x, 1, 0, 0, 15, ++$lft, ++$lft));
			$c->execute(array($lang['files'], $x, 2, 0, 0, 15, ++$lft, ++$lft));
			$c->execute(array($lang['foto'], $x, 3, 0, 0, 15, ++$lft, ++$lft));
			$c->execute(array($lang['links'], $x, 4, 0, 0, 15, ++$lft, ++$lft));
			$r->execute(array($lang['rss'], URL, $x));
			$this->rss[$x] = array($db->lastInsertId() => $lang['rss']);
		}

		#Menu
		$m->execute(array(1, 'Menu', $x, 1, 3, null));
		$menuID = $db->lastInsertId();
		$m->execute(array(3, $lang['cats'], $x, 1, 2, './mod/panels/cats.php'));
		$m->execute(array(4, $lang['pages'], 2, 1, 2, './mod/panels/pages.php'));
		$m->execute(array(5, $lang['new'], $x, 2, 2, './mod/panels/new.php'));
		$m->execute(array(6, $lang['poll'], $x, 2, 2, './mod/panels/poll.php'));
		$m->execute(array(7, $lang['stat'], $x, 2, 2, './mod/panels/online.php'));

		#First NEWS
		$n->execute(array($catID, $lang['1st'], $lang['NEWS'], gmdate('Y-m-d H:i:s'), 1, 1));

		#Menu links
		$i->execute(array($menuID, $lang['main'], 1, '.', 1));
		$i->execute(array($menuID, $lang['arch'], 2, 'archive', 2));
		$i->execute(array($menuID, $lang['links'], 2, 'cats/4', 3));
		$i->execute(array($menuID, $lang['foto'], 2, 'cats/3', 4));
		$i->execute(array($menuID, $lang['users'], 2, 'users', 5));
		$i->execute(array($menuID, $lang['group'], 2, 'groups', 6));
	}

	#Install all languages
	function setupAllLang()
	{
		foreach(scandir('lang') as $dir)
		{
			if($dir[0]!='.' && file_exists('install/lang/'.$dir.'.php'))
			{
				$this->setupLang($dir);
			}
		}
	}

	#Create user
	function admin($login, $pass)
	{
		$u = $this->db->prepare('REPLACE INTO '.PRE.'users (ID,login,pass,lv,regt) VALUES (?,?,?,?,?)');
		$u->execute(array(1, $login, md5($pass), 4, $_SERVER['REQUEST_TIME']));
	}

	#Save home categories IDs and finish
	function commit(&$data)
	{
		$cfg = array();
		Installer::$urlMode = $this->urls;

		#Content options - category IDs
		foreach($this->catid as $lang => $id)
		{
			$cfg['start'][$lang] = $id;
		}
		require './cfg/content.php';
		$o = new Config('content');
		$o->save($cfg);

		$cfg = array();
		require './cfg/main.php';

		#Get Sblam! key
		if(function_exists('fsockopen'))
		{
			$key = @file_get_contents('http://sblam.com/keygen.html');
			$cfg['captcha'] = 1;
			$cfg['sbKey'] = $key ? $key : NULL;
		}
		else
		{
			$cfg['captcha'] = 0;
			$cfg['sbKey'] = NULL;
		}

		#Main options - page title and URL format
		$cfg['title'] = $this->title;
		$cfg['RSS'] = $this->rss;

		$o = new Config('main');
		$o->add('cfg', $cfg);
		$o->save();

		#Database access - db.php
		$this->buildConfig($data);

		#Rebuild polls
		if(file_exists('./mod/polls'))
		{
			include './mod/polls/poll.php';
			RebuildPoll(null, $this->db);
		}

		#Sort categories
		include './lib/categories.php';
		RebuildTree($this->db);
		RSS(null, $this->db);

		#Create menu cache
		include './lib/mcache.php';
		RenderMenu($this->db);

		#Finish :)
		$this->db->commit();
	}

	#Create database access configuration file
	function buildConfig(&$data)
	{
		$f = new Config('./cfg/db.php');
		$f->add('db_db', $data['type']);
		$f->add('db_d', $data['file'] ? $data['file'] : $data['db']);
		$f->addConst('PRE', PRE);
		$f->addConst('PATH', PATH);
		$f->addConst('URL', URL);
		$f->addConst('NICEURL', $this->urls);

		#Only for MySQL
		if($data['type'] == 'mysql')
		{
			$f->add('db_h', $data['host']);
			$f->add('db_u', $data['user']);
			$f->add('db_p', $data['pass']);
		}
		return $f->save();
	}

	#Find skins
	function getSkins($selected)
	{
		$skins = '';
		foreach(scandir('style') as $x)
		{
			if($x[0]!='.' && file_exists('./style/'.$x.'/body.html'))
			{
				$skins .= '<option'.($selected==$x ? ' selected' : '').'>'.$x.'</option>';
			}
		}
		return $skins;
	}

	#Find languages
	function getLangs()
	{
		$data = array();
		foreach(scandir('lang') as $dir)
		{
			if($dir[0]!='.' && file_exists('install/lang/'.$dir.'.php'))
			{
				$data[] = $dir;
			}
		}
		return $data;
	}
	
	#Detect default URL format
	function urls()
	{
		if(function_exists('apache_get_modules') && file_exists('.htaccess'))
		{
			if(in_array('mod_rewrite', apache_get_modules()))
			{
				return 1;
			}
		}
		return 3;
	}

	#Detect CHMOD
	function chmods()
	{
		$table = array(
			'cache',
			'cfg',
			'rss',
			'img/user',
		);
		if(file_exists('cfg/db.db') && !is_writable('cfg/db.db'))
		{
			@chmod('cfg/db.db', 0600);
		}
		foreach($table as $folder)
		{
			if(!is_writable($folder) || !is_readable($folder))
			{
				@chmod($folder, 0777);
				if(!is_writable($folder))
				{
					$this->need[] = array(
						'file' => $folder,
						'good' => '777',
						'bad'  => substr(sprintf('%o', fileperms($folder)), -3)
					);
				}
			}
			foreach(scandir($folder) as $file)
			{
				$path = $folder.'/'.$file;
				if($file[0]!='.' && $file[0]!='0' && $file!='index.htm' && (!is_writable($path) || !is_readable($path)))
				{
					if(is_dir($path))
					{
						@chmod($path, 0777);
						$chmod = '777';
					}
					else
					{
						@chmod($path, 0666);
						$chmod = '666';
					}
					if(!is_writable($path))
					{
						$this->need[] = array(
							'file' => $path,
							'good' => $chmod,
							'bad'  => substr(sprintf('%o', fileperms($path)), -3)
						);
					}
				}
			}
		}
		return empty($this->need);
	}

	#Get CHMOD table
	function buildChmodTable()
	{
		return $this->need;
	}
}

#Build URL address
function url($x)
{
	switch(Installer::$urlMode)
	{
		case 1: return $x; break;
		case 2: return 'index.php/' . $x; break;
		default: return '?go=' . $x;
	}
}