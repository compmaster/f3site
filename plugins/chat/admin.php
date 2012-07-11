<?php
if(iCMSa!=1) exit;

$view->title = 'Chat';
$view->dir = './plugins/chat/';
$view->cache = './cache/chat/';
$view->add('admin', array('cfg' => &$cfg));
