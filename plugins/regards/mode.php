<?php
if(iCMS!=1) exit;
switch($_GET['co'])
{
 #Zapisz
 case 'd1':
  if($_SESSION['f3pzdt']>time())
  {
   exit('B³±d!');
  }
  else
  {
   $pzd_od=Words(clean($_POST['pzd_od'],1,1,0));
   $pzd_dla=Words(clean($_POST['pzd_dla'],1,1,0));
   if(empty($pzd_od) || empty($pzd_dla)) exit('B£¡D! Sprawd¼, czy pola OD i DLA s± wype³nione.');
   if(!empty($cfg['coml'])) $_SESSION['f3pzdt']=time()+$cfg['coml'];
   db_q('INSERT INTO '.$db_pre.'f3pzd VALUES ("","'.db_esc($pzd_od).'","'.db_esc($pzd_dla).'")');
   exit('Pozdrowienie zosta³o dodane.');
  }
  break;
 #Dodaj
 case 'd':
  if($_SESSION['f3pzdt']>time())
  {
   exit('Nie mo¿esz wys³aæ kolejnego pozdrowienia tak szybko po poprzednim. Spróbuj ponownie za chwilê.');
  }
  else
  {
   require('special.php');
   echo sHTML.'<form action="?mode=pozdrowka&amp;co=d1" method="post">';
   OpenBox('Dodaj pozdrowienie',2);
   echo '<tr>
    <td><b>1. Od:</b></td>
    <td><input maxlength="20" name="pzd_od" /></td>
   </tr>
   <tr>
    <td><b>2. Dla:</b></td>
    <td><input maxlength="20" name="pzd_dla" /></td>
   </tr>
   <tr>
    <td class="eth" colspan="2" align="center"><input type="button" value="Dodaj" onclick="if(pzd_od.value!=0 && pzd_dla.value!=0) { submit(); } else { alert(\'Wype³nij wszystkie pola.\'); }" /></td>
   </tr>';
   CloseBox();
   echo '</form>'.eHTML;
   exit;
  }
 #Usuñ
 case 'u':
  if($_GET['id'])
  {
   if(admit('F3PZD')) db_q('DELETE FROM '.$db_pre.'f3pzd WHERE ID='.$_GET['id']);
   exit('Pozdrowienie zosta³o usuniête.');
  }
  else
  {
   exit('Brak parametru ID!');
  }
 break;
 default: exit('B£¡D! Nieprawid³owy parametr akcji.');
}
exit;
?>
