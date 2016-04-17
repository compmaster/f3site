<?php if(iCMS!=1 || !IS_ADMIN) return;
require LANG_DIR.'adm.php';
require LANG_DIR.'admCfg.php';

$cfg['prvKey'] = $cfg['pubKey'] = $cfg['sbKey'] = '';

$view->title = 'Ustawienia TODO:lang';
$view->add('settings', array('cfg'=>$cfg));

