<?php
if(iCMS!=1) exit;

#Pobierz
if(file_exists('./cache/poll_'.LANG.'.php')):
include('./cache/poll_'.LANG.'.php');

#G³osowa³ na...
$voted = isset($_COOKIE['voted']) ? explode('o',$_COOKIE['voted']) : array();

#Wyniki
if(in_array($poll['ID'],$voted) || $poll['ison']==2 || ($poll['ison']==3 && !UID))
{
	#Brak g³osów?
	if($poll['num']==0) { echo '<div class="pollQuestion">'.$lang['novotes'].'</div>'; } else {

	#Procenty
	$item = array();
	foreach($option as &$o)
	{
		$item[] = array(
			'num'  => $o[2],
			'label' => $o[1],
			'color' => $o[3],
			'percent' => round($o[2] / $poll['num'] * 100 ,$cfg['pollRound'])
		);
	}
}
	#Styl
	include './mod/polls/little.php'; //Na razie domyœlny styl
	unset($poll,$item);
} else {

#Formularz do g³osowania
echo '<form action="vote.php" id="poll" method="post">
<div class="pollQuestion">'.$poll['q'].'</div><div class="pollOptions">';

$i=0;
foreach($option as $o)
{
	echo '<label><input id="o_'.++$i.'" name="vote'.(($poll['type']==2)?'['.$o[0].']" type="checkbox" ':'" value="'.$o[0].'" type="radio"').' /> '.$o[1].'</label><br />';
}

echo '</div><div class="pollSubmit">
	<input type="submit" value="OK" name="poll" class="pollVote" />'.($poll['num'] ?
	'<a href="'.url('poll/'.$poll['ID']).'" class="pollView">'.$lang['results'].'</a>' : '') .
'</div>
</form>';

unset($poll,$option,$voted,$pollproc); }

else:
	echo '<div style="text-align: center; margin: 5px 0">'.(admit('Q') ?
	'<a href="'.url('editPoll','ref','admin').'" class="add">'.$lang['add'].'</a>' : $lang['lack']).'</div>';
endif;