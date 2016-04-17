<?php
function cfg_escape($x)
{
	return str_replace( array('\\','\''), array('\\\\','\\\''), $x);
}

class Config
{
	public
		$var = 'cfg+',
		$in  = '',
		$file;

	function __construct($file)
	{
		$this->file = (strpos($file,'/')) ? $file : './cfg/'.$file.'.php';
	}

	#Dodaj dane z tablic
	private function loop(&$array)
	{
		foreach($array as $key=>$val)
		{
			if(is_array($val))
			{
				$this->in .= '\''.$key.'\'=>array(';
				$this->loop($val);
				$this->in .= '),';
			}
			else
			{
				if($val==='on') $val = 1;
				$this->in.='\''.$key.'\'=>'.((is_numeric($val))?$val:'\''.cfg_escape($val).'\'').',';
			}
		}
	}
	
	#Dodaj zmienn±
	function add($var,$val)
	{
		if(is_array($val))
		{
			$this->in .= '$'.$var.'=array(';
			$this->loop($val);
			$this->in .= ');';
		}
		else
		{
			$this->in.='$'.$var.'='.((is_numeric($val))?$val:'\''.cfg_escape($val).'\'').';';
		}
	}

	#Dodaj sta³±
	function addConst($n,$v)
	{
		$this->in.='define(\''.$n.'\','.((is_numeric($v))?$v:'\''.cfg_escape($v).'\'').');';
	}

	#Dodaj wiele warto¶ci
	function set(&$list)
	{
		$this->in .= '$'.$this->var.'=array(';
		$this->loop($list);
		$this->in .= ');';
	}

	#Zapisz
	function save(&$data=null)
	{
		if($data) $this->set($data);
		if(file_put_contents($this->file, '<?php '.$this->in, 2))
		{
			return true;
		}
		else
		{
			throw new Exception('Error: cannot write to '.$this->file);
			return false;
		}
	}
}