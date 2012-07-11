<?php
if(iCMS!=1) exit;
if(!isset($cfg['mailh'])) require './cfg/mail.php';

function sanitize($v)
{
	return str_replace(array("\n","\r",'<','>',';',','), '', $v);
}

class Mailer
{
	public
		$from,
		$mailFrom,
		$topic = '[no topic]',
		$siteTitle,
		$text,
		$url,
		$HTML = 1,
		$exceptions;
	private
		$bcc = array(),
		$header = array(),
		$method = 0,
		$debug = 0, #To display commands, set $debug to 1
		$o;

	#Send command
	private function cmd($c)
	{
		if(!$this->o) return 0;

		fwrite($this->o, $c."\r\n");
		$reply = fread($this->o, 199);

		if($this->debug)
			echo '&rarr; '.nl2br(htmlspecialchars($c)."\n&larr; ".htmlspecialchars($reply)).'<br />';

		return $reply;
	}

	#Sender
	function setSender($name,$mail=false)
	{
		$this->from = sanitize($name);
		if($mail) $this->mailFrom = sanitize($mail);
	}

	#Add header
	function addHeader($h)
	{
		$this->header[] = $h."\r\n";
	}

	#Add BCC
	function addBlindCopy($adr)
	{
		$this->bcc[] = sanitize($adr);
	}

	#Send E-mail
	function sendTo($name,$adr)
	{
		#Join headers
		$h = $this->header ? join("\r\n", $this->header) . "\r\n" : '';
		$h.= 'From: ' . $this->from . '<'.$this->mailFrom . ">\r\n";

		#HTML
		if($this->HTML)
		{
			$h.='Content-type: text/html; charset=utf-8'."\r\n";
			$this->text = nl2br($this->text);
		}

		#BBC
		if(count($this->bcc)>0) $h.='Bcc: '.join(',', $this->bcc)."\r\n";

		#Recipient
		$adr  = sanitize($adr);
		$name = sanitize($name);

		#Replacements
		$this->text = str_replace(
			array('%to%', '%to.email%', '%siteurl%', '%from%'),
			array($name, $adr, '<a href="'.$this->url.'">'.$this->siteTitle.'</a>', $this->from),
			$this->text);
		
		#Via SMTP
		if($this->method == 'SMTP')
		{
			#From
			$this->cmd('MAIL FROM:<'.$this->mailFrom.'>');

			#To
			$this->cmd('RCPT TO:<'.$adr.'>');
			foreach($this->bcc as $m) $this->cmd('RCPT TO:<'.$m.'>');

			#Data
			$this->cmd('DATA');

			#Headers and text
			$this->cmd('Subject: '.sanitize($this->topic)."\r\n" . 'To: '.$name."\r\n" . $h."\r\n" . $this->text);

			#Data
			$this->cmd('data');

			#Send (250 = success)
			$ok = strpos( $this->cmd('.'), '250' ) !== false ? true : false;

			#Reset
			$this->cmd('RSET'); return $ok;
		}

		#Via mail()
		elseif($this->method=='MAIL')
		{
			#Send
			return mail($name.' <'.$adr.'>', $this->topic, $this->text, $h);
		}
	}

	#Reset BCC and headers
	function reset()
	{
		$this->bcc = array();
		$this->header = array();
	}

	#Start
	function __construct()
	{
		global $cfg;
		$this->url = $cfg['adr'];
		$this->siteTitle = $cfg['title'];

		#Connect to SMTP
		if($cfg['mailh']==2 && isset($cfg['mailon']))
		{
			$this->method='SMTP';
			$i=0;

			#Try 3 times
			while($i<3 && !$this->o)
			{
				$this->o = fsockopen($cfg['smtp'],$cfg['mailport'],$no,$str,20);
				++$i;
				usleep(500000); #Wait half second
			}
			if(!$this->o) throw new Exception('Cannot send e-mail. Response: '.$str.' ('.$no.')');

			#Hello
			$this->cmd('EHLO '.$cfg['smtp']);

			#Password
			$this->cmd('AUTH LOGIN');
			$this->cmd(base64_encode($cfg['smtpl']));
			$this->cmd(base64_encode($cfg['smtph']));
		}

		#Mail()
		elseif(isset($cfg['mailon'])) $this->method='MAIL';

		#Default sender
		$this->setSender($cfg['title'], $cfg['mail']);
	}

	#Disconnect
	function __destruct()
	{
		if($this->o) { $this->cmd('QUIT'); fclose($this->o); }
	}
}