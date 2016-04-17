<?php /* Klasa wspomaga operacje: zmieñ ilo¶æ pozycji w kategoriach, sprawd¼ prawa... */
class Saver
{
	public
		$id = 0,
		$data = array(),
		$old = array(),
		$old_cat;

	function __construct(&$data, $id, $table, $cols='cat,access,author')
	{
		global $db,$lang;
		if($id) //Stare dane
		{
			$this->old = $db->query('SELECT '.$cols.' FROM '.PRE.$table.' WHERE ID='.$id)->fetch(2);
			$this->old_cat = $this->old ? $this->old['cat'] : null;
		}
		else
		{
			$this->old_cat = &$data['cat'];
		}

		//ID, dane
		$this->id = $id;
		$this->data =& $data;

		//Dane istniej±?
		if($this->old_cat !== null)
		{
			$db->beginTransaction();
		}
		else
		{
			throw new Exception($lang['noex']);
		}

		//Prawa do kategorii
		if(!admit($this->data['cat'], 'CAT') || ($this->data['cat'] != $this->old_cat && !admit($this->old_cat, 'CAT')))
		{
			throw new Exception($lang['nor']); //Skoñcz
		}

		//Autor
		if(isset($data['author'])) $data['author'] = $this->authorID($data['author']);
	}

	//Koniec
	function apply()
	{
		//Ilo¶æ pozycji w kategorii
		if($this->id)
		{
			if($this->old_cat != $this->data['cat'])
			{
				SetItems($this->old_cat,-1);
				if($this->data['access']==1) SetItems($this->data['cat'],1);
			}
			else
			{
				if($this->old['access'] > $this->data['access']) SetItems($this->old_cat,1);
				if($this->old['access'] < $this->data['access']) SetItems($this->old_cat,-1);
			}
		}
		else
		{
			if($this->data['access']==1) SetItems($this->data['cat'],1);
		}

		#Najnowsze
		Latest();

		#OK
		try
		{
			global $db;
			$db->commit(); return true;
		}
		catch(PDOException $e)
		{
			throw new Exception($e->errorInfo[2]);
		}
	}

	#Sprawd¼, czy autor jest zarejestrowany - $x musi byæ zabezpieczony!
	function authorID($x)
	{
		global $db,$user;
		if($x == $user['login'])
		{
			return UID;
		}
		if(is_numeric($x))
		{
			return $x;
		}
		if(isset($x[2]) && !isset($x[31]))
		{
			if($id = $db->query('SELECT ID FROM '.PRE.'users WHERE login='.$db->quote($x))->fetchColumn())
			{
				$x = $id;
			}
		}
		return $x;
	}
}