<?php
class BBCode
{
	private static
		$bb1 = array(
			'[b]','[i]','[u]','[sup]','[sub]',
			'[code]','[quote]',
			'[big]','[small]',
			'[center]','[right]'),
		$bb2 = array(
			'[/b]','[/i]','[/u]','[/sup]','[/sub]',
			'[/code]','[/quote]',
			'[/big]','[/small]',
			'[/center]','[/right]'),
		$html1 = array(
			'<b>', '<i>', '<u>', '<sup>', '<sub>',
			'<code>',
			'<blockquote>',
			'<big>',
			'<small>',
			'<center>',
			'<div align="right">'),
		$html2 = array(
			'</b>', '</i>', '</u>', '</sup>', '</sub>',
			'</code>', '</blockquote>',
			'</big>', '</small>',
			'</center>', '</div>');
}

function BBCode($x, $exc=false)
{
	global $lang,$cfg;
	static $bbc;
	if(!isset($cfg['bbcode'])) return $x;
	if(!$bbc)
	{
		$bbc[0] = array(
			'[b]','[i]','[u]','[sup]','[sub]',
			'[code]','[quote]',
			'[big]','[small]',
			'[center]','[right]'
		);
		$bbc[1] = array(
			'[/b]','[/i]','[/u]','[/sup]','[/sub]',
			'[/code]','[/quote]',
			'[/big]','[/small]',
			'[/center]','[/right]'
		);
		$bbc[2] = array(
			'<b>', '<i>', '<u>', '<sup>', '<sub>',
			'<code>',
			'<blockquote>',
			'<big>',
			'<small>',
			'<center>',
			'<div align="right">'
		);
		$bbc[3] = array(
			'</b>', '</i>', '</u>', '</sup>', '</sub>',
			'</code>', '</blockquote>',
			'</big>', '</small>',
			'</center>', '</div>'
		);
	}

	#Proste znaczniki
	$t = str_replace($bbc[0], $bbc[2], $x, $c1);
	$t = str_replace($bbc[1], $bbc[3], $t, $c2);

	#Znaczniki niedomknięte?
	if($c1 != $c2)
	{
		if($exc) throw new Exception(); else return $x;
	}

	#Kolor, e-mail
	$t = preg_replace(
		array(
			'@\[color=([A-Za-z0-9#].*?)\](.*?)\[/color\]@si',
			'#\[email\]([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#i',
			'#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i',
			'#==(.*?)==([\s]*)#i',
		), array(
			'<span style="color:\\1">\\2</span>',
			'<a href="mailto:\\1@\\2">\\1@\\2</a>',
			'\\1<a href="mailto:\\2@\\3">\\2@\\3</a>',
			'<h3>\\1</h3>'
		), $t);

	#Linki
	$t = preg_replace_callback(
		array(
			'#\[url]([^\]].*?)\[/url\]#i',
			'#\[url=([^\]]+)\](.*?)\[/url\]#i',
			'#[\n ]+(www\.[a-z0-9\-]+\.[a-z0-9\-.\~,\?!%\*_\#:;~\\&$@\/=\+]*)#i',
			'#[\n ]+(http+s?://[a-z0-9\-]+\.[a-z0-9\-.\~,\?!%\*_\#:;~\\&$@\/=\+]*)#i',
		), 'bburl', $t);

	#Obrazy i wideo
	$t = preg_replace_callback(array(
		'#\[(video)\](https?://[^\s<>"].*?)\[/video\]#i',
		'#\[(img)\](https?://[^\s<>"].*?)\[/img\]#i'), 'bbimg', $t);

	#Usuń JS i zwróć gotowy tekst
	return preg_replace_callback('#\<a(.*?)\>#si', 'bbcode_js', $t);
}

#Zabezpiecz obrazy
function bbimg($t)
{
	if(stripos($t[2],URL)===false && strpos($t[2],'.php')===false)
	{
		if($t[1] == 'video')
		{
			return '<video controls src="'.$t[2].'"><a href="'.$t[2].'">video</a></video>';
		}
		return '<img src="'.$t[2].'" alt="Image" style="max-width: 100%">';
	}
	return 'bad '.$t[1];
}

#Zabezpiecz URL i skróć link
function bburl($t)
{
	$link = trim(str_replace(' ', '%20', $t[1]), '.,');
	if(strpos($link, '"') !== false) return '';
	if(strpos($link, 'www.') === 0) $link = 'http://' . $link;
	if(isset($t[2]))
	{
		return '<a href="'.$link.'">'.$t[2].'</a>';
	}
	else
	{
		$text = isset($t[1][31]) ? substr($t[1], 0, 21) . '...' . substr($t[1], -8) : $t[1];
		return ' <a href="'.$link.'">'.$text.'</a>';
	}
}

#Anty JS
function bbcode_js($t)
{
	return str_ireplace( array('javascript:','vbscript:'), array('java_script','vb_script'), $t[0]);
}