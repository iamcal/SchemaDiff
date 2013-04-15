<?
	include('config.php');

	#
	# what schema are we viewing?
	#

	$default_key = array_shift(array_keys($schemas));
	$key = $default_key;
	if (in_array($_GET['k'], array_keys($schemas))) $key = $_GET['k'];
	$schema = $schemas[$key];


	#
	# turn both sides into files
	#

	$junk_files = array();

	$file_a = parse_file($schema, 'a');
	$file_b = parse_file($schema, 'b');


	function parse_file($schema, $suffix){

		if ($schema["file_$suffix"]){
			return $schema["file_$suffix"];
		}

		if ($schema["db_$suffix"]){
			$db = $schema["db_$suffix"];

			$dump = "mysqldump";
			if (isset($db[0])) $dump .= " -h$db[0]";
			if (isset($db[2])) $dump .= " -u$db[2]";
			if (isset($db[3])) $dump .= " -p$db[3]";
			$dump .= " $db[1] --no-data --no-create-db --compact 2>&1";

			$sed = "sed 's/ AUTO_INCREMENT=[0-9]\\+//'";

			# just let this cascade down...
			$schema["cmd_$suffix"] = "$dump | $sed";
		}

		if ($schema["cmd_$suffix"]){
			$temp = tempnam(sys_get_temp_dir(), 'schemadiff');
			$GLOBALS['junk_files'][] = $temp;
			$cmd = $schema["cmd_$suffix"];
			shell_exec("$cmd 1> $temp 2>&1");
			return $temp;
		}


		echo("don't know how to get content for this schema ($suffix):\n");
		print_r($schema);
		exit;
	}


	#
	# perform diff
	#

	$max_line = 100;
	$max2 = $max_line + $max_line + 2;

	$lines = shell_exec("diff -y -W$max2 --expand-tabs $file_a $file_b");

	foreach ($junk_files as $f){
		unlink($f);
	}


	#
	# parse diff
	#

	$lines = explode("\n", trim($lines));
	$pairs = array();
	foreach ($lines as $line){
		$a = trim(substr($line, 0, $max_line));
		$b = trim(substr($line, $max_line+3));
		$c = substr($line, $max_line, 1);

		if ((has_warnings($a) || has_warnings($b)) && $c == ' ') $c = '!';

		$pairs[] = array($a, $b, $c);

		if (preg_match('!;$!', $a) || preg_match('!;$!', $b)){
			$pairs[] = array('', '', $c);
		}
	}

	function has_warnings($str){
		foreach ($GLOBALS['warn_strings'] as $s) if (strpos($str, $s) !== false) return true;
		foreach ($GLOBALS['alert_strings'] as $s) if (strpos($str, $s) !== false) return true;
		return false;
	}


	#
	# collapse mode?
	#

	if ($max_context){
		$use_context = $max_context;
		$is_collapsed = !$_GET['show_all'];
	}else{
		$use_context = 5;
		$is_collapsed = !!$_GET['show_less'];
	}


	#
	# collapse same lines?
	#

	if ($is_collapsed){

		#
		# split into groups by type first
		#

		$groups = array();
		$last_grp = '?';
		$last_pairs = array();

		foreach ($pairs as $pair){
			if ($last_grp != $pair[2]){
				if (count($last_pairs)){
					$groups[] = array(
						'type' => $last_grp,
						'pairs' => $last_pairs,
					);
					$last_pairs = array();
				}
				$last_grp = $pair[2];
			}
			$last_pairs[] = $pair;
		}
		if ($last_grp == ' ' && !count($groups)){

			# no groups - it's all matched

			$groups[] = array(
				'type' => '=',
				'pairs' => array(
					array(
						"Content is equal",
						"Content is equal",
						"=",
					),
				),
			);

		}else{
			$groups[] = array(
				'type' => $last_grp,
				'pairs' => $last_pairs,
			);
		}


		$idx = 1;
		$max = count($groups);

		$pairs = array();
		foreach ($groups as $group){

			if ($group['type'] == ' '){

				if ($idx == 1){
					if (count($group['pairs']) <= $use_context+1){
						foreach ($group['pairs'] as $pair) $pairs[] = $pair;
					}else{
						$pairs[] = array('...', '...', '+');
						$snip = array_slice($group['pairs'], -$use_context, $use_context);
						foreach ($snip as $pair) $pairs[] = $pair;
					}
				}elseif ($idx == $max){
					if (count($group['pairs']) <= $use_context+1){
						foreach ($group['pairs'] as $pair) $pairs[] = $pair;
					}else{
						$snip = array_slice($group['pairs'], 0, $use_context);
						foreach ($snip as $pair) $pairs[] = $pair;
						$pairs[] = array('...', '...', '+');
					}
				}else{
					if (count($group['pairs']) <= $use_context+$use_context+1){
						foreach ($group['pairs'] as $pair) $pairs[] = $pair;
					}else{
						$pre = array_slice($group['pairs'], 0, $use_context);
						$post = array_slice($group['pairs'], -$use_context, $use_context);
						foreach ($pre as $pair) $pairs[] = $pair;
						$pairs[] = array('...', '...', '+');
						foreach ($post as $pair) $pairs[] = $pair;
					}
				}

			}else{
				foreach ($group['pairs'] as $pair) $pairs[] = $pair;
			}

			$idx++;
		}

		#echo '<pre>';
		#print_r($groups);
		#echo '</pre>';
		#exit;
	}


	# pre-escape these, since we'll be comparing them with escaped lines
	foreach ($GLOBALS['warn_strings' ] as $k => $v) $GLOBALS['warn_strings' ][$k] = HtmlSpecialChars($v);
	foreach ($GLOBALS['alert_strings'] as $k => $v) $GLOBALS['alert_strings'][$k] = HtmlSpecialChars($v);

	function display($x){
		if (strlen($x)){
			$str = HtmlSpecialChars($x);
			foreach ($GLOBALS['warn_strings'] as $s){
				$str = str_replace($s, '<span style="background-color: orange; color: white">'.$s.'</span>', $str);
			}
			foreach ($GLOBALS['alert_strings'] as $s){
				$str = str_replace($s, '<span style="background-color: red; color: white">'.$s.'</span>', $str);
			}
			return $str;
		}
		return '&nbsp;';
	}

	function get_line_class($x){
		if ($x == ' ') return 'ok';
		if ($x == '_') return 'ok';
		if ($x == '=') return 'match';
		if ($x == '|') return 'diff';
		if ($x == '<') return 'newleft';
		if ($x == '>') return 'newright';
		if ($x == '+') return 'collapse';
		return 'fail';
	}

	include('head.txt');
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
tr.diff td pre		{ border: 1px solid #F0F0BC; background-color: #FFFFCC; background-image: url(bullet_yellow.png); }
tr.newleft td.left pre	{ border: 1px solid #BB8888; background-color: #FFCCCC; background-image: url(bullet_delete.png); }
tr.newright td.right pre{ border: 1px solid #CDF0CD; background-color: #DDFFDD; background-image: url(bullet_add.png); }
tr.collapse td pre	{ border: 1px solid #BCBCF0; background-color: #CCCCFF; }
tr.match td pre		{ border: 1px solid #CDF0CD; background-color: #DDFFDD; }

.selnav {
	background-color: #f5f5f5;
	padding: 0.5em;
	margin-bottom: 0.5em;
}

</style>

<h1>SchemaDiff Comparison Tool</h1>

<?
	if (count($schemas) > 1){
?>
<div class="selnav">
	<div style="float: right">
<?
	list($base) = explode('?', $_SERVER['REQUEST_URI']);

	if ($max_context){
		$link = $is_collapsed ? "{$base}?show_all=1" : $base;
	}else{
		$link = $is_collapsed ? $base : "{$base}?show_less=1";
	}
	$label = $is_collapsed ? "Show All" : "Show Diffs";

	echo "<a href=\"{$link}\">{$label}</a>";
?>
	</div>
<?
	$bits = array();
	foreach ($schemas as $k => $v){

		$label = HtmlspecialChars($v['label']);
		$kk = HtmlspecialChars($k);

		if ($k == $key){
			$bits[] = "<b>$label</b>";
		}else{
			if ($k == $default_key){
				$bits[] = "<a href=\"./\">$label</a>";
			}else{
				$bits[] = "<a href=\"./?k=$kk\">$label</a>";
			}
		}
	}

	echo implode(' | ', $bits);
?>
</div>
<?
	}
?>

<table>
	<tr>
		<th><?=HtmlSpecialChars($schema['label_a'])?></th>
		<th><?=HtmlSpecialChars($schema['label_b'])?></th>
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

