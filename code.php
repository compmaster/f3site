<?php
define('iCMS',1);
require 'kernel.php';
if(!isset($cfg['captcha']) || $cfg['captcha']!=1) exit;

#Jako PNG
header('Content-type: image/png');

#Losujemy liczbê
$_SESSION['code'] = '';
for($i=0; $i<7; $i++) { $_SESSION['code'] .= chr(mt_rand(33,126)); }

#Generuj obrazek
$img = imagecreate(80,25);
imagecolorallocate($img,250,250,245);
$color = imagecolorallocate($img,mt_rand(1,255),50,80);
imagestring($img,mt_rand(4,6),mt_rand(1,15),mt_rand(1,9),$_SESSION['code'],$color);
imagepng($img);
imagedestroy($img);
exit;