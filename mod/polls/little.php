<?php
if(iCMS!=1) exit;
echo '<center>'.$poll['q'].'</center>';
?>
<table align="center" cellspacing="0" cellpadding="0" style="padding: 5px 1px; width: 100%">
<tbody>
<?php
#Generowanie
foreach($item as &$o)
{
	echo '<tr>
  <td>'.$o['label'].'</td>
	<td>&nbsp;<b>'.$o['num'].'</b></td>
</tr>
<tr>
  <td><div style="width: '.$o['percent'].'%;background-color:'.$o['color'].'" class="pollstrip"></div></td>
  <td style="width: 20px">&nbsp;'.$o['percent'].'%</td>
</tr>';
}
?>
</tbody>
</table>
<div align="center" style="padding: 2px">
	<a href="<?php echo url('poll/'.$poll['ID'])?>" class="pollMore"><?php echo $lang['results'] ?></a>
	<a href="<?php echo url('polls')?>" class="pollArchive"><?php echo $lang['archive'] ?></a>
</div>