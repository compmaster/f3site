<?php
if(iCMSa!=1 || !admit('DB')) exit;
require LANG_DIR.'admAll.php';

#Page title
$view->title = $lang['dbcopy'];

#Supported databases
switch($db_db)
{
	case 'mysql':
		$type = 'mysql';
		$show = 'SHOW TABLES';
		break;
	case 'sqlite':
		$type = 'sqlite';
		$show = 'SELECT name FROM sqlite_master WHERE type="table" ORDER BY name';
		break;
	default:
		$view->info('Cannot parse database type.'); return 1;
}

#Action: tables
if(isset($_POST['tab']))
{
	$n="\n";
	@set_time_limit(50);

	#Use gzip
	if(isset($_POST['gz']))
	{
		header('Content-type: application/x-gzip'); $ex='.sql.gz';
	}
	else
	{
		header('Content-type: text/plain'); $ex='.sql';
	}
	header('Content-Disposition: attachment; filename='.
		str_replace( array('?','*',':','\\','/','<','>','|','"'),'',strftime('%Y-%m-%d')).$ex);

	#Start gzip
	if(isset($_POST['gz'])) ob_start('ob_gzhandler'); else ob_start();

	#Add gravis to names (MySQL)
	if($type === 'mysql') $db->exec('SET SQL_QUOTE_SHOW_CREATE=1');

	#Header - comments
	echo '#'.$cfg['title'].' - Data Backup ('.strftime('%Y-%m-%d').')'.$n;
	echo '#Database: '.$db_d.$n;
	echo '#----------'.$n.$n;

	#Tables
	foreach($_POST['tab'] as $tab)
	{
		#Optimize first
		if($type === 'sqlite')
		{
			$db->exec('VACUUM '.$tab);
		}
		else
		{
			$db->query('OPTIMIZE LOCAL TABLE '.$tab)->fetchAll(3);
		}

		#Table creation
		if(isset($_POST['create']))
		{
			echo '#Creating table '.$tab.$n;

			#Delete first
			if(isset($_POST['del'])) echo 'DROP TABLE IF EXISTS `'.$tab.'`;'.$n;

			#Get SQL code
			if($type === 'sqlite')
			{
				echo $db->query('SELECT sql FROM sqlite_master WHERE name="'.$tab.'"') -> fetchColumn() .';'.$n.$n;
			}
			else
			{
				echo $db->query('SHOW CREATE TABLE '.$tab)->fetchColumn(1).';'.$n.$n;
			}

			#Flush output
			ob_flush();
		}

		#Get table data
		$all = $db->query('SELECT * FROM '.$tab);
		$all -> setFetchMode(3);
		echo '#Table data for '.$tab.$n;

		#Field values
		foreach($all as $row)
		{
			echo 'INSERT INTO `'.$tab.'` VALUES ('.join(',',array_map(array($db,'quote'),$row)).');'.$n;
			ob_flush(); //Free memory
		}
		unset($ile,$all,$row);
		echo $n;
		ob_flush(); //Free memory
	}

	#Finish job
	ob_end_flush();
	exit;
}

#Get tables
$list = $db->query($show) -> fetchAll(7); //COLUMN
$tabs = '';

foreach($list as $tab)
{
	$tabs .= '<option>'.$tab.'</option>';
}

#Template
$view->add('db', array(
	'tables' => $tabs,
	'gz'     => function_exists('gzopen')
));
