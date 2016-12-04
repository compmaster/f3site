<?php //Template compiler
class Compiler
{
	protected
		$data,
		$replace1,
		$replace2;
	public
		$removePHP, //Set TRUE to delete PHP code from templates
		$src,
		$cache,
		$file,
		$debug,
		$byteCode;

	#Compile everything at once if modified
	function examine()
	{
		if(!$f = opendir($this->src))
		{
			throw new Exception('ERROR: Cannot open scheme directory.');
		}
		while(false !== ($x = readdir($f)))
		{
			if(strpos($x,'.html'))
			{
				if(filemtime($this->src.$x) > @filemtime($this->cache.$x))
				{
					$this->compile($x);
				}
			}
		}
	}

	#Compile and save template to cache
	function compileAndCache($file, $src=null, $cache=null)
	{
	}
	
	#Compile template
	function compile($file, $src=null, $cache=null)
	{
		#Source and cache folder
		if(!isset($src)) $src = $this->src;
		if(!isset($cache)) $cache = $this->cache;

		#Add file extension
		if(!strpos($file, '.html')) $file .= '.html';

		#Check write rights
		if(!is_writable('cache'))
		{
			throw new Exception(sprintf('ERROR: You must CHMOD folder CACHE to 777 via FTP', $cache));
		}
		if(file_exists($cache.$file) && !is_writable($cache.$file))
		{
			throw new Exception(sprintf('ERROR: You must CHMOD all files inside %s to 666', $cache));
		}

		#Debug
		if($this->debug) echo 'Compiling file: '.$file.'... ';

		#Find template
		if(file_exists($src.$file))
		{
			$this->data = file_get_contents($src.$file);
		}
		elseif(file_exists(SKIN_DIR.$file))
		{
			$this->data = file_get_contents(SKIN_DIR.$file);
		}
		else
		{
			throw new Exception('ERROR: Template '.$file.' does not exist!');
		}

		#Store filename in object
		$this->file = $file;

		#Remove PHP (code stolen from PhpBB 3)
		if($this->removePHP)
		{
			$this->data = preg_replace( array(
				'#<([\?%])=?.*?\1>#s',
				'#<script\s+language\s*=\s*(["\']?)php\1\s*>.*?</script\s*>#s',
				'#<\?php(?:\r\n?|[ \n\t]).*?\?>#s'), '', $this->data);
		}

		#Process forms
		if(($pos = strpos($this->data, '<form')) !== false) $this->forms($pos);

		#Predefined constants and useless characters
		$in = array(
			'{CONTENT}', '{LEFT MENU}', '{RIGHT MENU}', '{LANG}', '{MENU}',
			'{TRAIL}', '{MAIN TITLE}', '{PAGE TITLE}', '{DESCRIPTION}',
			'{ROBOTS}', '{FOOTER}', '{TODAY}', '{USER PANEL}', "\t", "\n\n");

		$out = array(
			'<?php $this->main();?>',
			'<?php newnav(1);?>',
			'<?php newnav(2);?>',
			'<?php echo LANG;?>',
			'<?php echo $menu;?>',
			'<?php if($this->path)echo \'&raquo; \'.$this->path; elseif(isset($path))echo \'&raquo; \'.$path;?>',
			'<?php echo $cfg[\'title\'];?>',
			'<?php echo $this->title;?>',
			'<?php echo $this->desc;?>',
			'<?php echo $cfg[\'robots\'];?>',
			'<?php echo $cfg[\'footer\'];?>',
			'<?php echo strftime($cfg[\'now\']);?>',
			'<?php if(isset($cfg[\'userPanel\'])) include $this->find(\'top\',1);?>',
			'', "\n"
		);

		#Add RSS,CSS,JS tags into HEAD
		if($file == 'body.html' || $file == 'admin.html')
		{
			$in[] = '</head>';
			$out[] = '<?php echo $this->head ?></head>';
		}
		$this->data = str_ireplace($in, $out, $this->data);

		#Process loops
		while(($pos = strpos($this->data,'<!-- START')) !== false) $this->checkLoop($pos);

		#Variables, arrays, objects
		$in = array(
			'/\'\{([A-Za-z0-9_]+)\.([A-Za-z0-9:_ ]+)\}\'/', //lame but works
			'/\'\{([A-Za-z0-9_]+)\}\'/',
			'/\{this\.([A-Za-z0-9:_ ]+)\}/', //Obiekt $this
			'/\{([A-Za-z0-9_]+)\.([0-9]+)\}/', //Tablice numeryczne
			'/\{([A-Za-z0-9_]+)\.([A-Za-z0-9:_ ]+)\}/', //Tablice
			'/\{(nl2br|clean|htmlspecialchars|autor|formatDate): ([A-Za-z0-9_]+)\.([A-Za-z0-9:_ ]+)\}/',
			'/\{([A-Za-z0-9_]+)\-\>([A-Za-z0-9_]+)\}/', //Obiekty
			'/\{([A-Z0-9_]+)\}/', //Stałe
			'/\{([A-Za-z0-9_]+)\}/',
			'/\{(nl2br|clean|htmlspecialchars|autor|formatDate): ([A-Za-z0-9_]+)\}/',
			'#"url\(([\S]+):([\S]+)\)"#',
			'#"url\(([\S]+)\)"#',
			'/<!-- INCLUDE ([A-Za-z0-9_.]+) -->/');

		$out = array(
			'$\\1',
			'$\\1[\'\\2\']',
			'<?php echo $this->\\1;?>',
			'<?php echo $\\1[\\2];?>',
			'<?php echo $\\1[\'\\2\'];?>',
			'<?php echo \\1($\\2[\'\\3\']);?>',
			'<?php echo $\\1->\\2;?>',
			'<?php echo \\1;?>',
			'<?php echo $\\1;?>',
			'<?php echo \\1($\\2);?>',
			'"<?php echo url(\'$2\',\'\',\'$1\');?>"',
			'"<?php echo url(\'$1\');?>"',
			'<?php include $this->find(\'\\1\')?>');

		$this->data = preg_replace($in, $out, $this->data);

		#Process IFs
		$pos = 0;
		if(($pos = strpos($this->data,'<!-- IF')) !== false) $this->checkIF($pos);

		#Replace IF and ELSE
		$this->data = str_replace($this->replace1, $this->replace2, $this->data, $c1);
		$this->data = str_replace('<!-- END -->', '<?php } ?>', $this->data, $c2);
		$this->data = str_replace('<!-- ELSE -->', '<?php }else{?>', $this->data, $c3);

		#Compare IF and ELSE amount
		if($c1 != $c2 OR $c3 > $c1) { throw new Exception('IF condition is not closed in '.$this->file); }

		#Optimize PHP openings
		$this->data = str_replace( array('?><?php', "?>\n<?php"), '', $this->data);

		#Drop HTML comments
		$this->data = preg_replace('#\<!--(.*)--\>#Uis', '', $this->data);

		#Store as byte code or text
		if($this->byteCode && extension_loaded('bcompiler'))
		{
			if(file_put_contents($cache.'-temp.php', $this->data) !== false)
			{
				$f = fopen($cache.$file, 'w');
				bcompiler_write_header($f);
				bcompiler_write_file($f, $cache.'-temp.php');
				bcompiler_write_footer($f);
				fclose($f);
				unlink($cache.'-temp.php');

				if($this->debug) echo 'Saved as bytecode.<br />';
				return true;
			}
		}
		else
		{
			if(file_put_contents($cache.$file, $this->data) !== false)
			{
				if($this->debug) echo 'Done.<br />';
				return true;
			}
		}

		throw new Exception('ERROR: Cannot save template: '.$file);
	}

	#Loops
	protected function checkLoop($pos)
	{
		#Depth level
		static $lv = 1;

		#More loops inside?
		if(($pos2 = strpos($this->data, '<!-- START',$pos+9)) !== false)
		{
			if(strpos($this->data,'<!-- STOP',$pos) > $pos2)
			{
				++$lv; $this->checkLoop($pos2); --$lv;
			}
		}

		#Find array name and contents
		$frag = substr($this->data, $pos, strpos($this->data, '<!-- STOP -->')-$pos+13);
		$len  = strlen($frag);
		$end  = strpos($frag, ' -->');
		$var  = substr($frag, 11, $end-11);

		#Check variable correctness
		if(!ctype_alpha($var[0]) || !ctype_alnum($var))
		{
			throw new Exception(sprintf('Wrong variable name %s in START command in %s on line %d!',
			$var, $this->file, $pos));
		}

		#KEY and ITEM contants
		if(strpos($frag, '{KEY}'))
		{
			$frag = str_replace('\'{KEY}\'', '$key', $frag);
			$frag = str_replace('{KEY}', '<?php echo $key;?>', $frag);
			$key  = '$key=>&$i';
		}
		else
		{
			$key = '&$i';
		}
		$frag = str_replace('\'{ITEM}\'', '$i'.$lv, $frag);
		$frag = str_replace('{ITEM}', '<?php echo $i'.$lv.';?>', $frag);

		#Array name
		if($lv > 1)
		{
			$lv2 = 2;
			$full = 'i1';
			while($lv2 < $lv) $full .= '[$i'.++$lv2.']';
			$full .= '[\''.$var.'\']';
		}
		else
		{
			$full = $var;
		}

		#Replace loop definition
		$frag = str_replace('<!-- START '.$var.' -->', '<?php foreach($'.$full.' as '.$key.$lv.'){?>', $frag);

		#End loop
		$frag = substr_replace($frag, '<?php } ?>', -13);

		#Simple access to array elements
		$frag = preg_replace(
			array(
				'/\'\{([A-Za-z0-9_]+)\}\'/',
				'/\{([A-Za-z0-9_]+)\}/',
				'/\{(nl2br|clean|htmlspecialchars|autor|formatDate): ([A-Za-z0-9_]+)\}/',
				'/\<!-- IF ([A-Za-z0-9_])(.+) --\>/'),
			array(
				'$i'.$lv.'[\'\\1\']',
				'<?php echo $i'.$lv.'[\'\\1\'];?>',
				'<?php echo \\1($i'.$lv.'[\'\\2\']);?>',
				'<!-- IF i'.$lv.'.\\1\\2 -->'),
			$frag);

		#Update changes
		$this->data = substr_replace($this->data, $frag, $pos, $len);
	}

	#Forms
	protected function forms($pos)
	{
		$end  = strpos($this->data, '</form>')-$pos+7;
		$form = substr($this->data, $pos, $end);
		$in  = array();
		$out = array();

		#Default array
		preg_match('#f3:array="([A-Za-z0-9_].*?)"#i', $form, $array);

		#Isset mode for checkbox
		$isset = stripos($form, 'f3:mode="isset"') ? true : false;

		if($array OR strpos($form, 'f3:var'))
		{
			if($array) $form = str_replace(array(' '.$array[0], ' f3:mode="isset"'), '', $form);
			preg_match_all('#<input.*?type="(checkbox|radio)".*?>#i', $form, $inputs);

			#Get attibutes and join again
			foreach($inputs[0] as $tag)
			{
				preg_match_all('/\s?(\S+)="([^"]*)"/i', $tag, $list);
				$attr = array_combine($list[1],$list[2]);
				if(isset($attr['f3:var']))
				{
					$var = f3var($attr['f3:var']);
					array_pop($list[0]);
				}
				elseif($array && isset($attr['name']) && strpos($attr['name'],'[')===false)
				{
					$var = '$'.$array[1].'[\''.$attr['name'].'\']';
				}
				else continue;

				#Checkbox
				if($attr['type']==='checkbox' && !$isset)
				{
					$out[] = '<input'.join('',$list[0]).'<?php if('.$var.') echo \' checked="checked"\'?> />';
				}
				elseif($attr['type']==='checkbox')
				{
					$out[] = '<input'.join('',$list[0]).'<?php if(isset('.$var.')) echo \' checked="checked"\'?> />';
				}
				#Radio
				else
				{
					$out[] = '<input'.join('',$list[0]).'<?php if('.$var.'=='.((is_numeric($attr['value'])) ? $attr['value'] : '\''.$attr['value'].'\'').') echo \' checked="checked"\'?> />';
				}
				$in[] = $tag;
			}

			#Select - PREG_SET_ORDER
			preg_match_all('#<select name="([A-Za-z0-9].*?)"(.*?)>(.*?)</select>#si', $form, $inputs, 2);
			foreach($inputs as &$tag)
			{
				if(strpos($tag[3], '<option') == false) continue;
				if(strpos($tag[2], 'f3:var'))
				{
					preg_match('/f3:var="([A-Za-z0-9_.].*?)"/i', $tag[2], $var);
					if(isset($var[1])) $var = f3var($var[1]); else continue;;
				}
				elseif($array)
				{
					$var = '$'.$array[1].'[\''.$tag[1].'\']';
				}
				else continue;

				$in[]  = $tag[0];
				$out[] = '<select name="'.$tag[1].'"'. $tag[2] .'>' . preg_replace( array(
					'#<option value="([0-9]+)"(.*?)>(.*?)</option>#i',
					'#<option value="([^\'"]+)"([^<]*)>(.*?)</option>#i',
					'#<option>(.*?)</option>#i'
				), array(
					'<option value="\\1"<?php if('.$var.'==\\1)echo \' selected="selected"\'?>\\2>\\3</option>',
					'<option value="\\1"<?php if('.$var.'==\'\\1\')echo \' selected="selected"\'?>\\2>\\3</option>',
					'<option value="\\1"<?php if('.$var.'==\'\\1\')echo \' selected="selected"\'?>>\\1</option>'
				), $tag[3]) . '</select>';
			}
			$this->data = substr_replace($this->data, str_replace($in, $out, $form), $pos, $end);
		}
		if(($pos = strpos($this->data, '<form', $pos+7)) !== false) $this->forms($pos);
	}

	#Conditional statements
	protected function checkIF($pos)
	{
		#Fragment
		$frag = substr($this->data, $pos, strpos($this->data, ' -->', $pos)-$pos+4);
		$cond = substr($frag, 8, -4);

		#Macros
		$cond = str_replace(
			array('USER IS LOGGED IN', 'USER LOGGED IN', 'USER NOT LOGGED IN', 'USER IS GUEST', 'NOT '),
			array('UID', 'UID', '!UID', '!UID', '!'),
			$cond);
		
		#Remove unwanted characters
		$cond = str_replace( array(')', '(', '`', '@', '/', '\\'), '', $cond);
		$part = explode(' ', $cond);
		$cond = array();
		$str = 0;

		foreach($part as $x)
		{
			if(!ctype_alpha($x[0]) || strtoupper($x) === $x)
			{
				$cond[] = $x;
				continue;
			}
			else
			{
				$cond[] = f3var($x);
			}
		}

		#Add to further replace
		$this->replace1[] = $frag;
		$this->replace2[] = '<?php if('.join(' ', $cond).'){ ?>';

		#Other IF
		if($pos = strpos($this->data, '<!-- IF', $pos+1)) $this->checkIF($pos);
	}
}

#Format variable name
function f3var($x)
{
	$x = trim($x);
	if(!ctype_alpha($x[0])) return 'false';
	elseif(strpos($x, '.'))
	{
		$tmp  = explode('.', $x);
		return '$'.$tmp[0].'['. ((is_numeric($tmp[1])) ? $tmp[1] : '\''.$tmp[1].'\'').']';
	}
	else
	{
		return '$'.$x;
	}
}
