<?php if(iCMS!=1) exit; ?>

<form action="http://www.google.com/search">
<div align="center" style="padding: 2px; line-height: 23px">
	<input name="as_q" style="width: 90%; margin: 1px; height: 15px" />
	<input type="hidden" name="as_sitesearch" value="http://<?php echo $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']) ?>" />
 <input type="submit" value="<?php echo $lang['search'] ?>" />
 <br />
 <small>Powered by <a href="http://www.google.com">Google</a>.</small>
</div>
</form>