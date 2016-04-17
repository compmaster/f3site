<?php /* API prywatnych wiadomo�ci - pami�taj o obj�ciu operacji transakcj� bazy danych */
class PM
{
	public
		$to,
		$text,
		$topic,
		$thread = 0,
		$sender = UID, //Tylko ID
		$status = 1,
		$exceptions;

	#Sprawd� poprawno�� danych - u�yj $errRef, je�li ju� zdefiniowa�e� tablic� b��d�w
	function errors(&$errRef = null)
	{
		global $lang;

		#Tu zapisuj b��dy
		if($errRef && is_array($errRef))
		{
			$error = &$errRef;
		}
		else
		{
			$error = array();
		}

		#Tre�� za d�uga?
		if(isset($this->text[20001])) $error[] = $lang['pm18'];

		#Skrzynka pe�na?
		if($this->inboxFull($this->to)) $error[] = $lang['pm21'];

		#Wyrzu� b��d
		if($error)
		{
			if($this->exceptions)
			{
				throw new Exception($error);
			}
			elseif($errRef)
			{
				return true;
			}
			else
			{
				return $error;
			}
		}
		return false; //FALSE gdy brak b��d�w
	}

	#Wy�lij wiadomo��
	function send($force = false)
	{
		global $db;

		#Odbiorca
		if(!is_numeric($this->to)) $this->to = userID($this->to);

		#Gdy s� b��dy...
		if(!$force && $this->errors()) return false;

		#Zapytanie
		$q = $db->prepare('INSERT INTO '.PRE.'pms (th,topic,usr,owner,st,date,txt)
		VALUES (:th,:topic,:usr,:owner,:st,:date,:txt)');

		$q->execute( array(
			'owner' => $this->status > 2 ? $this->sender : $this->to,
			'usr'   => $this->status < 3 ? $this->sender : $this->to,
			'topic' => $this->topic,
			'txt'   => $this->text,
			'th'    => $this->thread,
			'st'    => $this->status,
			'date'  => $_SERVER['REQUEST_TIME']
		));

		#Zwi�ksz liczb� nieodebranych wiadomo�ci
		if($this->status === 1) $db->exec('UPDATE '.PRE.'users SET pms=pms+1 WHERE ID='.$this->to);
	}

	#Zapisz wiadomo�� (domy�lnie - kopia robocza)
	function update($id, $force=false)
	{
		global $db;

		#Odbiorca
		if(!is_numeric($this->to)) $this->to = userID($this->to);

		$q = $db->prepare('UPDATE '.PRE.'pms SET th=:th, topic=:topic, usr=:usr,
		owner=:owner, st=:st, date=:date, txt=:txt WHERE owner=:you AND st=3 AND ID=:id');

		$q->execute( array(
			'you'   => UID,
			'id'    => $id,
			'th'    => $this->thread,
			'owner' => $this->status > 2 ? $this->sender : $this->to,
			'usr'   => $this->status < 3 ? $this->sender : $this->to,
			'topic' => $this->topic,
			'txt'   => $this->text,
			'st'    => $this->status,
			'date'  => $_SERVER['REQUEST_TIME']
		));

		#Zwi�ksz liczb� nieodebranych wiadomo�ci
		if($this->status === 1) $db->exec('UPDATE '.PRE.'users SET pms=pms+1 WHERE ID='.$this->to);
	}

	#Czy skrzynka jest pe�na?
	function inboxFull($user=null)
	{
		global $cfg;
		if(!$user) $user = $this->to;
		return dbCount('pms WHERE owner='.(int)$user) >= $cfg['pmLimit'];
	}

	#Usu� wiadomo�ci
	function delete($id)
	{
		global $db;
		if(is_array($id))
		{
			$in = array();
			foreach($id as $x) $in[] = (int)$x;
		}
		elseif(is_numeric($id))
		{
			$in = array($id);
		}
		else return false;

		$db->exec('DELETE FROM '.PRE.'pms WHERE ID IN ('.join(',', $in).')');
		return $db->rowCount(); //Zwr�� ilo�� usuni�tych wiadomo�ci
	}
}

#Pobierz ID u�ytkownika
function userID($login)
{
	global $db,$lang;

	if($id = (int)$db->query('SELECT ID FROM '.PRE.'users WHERE login='.$db->quote($login))->fetchColumn())
	{
		return $id;
	}
	else
	{
		throw new Exception($lang['pm20']);
	}
}