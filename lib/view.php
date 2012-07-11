<?php /* Template system */
class View
{
	public
		$title,
		$desc,
		$layout = 'index',
		$path,
		$check = true,
		$dir;
	private
		$head,
		$file,
		$data = array();

	#Add template and assign variables
	function add($file, $data=array())
	{
		$this->file[] = $file;
		$this->data += $data;
	}
	
	#Display templates
	function main()
	{
		global $lang;

		#Info texts
		if(isset($this->info))
		{
			$info = $this->info;
			include $this->find('info', 1);
			if(!$this->data) return;
		}

		#Create references to omit $this in templates
		extract($this->data, EXTR_REFS);

		#Compile and output
		if($this->file)
		{
			foreach($this->file as $f)
			{
				include $this->find($f);
			}
		}
		else
		{
			include $this->find('404');
		}
	}

	#Display main template
	function display()
	{
		global $lang,$cfg,$user;

		#Additional data
		if($this->data) extract($this->data, EXTR_REFS);

		#Set 410 Gone header if page does not exist
		if(empty($this->file))
		{
			$this->title = $lang['404'];
			header('Gone', true, 410);
		}
		include $this->find($this->layout);
	}
	
	#Set site layout
	function setLayout($tpl, $data = null)
	{
		if($data)
		{
			$this->data += $data;
		}
		$this->layout = $tpl;
	}

	#Add information
	function info($text, $links=array())
	{
		$this->info[] = array('text' => $text, 'links' => $links, 'class' => 'info');
	}

	#Add error
	function error($text, $links=array())
	{
		$this->info[] = array('text' => $text, 'links' => $links, 'class' => 'error');
	}

	#Add stylesheet
	function css($file)
	{
		if(!strpos($file, '/'))
		{
			$file = SKIN_DIR.$file;
		}
		$this->head .= '<link rel="stylesheet" type="text/css" href="'.$file.'">';
	}

	#Add JavaScript file
	function script($file)
	{
		$this->head .= '<script src="'.$file.'"></script>';
	}

	#Add RSS channel
	function rss($file, $title=null)
	{
		if(!is_array($file))
		{
			$file = array($file => $title);
		}
		foreach($file as $f=>$t)
		{
			$this->head .= '<link rel="alternate" type="application/rss+xml" href="'.$f.'" title="'.$t.'">';
		}
	}

	#Show message or error
	function message($info, $link=null)
	{
		require LANG_DIR.'special.php';
		if(isset($lang[$info]))
		{
			$info = vsprintf($lang[$info], array_slice(func_get_args(), 2));
		}
		require $this->find('message', 1);
		exit;
	}

	#Compile template
	function find($file)
	{
		if($this->dir && file_exists($this->dir . $file . '.html'))
		{
			$path  = $this->dir;
			$cache = 'cache/' . str_replace('/', '%', $this->dir);
		}
		elseif(file_exists(SKIN_DIR . $file . '.html'))
		{
			$path  = SKIN_DIR;
			$cache = 'cache/' . str_replace('/', '%', SKIN_DIR);
		}
		elseif(file_exists('style/system/' . $file . '.html'))
		{
			$path  = 'style/system/';
			$cache = 'cache/system%';
		}
		else
		{
			exit('Cannot find template: '.$file.'.html');
		}

		#Check modification date
		if($this->check && filemtime($path . $file . '.html') > @filemtime($cache. $file . '.html'))
		{
			static $compiler;
			if(!isset($compiler))
			{
				include_once './lib/compiler.php';
				$compiler = new Compiler;
			}
			try
			{
				$compiler->compile($file, $path, $cache);
			}
			catch(Exception $e)
			{
				exit($e->getMessage());
			}
		}
		return $cache . $file . '.html';
	}
}