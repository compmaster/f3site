<?php #Sidebars generator
function RenderMenu(PDO $db = null)
{
	if(!$db) global $db;
	if(!is_writable('cache')) throw new Exception('ERROR: You must chmod /cache/ directory to 777');

	#Read blocks - ASSOC
	$block = $db->query('SELECT * FROM '.PRE.'menu WHERE disp!=2 ORDER BY seq')->fetchAll(2);

	#Read links - NUM
	$items = $db->query('SELECT menu,text,type,url,nw FROM '.PRE.'mitems ORDER BY seq')->fetchAll(3);

	#Languages
	foreach(scandir('lang') as $dir)
	{
		if($dir[0]=='.' || !is_dir('lang/'.$dir)) continue;
		$out = array(null,'','');

		foreach($block as &$b)
		{
			$page = $b['menu'];
			if($b['disp']=='3')
			{
				$out[$page] .= '<?php if(IS_ADMIN){?>';
			}
			elseif($b['disp']!='1' && $b['disp']!=$dir) continue;
			$out[$page] .= '<div class="mh"'.($b['img'] ? ' style="background: url('.$b['img'].') no-repeat bottom right"':'').'>'.$b['text'].'</div><div class="menu">';

			#Tekst, plik, linki
			switch($b['type'])
			{
				case 1: $out[$page] .= $b['value']; break;
				case 2: $out[$page] .= '<?php include \''.str_replace(array('\'','\\'),array('\\\'','\\\\'),$b['value']).'\'?>'; break;
				case 4: $out[$page] .= s($b['value']); break;
				case 5: $out[$page] .= s('mod/panels/cats.php'); break;
				case 6: $out[$page] .= s('mod/panels/new.php'); break;
				case 7: $out[$page] .= s('mod/panels/online.php'); break;
				case 8: $out[$page] .= s('mod/panels/pages.php'); break;
				case 9: $out[$page] .= s('mod/panels/poll.php'); break;
				case 10: $out[$page].= s('mod/panels/user.php'); break;
				default:

				$links = '';
				foreach($items as &$i)
				{
					if($i[0] == $b['ID'])
					{
						switch($i[2])
						{
							case 1: $url = '.'; break;
							case 3: $url = $i[3]; break;
							case 4: $url = strpos($i[3], 'www.')===0 ? 'http://'.$i[3] : $i[3]; break;
							case 6: $url = url('page/'.$i[3]); break;
							default: $url = url($i[3]);
						}
						$links .= '<li><a href="'.$url.'"'.($i[4]?' target="_blank"':'').'>'.$i[1].'</a></li>';
					}
				}
				if($links) $out[$page].= '<ul>'.$links.'</ul>';
			}
			$out[$page].='</div>' . ($b['disp']=='3' ? '<?php } ?>' : '');
		}

		#Ca³oœæ
		$out = '<?php function newnav($MID) { global $cfg,$lang,$db,$user; if($MID==1) {?>'.$out[1].'<?php } else {?>'.$out[2].'<?php } } ?>';

		#Redukuj otwarcia PHP
		$out = str_replace('?><?php', '', $out);

		#Zapisz
		file_put_contents('./cache/menu'.$dir.'.php', $out, 2); //2 = LOCK_EX
	}
}
function s($x)
{
	$got = file_get_contents($x);
	if(substr_count($got,'<?') > substr_count($got,'?>')) $got .= ' ?>';
	return $got;
}