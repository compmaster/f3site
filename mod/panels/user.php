<?php
if(iCMS!=1) exit;

#Zalogowany
if(UID)
{
	echo
	sprintf($lang['uwlogd'],'<a href="'.url('user').'">'.$user['login'].'</a>').'<ul>'.

	(IS_EDITOR ?
	'<li><a href="'.url('edit').'">'.$lang['mantxt'].'</a></li>' : '').

	(IS_ADMIN ?
	'<li><a href="admin">'.$lang['cpanel'].'</a></li>':'').

	(isset($cfg['pmOn'])?
	'<li><a href="'.url('pms').'"'.(($user['pms']>0)?' class="newpms"><b>'.$lang['pms'].' ('.$user['pms'].')</b>':'>'.$lang['pms']).'</a></li>':'').

	'<li><a href="'.url('account').'">'.$lang['upanel'].'</a></li><li><a href="login.php?logout">'.$lang['logout'].'</a></li></ul>';
} else {

?><form action="login.php" method="post"><div style="text-align: center">
	Login:
	<input name="u" style="height: 15px; width: 93%" />
	<?php echo $lang['pass'] ?>:
	<input name="p" type="password" style="height: 15px; width: 93%" />

	<div style="margin: 5px 0px">
	<input type="checkbox" name="auto" /> <?php echo $lang['remlog'] ?>
	</div>

	<input type="submit" value="<?php echo $lang['logme'] ?>" />
	<input type="submit" value="<?php echo $lang['regme'] ?>" name="reg" />
</div></form>
<?php }