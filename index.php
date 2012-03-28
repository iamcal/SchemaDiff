<?
	$current = 'deploy';
	$subnav = 'schema';
	include('head.txt');

	$dbs = array(
		'main'		=> array('dev-db1:speck_dev_main',	'dbmain1:ts_main'),
		'static'	=> array('dev-db1:speck_dev_static',	'dbmain1:ts_static'),
		'world'		=> array('dev-db1:speck_world',		'dev-db1:speck_world_prod'),
		'users-dev'	=> array('dev-db1:speck_dev_user1',	'dev-db1:speck_dev_user2'),
		'users-prod1'	=> array('dev-db1:speck_dev_user1',	'dbmain1:ts_user1'),
		'users-prod2'	=> array('dev-db1:speck_dev_user1',	'dbmain1:ts_user2'),
		'publisher'	=> array('dev-db1:speck_publisher',	'dev-db1:speck_publisher'),
	);

	$default_key = 'main';
	$key = $default_key;
	if (in_array($_GET[k], array_keys($dbs))) $key = $_GET[k];
	$db = $dbs[$key];


	$time = time();
	$temp1 = "/tmp/schema1_$time";
	$temp2 = "/tmp/schema2_$time";

	$max_line = 100;
	$max2 = $max_line + $max_line + 2;

	list($h1, $n1) = explode(':', $db[0]);
	list($h2, $n2) = explode(':', $db[1]);

	shell_exec("mysqldump -h$h1 -uroot $n1 --no-data --no-create-db --compact | sed 's/ AUTO_INCREMENT=[0-9]\+//' > $temp1");
	shell_exec("mysqldump -h$h2 -uroot $n2 --no-data --no-create-db --compact | sed 's/ AUTO_INCREMENT=[0-9]\+//' > $temp2");
	$lines = shell_exec("diff -y -W$max2 --expand-tabs $temp1 $temp2");
	shell_exec("rm $temp1 $temp2");

	$lines = explode("\n", trim($lines));
	$pairs = array();
	foreach ($lines as $line){
		$a = trim(substr($line, 0, $max_line));
		$b = trim(substr($line, $max_line+3));
		$c = substr($line, $max_line, 1);

		$pairs[] = array($a, $b, $c);

		if (preg_match('!;$!', $a)){
			$pairs[] = array('', '', '_');
		}
	}

	function display($x){
		if (strlen($x)){
			$str = HtmlSpecialChars($x);
			$str = str_replace('latin1', '<span style="background-color: red; color: white">latin1</span>', $str);
			$str = str_replace('MyISAM', '<span style="background-color: orange; color: white">MyISAM</span>', $str);
			$str = str_replace('character set', '<span style="background-color: orange; color: white">character set</span>', $str);
			return $str;
		}
		return '&nbsp;';
	}

	function get_line_class($x){
		if ($x == ' ') return 'ok';
		if ($x == '_') return 'ok';
		if ($x == '|') return 'diff';
		if ($x == '<') return 'newleft';
		if ($x == '>') return 'newright';
		return 'fail';
	}

?>
<style>

table { width: 100%; border-collapse:collapse; }
td { font-family:monospace; padding: 2px 2px 0; }
td pre {
	display: block;
	height: 100%;
	padding-left: 22px; 
	background-position:2px 50%;
	background-repeat:no-repeat;
	font-size:13px;
	line-height:1.3em;
}
th {
-moz-background-clip:border;
-moz-background-inline-policy:continuous;
-moz-background-origin:padding;
background:#4D4D4D none repeat scroll 0 0;
border-bottom:2px solid #000000;
border-right:1px solid #808080;
border-top:2px solid #808080;
color:#FFFFFF;
margin:3px 2px;
padding:5px 5px 5px 10px;
text-align:center;
}

pre {
margin: 0;
white-space:pre-wrap;
word-wrap:break-word;
}

tr td pre		{ border: 1px solid #F0F0F0; background-color: #F8F8F8; }
tr.diff td pre		{ border: 1px solid #F0F0BC; background-color: #FFFFCC; background-image: url(images/bullet_yellow.png); }
tr.newleft td.left pre	{ border: 1px solid #BB8888; background-color: #FFCCCC; background-image: url(images/bullet_delete.png); }
tr.newright td.right pre{ border: 1px solid #CDF0CD; background-color: #DDFFDD; background-image: url(images/bullet_add.png); }

.selnav {
	background-color: #f5f5f5;
	padding: 0.5em;
}

</style>

<h1>DB Schema Comparison</h1>

<p class="selnav">
<?
	$bits = array();
	foreach ($dbs as $k => $v){

		if ($k == $key){
			$bits[] = "<b>$k</b>";
		}else{
			if ($k == $default_key){
				$bits[] = "<a href=\"schema.php\">$k</a>";
			}else{
				$bits[] = "<a href=\"schema.php?k=$k\">$k</a>";
			}
		}
	}

	echo implode(' | ', $bits);
?>
</p>

<table>
	<tr>
		<th>Dev (<?=$db[0]?>)</th>
		<th>Prod (<?=$db[1]?>)</th>
	</tr>
<? foreach ($pairs as $pair){ ?>
	<tr class="<?=get_line_class($pair[2])?>">
		<td class="left"><pre><?=display($pair[0])?></pre></td>
		<td class="right"><pre><?=display($pair[1])?></pre></td>
	</tr>
<? } ?>
</table>

<?
	include('foot.txt');
?>
